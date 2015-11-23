<?php

	//-----------------------------------------------------------------------------
	//
	//  nbbc_main.php
	//
	//  This file is part of NBBC, the New BBCode Parser.
	//
	//  NBBC implements a fully-validating, high-speed, extensible parser for the
	//  BBCode document language.  Its output is XHTML 1.0 Strict conformant no
	//  matter what its input is.  NBBC supports the full standard BBCode language,
	//  as well as comments, columns, enhanced quotes, spoilers, acronyms, wiki
	//  links, several list styles, justification, indentation, and smileys, among
	//  other advanced features.
	//
	//-----------------------------------------------------------------------------
	//
	//  Copyright (c) 2008-9, the Phantom Inker.  All rights reserved.
	//
	//  Redistribution and use in source and binary forms, with or without
	//  modification, are permitted provided that the following conditions
	//  are met:
	//
	//    * Redistributions of source code must retain the above copyright
	//       notice, this list of conditions and the following disclaimer.
	//
	//    * Redistributions in binary form must reproduce the above copyright
	//       notice, this list of conditions and the following disclaimer in
	//       the documentation and/or other materials provided with the
	//       distribution.
	//
	//  THIS SOFTWARE IS PROVIDED BY THE PHANTOM INKER "AS IS" AND ANY EXPRESS
	//  OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
	//  WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	//  DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
	//  LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
	//  CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
	//  SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR
	//  BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
	//  WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
	//  OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN
	//  IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
	//
	//-----------------------------------------------------------------------------
	//
	//  This file provides definitions shared throughout the parser, and a
	//  uniform way to include all the parser's pieces.
	//
	//-----------------------------------------------------------------------------

	define("BBCODE_VERSION", "1.4.5");		// Current version number
	define("BBCODE_RELEASE", "2010-09-17");	// Current release date

	define("BBCODE_VERBATIM", 2);		// Content type:  Content is not processed as BBCode.
	define("BBCODE_REQUIRED", 1);		// Content type:  Content may not be empty or whitespace.
										// End tags:  End tag must be given by user.
	define("BBCODE_OPTIONAL", 0);		// Content type:  Content is permitted but not required.
										// End tags:  End tag is permitted but not required.
	define("BBCODE_PROHIBIT", -1);		// Content type:  Content may not be provided by user.
										// End tags:  End tag is disallowed; start tag only.

	define("BBCODE_CHECK", 1);			// Callback operation: Check validitity of input
	define("BBCODE_OUTPUT", 2);			// Callback operation: Generate HTML output

	define("BBCODE_ENDTAG", 5);			// Token: An [/end tag]
	define("BBCODE_TAG", 4);			// Token: A [start tag] or [empty tag]
	define("BBCODE_TEXT", 3);			// Token: Non-whitespace non-tag plain text
	define("BBCODE_NL", 2);				// Token: A single newline
	define("BBCODE_WS", 1);				// Token: Non-newline whitespace
	define("BBCODE_EOI", 0);			// Token: End-of-input

	define("BBCODE_LEXSTATE_TEXT", 0);	// Lexer: Next token is plain text.
	define("BBCODE_LEXSTATE_TAG", 1);	// Lexer: Next token is non-text element.

	define("BBCODE_MODE_SIMPLE", 0);	// Swap BBCode tags with HTML tags.
	define("BBCODE_MODE_CALLBACK", 1);	// Use provided callback function or method.
	define("BBCODE_MODE_INTERNAL", 2);	// Use internal callback function.
	define("BBCODE_MODE_LIBRARY", 3);	// Use library callback function.
	define("BBCODE_MODE_ENHANCED", 4);	// Insert BBCode input into the provided HTML template.

	define("BBCODE_STACK_TOKEN", 0);	// Stack node: Token type
	define("BBCODE_STACK_TEXT", 1);		// Stack node: HTML text content
	define("BBCODE_STACK_TAG", 2);		// Stack node: Tag contents (array)
	define("BBCODE_STACK_CLASS", 3);	// Stack node: Classname

	//-----------------------------------------------------------------------------
	//  This profiler class helps us to easily detect performance bottlenecks.
	//  We leave it out of the high-speed compressed version of NBBC for
	//  performance reasons; this is really a debugging aid more than anything.

	//<skip-when-compressing>
	class BBCode_Profiler {
		var $start_time, $total_times;
		
		function BBCode_Profiler()
			{ $start_time = Array(); $total_times = Array(); }

		function Now()
			{ list($usec, $sec) = explode(" ", microtime());
				return ((float)$usec + (float)$sec); }

		function Begin($group)
			{ $this->start_time[$group] = $this->Now(); }
		function End($group)
			{ $time = $this->Now() - $this->start_time[$group];
				if (!isset($this->total_times[$group]))
					$this->total_times[$group] = $time;
				else $this->total_times[$group] += $time; }
		function Reset($group)
			{ $this->total_times[$group] = 0; }
		function Total($group)
			{ return @$this->total_times[$group]; }

		function DumpAllGroups() {
			print "<div>Profiled times:\n<ul>\n";
			ksort($this->total_times);
			foreach ($this->total_times as $name => $time) {
				print "<li><b>" . htmlspecialchars($name) . "</b>: " . sprintf("%0.2f msec", $time * 1000) . "</li>\n";
			}
			print "</ul>\n</div>\n";
		}
	}
	//</skip-when-compressing>

	//-----------------------------------------------------------------------------
	//  PHP 4 doesn't have str_split(), so if it doesn't exist, we provide a
	//  (slower) substitute.

	if (!function_exists('str_split')) {
		function str_split($string, $split_length = 1) {
			$array = explode("\r\n", chunk_split($string, $split_length));
			array_pop($array);
			return $array;
		}
	}

	//-----------------------------------------------------------------------------

	// We use absolute paths to get around PHP-include weirdness; on some installs
	// of PHP, including a file that uses a relative pathname doesn't always work
	// correctly due to the way include_path is set.
	$BBCode_SourceDir = dirname(__FILE__);

	require_once("$BBCode_SourceDir/nbbc_lex.php");		// The lexical analyzer.
	require_once("$BBCode_SourceDir/nbbc_parse.php");	// The parser/converter.
	require_once("$BBCode_SourceDir/nbbc_lib.php");		// The library of standard BBCode rules and Smileys.
	require_once("$BBCode_SourceDir/nbbc_email.php");	// E-mail address validator.

?>
