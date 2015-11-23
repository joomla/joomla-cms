<?php

	//-----------------------------------------------------------------------------
	//
	//  nbbc_lib.php
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
	//  This file implements the standard BBCode language and our extensions,
	//  as well as a default set of smileys.  While this is not strictly necessary
	//  for the parser to work, without these definitions, the parser has nothing
	//  to do.  Generally, the definitions in this file are sufficient for most
	//  needs; however, if your needs differ, you don't want to change this file:
	//  If you want additional definitions, create a BBCode object and add them
	//  manually afterward:
	//
	//    $bbcode = new BBCode;
	//    $bbcode->AddRule(...);
	//    $bbcode->AddSmiley(...);
	//
	//-----------------------------------------------------------------------------

	class BBCodeLibrary {

		//-----------------------------------------------------------------------------
		// Standard library of smiley definitions.
	
		var $default_smileys = Array(
			':)' => 'smile.gif',       ':-)' => 'smile.gif',
			'=)' => 'smile.gif',       '=-)' => 'smile.gif',
			':(' => 'frown.gif',       ':-(' => 'frown.gif',
			'=(' => 'frown.gif',       '=-(' => 'frown.gif',
			':D' => 'bigsmile.gif',    ':-D' => 'bigsmile.gif',
			'=D' => 'bigsmile.gif',    '=-D' => 'bigsmile.gif',
			'>:('=> 'angry.gif',       '>:-('=> 'angry.gif',
			'>=('=> 'angry.gif',       '>=-('=> 'angry.gif',
			'D:' => 'angry.gif',       'D-:' => 'angry.gif',
			'D=' => 'angry.gif',       'D-=' => 'angry.gif',
			'>:)'=> 'evil.gif',        '>:-)'=> 'evil.gif',
			'>=)'=> 'evil.gif',        '>=-)'=> 'evil.gif',
			'>:D'=> 'evil.gif',        '>:-D'=> 'evil.gif',
			'>=D'=> 'evil.gif',        '>=-D'=> 'evil.gif',
			'>;)'=> 'sneaky.gif',      '>;-)'=> 'sneaky.gif',
			'>;D'=> 'sneaky.gif',      '>;-D'=> 'sneaky.gif',
			'O:)' => 'saint.gif',      'O:-)' => 'saint.gif',
			'O=)' => 'saint.gif',      'O=-)' => 'saint.gif',
			':O' => 'surprise.gif',    ':-O' => 'surprise.gif',
			'=O' => 'surprise.gif',    '=-O' => 'surprise.gif',
			':?' => 'confuse.gif',     ':-?' => 'confuse.gif',
			'=?' => 'confuse.gif',     '=-?' => 'confuse.gif',
			':s' => 'worry.gif',       ':-S' => 'worry.gif',
			'=s' => 'worry.gif',       '=-S' => 'worry.gif',
			':|' => 'neutral.gif',     ':-|' => 'neutral.gif',
			'=|' => 'neutral.gif',     '=-|' => 'neutral.gif',
			':I' => 'neutral.gif',     ':-I' => 'neutral.gif',
			'=I' => 'neutral.gif',     '=-I' => 'neutral.gif',
			':/' => 'irritated.gif',   ':-/' => 'irritated.gif',
			'=/' => 'irritated.gif',   '=-/' => 'irritated.gif',
			':\\' => 'irritated.gif', ':-\\' => 'irritated.gif',
			'=\\' => 'irritated.gif', '=-\\' => 'irritated.gif',
			':P' => 'tongue.gif',      ':-P' => 'tongue.gif',
			'=P' => 'tongue.gif',      '=-P' => 'tongue.gif',
									   'X-P' => 'tongue.gif',
			'8)' => 'bigeyes.gif',     '8-)' => 'bigeyes.gif',
			'B)' => 'cool.gif',        'B-)' => 'cool.gif',
			';)' => 'wink.gif',        ';-)' => 'wink.gif',
			';D' => 'bigwink.gif',     ';-D' => 'bigwink.gif',
			'^_^'=> 'anime.gif',       '^^;' => 'sweatdrop.gif',
			'>_>'=> 'lookright.gif',   '>.>' => 'lookright.gif',
			'<_<'=> 'lookleft.gif',    '<.<' => 'lookleft.gif',
			'XD' => 'laugh.gif',       'X-D' => 'laugh.gif',
			';D' => 'bigwink.gif',     ';-D' => 'bigwink.gif',
			':3' => 'smile3.gif',      ':-3' => 'smile3.gif',
			'=3' => 'smile3.gif',      '=-3' => 'smile3.gif',
			';3' => 'wink3.gif',       ';-3' => 'wink3.gif',
			'<g>' => 'teeth.gif',      '<G>' => 'teeth.gif',
			'o.O' => 'boggle.gif',     'O.o' => 'boggle.gif',
			':blue:' => 'blue.gif',
			':zzz:' => 'sleepy.gif',
			'<3' => 'heart.gif',
			':star:' => 'star.gif',
		);

		//-----------------------------------------------------------------------------
		// Standard rules for what to do when a BBCode tag is encountered.

		var $default_tag_rules = Array(
		
			'b' => Array(
				'simple_start' => "<b>",
				'simple_end' => "</b>",
				'class' => 'inline',
				'allow_in' => Array('listitem', 'block', 'columns', 'inline', 'link'),
				'plain_start' => "<b>",
				'plain_end' => "</b>",
			),
			'i' => Array(
				'simple_start' => "<i>",
				'simple_end' => "</i>",
				'class' => 'inline',
				'allow_in' => Array('listitem', 'block', 'columns', 'inline', 'link'),
				'plain_start' => "<i>",
				'plain_end' => "</i>",
			),
			'u' => Array(
				'simple_start' => "<u>",
				'simple_end' => "</u>",
				'class' => 'inline',
				'allow_in' => Array('listitem', 'block', 'columns', 'inline', 'link'),
				'plain_start' => "<u>",
				'plain_end' => "</u>",
			),
			's' => Array(
				'simple_start' => "<strike>",
				'simple_end' => "</strike>",
				'class' => 'inline',
				'allow_in' => Array('listitem', 'block', 'columns', 'inline', 'link'),
				'plain_start' => "<i>",
				'plain_end' => "</i>",
			),

			'font' => Array(
				'mode' => BBCODE_MODE_LIBRARY,
				'allow' => Array('_default' => '/^[a-zA-Z0-9._ -]+$/'),
				'method' => 'DoFont',
				'class' => 'inline',
				'allow_in' => Array('listitem', 'block', 'columns', 'inline', 'link'),
			),
			'color' => Array(
				'mode' => BBCODE_MODE_ENHANCED,
				'allow' => Array('_default' => '/^#?[a-zA-Z0-9._ -]+$/'),
				'template' => '<span style="color:{$_default/tw}">{$_content/v}</span>',
				'class' => 'inline',
				'allow_in' => Array('listitem', 'block', 'columns', 'inline', 'link'),
			),
			'size' => Array(
				'mode' => BBCODE_MODE_LIBRARY,
				'allow' => Array('_default' => '/^[0-9.]+$/D'),
				'method' => 'DoSize',
				'class' => 'inline',
				'allow_in' => Array('listitem', 'block', 'columns', 'inline', 'link'),
			),
			'sup' => Array(
				'simple_start' => "<sup>",
				'simple_end' => "</sup>",
				'class' => 'inline',
				'allow_in' => Array('listitem', 'block', 'columns', 'inline', 'link'),
			),
			'sub' => Array(
				'simple_start' => "<sub>",
				'simple_end' => "</sub>",
				'class' => 'inline',
				'allow_in' => Array('listitem', 'block', 'columns', 'inline', 'link'),
			),
			'spoiler' => Array(
				'simple_start' => "<span class=\"bbcode_spoiler\">",
				'simple_end' => "</span>",
				'class' => 'inline',
				'allow_in' => Array('listitem', 'block', 'columns', 'inline', 'link'),
			),
			'acronym' => Array(
				'mode' => BBCODE_MODE_ENHANCED,
				'template' => '<span class="bbcode_acronym" title="{$_default/e}">{$_content/v}</span>',
				'class' => 'inline',
				'allow_in' => Array('listitem', 'block', 'columns', 'inline', 'link'),
			),

			'url' => Array(
				'mode' => BBCODE_MODE_LIBRARY,
				'method' => 'DoURL',
				'class' => 'link',
				'allow_in' => Array('listitem', 'block', 'columns', 'inline'),
				'content' => BBCODE_REQUIRED,
				'plain_start' => "<a href=\"{\$link}\">",
				'plain_end' => "</a>",
				'plain_content' => Array('_content', '_default'),
				'plain_link' => Array('_default', '_content'),
			),
			'email' => Array(
				'mode' => BBCODE_MODE_LIBRARY,
				'method' => 'DoEmail',
				'class' => 'link',
				'allow_in' => Array('listitem', 'block', 'columns', 'inline'),
				'content' => BBCODE_REQUIRED,
				'plain_start' => "<a href=\"mailto:{\$link}\">",
				'plain_end' => "</a>",
				'plain_content' => Array('_content', '_default'),
				'plain_link' => Array('_default', '_content'),
			),
			'wiki' => Array(
				'mode' => BBCODE_MODE_LIBRARY,
				'method' => "DoWiki",
				'class' => 'link',
				'allow_in' => Array('listitem', 'block', 'columns', 'inline'),
				'end_tag' => BBCODE_PROHIBIT,
				'content' => BBCODE_PROHIBIT,
				'plain_start' => "<b>[",
				'plain_end' => "]</b>",
				'plain_content' => Array('title', '_default'),
				'plain_link' => Array('_default', '_content'),
			),

			'img' => Array(
				'mode' => BBCODE_MODE_LIBRARY,
				'method' => "DoImage",
				'class' => 'image',
				'allow_in' => Array('listitem', 'block', 'columns', 'inline', 'link'),
				'end_tag' => BBCODE_REQUIRED,
				'content' => BBCODE_REQUIRED,
				'plain_start' => "[image]",
				'plain_content' => Array(),
			),
			'rule' => Array(
				'mode' => BBCODE_MODE_LIBRARY,
				'method' => "DoRule",
				'class' => 'block',
				'allow_in' => Array('listitem', 'block', 'columns'),
				'end_tag' => BBCODE_PROHIBIT,
				'content' => BBCODE_PROHIBIT,
				'before_tag' => "sns",
				'after_tag' => "sns",
				'plain_start' => "\n-----\n",
				'plain_end' => "",
				'plain_content' => Array(),
			),
			'br' => Array(
				'mode' => BBCODE_MODE_SIMPLE,
				'simple_start' => "<br />\n",
				'simple_end' => "",
				'class' => 'inline',
				'allow_in' => Array('listitem', 'block', 'columns', 'inline', 'link'),
				'end_tag' => BBCODE_PROHIBIT,
				'content' => BBCODE_PROHIBIT,
				'before_tag' => "s",
				'after_tag' => "s",
				'plain_start' => "\n",
				'plain_end' => "",
				'plain_content' => Array(),
			),

			'left' => Array(
				'simple_start' => "\n<div class=\"bbcode_left\" style=\"text-align:left\">\n",
				'simple_end' => "\n</div>\n",
				'allow_in' => Array('listitem', 'block', 'columns'),
				'before_tag' => "sns",
				'after_tag' => "sns",
				'before_endtag' => "sns",
				'after_endtag' => "sns",
				'plain_start' => "\n",
				'plain_end' => "\n",
			),
			'right' => Array(
				'simple_start' => "\n<div class=\"bbcode_right\" style=\"text-align:right\">\n",
				'simple_end' => "\n</div>\n",
				'allow_in' => Array('listitem', 'block', 'columns'),
				'before_tag' => "sns",
				'after_tag' => "sns",
				'before_endtag' => "sns",
				'after_endtag' => "sns",
				'plain_start' => "\n",
				'plain_end' => "\n",
			),
			'center' => Array(
				'simple_start' => "\n<div class=\"bbcode_center\" style=\"text-align:center\">\n",
				'simple_end' => "\n</div>\n",
				'allow_in' => Array('listitem', 'block', 'columns'),
				'before_tag' => "sns",
				'after_tag' => "sns",
				'before_endtag' => "sns",
				'after_endtag' => "sns",
				'plain_start' => "\n",
				'plain_end' => "\n",
			),
			'indent' => Array(
				'simple_start' => "\n<div class=\"bbcode_indent\" style=\"margin-left:4em\">\n",
				'simple_end' => "\n</div>\n",
				'allow_in' => Array('listitem', 'block', 'columns'),
				'before_tag' => "sns",
				'after_tag' => "sns",
				'before_endtag' => "sns",
				'after_endtag' => "sns",
				'plain_start' => "\n",
				'plain_end' => "\n",
			),

			'columns' => Array(
				'simple_start' => "\n<table class=\"bbcode_columns\"><tbody><tr><td class=\"bbcode_column bbcode_firstcolumn\">\n",
				'simple_end' => "\n</td></tr></tbody></table>\n",
				'class' => 'columns',
				'allow_in' => Array('listitem', 'block', 'columns'),
				'end_tag' => BBCODE_REQUIRED,
				'content' => BBCODE_REQUIRED,
				'before_tag' => "sns",
				'after_tag' => "sns",
				'before_endtag' => "sns",
				'after_endtag' => "sns",
				'plain_start' => "\n",
				'plain_end' => "\n",
			),
			'nextcol' => Array(
				'simple_start' => "\n</td><td class=\"bbcode_column\">\n",
				'class' => 'nextcol',
				'allow_in' => Array('columns'),
				'end_tag' => BBCODE_PROHIBIT,
				'content' => BBCODE_PROHIBIT,
				'before_tag' => "sns",
				'after_tag' => "sns",
				'before_endtag' => "sns",
				'after_endtag' => "sns",
				'plain_start' => "\n",
				'plain_end' => "",
			),

			'code' => Array(
				'mode' => BBCODE_MODE_ENHANCED,
				'template' => "\n<div class=\"bbcode_code\">\n<div class=\"bbcode_code_head\">Code:</div>\n<div class=\"bbcode_code_body\" style=\"white-space:pre\">{\$_content/v}</div>\n</div>\n",
				'class' => 'code',
				'allow_in' => Array('listitem', 'block', 'columns'),
				'content' => BBCODE_VERBATIM,
				'before_tag' => "sns",
				'after_tag' => "sn",
				'before_endtag' => "sn",
				'after_endtag' => "sns",
				'plain_start' => "\n<b>Code:</b>\n",
				'plain_end' => "\n",
			),
			'quote' => Array(
				'mode' => BBCODE_MODE_LIBRARY,
				'method' => "DoQuote",
				'allow_in' => Array('listitem', 'block', 'columns'),
				'before_tag' => "sns",
				'after_tag' => "sns",
				'before_endtag' => "sns",
				'after_endtag' => "sns",
				'plain_start' => "\n<b>Quote:</b>\n",
				'plain_end' => "\n",
			),

			'list' => Array(
				'mode' => BBCODE_MODE_LIBRARY,
				'method' => 'DoList',
				'class' => 'list',
				'allow_in' => Array('listitem', 'block', 'columns'),
				'before_tag' => "sns",
				'after_tag' => "sns",
				'before_endtag' => "sns",
				'after_endtag' => "sns",
				'plain_start' => "\n",
				'plain_end' => "\n",
			),
			'*' => Array(
				'simple_start' => "<li>",
				'simple_end' => "</li>\n",
				'class' => 'listitem',
				'allow_in' => Array('list'),
				'end_tag' => BBCODE_OPTIONAL,
				'before_tag' => "s",
				'after_tag' => "s",
				'before_endtag' => "sns",
				'after_endtag' => "sns",
				'plain_start' => "\n * ",
				'plain_end' => "\n",
			),
			
			'ul' => Array(
				'mode' => BBCODE_MODE_LIBRARY,
				'method' => 'DoList',
				'class' => 'list',
				'allow_in' => Array('listitem', 'block', 'columns'),
				'before_tag' => "sns",
				'after_tag' => "sns",
				'before_endtag' => "sns",
				'after_endtag' => "sns",
				'plain_start' => "\n",
				'plain_end' => "\n",
			),
			
			'ol' => Array(
				'mode' => BBCODE_MODE_LIBRARY,
				'method' => 'DoList',
				'class' => 'list',
				'allow_in' => Array('listitem', 'block', 'columns'),
				'before_tag' => "sns",
				'after_tag' => "sns",
				'before_endtag' => "sns",
				'after_endtag' => "sns",
				'plain_start' => "\n",
				'plain_end' => "\n",
			),
			
			'li' => Array(
				'simple_start' => "<li>",
				'simple_end' => "</li>\n",
				'class' => 'listitem',
				'allow_in' => Array('list'),
				'end_tag' => BBCODE_OPTIONAL,
				'before_tag' => "s",
				'after_tag' => "s",
				'before_endtag' => "sns",
				'after_endtag' => "sns",
				'plain_start' => "\n * ",
				'plain_end' => "\n",
			),
		);

		//-----------------------------------------------------------------------------
		//  Standard library of BBCode formatting routines.

		// Format a [url] tag by producing an <a>...</a> element.
		// The URL only allows http, https, mailto, and ftp protocols for safety.
		function DoURL($bbcode, $action, $name, $default, $params, $content) {
			// We can't check this with BBCODE_CHECK because we may have no URL before the content
			// has been processed.
			if ($action == BBCODE_CHECK) return true;

			$url = is_string($default) ? $default : $bbcode->UnHTMLEncode(strip_tags($content));
			if ($bbcode->IsValidURL($url)) {
				if ($bbcode->debug)
					print "ISVALIDURL<br />";
				if ($bbcode->url_targetable !== false && isset($params['target']))
					$target = " target=\"" . htmlspecialchars($params['target']) . "\"";
				else $target = "";
				if ($bbcode->url_target !== false)
					if (!($bbcode->url_targetable == 'override' && isset($params['target'])))
						$target = " target=\"" . htmlspecialchars($bbcode->url_target) . "\"";
				return '<a href="' . htmlspecialchars($url) . '" class="bbcode_url"' . $target . '>' . $content . '</a>';
			}
			else return htmlspecialchars($params['_tag']) . $content . htmlspecialchars($params['_endtag']);
		}

		// Format an [email] tag by producing an <a>...</a> element.
		// The e-mail address must be a valid address including at least a '@' and a valid domain
		// name or IPv4 or IPv6 address after the '@'.
		function DoEmail($bbcode, $action, $name, $default, $params, $content) {
			// We can't check this with BBCODE_CHECK because we may have no URL before the content
			// has been processed.
			if ($action == BBCODE_CHECK) return true;

			$email = is_string($default) ? $default : $bbcode->UnHTMLEncode(strip_tags($content));
			if ($bbcode->IsValidEmail($email))
				return '<a href="mailto:' . htmlspecialchars($email) . '" class="bbcode_email">' . $content . '</a>';
			else return htmlspecialchars($params['_tag']) . $content . htmlspecialchars($params['_endtag']);
		}
		
		// Format a [size] tag by producing a <span> with a style with a different font-size.
		function DoSize($bbcode, $action, $name, $default, $params, $content) {
			switch ($default) {
			case '0': $size = '.5em'; break;
			case '1': $size = '.67em'; break;
			case '2': $size = '.83em'; break;
			default:
			case '3': $size = '1.0em'; break;
			case '4': $size = '1.17em'; break;
			case '5': $size = '1.5em'; break;
			case '6': $size = '2.0em'; break;
			case '7': $size = '2.5em'; break;
			}
			return "<span style=\"font-size:$size\">$content</span>";
		}

		// Format a [font] tag by producing a <span> with a style with a different font-family.
		// This is complicated by the fact that we have to recognize the five special font
		// names and quote all the others.
		function DoFont($bbcode, $action, $name, $default, $params, $content) {
			$fonts = explode(",", $default);
			$result = "";
			$special_fonts = Array(
				'serif' => 'serif',
				'sans-serif' => 'sans-serif',
				'sans serif' => 'sans-serif',
				'sansserif' => 'sans-serif',
				'sans' => 'sans-serif',
				'cursive' => 'cursive',
				'fantasy' => 'fantasy',
				'monospace' => 'monospace',
				'mono' => 'monospace',
			);
			foreach ($fonts as $font) {
				$font = trim($font);
				if (isset($special_fonts[$font])) {
					if (strlen($result) > 0) $result .= ",";
					$result .= $special_fonts[$font];
				}
				else if (strlen($font) > 0) {
					if (strlen($result) > 0) $result .= ",";
					$result .= "'$font'";
				}
			}
			return "<span style=\"font-family:$result\">$content</span>";
		}

		// Format a [wiki] tag by producing an <a>...</a> element.
		function DoWiki($bbcode, $action, $name, $default, $params, $content) {
			$name = $bbcode->Wikify($default);
			if ($action == BBCODE_CHECK)
				return strlen($name) > 0;
			$title = trim(@$params['title']);
			if (strlen($title) <= 0) $title = trim($default);
			return "<a href=\"{$bbcode->wiki_url}$name\" class=\"bbcode_wiki\">"
				. htmlspecialchars($title) . "</a>";
		}

		// Format an [img] tag.  The URL only allows http, https, and ftp protocols for safety.
		function DoImage($bbcode, $action, $name, $default, $params, $content) {
			// We can't validate this until we have its content.
			if ($action == BBCODE_CHECK) return true;

			$content = trim($bbcode->UnHTMLEncode(strip_tags($content)));
			if (preg_match("/\\.(?:gif|jpeg|jpg|jpe|png)$/", $content)) {
				if (preg_match("/^[a-zA-Z0-9_][^:]+$/", $content)) {
					// No protocol, so the image is in our local image directory, or somewhere under it.
					if (!preg_match("/(?:\\/\\.\\.\\/)|(?:^\\.\\.\\/)|(?:^\\/)/", $content)) {
						$info = @getimagesize("{$bbcode->local_img_dir}/{$content}");
						if ($info[2] == IMAGETYPE_GIF || $info[2] == IMAGETYPE_JPEG || $info[2] == IMAGETYPE_PNG) {
							return "<img src=\""
								. htmlspecialchars("{$bbcode->local_img_url}/{$content}") . "\" alt=\""
								. htmlspecialchars(basename($content)) . "\" width=\""
								. htmlspecialchars($info[0]) . "\" height=\""
								. htmlspecialchars($info[1]) . "\" class=\"bbcode_img\" />";
						}
					}
				}
				else if ($bbcode->IsValidURL($content, false)) {
					// Remote URL, or at least we don't know where it is.
					return "<img src=\"" . htmlspecialchars($content) . "\" alt=\""
						. htmlspecialchars(basename($content)) . "\" class=\"bbcode_img\" />";
				}
			}

			return htmlspecialchars($params['_tag']) . htmlspecialchars($content) . htmlspecialchars($params['_endtag']);
		}

		// Format a [rule] tag.  This substitutes the content provided by the BBCode
		// object, whatever that may be.
		function DoRule($bbcode, $action, $name, $default, $params, $content) {
			if ($action == BBCODE_CHECK) return true;
			else return $bbcode->rule_html;
		}

		// Format a [quote] tag.  This tag can come in a variety of flavors:
		//
		//  [quote]...[/quote]
		//  [quote=Tom]...[/quote]
		//  [quote name="Tom"]...[/quote]
		//
		// In the third form, you can also add a date="" parameter to display the date
		// on which Tom wrote it, and you can add a url="" parameter to turn the author's
		// name into a link.  A full example might be:
		//
		//  [quote name="Tom" date="July 4, 1776 3:48 PM" url="http://www.constitution.gov"]...[/quote]
		//
		// The URL only allows http, https, mailto, gopher, ftp, and feed protocols for safety.
		function DoQuote($bbcode, $action, $name, $default, $params, $content) {
			if ($action == BBCODE_CHECK) return true;

			if (isset($params['name'])) {
				$title = htmlspecialchars(trim($params['name'])) . " wrote";
				if (isset($params['date']))
					$title .= " on " . htmlspecialchars(trim($params['date']));
				$title .= ":";
				if (isset($params['url'])) {
					$url = trim($params['url']);
					if ($bbcode->IsValidURL($url))
						$title = "<a href=\"" . htmlspecialchars($params['url']) . "\">" . $title . "</a>";
				}
			}
			else if (!is_string($default))
				$title = "Quote:";
			else $title = htmlspecialchars(trim($default)) . " wrote:";
			return "\n<div class=\"bbcode_quote\">\n<div class=\"bbcode_quote_head\">"
				. $title . "</div>\n<div class=\"bbcode_quote_body\">"
				. $content . "</div>\n</div>\n";
		}

		// Format a [list] tag, which is complicated by the number of different
		// ways a list can be started.  The following parameters are allowed:
		//
		//   [list]           Unordered list, using default marker
		//   [list=circle]    Unordered list, using circle marker
		//   [list=disc]      Unordered list, using disc marker
		//   [list=square]    Unordered list, using square marker
		//
		//   [list=1]         Ordered list, numeric, starting at 1
		//   [list=A]         Ordered list, capital letters, starting at A
		//   [list=a]         Ordered list, lowercase letters, starting at a
		//   [list=I]         Ordered list, capital Roman numerals, starting at I
		//   [list=i]         Ordered list, lowercase Roman numerals, starting at i
		//   [list=greek]     Ordered list, lowercase Greek letters, starting at alpha
		//   [list=01]        Ordered list, two-digit numeric with 0-padding, starting at 01
		function DoList($bbcode, $action, $name, $default, $params, $content) {

			// Allowed list styles, striaght from the CSS 2.1 spec.  The only prohibited
			// list style is that with image-based markers, which often slows down web sites.
			$list_styles = Array(
				'1' => 'decimal',
				'01' => 'decimal-leading-zero',
				'i' => 'lower-roman',
				'I' => 'upper-roman',
				'a' => 'lower-alpha',
				'A' => 'upper-alpha',
			);
			$ci_list_styles = Array(
				'circle' => 'circle',
				'disc' => 'disc',
				'square' => 'square',
				'greek' => 'lower-greek',
				'armenian' => 'armenian',
				'georgian' => 'georgian',
			);
			$ul_types = Array(
				'circle' => 'circle',
				'disc' => 'disc',
				'square' => 'square',
			);

			$default = trim($default);

			if ($action == BBCODE_CHECK) {
				if (!is_string($default) || strlen($default) == "") return true;
				else if (isset($list_styles[$default])) return true;
				else if (isset($ci_list_styles[strtolower($default)])) return true;
				else return false;
			}

			// Choose a list element (<ul> or <ol>) and a style.
			if (!is_string($default) || strlen($default) == "") {
				$elem = 'ul';
				$type = '';
			}
			else if ($default == '1') {
				$elem = 'ol';
				$type = '';
			}
			else if (isset($list_styles[$default])) {
				$elem = 'ol';
				$type = $list_styles[$default];
			}
			else {
				$default = strtolower($default);
				if (isset($ul_types[$default])) {
					$elem = 'ul';
					$type = $ul_types[$default];
				}
				else if (isset($ci_list_styles[$default])) {
					$elem = 'ol';
					$type = $ci_list_styles[$default];
				}
			}

			// Generate the HTML for it.
			if (strlen($type))
				return "\n<$elem class=\"bbcode_list\" style=\"list-style-type:$type\">\n$content</$elem>\n";
			else return "\n<$elem class=\"bbcode_list\">\n$content</$elem>\n";
		}

	}

?>
