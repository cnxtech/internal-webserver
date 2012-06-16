<?php

/**
 * This file is automatically generated. Use 'arc liberate' to rebuild it.
 * @generated
 * @phutil-library-version 2
 */

phutil_register_library_map(array(
  '__library_version__' => 2,
  'class' =>
  array(
    'AASTNode' => 'parser/aast/api/AASTNode.php',
    'AASTNodeList' => 'parser/aast/api/AASTNodeList.php',
    'AASTToken' => 'parser/aast/api/AASTToken.php',
    'AASTTree' => 'parser/aast/api/AASTTree.php',
    'AbstractDirectedGraph' => 'utils/AbstractDirectedGraph.php',
    'AbstractDirectedGraphTestCase' => 'utils/__tests__/AbstractDirectedGraphTestCase.php',
    'BaseHTTPFuture' => 'future/http/BaseHTTPFuture.php',
    'CommandException' => 'future/CommandException.php',
    'ConduitClient' => 'conduit/ConduitClient.php',
    'ConduitClientException' => 'conduit/ConduitClientException.php',
    'ConduitFuture' => 'conduit/ConduitFuture.php',
    'ExecFuture' => 'future/ExecFuture.php',
    'ExecFutureTestCase' => 'future/__tests__/ExecFutureTestCase.php',
    'FileFinder' => 'filesystem/FileFinder.php',
    'FileList' => 'filesystem/FileList.php',
    'Filesystem' => 'filesystem/Filesystem.php',
    'FilesystemException' => 'filesystem/FilesystemException.php',
    'Future' => 'future/Future.php',
    'FutureIterator' => 'future/FutureIterator.php',
    'FutureProxy' => 'future/FutureProxy.php',
    'HTTPFuture' => 'future/http/HTTPFuture.php',
    'HTTPFutureResponseStatus' => 'future/http/status/HTTPFutureResponseStatus.php',
    'HTTPFutureResponseStatusCURL' => 'future/http/status/HTTPFutureResponseStatusCURL.php',
    'HTTPFutureResponseStatusHTTP' => 'future/http/status/HTTPFutureResponseStatusHTTP.php',
    'HTTPFutureResponseStatusParse' => 'future/http/status/HTTPFutureResponseStatusParse.php',
    'HTTPFutureResponseStatusTransport' => 'future/http/status/HTTPFutureResponseStatusTransport.php',
    'HTTPSFuture' => 'future/http/HTTPSFuture.php',
    'ImmediateFuture' => 'future/ImmediateFuture.php',
    'LinesOfALarge' => 'filesystem/linesofalarge/LinesOfALarge.php',
    'LinesOfALargeExecFuture' => 'filesystem/linesofalarge/LinesOfALargeExecFuture.php',
    'LinesOfALargeExecFutureTestCase' => 'filesystem/linesofalarge/__tests__/LinesOfALargeExecFutureTestCase.php',
    'LinesOfALargeFile' => 'filesystem/linesofalarge/LinesOfALargeFile.php',
    'LinesOfALargeFileTestCase' => 'filesystem/linesofalarge/__tests__/LinesOfALargeFileTestCase.php',
    'MFilterTestHelper' => 'utils/__tests__/MFilterTestHelper.php',
    'PhutilAWSEC2Future' => 'future/aws/PhutilAWSEC2Future.php',
    'PhutilAWSException' => 'future/aws/PhutilAWSException.php',
    'PhutilAWSFuture' => 'future/aws/PhutilAWSFuture.php',
    'PhutilAggregateException' => 'error/PhutilAggregateException.php',
    'PhutilArgumentParser' => 'parser/argument/PhutilArgumentParser.php',
    'PhutilArgumentParserException' => 'parser/argument/exception/PhutilArgumentParserException.php',
    'PhutilArgumentParserTestCase' => 'parser/argument/__tests__/PhutilArgumentParserTestCase.php',
    'PhutilArgumentSpecification' => 'parser/argument/PhutilArgumentSpecification.php',
    'PhutilArgumentSpecificationException' => 'parser/argument/exception/PhutilArgumentSpecificationException.php',
    'PhutilArgumentSpecificationTestCase' => 'parser/argument/__tests__/PhutilArgumentSpecificationTestCase.php',
    'PhutilArgumentUsageException' => 'parser/argument/exception/PhutilArgumentUsageException.php',
    'PhutilArgumentWorkflow' => 'parser/argument/workflow/PhutilArgumentWorkflow.php',
    'PhutilChannel' => 'channel/PhutilChannel.php',
    'PhutilChannelChannel' => 'channel/PhutilChannelChannel.php',
    'PhutilConsoleFormatter' => 'console/PhutilConsoleFormatter.php',
    'PhutilConsoleStdinNotInteractiveException' => 'console/PhutilConsoleStdinNotInteractiveException.php',
    'PhutilConsoleSyntaxHighlighter' => 'markup/syntax/highlighter/PhutilConsoleSyntaxHighlighter.php',
    'PhutilConsoleWrapTestCase' => 'console/__tests__/PhutilConsoleWrapTestCase.php',
    'PhutilDaemon' => 'daemon/PhutilDaemon.php',
    'PhutilDaemonOverseer' => 'daemon/PhutilDaemonOverseer.php',
    'PhutilDefaultSyntaxHighlighter' => 'markup/syntax/highlighter/PhutilDefaultSyntaxHighlighter.php',
    'PhutilDefaultSyntaxHighlighterEngine' => 'markup/syntax/engine/PhutilDefaultSyntaxHighlighterEngine.php',
    'PhutilDefaultSyntaxHighlighterEnginePygmentsFuture' => 'markup/syntax/highlighter/pygments/PhutilDefaultSyntaxHighlighterEnginePygmentsFuture.php',
    'PhutilDefaultSyntaxHighlighterEngineTestCase' => 'markup/syntax/engine/__tests__/PhutilDefaultSyntaxHighlighterEngineTestCase.php',
    'PhutilDeferredLog' => 'filesystem/PhutilDeferredLog.php',
    'PhutilDeferredLogTestCase' => 'filesystem/__tests__/PhutilDeferredLogTestCase.php',
    'PhutilDivinerSyntaxHighlighter' => 'markup/syntax/highlighter/PhutilDivinerSyntaxHighlighter.php',
    'PhutilDocblockParser' => 'parser/PhutilDocblockParser.php',
    'PhutilDocblockParserTestCase' => 'parser/__tests__/PhutilDocblockParserTestCase.php',
    'PhutilEmailAddress' => 'parser/PhutilEmailAddress.php',
    'PhutilEmailAddressTestCase' => 'parser/__tests__/PhutilEmailAddressTestCase.php',
    'PhutilErrorHandler' => 'error/PhutilErrorHandler.php',
    'PhutilEvent' => 'events/PhutilEvent.php',
    'PhutilEventConstants' => 'events/constant/PhutilEventConstants.php',
    'PhutilEventEngine' => 'events/PhutilEventEngine.php',
    'PhutilEventListener' => 'events/PhutilEventListener.php',
    'PhutilEventType' => 'events/constant/PhutilEventType.php',
    'PhutilExcessiveServiceCallsDaemon' => 'daemon/torture/PhutilExcessiveServiceCallsDaemon.php',
    'PhutilExecChannel' => 'channel/PhutilExecChannel.php',
    'PhutilFatalDaemon' => 'daemon/torture/PhutilFatalDaemon.php',
    'PhutilHangForeverDaemon' => 'daemon/torture/PhutilHangForeverDaemon.php',
    'PhutilHelpArgumentWorkflow' => 'parser/argument/workflow/PhutilHelpArgumentWorkflow.php',
    'PhutilInteractiveEditor' => 'console/PhutilInteractiveEditor.php',
    'PhutilJSON' => 'parser/PhutilJSON.php',
    'PhutilJSONTestCase' => 'parser/__tests__/PhutilJSONTestCase.php',
    'PhutilLanguageGuesser' => 'parser/PhutilLanguageGuesser.php',
    'PhutilLanguageGuesserTestCase' => 'parser/__tests__/PhutilLanguageGuesserTestCase.php',
    'PhutilMarkupEngine' => 'markup/PhutilMarkupEngine.php',
    'PhutilMarkupTestCase' => 'markup/__tests__/PhutilMarkupTestCase.php',
    'PhutilMissingSymbolException' => 'symbols/exception/PhutilMissingSymbolException.php',
    'PhutilNiceDaemon' => 'daemon/torture/PhutilNiceDaemon.php',
    'PhutilPHTTestCase' => 'internationalization/__tests__/PhutilPHTTestCase.php',
    'PhutilPerson' => 'internationalization/PhutilPerson.php',
    'PhutilPersonTest' => 'internationalization/__tests__/PhutilPersonTest.php',
    'PhutilProcessGroupDaemon' => 'daemon/torture/PhutilProcessGroupDaemon.php',
    'PhutilProtocolChannel' => 'channel/PhutilProtocolChannel.php',
    'PhutilPygmentsSyntaxHighlighter' => 'markup/syntax/highlighter/PhutilPygmentsSyntaxHighlighter.php',
    'PhutilRainbowSyntaxHighlighter' => 'markup/syntax/highlighter/PhutilRainbowSyntaxHighlighter.php',
    'PhutilReadableSerializer' => 'readableserializer/PhutilReadableSerializer.php',
    'PhutilRemarkupBlockStorage' => 'markup/engine/remarkup/PhutilRemarkupBlockStorage.php',
    'PhutilRemarkupEngine' => 'markup/engine/PhutilRemarkupEngine.php',
    'PhutilRemarkupEngineBlockRule' => 'markup/engine/remarkup/blockrule/PhutilRemarkupEngineBlockRule.php',
    'PhutilRemarkupEngineRemarkupCodeBlockRule' => 'markup/engine/remarkup/blockrule/PhutilRemarkupEngineRemarkupCodeBlockRule.php',
    'PhutilRemarkupEngineRemarkupDefaultBlockRule' => 'markup/engine/remarkup/blockrule/PhutilRemarkupEngineRemarkupDefaultBlockRule.php',
    'PhutilRemarkupEngineRemarkupHeaderBlockRule' => 'markup/engine/remarkup/blockrule/PhutilRemarkupEngineRemarkupHeaderBlockRule.php',
    'PhutilRemarkupEngineRemarkupInlineBlockRule' => 'markup/engine/remarkup/blockrule/PhutilRemarkupEngineRemarkupInlineBlockRule.php',
    'PhutilRemarkupEngineRemarkupListBlockRule' => 'markup/engine/remarkup/blockrule/PhutilRemarkupEngineRemarkupListBlockRule.php',
    'PhutilRemarkupEngineRemarkupLiteralBlockRule' => 'markup/engine/remarkup/blockrule/PhutilRemarkupEngineRemarkupLiteralBlockRule.php',
    'PhutilRemarkupEngineRemarkupNoteBlockRule' => 'markup/engine/remarkup/blockrule/PhutilRemarkupEngineRemarkupNoteBlockRule.php',
    'PhutilRemarkupEngineRemarkupQuotesBlockRule' => 'markup/engine/remarkup/blockrule/PhutilRemarkupEngineRemarkupQuotesBlockRule.php',
    'PhutilRemarkupEngineTestCase' => 'markup/engine/__tests__/PhutilRemarkupEngineTestCase.php',
    'PhutilRemarkupRule' => 'markup/engine/remarkup/markuprule/PhutilRemarkupRule.php',
    'PhutilRemarkupRuleBold' => 'markup/engine/remarkup/markuprule/PhutilRemarkupRuleBold.php',
    'PhutilRemarkupRuleDel' => 'markup/engine/remarkup/markuprule/PhutilRemarkupRuleDel.php',
    'PhutilRemarkupRuleEscapeHTML' => 'markup/engine/remarkup/markuprule/PhutilRemarkupRuleEscapeHTML.php',
    'PhutilRemarkupRuleEscapeRemarkup' => 'markup/engine/remarkup/markuprule/PhutilRemarkupRuleEscapeRemarkup.php',
    'PhutilRemarkupRuleHyperlink' => 'markup/engine/remarkup/markuprule/PhutilRemarkupRuleHyperlink.php',
    'PhutilRemarkupRuleItalic' => 'markup/engine/remarkup/markuprule/PhutilRemarkupRuleItalic.php',
    'PhutilRemarkupRuleLinebreaks' => 'markup/engine/remarkup/markuprule/PhutilRemarkupRuleLinebreaks.php',
    'PhutilRemarkupRuleMonospace' => 'markup/engine/remarkup/markuprule/PhutilRemarkupRuleMonospace.php',
    'PhutilSaturateStdoutDaemon' => 'daemon/torture/PhutilSaturateStdoutDaemon.php',
    'PhutilServiceProfiler' => 'serviceprofiler/PhutilServiceProfiler.php',
    'PhutilSimpleOptions' => 'parser/PhutilSimpleOptions.php',
    'PhutilSimpleOptionsTestCase' => 'parser/__tests__/PhutilSimpleOptionsTestCase.php',
    'PhutilSocketChannel' => 'channel/PhutilSocketChannel.php',
    'PhutilSymbolLoader' => 'symbols/PhutilSymbolLoader.php',
    'PhutilSyntaxHighlighter' => 'markup/syntax/highlighter/PhutilSyntaxHighlighter.php',
    'PhutilSyntaxHighlighterEngine' => 'markup/syntax/engine/PhutilSyntaxHighlighterEngine.php',
    'PhutilSyntaxHighlighterException' => 'markup/syntax/highlighter/PhutilSyntaxHighlighterException.php',
    'PhutilTortureTestDaemon' => 'daemon/torture/PhutilTortureTestDaemon.php',
    'PhutilTranslator' => 'internationalization/PhutilTranslator.php',
    'PhutilTranslatorTestCase' => 'internationalization/__tests__/PhutilTranslatorTestCase.php',
    'PhutilURI' => 'parser/PhutilURI.php',
    'PhutilURITestCase' => 'parser/__tests__/PhutilURITestCase.php',
    'PhutilUTF8TestCase' => 'utils/__tests__/PhutilUTF8TestCase.php',
    'PhutilUtilsTestCase' => 'utils/__tests__/PhutilUtilsTestCase.php',
    'PhutilXHPASTSyntaxHighlighter' => 'markup/syntax/highlighter/PhutilXHPASTSyntaxHighlighter.php',
    'PhutilXHPASTSyntaxHighlighterTestCase' => 'markup/syntax/highlighter/__tests__/PhutilXHPASTSyntaxHighlighterTestCase.php',
    'TempFile' => 'filesystem/TempFile.php',
    'TestAbstractDirectedGraph' => 'utils/__tests__/TestAbstractDirectedGraph.php',
    'XHPASTNode' => 'parser/xhpast/api/XHPASTNode.php',
    'XHPASTSyntaxErrorException' => 'parser/xhpast/api/XHPASTSyntaxErrorException.php',
    'XHPASTToken' => 'parser/xhpast/api/XHPASTToken.php',
    'XHPASTTree' => 'parser/xhpast/api/XHPASTTree.php',
    'XHPASTTreeTestCase' => 'parser/xhpast/api/__tests__/XHPASTTreeTestCase.php',
  ),
  'function' =>
  array(
    'Futures' => 'future/functions.php',
    'array_mergev' => 'utils/utils.php',
    'array_select_keys' => 'utils/utils.php',
    'assert_instances_of' => 'utils/utils.php',
    'coalesce' => 'utils/utils.php',
    'csprintf' => 'xsprintf/csprintf.php',
    'exec_manual' => 'future/execx.php',
    'execx' => 'future/execx.php',
    'head' => 'utils/utils.php',
    'head_key' => 'utils/utils.php',
    'hsprintf' => 'markup/render.php',
    'id' => 'utils/utils.php',
    'idx' => 'utils/utils.php',
    'ifilter' => 'utils/utils.php',
    'igroup' => 'utils/utils.php',
    'ipull' => 'utils/utils.php',
    'isort' => 'utils/utils.php',
    'jsprintf' => 'xsprintf/jsprintf.php',
    'last' => 'utils/utils.php',
    'last_key' => 'utils/utils.php',
    'mfilter' => 'utils/utils.php',
    'mgroup' => 'utils/utils.php',
    'mpull' => 'utils/utils.php',
    'msort' => 'utils/utils.php',
    'newv' => 'utils/utils.php',
    'nonempty' => 'utils/utils.php',
    'phlog' => 'error/phlog.php',
    'pht' => 'internationalization/pht.php',
    'phutil_console_confirm' => 'console/format.php',
    'phutil_console_format' => 'console/format.php',
    'phutil_console_prompt' => 'console/format.php',
    'phutil_console_require_tty' => 'console/format.php',
    'phutil_console_wrap' => 'console/format.php',
    'phutil_deprecated' => 'moduleutils/moduleutils.php',
    'phutil_error_listener_example' => 'error/phlog.php',
    'phutil_escape_html' => 'markup/render.php',
    'phutil_escape_uri' => 'markup/render.php',
    'phutil_escape_uri_path_component' => 'markup/render.php',
    'phutil_get_library_name_for_root' => 'moduleutils/moduleutils.php',
    'phutil_get_library_root' => 'moduleutils/moduleutils.php',
    'phutil_get_library_root_for_path' => 'moduleutils/moduleutils.php',
    'phutil_is_utf8' => 'utils/utf8.php',
    'phutil_passthru' => 'future/execx.php',
    'phutil_render_tag' => 'markup/render.php',
    'phutil_unescape_uri_path_component' => 'markup/render.php',
    'phutil_utf8_hard_wrap_html' => 'utils/utf8.php',
    'phutil_utf8_shorten' => 'utils/utf8.php',
    'phutil_utf8_strlen' => 'utils/utf8.php',
    'phutil_utf8ize' => 'utils/utf8.php',
    'phutil_utf8v' => 'utils/utf8.php',
    'vcsprintf' => 'xsprintf/csprintf.php',
    'vjsprintf' => 'xsprintf/jsprintf.php',
    'xhp_parser_node_constants' => 'parser/xhpast/parser_nodes.php',
    'xhpast_get_binary_path' => 'parser/xhpast/bin/xhpast_parse.php',
    'xhpast_get_build_instructions' => 'parser/xhpast/bin/xhpast_parse.php',
    'xhpast_get_parser_future' => 'parser/xhpast/bin/xhpast_parse.php',
    'xhpast_is_available' => 'parser/xhpast/bin/xhpast_parse.php',
    'xhpast_parser_token_constants' => 'parser/xhpast/parser_tokens.php',
    'xsprintf' => 'xsprintf/xsprintf.php',
    'xsprintf_callback_example' => 'xsprintf/xsprintf.php',
    'xsprintf_command' => 'xsprintf/csprintf.php',
    'xsprintf_javascript' => 'xsprintf/jsprintf.php',
  ),
  'xmap' =>
  array(
    'AASTNodeList' =>
    array(
      0 => 'Iterator',
      1 => 'Countable',
    ),
    'AbstractDirectedGraphTestCase' => 'ArcanistPhutilTestCase',
    'BaseHTTPFuture' => 'Future',
    'CommandException' => 'Exception',
    'ConduitClientException' => 'Exception',
    'ConduitFuture' => 'FutureProxy',
    'ExecFuture' => 'Future',
    'ExecFutureTestCase' => 'ArcanistPhutilTestCase',
    'FilesystemException' => 'Exception',
    'FutureIterator' => 'Iterator',
    'FutureProxy' => 'Future',
    'HTTPFuture' => 'BaseHTTPFuture',
    'HTTPFutureResponseStatus' => 'Exception',
    'HTTPFutureResponseStatusCURL' => 'HTTPFutureResponseStatus',
    'HTTPFutureResponseStatusHTTP' => 'HTTPFutureResponseStatus',
    'HTTPFutureResponseStatusParse' => 'HTTPFutureResponseStatus',
    'HTTPFutureResponseStatusTransport' => 'HTTPFutureResponseStatus',
    'HTTPSFuture' => 'BaseHTTPFuture',
    'ImmediateFuture' => 'Future',
    'LinesOfALarge' => 'Iterator',
    'LinesOfALargeExecFuture' => 'LinesOfALarge',
    'LinesOfALargeExecFutureTestCase' => 'ArcanistPhutilTestCase',
    'LinesOfALargeFile' => 'LinesOfALarge',
    'LinesOfALargeFileTestCase' => 'ArcanistPhutilTestCase',
    'PhutilAWSEC2Future' => 'PhutilAWSFuture',
    'PhutilAWSException' => 'Exception',
    'PhutilAWSFuture' => 'FutureProxy',
    'PhutilAggregateException' => 'Exception',
    'PhutilArgumentParserException' => 'Exception',
    'PhutilArgumentParserTestCase' => 'ArcanistPhutilTestCase',
    'PhutilArgumentSpecificationException' => 'PhutilArgumentParserException',
    'PhutilArgumentSpecificationTestCase' => 'ArcanistPhutilTestCase',
    'PhutilArgumentUsageException' => 'PhutilArgumentParserException',
    'PhutilChannelChannel' => 'PhutilChannel',
    'PhutilConsoleStdinNotInteractiveException' => 'Exception',
    'PhutilConsoleWrapTestCase' => 'ArcanistPhutilTestCase',
    'PhutilDefaultSyntaxHighlighterEngine' => 'PhutilSyntaxHighlighterEngine',
    'PhutilDefaultSyntaxHighlighterEnginePygmentsFuture' => 'FutureProxy',
    'PhutilDefaultSyntaxHighlighterEngineTestCase' => 'ArcanistPhutilTestCase',
    'PhutilDeferredLogTestCase' => 'ArcanistPhutilTestCase',
    'PhutilDocblockParserTestCase' => 'ArcanistPhutilTestCase',
    'PhutilEmailAddressTestCase' => 'ArcanistPhutilTestCase',
    'PhutilEventType' => 'PhutilEventConstants',
    'PhutilExcessiveServiceCallsDaemon' => 'PhutilTortureTestDaemon',
    'PhutilExecChannel' => 'PhutilChannel',
    'PhutilFatalDaemon' => 'PhutilTortureTestDaemon',
    'PhutilHangForeverDaemon' => 'PhutilTortureTestDaemon',
    'PhutilHelpArgumentWorkflow' => 'PhutilArgumentWorkflow',
    'PhutilJSONTestCase' => 'ArcanistPhutilTestCase',
    'PhutilLanguageGuesserTestCase' => 'ArcanistPhutilTestCase',
    'PhutilMarkupTestCase' => 'ArcanistPhutilTestCase',
    'PhutilMissingSymbolException' => 'Exception',
    'PhutilNiceDaemon' => 'PhutilTortureTestDaemon',
    'PhutilPHTTestCase' => 'ArcanistPhutilTestCase',
    'PhutilPersonTest' => 'PhutilPerson',
    'PhutilProcessGroupDaemon' => 'PhutilTortureTestDaemon',
    'PhutilProtocolChannel' => 'PhutilChannelChannel',
    'PhutilRemarkupEngine' => 'PhutilMarkupEngine',
    'PhutilRemarkupEngineRemarkupCodeBlockRule' => 'PhutilRemarkupEngineBlockRule',
    'PhutilRemarkupEngineRemarkupDefaultBlockRule' => 'PhutilRemarkupEngineBlockRule',
    'PhutilRemarkupEngineRemarkupHeaderBlockRule' => 'PhutilRemarkupEngineBlockRule',
    'PhutilRemarkupEngineRemarkupInlineBlockRule' => 'PhutilRemarkupEngineBlockRule',
    'PhutilRemarkupEngineRemarkupListBlockRule' => 'PhutilRemarkupEngineBlockRule',
    'PhutilRemarkupEngineRemarkupLiteralBlockRule' => 'PhutilRemarkupEngineBlockRule',
    'PhutilRemarkupEngineRemarkupNoteBlockRule' => 'PhutilRemarkupEngineBlockRule',
    'PhutilRemarkupEngineRemarkupQuotesBlockRule' => 'PhutilRemarkupEngineBlockRule',
    'PhutilRemarkupEngineTestCase' => 'ArcanistPhutilTestCase',
    'PhutilRemarkupRuleBold' => 'PhutilRemarkupRule',
    'PhutilRemarkupRuleDel' => 'PhutilRemarkupRule',
    'PhutilRemarkupRuleEscapeHTML' => 'PhutilRemarkupRule',
    'PhutilRemarkupRuleEscapeRemarkup' => 'PhutilRemarkupRule',
    'PhutilRemarkupRuleHyperlink' => 'PhutilRemarkupRule',
    'PhutilRemarkupRuleItalic' => 'PhutilRemarkupRule',
    'PhutilRemarkupRuleLinebreaks' => 'PhutilRemarkupRule',
    'PhutilRemarkupRuleMonospace' => 'PhutilRemarkupRule',
    'PhutilSaturateStdoutDaemon' => 'PhutilTortureTestDaemon',
    'PhutilSimpleOptionsTestCase' => 'ArcanistPhutilTestCase',
    'PhutilSocketChannel' => 'PhutilChannel',
    'PhutilSyntaxHighlighterException' => 'Exception',
    'PhutilTortureTestDaemon' => 'PhutilDaemon',
    'PhutilTranslatorTestCase' => 'ArcanistPhutilTestCase',
    'PhutilURITestCase' => 'ArcanistPhutilTestCase',
    'PhutilUTF8TestCase' => 'ArcanistPhutilTestCase',
    'PhutilUtilsTestCase' => 'ArcanistPhutilTestCase',
    'PhutilXHPASTSyntaxHighlighterTestCase' => 'ArcanistPhutilTestCase',
    'TestAbstractDirectedGraph' => 'AbstractDirectedGraph',
    'XHPASTNode' => 'AASTNode',
    'XHPASTSyntaxErrorException' => 'Exception',
    'XHPASTToken' => 'AASTToken',
    'XHPASTTree' => 'AASTTree',
    'XHPASTTreeTestCase' => 'ArcanistPhutilTestCase',
  ),
));
