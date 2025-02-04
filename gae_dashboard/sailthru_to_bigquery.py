#!/usr/bin/env python

"""Send blast data from Sailthru to bigquery

This script exports the campaign and blasts data from Sailthru to
bigquery.  This is set-up to run daily in the internal-services
cluster using a cron job that will fetch the campaigns table from
forever (assume January 1, 2010) to now. Also, it gets the list of
blast IDs of the blasts that were fired in the past 7 days and
generates tables for each of those blasts. The tables are generated in
bigquery in the 'sailthru_blasts' dataset. The overall summary of all
campaigns is the 'campaigns' table the the individual blasts' tables
are called 'blast_<blast iD>'.

To run this script, use the following (example):
Get data for individual blast:
    python gae_dashboard/sailthru_to_bigquery.py blast --blast_id 8260012
Get the overall campaign data:
    python gae_dashboard/sailthru_to_bigquery.py campaigns --status 'sent'
    --start_date 'January 1 2017' --end_date 'January 13 2017'
To directly run the script that runs on cron use:
    ./sailthru_to_bigquery.py export

If you want verbose to be true, you can select that as well.

Since the campaign data is not in the same format at all times,
use the below scripts to get the unique keys at different levels
from the json file of the data:
To get the top level: `cat ~/Downloads/file.json | while read -r line;
                    do echo "$line" | jq -r 'keys | .[]'; done | sort -u`
To get the 3rd level (below 'stats' and 'total'):
                `cat ~/Downloads/file.json | while read -r line;
                do echo "$line" | jq -r '.stats.total | keys | .[]';
                done | sort -u`

TODO(Ragini): Create a script to get the schema of the table that'll get
created in bigquery. There might be a possible problem in the future due to
the fact that the schema of data generated by sailthru is not always the same.

"""

import argparse
import contextlib
import csv
import datetime
import json
import multiprocessing.dummy
import os
import re
import shutil
import tempfile
import threading
import time
import urllib

import bq_util
import pytz
from sailthru import sailthru_client

try:
    import sailthru_secrets
    _SAILTHRU_KEY = sailthru_secrets.sailthru_key
    _SAILTHRU_SECRET = sailthru_secrets.sailthru_secret
except ImportError:
    # If you don't want to make a sailthru_secrets file, you can just
    # export these via envvars.
    _SAILTHRU_KEY = os.getenv("SAILTHRU_KEY")
    _SAILTHRU_SECRET = os.getenv("SAILTHRU_SECRET")


def _get_client():
    """Retrieve the Sailthru API Client.

    Arguments:
        timeout: How many seconds the client should wait for an API
            call to return before aborting the request.
    """
    return sailthru_client.SailthruClient(_SAILTHRU_KEY, _SAILTHRU_SECRET,
                                          timeout=40, retries=2)


class SailthruAPIException(Exception):
    """Exception for logging problems connecting to the Sailthru API."""
    def __init__(self, response):
        self.response = response
        super(SailthruAPIException, self).__init__(self.log_message())

    def log_message(self):
        stapi_error = self.response.get_error()
        return u"Sailthru API returned {}, error code {}: {}".format(
            self.response.get_status_code(),
            stapi_error.get_error_code(),
            stapi_error.get_message())


def _post(arg, **kwargs):
    if kwargs.get('verbose'):
        print "Calling sailthru's blast_query for blast_id = %s" % kwargs.get(
            'blast_id')
    client = _get_client()
    response = client.api_post(arg, kwargs)
    if not response.is_ok():
        raise SailthruAPIException(response)
    return response


def _get(arg, **kwargs):
    client = _get_client()
    response = client.api_get(arg, kwargs)
    if not response.is_ok():
        raise SailthruAPIException(response)
    return response


_sailthru_timezone_utc_offset = None


def _get_sailthru_timezone_utc_offset():
    global _sailthru_timezone_utc_offset

    if not _sailthru_timezone_utc_offset:
        tz_string = _get('settings').get_body().get('timezone')
        # Keep in mind that this returns a different offset during DST.
        tz_offset = datetime.datetime.now(
            pytz.timezone(tz_string)).strftime('%z')
        # We have a string like -0300 or +0530, and we want a string
        # like -03:00 or +05:30.
        assert re.match('^[+-]\d{4}$', tz_offset)
        _sailthru_timezone_utc_offset = "%s:%s" % (
            tz_offset[0:3], tz_offset[3:5])

    return _sailthru_timezone_utc_offset


