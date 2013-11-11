<?php

/**
 * Wrapper arounds streams, pipes, and other things that have basic read/write
 * I/O characteristics.
 *
 * Channels include buffering, so you can do fire-and-forget writes and reads
 * without worrying about network/pipe buffers. Use @{method:read} and
 * @{method:write} to read and write.
 *
 * Channels are nonblocking and provide a select()-oriented interface so you
 * can reasonably write server-like and daemon-like things with them. Use
 * @{method:waitForAny} to select channels.
 *
 * Channel operations other than @{method:update} generally operate on buffers.
 * Writes and reads affect buffers, while @{method:update} flushes output
 * buffers and fills input buffers.
 *
 * Channels are either "open" or "closed". You can detect that a channel has
 * closed by calling @{method:isOpen} or examining the return value of
 * @{method:update}.
 *
 * NOTE: Channels are new (as of June 2012) and subject to interface changes.
 *
 * @task  io      Reading and Writing
 * @task  wait    Waiting for Activity
 * @task  update  Responding to Activity
 * @task  impl    Channel Implementation
 *
 * @group channel
 */
abstract class PhutilChannel {

  private $ibuf = '';
  private $obuf = '';
  private $name;


/* -(  Reading and Writing  )------------------------------------------------ */


  /**
   * Read from the channel. A channel defines the format of data that is read
   * from it, so this method may return strings, objects, or anything else.
   *
   * The default implementation returns bytes.
   *
   * @return wild  Data from the channel, normally bytes.
   *
   * @task io
   */
  public function read() {
    $result = $this->ibuf;
    $this->ibuf = '';
    return $result;
  }


  /**
   * Write to the channel. A channel defines what data format it accepts,
   * so this method may take strings, objects, or anything else.
   *
   * The default implementation accepts bytes.
   *
   * @param   wild  Data to write to the channel, normally bytes.
   * @return  this
   *
   * @task io
   */
  public function write($bytes) {
    if (!is_scalar($bytes)) {
      throw new Exception("PhutilChannel->write() may only write strings!");
    }

    $this->obuf .= $bytes;
    return $this;
  }


/* -(  Waiting for Activity  )----------------------------------------------- */


  /**
   * Wait (using select()) for activity on any channel. This method blocks until
   * some channel is ready to be updated.
   *
   * It does not provide a way to determine which channels are ready to be
   * updated. The expectation is that you'll just update every channel. This
   * might change eventually.
   *
   * Available options are:
   *
   *  - 'read' (list<stream>) Additional streams to select for read.
   *  - 'write' (list<stream>) Additional streams to select for write.
   *  - 'except' (list<stream>) Additional streams to select for except.
   *  - 'timeout' (float) Select timeout, defaults to 1.
   *
   * NOTE: Extra streams must be //streams//, not //sockets//, because this
   * method uses `stream_select()`, not `socket_select()`.
   *
   * @param   list<PhutilChannel>   A list of channels to wait for.
   * @param   dict                  Options, see above.
   * @return  void
   *
   * @task wait
   */
  public static function waitForAny(array $channels, array $options = array()) {
    assert_instances_of($channels, 'PhutilChannel');

    $read   = idx($options, 'read',     array());
    $write  = idx($options, 'write',    array());
    $except = idx($options, 'except',   array());
    $wait   = idx($options, 'timeout',  1);

    foreach ($channels as $channel) {

      // If any of the channels have data in read buffers, return immediately.
      // If we don't, we risk running select() on a bunch of sockets which won't
      // become readable because the data the application expects is already
      // in a read buffer.

      if (!$channel->isReadBufferEmpty()) {
        return;
      }

      $r_sockets = $channel->getReadSockets();
      $w_sockets = $channel->getWriteSockets();

      // If any channel has no read sockets and no write sockets, assume it
      // isn't selectable and return immediately (effectively degrading to a
      // busy wait).

      if (!$r_sockets && !$w_sockets) {
        return;
      }

      foreach ($r_sockets as $socket) {
        $read[] = $socket;
        $except[] = $socket;
      }

      foreach ($w_sockets as $socket) {
        $write[] = $socket;
        $except[] = $socket;
      }
    }

    if (!$read && !$write && !$except) {
      return false;
    }

    $wait_sec = (int)$wait;
    $wait_usec = 1000000 * ($wait - $wait_sec);

    @stream_select($read, $write, $except, $wait_sec, $wait_usec);
  }


/* -(  Responding to Activity  )--------------------------------------------- */