# Toby only has one core at time of writing (July 2017), so we don't want to
# do CPU-bound tasks in parallel.
_CPU_LOCK = threading.Lock()


def _send_blast_details_to_bq(blast_id, temp_file,
                              verbose, dry_run, keep_temp):
    """Export blast data to BigQuery.

    Arguments:
      blast_id: ID of the blast to fetch data for.
      temp_file: A file to store the data, to be used by 'bq load'.
      verbose: True if you want to show debug messages, else False.
      dry_run: True if we should skip writing to bq, and instead log what
               would have happened. For normal behavior, set False.
      keep_temp: True if we should keep the temp_file that we write.
    """

    # Map associating blast_query response column names with separators.
    #
    # The blast_query response contains columns that contain multiple items.
    # These columns have different separators.
    #
    # The absence of a column name in this associative array indicates that
    # cells under this column each only contain a single item.
    blast_report_list_column_seperators = {
        "first_ten_clicks": " ",
        "first_ten_clicks_time": "|"
    }

    # Map associating Sailthru blast report header names with bq column names.
    blast_report_header_corrections = {
        "email hash": "email_hash",  # Spaces are annoying in SQL!
        "extid": "kaid",  # Might as well be precise.
    }

    # Fields for which we should append timezone information.
    blast_report_timestamp_columns = None
    with open(os.path.join(os.path.dirname(__file__),
                           "sailthru_blast_export_schema.json")) as f:
        blast_report_timestamp_columns = {
            column.get("name"): True for column in json.load(f)
            if column.get("type") == "TIMESTAMP"}

    tz_utc_offset = _get_sailthru_timezone_utc_offset()

    response_1 = _post('job', job="blast_query", blast_id=blast_id,
                       verbose=verbose)

    job_id = response_1.get_body().get('job_id')

    if job_id is None:
        print ("WARNING: For the blast_query job with blast_id = %s, "
               "the job_id returned from Sailthru's job=blast_query is "
               "None" % blast_id)
        return

    if verbose:
        print ("For the blast_query job with blast_id = %s, calling "
               "sailthru's job status API for job_id = %s" % (blast_id,
                                                              job_id))
    response_2 = _get('job', job_id=job_id)

    while response_2.get_body().get('status') != "completed":
        if verbose:
            print ("For the blast_query job with blast_id = %s, polled "
                   "sailthru's job status API for job_id = %s " %
                   (blast_id, job_id))
            print "Will poll again in 5 seconds."
        time.sleep(5)
        response_2 = _get('job', job_id=job_id)
        if response_2.get_body().get('status') == "expired":
            raise SailthruAPIException(response_2)

    filename_url = response_2.get_body().get('export_url')

    if verbose:
        print ("For the blast_query job with blast_id = %s, creating a jsonl "
               "file from the sailthru data" % blast_id)

    with _CPU_LOCK:
        try:
            with open(temp_file, "wb") as f:
                with contextlib.closing(
                        urllib.urlopen(filename_url)) as csvdata:
                    # Take the csv data from the Sailthru API and
                    # convert it to JSON. bq can read columns in
                    # REPEATED mode from JSON files, but not from
                    # CSVs, and we have cells that contain multiple
                    # items.
                    reader = csv.reader(csvdata, delimiter=',', quotechar='"')

                    headers = reader.next()

                    # Correct confusing header names.
                    headers = [
                        blast_report_header_corrections[hdr]
                        if hdr in blast_report_header_corrections else hdr
                        for hdr in headers]

                    for row_csv in reader:
                        row_object = {}
                        for idx, column_name in enumerate(headers):
                            cell_content = row_csv[idx].strip()
                            if cell_content == "":
                                row_object[column_name] = None
                            elif (column_name in
                                    blast_report_list_column_seperators):
                                sep = blast_report_list_column_seperators[
                                    column_name]
                                row_object[column_name] = cell_content.split(
                                    sep)
                            else:
                                row_object[column_name] = cell_content

                            #  Append timezone information to TIMESTAMP cells.
                            if column_name in blast_report_timestamp_columns:
                                if isinstance(row_object[column_name], str):
                                    row_object[column_name] += " %s" % (
                                        tz_utc_offset)
                                elif isinstance(row_object[column_name], list):
                                    row_object[column_name] = [
                                        "%s %s" % (date, tz_utc_offset)
                                        for date in row_object[column_name]]
                                else:
                                    assert(row_object[column_name] is None)

                        # Append the blast ID to each row.  This way
                        # we can join/union this blast table with
                        # other tables while preserving
                        # blast_ids. Otherwise, the blast_id would
                        # only be accessible from the table name.
                        row_object["blast_id"] = str(blast_id)

                        # Write each row.
                        # In JSON mode, bq expects a JSON object on each line.
                        f.write("%s\n" % (json.dumps(row_object)))

            table_name = "sailthru_blasts.blast_%s" % str(blast_id)

            # (TODO: Update schema to port dates in TIMESTAMP format in bq)

            if dry_run:
                print ("DRY RUN: if this was for real, for the blast_query "
                       "job with blast_id = %s, we would write data at path "
                       "'%s' to bq table '%s'"
                       % (blast_id, temp_file, table_name))
            else:
                if verbose:
                    print ("For the blast_query job with blast_id = %s, "
                           "writing jsonl file to bigquery" % blast_id)
                bq_util.call_bq(['load',
                                 '--source_format=NEWLINE_DELIMITED_JSON',
                                 '--replace', table_name,
                                 temp_file,
                                 os.path.join(
                                     os.path.dirname(__file__),
                                     'sailthru_blast_export_schema.json')
                                 ],
                                project='khanacademy.org:deductive-jet-827',
                                return_output=False)
        finally:
            if not keep_temp:
                os.unlink(temp_file)


def _send_campaign_report(status, start_date, end_date, temp_file, verbose,
                          dry_run, keep_temp):
    """Export data about all campaigns in a date range to Bigquery.
    This selects campaigns that started between start_date and end_date
    inclusive.

    Arguments:
      status: Export only the details of campaigns with this status.
              Options are 'sent', 'sending', 'scheduled' and 'draft'.
      start_date: Start date of blasts (format example: 'January 1 2017')
      end_date: End date of blasts (format example: 'January 1 2017')
      temp_file: A file to store the data, to be used by 'bq load'.
      verbose: True if you want to show debug messages, else False.
      dry_run: True if we should skip writing to bq.
      keep_temp: True if we should keep the temp_file that we write.

    Returns:
      Returns a python set of the blast IDs for the blasts that were
      started within 7 days before end_date inclusive both the end_date
      and seven days before end_date. The returned set has nothing to
      do with the start-date.
    """
    recent_blast_ids = set()
    response = _get('blast', status=status, start_date=start_date,
                    end_date=end_date, limit=0)

    blasts_info_json = response.get_body().get('blasts')
    all_blasts_length = len(blasts_info_json)

    try:
        with open(temp_file, "wb") as json_file:
            for i in range(all_blasts_length):
                # Get the date a blast was started.
                date = datetime.datetime.strptime(
                    blasts_info_json[i]['start_time'],
                    '%a, %d %b %Y %H:%M:%S -%f')
                # Store a list of all blast IDs that started in the
                # last 7 days of end_date.
                if date >= datetime.datetime.strptime(
                        end_date, '%B %d %Y') - datetime.timedelta(days=7):
                    recent_blast_ids.add(blasts_info_json[i]['blast_id'])
                json.dump(blasts_info_json[i], json_file)

                if i != len(blasts_info_json) - 1:
                    json_file.write("\n")

        table_name = "sailthru_blasts.campaigns"

        if dry_run:
            print ("DRY RUN: if this was for real, we would write data at path"
                   " '%s' to bq table '%s'" % (temp_file, table_name))
        else:
            if verbose:
                print ("Writing json file with %s lines to bigquery table %s"
                       % (all_blasts_length, table_name))
            bq_util.call_bq(['load', '--source_format=NEWLINE_DELIMITED_JSON',
                             '--replace', table_name,
                             temp_file,
                             os.path.join(
                                 os.path.dirname(__file__),
                                 'sailthru_campaign_export_schema.json')
                             ],
                            project='khanacademy.org:deductive-jet-827',
                            return_output=False)
    finally:
        if not keep_temp:
            os.unlink(temp_file)

    return recent_blast_ids