  /**
   * Updates the channel, filling input buffers and flushing output buffers.
   * Returns false if the channel has closed.
   *
   * @return bool True if the channel is still open.
   *
   * @task update
   */
  public function update() {
    while (true) {
      $in = $this->readBytes();
      if (!strlen($in)) {
        // Reading is blocked for now.
        break;
      }
      $this->ibuf .= $in;
    }

    while (strlen($this->obuf)) {
      $len = $this->writeBytes($this->obuf);
      if (!$len) {
        // Writing is blocked for now.
        break;
      }
      $this->obuf = substr($this->obuf, $len);
    }

    return $this->isOpen();
  }


/* -(  Channel Implementation  )--------------------------------------------- */


  /**
   * Set a channel name. This is primarily intended to allow you to debug
   * channel code more easily, by naming channels something meaningful.
   *
   * @param string Channel name.
   * @return this
   *
   * @task impl
   */
  public function setName($name) {
    $this->name = $name;
    return $this;
  }


  /**
   * Get the channel name, as set by @{method:setName}.
   *
   * @return string Name of the channel.
   *
   * @task impl
   */
  public function getName() {
    return coalesce($this->name, get_class($this));
  }


  /**
   * Test if the channel is open: active, can be read from and written to, etc.
   *
   * @return bool True if the channel is open.
   *
   * @task impl
   */
  abstract public function isOpen();


  /**
   * Close the channel for writing.
   *
   * @return void
   * @task impl
   */
  abstract public function closeWriteChannel();

  /**
   * Test if the channel is open for reading.
   *
   * @return bool True if the channel is open for reading.
   *
   * @task impl
   */
  public function isOpenForReading() {
    return $this->isOpen();
  }


  /**
   * Test if the channel is open for writing.
   *
   * @return bool True if the channel is open for writing.
   *
   * @task impl
   */
  public function isOpenForWriting() {
    return $this->isOpen();
  }


  /**
   * Read from the channel's underlying I/O.
   *
   * @return string Bytes, if available.
   *
   * @task impl
   */
  abstract protected function readBytes();


  /**
   * Write to the channel's underlying I/O.
   *
   * @param string Bytes to write.
   * @return int Number of bytes written.
   *
   * @task impl
   */
  abstract protected function writeBytes($bytes);


  /**
   * Get sockets to select for reading.
   *
   * @return list<stream> Read sockets.
   *
   * @task impl
   */
  protected function getReadSockets() {
    return array();
  }


  /**
   * Get sockets to select for writing.
   *
   * @return list<stream> Write sockets.
   *
   * @task impl
   */
  protected function getWriteSockets() {
    return array();
  }


  /**
   * Test state of the read buffer.
   *
   * @return bool True if the read buffer is empty.
   *
   * @task impl
   */
  protected function isReadBufferEmpty() {
    return (strlen($this->ibuf) == 0);
  }


  /**
   * Test state of the write buffer.
   *
   * @return bool True if the write buffer is empty.
   *
   * @task impl
   */
  protected function isWriteBufferEmpty() {
    return (strlen($this->obuf) == 0);
  }


  /**
   * Wait for any buffered writes to complete. This is a blocking call. When
   * the call returns, the write buffer will be empty.
   *
   * @task impl
   */
  public function flush() {
    while (!$this->isWriteBufferEmpty()) {
      self::waitForAny(array($this));
      if (!$this->update()) {
        throw new Exception("Channel closed while flushing output!");
      }
    }
    return $this;
  }

}