if __name__ == "__main__":
    # Create a temp directory to hold temporary files
    temp_dir = tempfile.mkdtemp("sailthru_data_dir")

    parser = argparse.ArgumentParser()
    parser.add_argument('--verbose', '-v', action='store_true',
                        help="Show more information")
    parser.add_argument('--dry-run', '-n', action='store_true',
                        help="Do not upload data to BigQuery "
                             "(implicitly sets --keep-temp)")
    parser.add_argument('--keep-temp', '-k', action='store_true',
                        help="Do not remove the temporary directory on "
                             "success. This may be helpful for debugging.")

    subparsers = parser.add_subparsers(dest='subparser_name',
                                       help='sub-command help')
    parser_blast = subparsers.add_parser(
        'blast',
        help='export blast data to BigQuery')
    parser_blast.add_argument('--blast_id', required=True,
                              help='Blast to fetch data for')

    parser_campaign = subparsers.add_parser(
        'campaigns',
        help='export campaigns to BigQuery')
    parser_campaign.add_argument(
        '--status', required=True,
        choices=('sent', 'sending', 'scheduled', 'draft'),
        help="Export only campaigns with this status")
    parser_campaign.add_argument(
        '--start_date', required=True,
        help="Start date of blasts (format: 'January 1 2017')")
    parser_campaign.add_argument(
        '--end_date', required=True,
        help="End date of blasts (format: 'January 1 2017')")

    parser_export = subparsers.add_parser('export',
                                          help='export all as one script')

    args = parser.parse_args()

    if args.dry_run:
        # dry_run should implicitly set keep_temp as when doing dry run, the
        # only way to inspect the data is using the temp_dir
        args.keep_temp = True

    if args.verbose:
        # Log the path of temp directory for debugging
        print "temp_dir is %s" % temp_dir

    if args.subparser_name == 'blast':
        temp_file = os.path.join(temp_dir, "blast_export.jsonl")
        _send_blast_details_to_bq(blast_id=args.blast_id,
                                  temp_file=temp_file,
                                  verbose=args.verbose,
                                  dry_run=args.dry_run,
                                  keep_temp=args.keep_temp)
    elif args.subparser_name == 'campaigns':
        temp_file = os.path.join(temp_dir, "campaigns_export.json")
        _send_campaign_report(status=args.status,
                              start_date=args.start_date,
                              end_date=args.end_date,
                              temp_file=temp_file,
                              verbose=args.verbose,
                              dry_run=args.dry_run,
                              keep_temp=args.keep_temp)
    else:
        # Call the script directly to generate the all campaigns table and
        # tables for blasts fired in the past 7 days.
        # TODO: If fetching the campaigns table from 2010 until now becomes
        # too expensive, get the old data from the previous campaigns table.
        temp_file = os.path.join(temp_dir, "campaigns_export.json")
        recent_blasts = _send_campaign_report(
            status="sent",
            start_date="January 1 2010",
            end_date="{:%B %d %Y}".format(datetime.date.today()),
            temp_file=temp_file,
            verbose=args.verbose,
            dry_run=args.dry_run,
            keep_temp=args.keep_temp)

        # When attempting 15 threads, the Sailthru UI only showed 12 jobs
        # running (the rest were "waiting"). Therefore, there's no point
        # in running more than 12 threads. Lets only use 8 threads so we
        # don't block other consumers.
        #
        # In dry_run mode, we're probably debugging and do not want to
        # create lots of spurious jobs.
        THREAD_COUNT = 2 if args.dry_run else 8

        # We need Python to raise a KeyboardInterrupt so that ctrl+c actually
        # stops the application, which is broken when not specifying a timeout.
        # As a workaround, we specify a timeout. This is about 116 days, so
        # it should not ever be hit, it's just for the workaround.
        # See https://stackoverflow.com/a/1408476
        TIMEOUT = 10000000  # seconds

        pool = multiprocessing.dummy.Pool(THREAD_COUNT)
        pool.map_async(lambda id:
                       _send_blast_details_to_bq(
                           blast_id=id,
                           temp_file=os.path.join(
                               temp_dir, "blast_export.%s.jsonl" % id),
                           verbose=args.verbose,
                           dry_run=args.dry_run,
                           keep_temp=args.keep_temp),
                       recent_blasts).get(TIMEOUT)
        pool.close()
        pool.join()

    if args.keep_temp:
        print "Not removing temp_dir %s" % (temp_dir)
    else:
        shutil.rmtree(temp_dir)

