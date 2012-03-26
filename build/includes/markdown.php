<?php
/**
 * Elephant Markdown - A text-to-HTML conversion tool for web writers
 *
 * @package	   ElephantMarkdown
 * @copyright  Copyright 2004 - 2008 Michel Fortin <http://www.michelf.com/projects/php-markdown/>. All rights reserved.
 * @copyright  Copyright 2004 - 2006 John Gruber <http://daringfireball.net/projects/markdown/>. All rights reserved.
 * @copyright  Copyright 2011 Alexandre Gomes Gaigalas <http://gaigalas.net>. All rights reserved.
 * @license    BSD-style license.
 */

/**
 * Converts markdown text to HTML.
 *
 * @param   string  $text  The markdown formatted text.
 *
 * @return  string
 */
function Markdown($text)
{
	static $parser;

	if (!isset($parser))
	{
		$parser = new ElephantMarkdown;
	}

	return $parser->transform($text);
}

/**
 * Class for converting markdown formatted text into HTML.
 *
 * @package  ElephantMarkdown
 */
class ElephantMarkdown
{
	/**
	 * @var  integer
	 */
	const NESTED_BRACKETS_DEPTH = 6;

	/**
	 * @var  integer
	 */
	const NESTED_URL_PARENTHESIS_DEPTH = 4;

	/**
	 * @var  string
	 */
	const ESCAPE_CHARS = '[\\`\*_\{\}\[\]\(\)\>#\+\-\.\!\:\|]';

	/**
	 * @var  integer
	 */
	const TAB_WIDTH = 4;

	/**
	 * @var  boolean
	 */
	const NO_MARKUP = false;

	/**
	 * @var  boolean
	 */
	const NO_ENTITIES = false;

	/**
	 * @var  string
	 */
	const BLOCK_TAGS_REGEX = 'p|div|h[1-6]|blockquote|pre|table|dl|ol|ul|address|form|fieldset|iframe|hr|legend';

	/**
	 * @var  string
	 */
	const CONTEXT_BLOCK_TAGS_REGEX = 'script|noscript|math|ins|del';

	/**
	 * @var  string
	 */
	const CONTAIN_SPAN_TAGS_REGEX = 'p|h[1-6]|li|dd|dt|td|th|legend|address';

	/**
	 * @var  string
	 */
	const CLEAN_TAGS_REGEX = 'script|math';

	/**
	 * @var  string
	 */
	const AUTO_CLOSE_TAGS_REGEX = 'hr|img';

	/**
	 * @var  string
	 */
	const HEADER_SELFLINK_TEXT= "&larr;";

	/**
	 * @var  string
	 */
	const NESTED_BRACKETS_REGEX = '(?>[^\[\]]+|\[(?>[^\[\]]+|\[(?>[^\[\]]+|\[(?>[^\[\]]+|\[(?>[^\[\]]+|\[(?>[^\[\]]+|\[\])*\])*\])*\])*\])*\])*';

	/**
	 * @var  string
	 */
	const NESTED_URL_PARENTHESIS_REGEX = '(?>[^()\s]+|\((?>[^()\s]+|\((?>[^()\s]+|\((?>[^()\s]+|\((?>\)))*(?>\)))*(?>\)))*(?>\)))*';

	/**
	 * @var  array
	 */
	protected static $emStrongPreparedRegexList = array(
		'' => '{((?:(?<!\\*)\\*\\*\\*(?!\\*)|(?<![a-zA-Z0-9_])___(?!_))(?=\\S)(?![.,:;]\\s)|(?:(?<!\\*)\\*(?!\\*)|(?<![a-zA-Z0-9_])_(?!_))(?=\\S)(?![.,:;]\\s)|(?:(?<!\\*)\\*\\*(?!\\*)|(?<![a-zA-Z0-9_])__(?!_))(?=\\S)(?![.,:;]\\s))}',
		'**' => '{((?:(?<!\\*)\\*(?!\\*)|(?<![a-zA-Z0-9_])_(?!_))(?=\\S)(?![.,:;]\\s)|(?<=\\S)(?<!\\*)\\*\\*(?!\\*))}',
		'__' => '{((?:(?<!\\*)\\*(?!\\*)|(?<![a-zA-Z0-9_])_(?!_))(?=\\S)(?![.,:;]\\s)|(?<=\\S)(?<!_)__(?![a-zA-Z0-9_]))}',
		'*' => '{((?<=\\S)(?<!\\*)\\*(?!\\*)|(?:(?<!\\*)\\*\\*(?!\\*)|(?<![a-zA-Z0-9_])__(?!_))(?=\\S)(?![.,:;]\\s))}',
		'***' => '{((?<=\\S)(?<!\\*)\\*\\*\\*(?!\\*)|(?<=\\S)(?<!\\*)\\*(?!\\*)|(?<=\\S)(?<!\\*)\\*\\*(?!\\*))}',
		'*__' => '{((?<=\\S)(?<!\\*)\\*(?!\\*)|(?<=\\S)(?<!_)__(?![a-zA-Z0-9_]))}',
		'_' => '{((?<=\\S)(?<!_)_(?![a-zA-Z0-9_])|(?:(?<!\\*)\\*\\*(?!\\*)|(?<![a-zA-Z0-9_])__(?!_))(?=\\S)(?![.,:;]\\s))}',
		'_**' => '{((?<=\\S)(?<!_)_(?![a-zA-Z0-9_])|(?<=\\S)(?<!\\*)\\*\\*(?!\\*))}',
		'___' => '{((?<=\\S)(?<!_)___(?![a-zA-Z0-9_])|(?<=\\S)(?<!_)_(?![a-zA-Z0-9_])|(?<=\\S)(?<!_)__(?![a-zA-Z0-9_]))}',
	);

	/**
	 * @var  array
	 */
	protected $urls = array();

	/**
	 * @var  array
	 */
	protected $titles = array();

	/**
	 * @var  array
	 */
	protected $htmlHashes = array();

	/**
	 * @var  array
	 */
	protected $inAnchor = false;

	/**
	 * @var  array
	 */
	protected $footnotes = array();

	/**
	 * @var  array
	 */
	protected $orderedFootnotes = array();

	/**
	 * @var  array
	 */
	protected $abbrDescriptions = array();

	/**
	 * @var  string
	 */
	protected $abbrWordsRegex = '';

	/**
	 * @var  integer
	 */
	protected $footnoteCounter = 1;

	/**
	 * @var  integer
	 */
	protected $listLevel = 0;

	/**
	 * Parses markdown formatted text into HTML (static version).
	 *
	 * @param   string  $text  The markdown formatted text.
	 *
	 * @return  string
	 */
	public static function parse($text)
	{
		$md = new static;

		return $md->transform($text);
	}

	/**
	 * Transforms markdown formatted text into HTML.
	 *
	 * @param   string  $text  The markdown formatted text.
	 *
	 * @return  string
	 */
	public function transform($text)
	{
		//Remove UTF-8 BOM and marker character in input, if present
		$text = preg_replace('{^\xEF\xBB\xBF|\x1A}', '', $text);

		// Standardize line endings
		$text = preg_replace('{\r\n?}', "\n", $text);
		$text .= "\n\n";
		$text = $this->detab($text);

		// Turn block-level HTML blocks into hash entries
		$text = $this->hashHTMLBlocks($text);

		// Strip lines with spaces only
		$text = preg_replace('/^[ ]+$/m', '', $text);
		$text = $this->runDocumentGamut($text);
		$this->cleanUp();

		return $text . "\n";
	}

	/**
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function runDocumentGamut($text)
	{
		$text = $this->doFencedCodeBlocks($text);
		$text = $this->stripFootnotes($text);
		$text = $this->stripLinkDefinitions($text);
		$text = $this->stripAbbreviations($text);
		$text = $this->runBasicBlockGamut($text);
		$text = $this->appendFootnotes($text);

		return $text;
	}

	/**
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function stripLinkDefinitions($text)
	{
		// Link defs are in the form: ^[id]: url "optional title"
		$text = preg_replace_callback(
			'{
				^[ ]{0,' . (static::TAB_WIDTH - 1) . '}\[(.+)\][ ]?:	# id = $1
				  [ ]*
				  \n?				# maybe *one* newline
				  [ ]*
				<?(\S+?)>?			# url = $2
				  [ ]*
				  \n?				# maybe one newline
				  [ ]*
				(?:
					(?<=\s)			# lookbehind for whitespace
					["\'(]
					(.*?)			# title = $3
					[")\']
					[ ]*
				)?					# title is optional
				(?:\n+|\Z)
			}xm',
			array(&$this, '_stripLinkDefinitions_callback'),
			$text
		);

		return $text;
	}

	/**
	 * Callback for stripLinkDefinitions regex replace.
	 *
	 * @param   array  $matches
	 *
	 * @return  string  An empty string.
	 */
	public function _stripLinkDefinitions_callback($matches)
	{
		$link_id = strtolower($matches[1]);
		$this->urls[$link_id] = $matches[2];
		$this->titles[$link_id] = & $matches[3];

		// String that will replace the block
		return '';
	}

	/**
	 * Callback for hashHTMLBlocks regex replace.
	 *
	 * @param   array  $matches
	 *
	 * @return  string  An empty string.
	 */
	public function _hashHTMLBlocks_callback($matches)
	{
		$text = $matches[1];
		$key = $this->hashBlock($text);

		return "\n\n$key\n\n";
	}

	/**
	 * Called whenever a tag must be hashed when a public function insert an atomic
	 * element in the text stream. Passing $text to through this public function gives
	 * a unique text-token which will be reverted back when calling unhash.
	 *
	 * @param   string  $text
	 * @param   string  $boundary  The $boundary argument specify what character should be used to surround
	 *                             the token. By convension, "B" is used for block elements that needs not
	 *                             to be wrapped into paragraph tags at the end, ":" is used for elements
	 *                             that are word separators and "X" is used in the general case.
	 *
	 * @return  string  The string that will replace the tag.
	 */
	public function hashPart($text, $boundary = 'X')
	{
		// Swap back any tag hash found in $text so we do not have to `unhash`
		// multiple times at the end.
		$text = $this->unhash($text);

		// Then hash the block.
		static $i = 0;

		$key = "$boundary\x1A" . ++$i . $boundary;
		$this->htmlHashes[$key] = $text;

		return $key;
	}

	/**
	 * Shortcut public function for hashPart with block-level boundaries.
	 *
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function hashBlock($text)
	{
		return $this->hashPart($text, 'B');
	}

	/**
	 * Run block gamut tranformations.
	 *
	 * We need to escape raw HTML in Markdown source before doing anything
	 * else. This need to be done for each block, and not only at the
	 * begining in the Markdown public function since hashed blocks can be part of
	 * list items and could have been indented. Indented blocks would have
	 * been seen as a code block in a previous pass of hashHTMLBlocks.
	 *
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function runBlockGamut($text)
	{
		$text = $this->hashHTMLBlocks($text);

		return $this->runBasicBlockGamut($text);
	}

	/**
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function runBasicBlockGamut($text)
	{
		$text = $this->doFencedCodeBlocks($text);
		$text = $this->doHeaders($text);
		$text = $this->doTables($text);
		$text = $this->doHorizontalRules($text);
		$text = $this->doLists($text);
		$text = $this->doDefLists($text);
		$text = $this->doCodeBlocks($text);
		$text = $this->doBlockQuotes($text);
		$text = $this->formParagraphs($text);

		return $text;
	}

	/**
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function doHorizontalRules($text)
	{
		return preg_replace(
			'{
				^[ ]{0,3}	# Leading space
				([-*_])		# $1: First marker
				(?>			# Repeated marker group
					[ ]{0,2}	# Zero, one, or two spaces.
					\1			# Marker character
				){2,}		# Group repeated at least twice
				[ ]*		# Tailing spaces
				$			# End of line.
			}mx',
			"\n" . $this->hashBlock("<hr />") . "\n", $text);
	}

	/**
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function runSpanGamut($text)
	{
		$text = $this->parseSpan($text);
		$text = $this->doFootnotes($text);
		$text = $this->doImages($text);
		$text = $this->doAnchors($text);
		$text = $this->doAutoLinks($text);
		$text = $this->encodeAmpsAndAngles($text);
		$text = $this->doItalicsAndBold($text);
		$text = $this->doHardBreaks($text);
		$text = $this->doAbbreviations($text);
		return $text;
	}

	/**
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function doHardBreaks($text)
	{
		return preg_replace_callback(
			'/ {2,}\n/',
			array(&$this, '_doHardBreaks_callback'),
			$text
		);
	}

	/**
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function _doHardBreaks_callback($matches)
	{
		return $this->hashPart("<br />\n");
	}

	/**
	 * Turns Markdown link shortcuts into XHTML <a> tags.
	 *
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function doAnchors($text)
	{
		if ($this->inAnchor)
		{
			return $text;
		}

		$this->inAnchor = true;

		//
		// First, handle reference-style links: [link text] [id]
		//
		$text = preg_replace_callback(
			'{
				(					# wrap whole match in $1
				  \[
					(' . static::NESTED_BRACKETS_REGEX . ')	# link text = $2
				  \]

				  [ ]?				# one optional space
				  (?:\n[ ]*)?		# one optional newline followed by spaces

				  \[
					(.*?)		# id = $3
				  \]
				)
			}xs',
			array(&$this, '_doAnchors_reference_callback'),
			$text
		);

		//
		// Next, inline-style links: [link text](url "optional title")
		//
		$text = preg_replace_callback(
			'{
				(				# wrap whole match in $1
				  \[
					(' . static::NESTED_BRACKETS_REGEX . ')	# link text = $2
				  \]
				  \(			# literal paren
					[ ]*
					(?:
						<(\S*)>	# href = $3
					|
						(' . static::NESTED_URL_PARENTHESIS_REGEX . ')	# href = $4
					)
					[ ]*
					(			# $5
					  ([\'"])	# quote char = $6
					  (.*?)		# Title = $7
					  \6		# matching quote
					  [ ]*	# ignore any spaces/tabs between closing quote and )
					)?			# title is optional
				  \)
				)
			}xs',
			array(&$this, '_doAnchors_inline_callback'),
			$text
		);

		$this->inAnchor = false;

		return $text;
	}

	/**
	 * Turn Markdown image shortcuts into <img> tags.
	 *
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function doImages($text)
	{
		//
		// First, handle reference-style labeled images: ![alt text][id]
		//
		$text = preg_replace_callback(
			'{
				(				# wrap whole match in $1
				  !\[
					(' . static::NESTED_BRACKETS_REGEX . ')		# alt text = $2
				  \]

				  [ ]?				# one optional space
				  (?:\n[ ]*)?		# one optional newline followed by spaces

				  \[
					(.*?)		# id = $3
				  \]

				)
			}xs',
			array(&$this, '_doImages_reference_callback'),
			$text
		);

		//
		// Next, handle inline images:  ![alt text](url "optional title")
		// Don't forget: encode * and _
		//
		$text = preg_replace_callback(
			'{
				(				# wrap whole match in $1
				  !\[
					(' . static::NESTED_BRACKETS_REGEX . ')		# alt text = $2
				  \]
				  \s?			# One optional whitespace character
				  \(			# literal paren
					[ ]*
					(?:
						<(\S*)>	# src url = $3
					|
						(' . static::NESTED_URL_PARENTHESIS_REGEX . ')	# src url = $4
					)
					[ ]*
					(			# $5
					  ([\'"])	# quote char = $6
					  (.*?)		# title = $7
					  \6		# matching quote
					  [ ]*
					)?			# title is optional
				  \)
				)
			}xs',
			array(&$this, '_doImages_inline_callback'),
			$text
		);

		return $text;
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _doImages_reference_callback($matches)
	{
		$whole_match = $matches[1];
		$alt_text = $matches[2];
		$link_id = strtolower($matches[3]);

		if ($link_id == '')
		{
			$link_id = strtolower($alt_text); # for shortcut links like ![this][].
		}

		$alt_text = $this->encodeAttribute($alt_text);

		if (isset($this->urls[$link_id]))
		{
			$url = $this->encodeAttribute($this->urls[$link_id]);
			$result = "<img src=\"$url\" alt=\"$alt_text\"";

			if (isset($this->titles[$link_id]))
			{
				$title = $this->titles[$link_id];
				$title = $this->encodeAttribute($title);
				$result .= " title=\"$title\"";
			}

			$result .= " />";
			$result = $this->hashPart($result);
		}
		else
		{
			// If there's no such link ID, leave intact:
			$result = $whole_match;
		}

		return $result;
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _doImages_inline_callback($matches)
	{
		$whole_match = $matches[1];
		$alt_text = $matches[2];
		$url = $matches[3] == '' ? $matches[4] : $matches[3];
		$title = & $matches[7];

		$alt_text = $this->encodeAttribute($alt_text);
		$url = $this->encodeAttribute($url);
		$result = "<img src=\"$url\" alt=\"$alt_text\"";

		if (isset($title))
		{
			$title = $this->encodeAttribute($title);
			$result .= " title=\"$title\""; # $title already quoted
		}

		$result .= " />";

		return $this->hashPart($result);
	}

	/**
	 * Form HTML ordered (numbered) and unordered (bulleted) lists.
	 *
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function doLists($text)
	{
		$less_than_tab = static::TAB_WIDTH - 1;

		// Re-usable patterns to match list item bullets and number markers:
		$marker_ul_re = '[*+-]';
		$marker_ol_re = '\d+[.]';
		$marker_any_re = "(?:$marker_ul_re|$marker_ol_re)";

		$markers_relist = array($marker_ul_re, $marker_ol_re);

		foreach ($markers_relist as $marker_re)
		{
			// Re-usable pattern to match any entirel ul or ol list:
			$whole_list_re = '
				(								# $1 = whole list
				  (								# $2
					[ ]{0,' . $less_than_tab . '}
					(' . $marker_re . ')			# $3 = first list item marker
					[ ]+
				  )
				  (?s:.+?)
				  (								# $4
					  \z
					|
					  \n{2,}
					  (?=\S)
					  (?!						# Negative lookahead for another list item marker
						[ ]*
						' . $marker_re . '[ ]+
					  )
				  )
				)
			'; // mx
			// We use a different prefix before nested lists than top-level lists.
			// See extended comment in _ProcessListItems().

			if ($this->listLevel)
			{
				$text = preg_replace_callback(
					'{
						^
						' . $whole_list_re . '
					}mx',
					array(&$this, '_doLists_callback'),
					$text
				);
			}
			else
			{
				$text = preg_replace_callback(
					'{
						(?:(?<=\n)\n|\A\n?) # Must eat the newline
						' . $whole_list_re . '
					}mx',
					array(&$this, '_doLists_callback'),
					$text
				);
			}
		}

		return $text;
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _doLists_callback($matches)
	{
		# Re-usable patterns to match list item bullets and number markers:
		$marker_ul_re = '[*+-]';
		$marker_ol_re = '\d+[.]';
		$marker_any_re = "(?:$marker_ul_re|$marker_ol_re)";

		$list = $matches[1];
		$list_type = preg_match("/$marker_ul_re/", $matches[3]) ? "ul" : "ol";

		$marker_any_re = ( $list_type == "ul" ? $marker_ul_re : $marker_ol_re );

		$list .= "\n";
		$result = $this->processListItems($list, $marker_any_re);

		$result = $this->hashBlock("<$list_type>\n" . $result . "</$list_type>");
		return "\n" . $result . "\n\n";
	}

	/**
	 * Process the contents of a single ordered or unordered list, splitting it into individual list items.
	 *
	 * @param   string  $list_str
	 * @param   string  $marker_any_re
	 *
	 * @return  string
	 */
	public function processListItems($list_str, $marker_any_re)
	{
		// The $this->list_level global keeps track of when we're inside a list.
		// Each time we enter a list, we increment it; when we leave a list,
		// we decrement. If it's zero, we're not in a list anymore.
		//
		// We do this because when we're not inside a list, we want to treat
		// something like this:
		//
		//		I recommend upgrading to version
		//		8. Oops, now this line is treated
		//		as a sub-list.
		//
		// As a single paragraph, despite the fact that the second line starts
		// with a digit-period-space sequence.
		//
		// Whereas when we're inside a list (or sub-list), that line will be
		// treated as the start of a sub-list. What a kludge, huh? This is
		// an aspect of Markdown's syntax that's hard to parse perfectly
		// without resorting to mind-reading. Perhaps the solution is to
		// change the syntax rules such that sub-lists must start with a
		// starting cardinal number; e.g. "1." or "a.".

		$this->listLevel++;

		# trim trailing blank lines:
		$list_str = preg_replace("/\n{2,}\\z/", "\n", $list_str);

		$list_str = preg_replace_callback(
			'{
				(\n)?							# leading line = $1
				(^[ ]*)							# leading whitespace = $2
				(' . $marker_any_re . '				# list marker and space = $3
					(?:[ ]+|(?=\n))	# space only required if item is not empty
				)
				((?s:.*?))						# list item text   = $4
				(?:(\n+(?=\n))|\n)				# tailing blank line = $5
				(?= \n* (\z | \2 (' . $marker_any_re . ') (?:[ ]+|(?=\n))))
			}xm',
			array(&$this, '_processListItems_callback'),
			$list_str
		);

		$this->listLevel--;

		return $list_str;
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _processListItems_callback($matches)
	{
		$item = $matches[4];
		$leading_line = & $matches[1];
		$leading_space = & $matches[2];
		$marker_space = $matches[3];
		$tailing_blank_line = & $matches[5];

		if ($leading_line || $tailing_blank_line || preg_match('/\n{2,}/', $item))
		{
			// Replace marker with the appropriate whitespace indentation
			$item = $leading_space . str_repeat(' ', strlen($marker_space)) . $item;
			$item = $this->runBlockGamut($this->outdent($item) . "\n");
		}
		else
		{
			// Recursion for sub-lists:
			$item = $this->doLists($this->outdent($item));
			$item = preg_replace('/\n+$/', '', $item);
			$item = $this->runSpanGamut($item);
		}

		return "<li>" . $item . "</li>\n";
	}

	/**
	 * Process Markdown `<pre><code>` blocks.
	 *
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function doCodeBlocks($text)
	{
		$text = preg_replace_callback(
			'{
				(?:\n\n|\A\n?)
				(				# $1 = the code block -- one or more lines, starting with a space/tab
				  (?>
					[ ]{' . static::TAB_WIDTH . '}  # Lines must start with a tab or a tab-width of spaces
					.*\n+
				  )+
				)
				((?=^[ ]{0,' . static::TAB_WIDTH . '}\S)|\Z)	# Lookahead for non-space at line-start, or end of doc
			}xm',
			array(&$this, '_doCodeBlocks_callback'),
			$text
		);

		return $text;
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _doCodeBlocks_callback($matches)
	{
		$codeblock = $matches[1];

		$codeblock = $this->outdent($codeblock);
		$codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);

		# trim leading newlines and trailing newlines
		$codeblock = preg_replace('/\A\n+|\n+\z/', '', $codeblock);

		$codeblock = "<pre><code>$codeblock\n</code></pre>";

		return "\n\n" . $this->hashBlock($codeblock) . "\n\n";
	}

	/**
	 * Create a code span markup for $code. Called from handleSpanToken.
	 *
	 * @param   string  $code
	 *
	 * @return  string
	 */
	public function makeCodeSpan($code)
	{
		$code = htmlspecialchars(trim($code), ENT_NOQUOTES);

		return $this->hashPart("<code>$code</code>");
	}

	/**
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function doItalicsAndBold($text)
	{
		$token_stack = array('');
		$text_stack = array('');
		$em = '';
		$strong = '';
		$tree_char_em = false;

		while (1)
		{
			//
			// Get prepared regular expression for seraching emphasis tokens
			// in current context.
			//
			$token_re = static::$emStrongPreparedRegexList["$em$strong"];

			//
			// Each loop iteration seach for the next emphasis token.
			// Each token is then passed to handleSpanToken.
			//
			$parts = preg_split($token_re, $text, 2, PREG_SPLIT_DELIM_CAPTURE);
			$text_stack[0] .= $parts[0];
			$token = & $parts[1];
			$text = & $parts[2];

			if (empty($token))
			{
				// Reached end of text span: empty stack without emitting.
				// any more emphasis.
				while ($token_stack[0])
				{
					$text_stack[1] .= array_shift($token_stack);
					$text_stack[0] .= array_shift($text_stack);
				}

				break;
			}

			$token_len = strlen($token);
			if ($tree_char_em)
			{
				// Reached closing marker while inside a three-char emphasis.
				if ($token_len == 3)
				{
					// Three-char closing marker, close em and strong.
					array_shift($token_stack);
					$span = array_shift($text_stack);
					$span = $this->runSpanGamut($span);
					$span = "<strong><em>$span</em></strong>";
					$text_stack[0] .= $this->hashPart($span);
					$em = '';
					$strong = '';
				}
				else
				{
					// Other closing marker: close one em or strong and
					// change current token state to match the other
					$token_stack[0] = str_repeat($token{0}, 3 - $token_len);
					$tag = $token_len == 2 ? "strong" : "em";
					$span = $text_stack[0];
					$span = $this->runSpanGamut($span);
					$span = "<$tag>$span</$tag>";
					$text_stack[0] = $this->hashPart($span);
					$$tag = ''; # $$tag stands for $em or $strong
				}

				$tree_char_em = false;
			}
			else if ($token_len == 3)
			{
				if ($em)
				{
					// Reached closing marker for both em and strong.
					// Closing strong marker:
					for ($i = 0; $i < 2; ++$i)
					{
						$shifted_token = array_shift($token_stack);
						$tag = strlen($shifted_token) == 2 ? "strong" : "em";
						$span = array_shift($text_stack);
						$span = $this->runSpanGamut($span);
						$span = "<$tag>$span</$tag>";
						$text_stack[0] .= $this->hashPart($span);
						$$tag = ''; # $$tag stands for $em or $strong
					}
				}
				else
				{
					// Reached opening three-char emphasis marker. Push on token
					// stack; will be handled by the special condition above.
					$em = $token{0};
					$strong = "$em$em";
					array_unshift($token_stack, $token);
					array_unshift($text_stack, '');
					$tree_char_em = true;
				}
			}
			else if ($token_len == 2)
			{
				if ($strong)
				{
					// Unwind any dangling emphasis marker:
					if (strlen($token_stack[0]) == 1) {
						$text_stack[1] .= array_shift($token_stack);
						$text_stack[0] .= array_shift($text_stack);
					}

					// Closing strong marker:
					array_shift($token_stack);
					$span = array_shift($text_stack);
					$span = $this->runSpanGamut($span);
					$span = "<strong>$span</strong>";
					$text_stack[0] .= $this->hashPart($span);
					$strong = '';
				}
				else
				{
					array_unshift($token_stack, $token);
					array_unshift($text_stack, '');
					$strong = $token;
				}
			}
			else
			{
				// Here $token_len == 1
				if ($em)
				{
					if (strlen($token_stack[0]) == 1)
					{
						# Closing emphasis marker:
						array_shift($token_stack);
						$span = array_shift($text_stack);
						$span = $this->runSpanGamut($span);
						$span = "<em>$span</em>";
						$text_stack[0] .= $this->hashPart($span);
						$em = '';
					}
					else
					{
						$text_stack[0] .= $token;
					}
				}
				else
				{
					array_unshift($token_stack, $token);
					array_unshift($text_stack, '');
					$em = $token;
				}
			}
		}

		return $text_stack[0];
	}

	/**
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function doBlockQuotes($text)
	{
		$text = preg_replace_callback(
			'/
			  (								# Wrap whole match in $1
				(?>
				  ^[ ]*>[ ]?			# ">" at the start of a line
					.+\n					# rest of the first line
				  (.+\n)*					# subsequent consecutive lines
				  \n*						# blanks
				)+
			  )
			/xm',
			array(&$this, '_doBlockQuotes_callback'),
			$text
		);

		return $text;
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _doBlockQuotes_callback($matches)
	{
		$bq = $matches[1];
		// trim one level of quoting - trim whitespace-only lines
		$bq = preg_replace('/^[ ]*>[ ]?|^[ ]+$/m', '', $bq);
		$bq = $this->runBlockGamut($bq);  # recurse

		$bq = preg_replace('/^/m', "  ", $bq);
		// These leading spaces cause problem with <pre> content,
		// so we need to fix that:
		$bq = preg_replace_callback('{(\s*<pre>.+?</pre>)}sx',
				array(&$this, '_DoBlockQuotes_callback2'), $bq);

		return "\n" . $this->hashBlock("<blockquote>\n$bq\n</blockquote>") . "\n\n";
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _doBlockQuotes_callback2($matches)
	{
		$pre = $matches[1];
		$pre = preg_replace('/^  /m', '', $pre);

		return $pre;
	}

	/**
	 * Encode text for a double-quoted HTML attribute.
	 *
	 * This function is *not* suitable for attributes enclosed in single quotes.
	 *
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function encodeAttribute($text)
	{
		$text = $this->encodeAmpsAndAngles($text);
		$text = str_replace('"', '&quot;', $text);

		return $text;
	}

	/**
	 * Smart processing for ampersands and angle brackets that need to be encoded.
	 *
	 * Valid character entities are left alone unless the no-entities mode is set.
	 *
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function encodeAmpsAndAngles($text)
	{
		if (static::NO_ENTITIES)
		{
			$text = str_replace('&', '&amp;', $text);
		}
		else
		{
			// Ampersand-encoding based entirely on Nat Irons's Amputator
			// MT plugin: <http://bumppo.net/projects/amputator/>
			$text = preg_replace(
				'/&(?!#?[xX]?(?:[0-9a-fA-F]+|\w+);)/',
				'&amp;',
				$text
			);
		}

		// Encode remaining <'s
		$text = str_replace('<', '&lt;', $text);

		return $text;
	}

	/**
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function doAutoLinks($text)
	{
		$text = preg_replace_callback(
			'{<((https?|ftp|dict):[^\'">\s]+)>}i',
			array(&$this, '_doAutoLinks_url_callback'),
			$text
		);

		// Email addresses: <address@domain.foo>
		$text = preg_replace_callback(
			'{
				<
				(?:mailto:)?
				(
					[-.\w\x80-\xFF]+
					\@
					[-a-z0-9\x80-\xFF]+(\.[-a-z0-9\x80-\xFF]+)*\.[a-z]+
				)
				>
			}xi',
			array(&$this, '_doAutoLinks_email_callback'),
			$text
		);

		return $text;
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _doAutoLinks_url_callback($matches)
	{
		$url = $this->encodeAttribute($matches[1]);
		$link = "<a href=\"$url\">$url</a>";

		return $this->hashPart($link);
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _doAutoLinks_email_callback($matches)
	{
		$address = $matches[1];
		$link = $this->encodeEmailAddress($address);

		return $this->hashPart($link);
	}

	/**
	 * @param   string  $addr
	 *
	 * @return  string
	 */
	public function encodeEmailAddress($addr)
	{
		//
		// Input: an email address, e.g. "foo@example.com"
		//
		// Output: the email address as a mailto link, with each character
		//		of the address encoded as either a decimal or hex entity, in
		//		the hopes of foiling most address harvesting spam bots. E.g.:
		//
		//	  <p><a href="&#109;&#x61;&#105;&#x6c;&#116;&#x6f;&#58;&#x66;o&#111;
		//		&#x40;&#101;&#x78;&#97;&#x6d;&#112;&#x6c;&#101;&#46;&#x63;&#111;
		//		&#x6d;">&#x66;o&#111;&#x40;&#101;&#x78;&#97;&#x6d;&#112;&#x6c;
		//		&#101;&#46;&#x63;&#111;&#x6d;</a></p>
		//
		// Based by a filter by Matthew Wickline, posted to BBEdit-Talk.
		// With some optimizations by Milian Wolff.
		//
		$addr = "mailto:" . $addr;
		$chars = preg_split('/(?<!^)(?!$)/', $addr);
		$seed = (int) abs(crc32($addr) / strlen($addr)); # Deterministic seed.

		foreach ($chars as $key => $char)
		{
			$ord = ord($char);
			// Ignore non-ascii chars.
			if ($ord < 128)
			{
				// Pseudo-random function.
				$r = ($seed * (1 + $key)) % 100;
				// roughly 10% raw, 45% hex, 45% dec
				// '@' *must* be encoded. I insist.
				if ($r > 90 && $char != '@') {
					/* do nothing */
				}
				else if ($r < 45)
				{
					$chars[$key] = '&#x' . dechex($ord) . ';';
				}
				else
				{
					$chars[$key] = '&#' . $ord . ';';
				}
			}
		}

		$addr = implode('', $chars);
		$text = implode('', array_slice($chars, 7)); # text without `mailto:`
		$addr = "<a href=\"$addr\">$text</a>";

		return $addr;
	}

	/**
	 * @param   string  $str
	 *
	 * @return  string
	 */
	public function parseSpan($str)
	{
		//
		// Take the string $str and parse it into tokens, hashing embeded HTML,
		// escaped characters and handling code spans.
		//
		$output = '';

		$span_re = '{
			(
					\\\\' . static::ESCAPE_CHARS . '
				|
					(?<![`\\\\])
					`+						# code span marker
			' . ( static::NO_MARKUP ? '' : '
				|
					<!--	.*?	 -->		# comment
				|
					<\?.*?\?> | <%.*?%>		# processing instruction
				|
					<[/!$]?[-a-zA-Z0-9:]+	# regular tags
					(?>
						\s
						(?>[^"\'>]+|"[^"]*"|\'[^\']*\')*
					)?
					>
				') . '
			)
		}xs';

		while (1)
		{
			//
			// Each loop iteration seach for either the next tag, the next
			// openning code span marker, or the next escaped character.
			// Each token is then passed to handleSpanToken.
			//
			$parts = preg_split($span_re, $str, 2, PREG_SPLIT_DELIM_CAPTURE);

			// Create token from text preceding tag.
			if ($parts[0] != '')
			{
				$output .= $parts[0];
			}

			// Check if we reach the end.
			if (isset($parts[1]))
			{
				$output .= $this->handleSpanToken($parts[1], $parts[2]);
				$str = $parts[2];
			}
			else
			{
				break;
			}
		}

		return $output;
	}

	/**
	 * @param   string  $token
	 * @param   string  $str
	 *
	 * @return  string
	 */
	public function handleSpanToken($token, &$str)
	{
		//
		// Handle $token provided by parseSpan by determining its nature and
		// returning the corresponding value that should replace it.
		//
		switch ($token{0})
		{
			case "\\":
				return $this->hashPart("&#" . ord($token{1}) . ";");

			case "`":
				// Search for end marker in remaining text.
				if (preg_match('/^(.*?[^`])' . preg_quote($token) . '(?!`)(.*)$/sm', $str, $matches))
				{
					$str = $matches[2];
					$codespan = $this->makeCodeSpan($matches[1]);
					return $this->hashPart($codespan);
				}

				return $token; // return as text since no ending marker found.

			default:
				return $this->hashPart($token);
		}
	}

	/**
	 * Removes one level of line-leading tabs or spaces.
	 *
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function outdent($text)
	{
		return preg_replace(
			'/^(\t|[ ]{1,' . static::TAB_WIDTH . '})/m',
			'',
			$text
		);
	}

	/**
	 * Replace tabs with the appropriate amount of space.
	 *
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function detab($text)
	{
		// For each line we separate the line in blocks delemited by
		// tab characters. Then we reconstruct every line by adding the
		// appropriate number of space between each blocks.

		$text = preg_replace_callback(
			'/^.*\t.*$/m',
			array(&$this, '_detab_callback'),
			$text
		);

		return $text;
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _detab_callback($matches)
	{
		$line = $matches[0];

		// Split in blocks.
		$blocks = explode("\t", $line);

		// Add each blocks to the line.
		$line = $blocks[0];

		// Do not add first block twice.
		unset($blocks[0]);

		foreach ($blocks as $block)
		{
			// Calculate amount of space, insert spaces, insert block.
			$amount = static::TAB_WIDTH - mb_strlen($line, 'UTF-8') % static::TAB_WIDTH;
			$line .= str_repeat(" ", $amount) . $block;
		}

		return $line;
	}

	/**
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function unhash($text)
	{
		#
		# Swap back in all the tags hashed by _HashHTMLBlocks.
		#
		return preg_replace_callback(
			'/(.)\x1A[0-9]+\1/',
			array(&$this, '_unhash_callback'),
			$text
		);
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _unhash_callback($matches)
	{
		return $this->htmlHashes[$matches[0]];
	}

	/**
	 * @return  void
	 */
	public function cleanUp()
	{
		// Setting up Extra-specific variables.
		//
		// Clear global hashes.
		$this->urls = array();
		$this->titles = array();
		$this->htmlHashes = array();

		$in_anchor = false;

		$this->footnotes = array();
		$this->orderedFootnotes = array();
		$this->abbrDescriptions = array();
		$this->abbrWordsRegex = '';
		$this->footnoteCounter = 1;
	}

	/**
	 * Hashify HTML Blocks and "clean tags".
	 *
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function hashHTMLBlocks($text)
	{
		// We only want to do this for block-level HTML tags, such as headers,
		// lists, and tables. That's because we still want to wrap <p>s around
		// "paragraphs" that are wrapped in non-block-level tags, such as anchors,
		// phrase emphasis, and spans. The list of tags we're looking for is
		// hard-coded.
		//
		// This works by calling _HashHTMLBlocks_InMarkdown, which then calls
		// _HashHTMLBlocks_InHTML when it encounter block tags. When the markdown="1"
		// attribute is found whitin a tag, _HashHTMLBlocks_InHTML calls back
		//  _HashHTMLBlocks_InMarkdown to handle the Markdown syntax within the tag.
		// These two functions are calling each other. It's recursive!
		//
		//
		// Call the HTML-in-Markdown hasher.
		//
		list($text, ) = $this->_hashHTMLBlocks_inMarkdown($text);

		return $text;
	}


	/**
	 * Parses markdown text, calling _HashHTMLBlocks_InHTML for block tags.
	 *
	 * @param   string  $text
	 * @param   string  $indent            The number of space to be ignored when checking for code
	 *                                     blocks. This is important because if we don't take the indent into
	 *                                     account, something like this (which looks right) won't work as expected:
	 *                                     <div>
	 *                                         <div markdown="1">
	 *                                             Hello World.  <-- Is this a Markdown code block or text?
	 *                                         </div>  <-- Is this a Markdown code block or a real tag?
	 *                                     <div>
	 *
	 *                                     If you don't like this, just don't indent the tag on which
	 *                                     you apply the markdown="1" attribute.
	 * @param   string  $enclosing_tag_re  If $enclosing_tag_re is not empty, stops at the first unmatched closing
	 *                                     tag with that name. Nested tags supported.
	 * @param   string  $span              If $span is true, text inside must treated as span. So any double
	 *                                     newline will be replaced by a single newline so that it does not create
	 *                                     paragraphs.
	 *
	 * @return  array  An array of that form: ( processed text , remaining text )
	 */
	public function _hashHTMLBlocks_inMarkdown($text, $indent = 0, $enclosing_tag_re = '', $span = false)
	{
		if ($text === '')
		{
			return array('', '');
		}

		# Regex to check for the presense of newlines around a block tag.
		$newline_before_re = '/(?:^\n?|\n\n)*$/';
		$newline_after_re =
			'{
				^						# Start of text following the tag.
				(?>[ ]*<!--.*?-->)?		# Optional comment.
				[ ]*\n					# Must be followed by newline.
			}xs';

		# Regex to match any tag.
		$block_tag_re =
			'{
				(					# $2: Capture hole tag.
					</?					# Any opening or closing tag.
						(?>				# Tag name.
							' . static::BLOCK_TAGS_REGEX . '			|
							' . static::CONTEXT_BLOCK_TAGS_REGEX . '	|
							' . static::CLEAN_TAGS_REGEX . '			|
							(?!\s)' . $enclosing_tag_re . '
						)
						(?:
							(?=[\s"\'/a-zA-Z0-9])	# Allowed characters after tag name.
							(?>
								".*?"		|	# Double quotes (can contain `>`)
								\'.*?\'   	|	# Single quotes (can contain `>`)
								.+?				# Anything but quotes and `>`.
							)*?
						)?
					>					# End of tag.
				|
					<!--	.*?	 -->	# HTML Comment
				|
					<\?.*?\?> | <%.*?%>	# Processing instruction
				|
					<!\[CDATA\[.*?\]\]>	# CData Block
				|
					# Code span marker
					`+
				' . (!$span ? ' # If not in span.
				|
					# Indented code block
					(?> ^[ ]*\n? | \n[ ]*\n )
					[ ]{' . ($indent + 4) . '}[^\n]* \n
					(?>
						(?: [ ]{' . ($indent + 4) . '}[^\n]* | [ ]* ) \n
					)*
				|
					# Fenced code block marker
					(?> ^ | \n )
					[ ]{' . ($indent) . '}~~~+[ ]*\n
				' : '' ) . ' # End (if not is span).
				)
			}xs';


		// Current depth inside the tag tree.
		$depth = 0;

		// Parsed text that will be returned.
		$parsed = '';

		//
		// Loop through every tag until we find the closing tag of the parent
		// or loop until reaching the end of text if no parent tag specified.
		//
		do
		{
			//
			// Split the text using the first $tag_match pattern found.
			// Text before  pattern will be first in the array, text after
			// pattern will be at the end, and between will be any catches made
			// by the pattern.
			//
			$parts = preg_split($block_tag_re, $text, 2,
					PREG_SPLIT_DELIM_CAPTURE);

			// If in Markdown span mode, add a empty-string span-level hash
			// after each newline to prevent triggering any block element.
			if ($span)
			{
				$void = $this->hashPart("", ':');
				$newline = "$void\n";
				$parts[0] = $void . str_replace("\n", $newline, $parts[0]) . $void;
			}

			// Text before current tag.
			$parsed .= $parts[0];

			// If end of $text has been reached. Stop loop.
			if (count($parts) < 3)
			{
				$text = '';
				break;
			}

			// Tag to handle.
			$tag = $parts[1];

			// Remaining text after current tag.
			$text = $parts[2];

			// For use in a regular expression.
			$tag_re = preg_quote($tag);

			//
			// Check for: Code span marker
			//
			if ($tag{0} == '`')
			{
				// Find corresponding end marker.
				$tag_re = preg_quote($tag);

				if (preg_match('{^(?>.+?|\n(?!\n))*?(?<!`)' . $tag_re . '(?!`)}', $text, $matches))
				{
					// End marker found: pass text unchanged until marker.
					$parsed .= $tag . $matches[0];
					$text = substr($text, strlen($matches[0]));
				}
				else
				{
					// Unmatched marker: just skip it.
					$parsed .= $tag;
				}
			}

			//
			// Check for: Indented code block or fenced code block marker.
			//
			else if ($tag{0} == "\n" || $tag{0} == '~')
			{
				if ($tag{1} == "\n" || $tag{1} == ' ')
				{
					// Indented code block: pass it unchanged, will be handled later.
					$parsed .= $tag;
				}
				else
				{
					// Fenced code block marker: find matching end marker.
					$tag_re = preg_quote(trim($tag));

					if (preg_match('{^(?>.*\n)+?' . $tag_re . ' *\n}', $text, $matches))
					{
						// End marker found: pass text unchanged until marker.
						$parsed .= $tag . $matches[0];
						$text = substr($text, strlen($matches[0]));
					}
					else
					{
						// No end marker: just skip it.
						$parsed .= $tag;
					}
				}
			}

			//
			// Check for:
			//  Opening Block level tag or
			//  Opening Context Block tag (like ins and del)
			//  used as a block tag (tag is alone on it's line).
			//
			else if (preg_match('{^<(?:' . static::BLOCK_TAGS_REGEX . ')\b}', $tag) ||
				( preg_match('{^<(?:' . static::CONTEXT_BLOCK_TAGS_REGEX . ')\b}',
					$tag) &&
				preg_match($newline_before_re, $parsed) &&
				preg_match($newline_after_re, $text) )
			)
			{
				// Need to parse tag and following text using the HTML parser.
				list($block_text, $text) = $this->_hashHTMLBlocks_inHTML($tag . $text, "hashBlock", true);

				// Make sure it stays outside of any paragraph by adding newlines.
				$parsed .= "\n\n$block_text\n\n";
			}

			//
			// Check for: Clean tag (like script, math) HTML Comments, processing instructions.
			//
			else if (preg_match('{^<(?:' . static::CLEAN_TAGS_REGEX . ')\b}', $tag) ||
				$tag{1} == '!' || $tag{1} == '?') {
				// Need to parse tag and following text using the HTML parser.
				// (don't check for markdown attribute)
				list($block_text, $text) = $this->_hashHTMLBlocks_inHTML($tag . $text, 'hashClean', false);

				$parsed .= $block_text;
			}

			//
			// Check for: Tag with same name as enclosing tag.
			//
			else if ($enclosing_tag_re !== '' &&
				# Same name as enclosing tag.
				preg_match('{^</?(?:' . $enclosing_tag_re . ')\b}', $tag)) {
				//
				// Increase/decrease nested tag count.
				//
				if ($tag{1} == '/')
				{
					$depth--;
				}
				else if ($tag{strlen($tag) - 2} != '/')
				{
					$depth++;
				}

				if ($depth < 0)
				{
					//
					// Going out of parent element. Clean up and break so we
					// return to the calling function.
					//
					$text = $tag . $text;
					break;
				}

				$parsed .= $tag;
			}
			else
			{
				$parsed .= $tag;
			}
		}
		while ($depth >= 0);

		return array($parsed, $text);
	}

	/**
	 * Parse HTML, calling _HashHTMLBlocks_InMarkdown for block tags.
	 *
	 * @param   string  $text
	 * @param   string  $hash_method  Calls $hash_method to convert any blocks.  Stops when the first opening tag closes.
	 * @param   string  $md_attr      Indicate if the use of the `markdown="1"` attribute is allowed
	 *                                (it is not inside clean tags).
	 * @return  array  An array of that form: ( processed text , remaining text )
	 */
	public function _hashHTMLBlocks_inHTML($text, $hash_method, $md_attr)
	{
		if ($text === '')
		{
			return array('', '');
		}

		// Regex to match `markdown` attribute inside of a tag.
		$markdown_attr_re = '
			{
				\s*			# Eat whitespace before the `markdown` attribute
				markdown
				\s*=\s*
				(?>
					(["\'])		# $1: quote delimiter
					(.*?)		# $2: attribute value
					\1			# matching delimiter
				|
					([^\s>]*)	# $3: unquoted attribute value
				)
				()				# $4: make $3 always defined (avoid warnings)
			}xs';

		// Regex to match any tag.
		$tag_re = '{
				(					# $2: Capture hole tag.
					</?					# Any opening or closing tag.
						[\w:$]+			# Tag name.
						(?:
							(?=[\s"\'/a-zA-Z0-9])	# Allowed characters after tag name.
							(?>
								".*?"		|	# Double quotes (can contain `>`)
								\'.*?\'   	|	# Single quotes (can contain `>`)
								.+?				# Anything but quotes and `>`.
							)*?
						)?
					>					# End of tag.
				|
					<!--	.*?	 -->	# HTML Comment
				|
					<\?.*?\?> | <%.*?%>	# Processing instruction
				|
					<!\[CDATA\[.*?\]\]>	# CData Block
				)
			}xs';

		// Save original text in case of faliure.
		$original_text = $text;

		// Current depth inside the tag tree.
		$depth = 0;

		// Temporary text holder for current text.
		$block_text = '';

		// Parsed text that will be returned.
		$parsed = '';

		//
		// Get the name of the starting tag.
		// (This pattern makes $base_tag_name_re safe without quoting.)
		//
		if (preg_match('/^<([\w:$]*)\b/', $text, $matches))
		{
			$base_tag_name_re = $matches[1];
		}

		//
		// Loop through every tag until we find the corresponding closing tag.
		//
		do
		{
			//
			// Split the text using the first $tag_match pattern found.
			// Text before  pattern will be first in the array, text after
			// pattern will be at the end, and between will be any catches made
			// by the pattern.
			//
			$parts = preg_split($tag_re, $text, 2, PREG_SPLIT_DELIM_CAPTURE);

			if (count($parts) < 3)
			{
				//
				// End of $text reached with unbalenced tag(s).
				// In that case, we return original text unchanged and pass the
				// first character as filtered to prevent an infinite loop in the
				// parent function.
				//
				return array($original_text{0}, substr($original_text, 1));
			}

			// Text before current tag.
			$block_text .= $parts[0];

			// Tag to handle.
			$tag = $parts[1];

			// Remaining text after current tag.
			$text = $parts[2];

			//
			// Check for: Auto-close tag (like <hr/>) Comments and Processing Instructions.
			//
			if (preg_match('{^</?(?:' . static::AUTO_CLOSE_TAGS_REGEX . ')\b}', $tag) ||
				$tag{1} == '!' || $tag{1} == '?')
			{
				// Just add the tag to the block as if it was text.
				$block_text .= $tag;
			}
			else
			{
				//
				// Increase/decrease nested tag count. Only do so if
				// the tag's name match base tag's.
				//
				if (preg_match('{^</?' . $base_tag_name_re . '\b}', $tag))
				{
					if ($tag{1} == '/')
					{
						$depth--;
					}
					else if ($tag{strlen($tag) - 2} != '/')
					{
						$depth++;
					}
				}

				//
				// Check for `markdown="1"` attribute and handle it.
				//
				if ($md_attr &&
					preg_match($markdown_attr_re, $tag, $attr_m) &&
					preg_match('/^1|block|span$/', $attr_m[2] . $attr_m[3]))
				{
					// Remove `markdown` attribute from opening tag.
					$tag = preg_replace($markdown_attr_re, '', $tag);

					// Check if text inside this tag must be parsed in span mode.
					$this->mode = $attr_m[2] . $attr_m[3];
					$span_mode = $this->mode == 'span' || $this->mode != 'block' &&
						preg_match('{^<(?:' . static::CONTAIN_SPAN_TAGS_REGEX . ')\b}',
							$tag);

					// Calculate indent before tag.
					if (preg_match('/(?:^|\n)( *?)(?! ).*?$/', $block_text,
							$matches))
					{
						$indent = mb_strlen($matches[1], 'UTF-8');
					}
					else
					{
						$indent = 0;
					}

					// End preceding block with this tag.
					$block_text .= $tag;
					$parsed .= $this->$hash_method($block_text);

					// Get enclosing tag name for the ParseMarkdown function.
					// (This pattern makes $tag_name_re safe without quoting.)
					preg_match('/^<([\w:$]*)\b/', $tag, $matches);
					$tag_name_re = $matches[1];

					// Parse the content using the HTML-in-Markdown parser.
					list ($block_text, $text) = $this->_hashHTMLBlocks_inMarkdown($text, $indent, $tag_name_re, $span_mode);

					// Outdent markdown text.
					if ($indent > 0) {
						$block_text = preg_replace("/^[ ]{1,$indent}/m", '', $block_text);
					}

					// Append tag content to parsed text.
					if (!$span_mode)
					{
						$parsed .= "\n\n$block_text\n\n";
					}
					else
					{
						$parsed .= "$block_text";
					}

					// Start over a new block.
					$block_text = '';
				}
				else
				{
					$block_text .= $tag;
				}
			}
		}
		while ($depth > 0);

		//
		// Hash last block text that wasn't processed inside the loop.
		//
		$parsed .= $this->$hash_method($block_text);

		return array($parsed, $text);
	}

	/**
	 * Called whenever a tag must be hashed when a public function insert a "clean" tag
	 * in $text, it pass through this public function and is automaticaly escaped,
	 * blocking invalid nested overlap.
	 *
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function hashClean($text)
	{
		return $this->hashPart($text, 'C');
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _doAnchors_inline_callback($matches)
	{
		// $whole_match	=  $matches[1];
		$link_text = $this->runSpanGamut($matches[2]);
		$url = $matches[3] == '' ? $matches[4] : $matches[3];
		$title = & $matches[7];

		$url = $this->encodeAttribute($url);

		$result = "<a href=\"$url\"";

		if (isset($title))
		{
			$title = $this->encodeAttribute($title);
			$result .= " title=\"$title\"";
		}

		$link_text = $this->runSpanGamut($link_text);
		$result .= ">$link_text</a>";

		return $this->hashPart($result);
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _doAnchors_reference_callback($matches)
	{
		$whole_match = $matches[1];
		$link_text = $matches[2];
		$link_id = & $matches[3];
		$result = '';

		if ($link_id == '')
		{
			// for shortcut links like [this][] or [this].
			$link_id = $link_text;
		}

		// lower-case and turn embedded newlines into spaces
		$link_id = strtolower($link_id);
		$link_id = preg_replace('{[ ]?\n}', ' ', $link_id);

		if (isset($this->urls[$link_id]))
		{
			$url = $this->urls[$link_id];
			$url = $this->encodeAttribute($url);

			$result = "<a href=\"$url\"";
			if (isset($this->titles[$link_id]))
			{
				$title = $this->titles[$link_id];
				$title = $this->encodeAttribute($title);
				$result .= " title=\"$title\"";
			}

			$link_text = $this->runSpanGamut($link_text);
			$result .= ">$link_text</a>";
			$result = $this->hashPart($result);
		}
		else
		{
			$result = $whole_match;
		}

		return $result;
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function doHeaders($text)
	{
		//
		// Redefined to add id attribute support.
		//
		// Setext-style headers:
		// Header 1 {#header1}
		// ========
		//
		// Header 2 {#header2}
		// --------
		//
		$text = preg_replace_callback(
				'{
				(^.+?)								# $1: Header text
				(?:[ ]+\{\#([-_:a-zA-Z0-9]+)\})?	# $2: Id attribute
				[ ]*\n(=+|-+)[ ]*\n+				# $3: Header footer
			}mx',
				array(&$this, '_doHeaders_callback_setext'), $text);

		// atx-style headers:
		// # Header 1 {#header1}
		// ## Header 2 {#header2}
		// ## Header 2 with closing hashes ## {#header3}
		// ...
		// ###### Header 6 {#header6}
		//
		$text = preg_replace_callback(
			'{
				^(\#{1,6})	# $1 = string of #\'s
				[ ]*
				(.+?)		# $2 = Header text
				[ ]*
				\#*			# optional closing #\'s (not counted)
				(?:[ ]+\{\#([-_:a-zA-Z0-9]+)\})? # id attribute
				[ ]*
				\n+
			}xm',
			array(&$this, '_doHeaders_callback_atx'),
			$text
		);

		return $text;
	}

	/**
	 * @param   string  $attr
	 *
	 * @return  string
	 */
	public function _doHeaders_attr($attr)
	{
		if (empty($attr))
		{
			return '';
		}

		return " id=\"$attr\"";
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _doHeaders_callback_setext($matches)
	{
		if ($matches[3] == '-' && preg_match('{^- }', $matches[1]))
		{
			return $matches[0];
		}

		$level = $matches[3]{0} == '=' ? 1 : 2;
		$attr = $this->_doHeaders_attr($id = & $matches[2]);
		$body = $this->runSpanGamut($matches[1]);
		$body = $this->_doHeaders_selflink($id, $body);

		$block = "<h$level$attr>$body</h$level>";

		return "\n" . $this->hashBlock($block) . "\n\n";
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _doHeaders_callback_atx($matches)
	{
		$level = strlen($matches[1]);
		$attr = $this->_doHeaders_attr($id = & $matches[3]);
		$body = $this->runSpanGamut($matches[2]);
		$body = $this->_doHeaders_selflink($id, $body);

		$block = "<h$level$attr>$body</h$level>";

		return "\n" . $this->hashBlock($block) . "\n\n";
	}

	/**
	 * @param   string  $id
	 * @param   string  $body
	 *
	 * @return  string
	 */
	public function _doHeaders_selflink($id, $body)
	{
		if (!empty($id))
		{
			$link = '<a href="#' . $id . '"';

			$link .= '>' . static::HEADER_SELFLINK_TEXT . '</a>';

			$body .= $link;
		}

		return $body;
	}

	/**
	 * Form HTML tables.
	 *
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function doTables($text)
	{
		$less_than_tab = static::TAB_WIDTH - 1;

		//
		// Find tables with leading pipe.
		//
		//	| Header 1 | Header 2
		//	| -------- | --------
		//	| Cell 1   | Cell 2
		//	| Cell 3   | Cell 4
		//
		$text = preg_replace_callback(
			'{
				^							# Start of a line
				[ ]{0,' . $less_than_tab . '}	# Allowed whitespace.
				[|]							# Optional leading pipe (present)
				(.+) \n						# $1: Header row (at least one pipe)

				[ ]{0,' . $less_than_tab . '}	# Allowed whitespace.
				[|] ([ ]*[-:]+[-| :]*) \n	# $2: Header underline

				(							# $3: Cells
					(?>
						[ ]*				# Allowed whitespace.
						[|] .* \n			# Row content.
					)*
				)
				(?=\n|\Z)					# Stop at final double newline.
			}xm',
			array(&$this, '_doTable_leadingPipe_callback'),
			$text
		);

		//
		// Find tables without leading pipe.
		//
		//	Header 1 | Header 2
		//	-------- | --------
		//	Cell 1   | Cell 2
		//	Cell 3   | Cell 4
		//
		$text = preg_replace_callback(
			'{
				^							# Start of a line
				[ ]{0,' . $less_than_tab . '}	# Allowed whitespace.
				(\S.*[|].*) \n				# $1: Header row (at least one pipe)

				[ ]{0,' . $less_than_tab . '}	# Allowed whitespace.
				([-:]+[ ]*[|][-| :]*) \n	# $2: Header underline

				(							# $3: Cells
					(?>
						.* [|] .* \n		# Row content
					)*
				)
				(?=\n|\Z)					# Stop at final double newline.
			}xm',
			array(&$this, '_DoTable_callback'),
			$text
		);

		return $text;
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _doTable_leadingPipe_callback($matches)
	{
		$head = $matches[1];
		$underline = $matches[2];
		$content = $matches[3];

		// Remove leading pipe for each row.
		$content = preg_replace('/^ *[|]/m', '', $content);

		return $this->_doTable_callback(array($matches[0], $head, $underline, $content));
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _doTable_callback($matches)
	{
		$head = $matches[1];
		$underline = $matches[2];
		$content = $matches[3];

		# Remove any tailing pipes for each line.
		$head = preg_replace('/[|] *$/m', '', $head);
		$underline = preg_replace('/[|] *$/m', '', $underline);
		$content = preg_replace('/[|] *$/m', '', $content);

		# Reading alignement from header underline.
		$separators = preg_split('/ *[|] */', $underline);

		foreach ($separators as $n => $s)
		{
			if (preg_match('/^ *-+: *$/', $s))
			{
				$attr[$n] = ' align="right"';
			}
			else if (preg_match('/^ *:-+: *$/', $s))
			{
				$attr[$n] = ' align="center"';
			}
			else if (preg_match('/^ *:-+ *$/', $s))
			{
				$attr[$n] = ' align="left"';
			}
			else
			{
				$attr[$n] = '';
			}
		}

		// Parsing span elements, including code spans, character escapes,
		// and inline HTML tags, so that pipes inside those gets ignored.
		$head = $this->parseSpan($head);
		$headers = preg_split('/ *[|] */', $head);
		$col_count = count($headers);

		// Write column headers.
		$text = "<table>\n";
		$text .= "<thead>\n";
		$text .= "<tr>\n";

		foreach ($headers as $n => $header)
		{
			$text .= "  <th$attr[$n]>" . $this->runSpanGamut(trim($header)) . "</th>\n";
		}

		$text .= "</tr>\n";
		$text .= "</thead>\n";

		// Split content by row.
		$rows = explode("\n", trim($content, "\n"));

		$text .= "<tbody>\n";

		foreach ($rows as $row)
		{
			// Parsing span elements, including code spans, character escapes,
			// and inline HTML tags, so that pipes inside those gets ignored.
			$row = $this->parseSpan($row);

			// Split row by cell.
			$row_cells = preg_split('/ *[|] */', $row, $col_count);
			$row_cells = array_pad($row_cells, $col_count, '');

			$text .= "<tr>\n";

			foreach ($row_cells as $n => $cell)
			{
				$text .= "  <td$attr[$n]>" . $this->runSpanGamut(trim($cell)) . "</td>\n";
			}

			$text .= "</tr>\n";
		}

		$text .= "</tbody>\n";
		$text .= "</table>";

		return $this->hashBlock($text) . "\n";
	}

	/**
	 * Form HTML definition lists.
	 *
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function doDefLists($text)
	{
		$less_than_tab = static::TAB_WIDTH - 1;

		// Re-usable pattern to match any entire dl list:
		$whole_list_re = '(?>
			(								# $1 = whole list
			  (								# $2
				[ ]{0,' . $less_than_tab . '}
				((?>.*\S.*\n)+)				# $3 = defined term
				\n?
				[ ]{0,' . $less_than_tab . '}:[ ]+ # colon starting definition
			  )
			  (?s:.+?)
			  (								# $4
				  \z
				|
				  \n{2,}
				  (?=\S)
				  (?!						# Negative lookahead for another term
					[ ]{0,' . $less_than_tab . '}
					(?: \S.*\n )+?			# defined term
					\n?
					[ ]{0,' . $less_than_tab . '}:[ ]+ # colon starting definition
				  )
				  (?!						# Negative lookahead for another definition
					[ ]{0,' . $less_than_tab . '}:[ ]+ # colon starting definition
				  )
			  )
			)
		)'; // mx

		$text = preg_replace_callback(
			'{
				(?>\A\n?|(?<=\n\n))
				' . $whole_list_re . '
			}mx',
			array(&$this, '_doDefLists_callback'),
			$text
		);

		return $text;
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _doDefLists_callback($matches)
	{
		// Re-usable patterns to match list item bullets and number markers:
		$list = $matches[1];

		// Turn double returns into triple returns, so that we can make a
		// paragraph for the last item in a list, if necessary:
		$result = trim($this->processDefListItems($list));
		$result = "<dl>\n" . $result . "\n</dl>";

		return $this->hashBlock($result) . "\n\n";
	}

	/**
	 * Process the contents of a single definition list, splitting it
	 * into individual term and definition list items.
	 *
	 * @param   string  $list_str
	 *
	 * @return  string
	 */
	public function processDefListItems($list_str)
	{
		$less_than_tab = static::TAB_WIDTH - 1;

		// trim trailing blank lines:
		$list_str = preg_replace("/\n{2,}\\z/", "\n", $list_str);

		// Process definition terms.
		$list_str = preg_replace_callback('{
			(?>\A\n?|\n\n+)					# leading line
			(								# definition terms = $1
				[ ]{0,' . $less_than_tab . '}	# leading whitespace
				(?![:][ ]|[ ])				# negative lookahead for a definition
											#   mark (colon) or more whitespace.
				(?> \S.* \n)+?				# actual term (not whitespace).
			)
			(?=\n?[ ]{0,3}:[ ])				# lookahead for following line feed
											#   with a definition mark.
			}xm',
			array(&$this, '_processDefListItems_callback_dt'),
			$list_str
		);

		// Process actual definitions.
		$list_str = preg_replace_callback(
			'{
				\n(\n+)?						# leading line = $1
				(								# marker space = $2
					[ ]{0,' . $less_than_tab . '}	# whitespace before colon
					[:][ ]+						# definition mark (colon)
				)
				((?s:.+?))						# definition text = $3
				(?= \n+ 						# stop at next definition mark,
					(?:							# next term or end of text
						[ ]{0,' . $less_than_tab . '} [:][ ]	|
						<dt | \z
					)
				)
			}xm',
			array(&$this, '_processDefListItems_callback_dd'),
			$list_str
		);

		return $list_str;
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _processDefListItems_callback_dt($matches)
	{
		$anchor_regexp = '/\{\#([-_:a-zA-Z0-9]+)\}/';
		$terms = explode("\n", trim($matches[1]));
		$text = '';
		$id = array();

		foreach ($terms as $term)
		{
			$id = '';
			if (preg_match($anchor_regexp, $term, $id) > 0)
			{
				$term = preg_replace($anchor_regexp, '', $term);
				$id = ' id="' . trim($id[1]) . '"';
			}

			if (count($id) === 0)
			{
				$id = '';
			}

			$term = $this->runSpanGamut(trim($term));
			$text .= "\n<dt$id>" . $term . "</dt>";
		}

		return $text . "\n";
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _processDefListItems_callback_dd($matches)
	{
		$leading_line = $matches[1];
		$marker_space = $matches[2];
		$def = $matches[3];

		if ($leading_line || preg_match('/\n{2,}/', $def))
		{
			// Replace marker with the appropriate whitespace indentation
			$def = str_repeat(' ', strlen($marker_space)) . $def;
			$def = $this->runBlockGamut($this->outdent($def . "\n\n"));
			$def = "\n" . $def . "\n";
		}
		else
		{
			$def = rtrim($def);
			$def = $this->runSpanGamut($this->outdent($def));
		}

		return "\n<dd>" . $def . "</dd>\n";
	}

	/**
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function doFencedCodeBlocks($text)
	{
		//
		// Adding the fenced code block syntax to regular Markdown:
		//
		// ~~~
		// Code block
		// ~~~
		//
		$less_than_tab = static::TAB_WIDTH;

		$text = preg_replace_callback(
			'{
				(?:\n|\A)
				# 1: Opening marker
				(
					~{3,} # Marker: three tilde or more.
				)
				[ ]* \n # Whitespace and newline following marker.

				# 2: Content
				(
					(?>
						(?!\1 [ ]* \n)	# Not a closing marker.
						.*\n+
					)+
				)

				# Closing marker.
				\1 [ ]* \n
			}xm',
			array(&$this, '_doFencedCodeBlocks_callback'),
			$text
		);

		return $text;
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _doFencedCodeBlocks_callback($matches)
	{
		$codeblock = $matches[2];
		$codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);
		$codeblock = preg_replace_callback(
			'/^\n+/',
			array(&$this, '_doFencedCodeBlocks_newlines'),
			$codeblock
		);
		$codeblock = "<pre><code>$codeblock</code></pre>";

		return "\n\n" . $this->hashBlock($codeblock) . "\n\n";
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _doFencedCodeBlocks_newlines($matches)
	{
		return str_repeat("<br />", strlen($matches[0]));
	}

	/**
	 * @param   string  $text  String to process with html <p> tags
	 *
	 * @return  string
	 */
	public function formParagraphs($text)
	{
		// Strip leading and trailing lines:
		$text = preg_replace('/\A\n+|\n+\z/', '', $text);

		$grafs = preg_split('/\n{2,}/', $text, -1, PREG_SPLIT_NO_EMPTY);

		//
		// Wrap <p> tags and unhashify HTML blocks
		//
		foreach ($grafs as $key => $value)
		{
			$value = trim($this->runSpanGamut($value));

			// Check if this should be enclosed in a paragraph.
			// Clean tag hashes & block tag hashes are left alone.
			$is_p = !preg_match('/^B\x1A[0-9]+B|^C\x1A[0-9]+C$/', $value);

			if ($is_p)
			{
				$value = "<p>$value</p>";
			}

			$grafs[$key] = $value;
		}

		// Join grafs in one text, then unhash HTML tags.
		$text = implode("\n\n", $grafs);

		// Finish by removing any tag hashes still present in $text.
		$text = $this->unhash($text);

		return $text;
	}

	/**
	 * Strips link definitions from text, stores the URLs and titles in hash references.
	 *
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function stripFootnotes($text)
	{
		$less_than_tab = static::TAB_WIDTH - 1;

		// Link defs are in the form: [^id]: url "optional title"
		$text = preg_replace_callback(
			'{
				^[ ]{0,' . $less_than_tab . '}\[\^(.+?)\][ ]?:	# note_id = $1
				  [ ]*
				  \n?					# maybe *one* newline
				(						# text = $2 (no blank lines allowed)
					(?:
						.+				# actual text
					|
						\n				# newlines but
						(?!\[\^.+?\]:\s)# negative lookahead for footnote marker.
						(?!\n+[ ]{0,3}\S)# ensure line is not blank and followed
										# by non-indented content
					)*
				)
			}xm',
			array(&$this, '_stripFootnotes_callback'),
			$text
		);

		return $text;
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _stripFootnotes_callback($matches)
	{
		$note_id = $matches[1];
		$this->footnotes[$note_id] = $this->outdent($matches[2]);

		// String that will replace the block
		return '';
	}

	/**
	 * Replace footnote references in $text [^id] with a special text-token
	 * which will be replaced by the actual footnote marker in appendFootnotes.
	 *
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function doFootnotes($text)
	{
		if (!$this->inAnchor)
		{
			$text = preg_replace('{\[\^(.+?)\]}', "F\x1Afn:\\1\x1A:", $text);
		}

		return $text;
	}

	/**
	 * Append footnote list to text.
	 *
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function appendFootnotes($text)
	{
		$text = preg_replace_callback(
			'{F\x1Afn:(.*?)\x1A:}',
			array(&$this, '_appendFootnotes_callback'),
			$text
		);

		if (!empty($this->orderedFootnotes))
		{
			$text .= "\n\n";
			$text .= "<div class=\"footnotes\">\n";
			$text .= "<hr />\n";
			$text .= "<ol>\n\n";

			$attr = " rev=\"footnote\"";
			$num = 0;

			while (!empty($this->orderedFootnotes))
			{
				$footnote = reset($this->orderedFootnotes);
				$note_id = key($this->orderedFootnotes);
				unset($this->orderedFootnotes[$note_id]);

				$footnote .= "\n"; # Need to append newline before parsing.
				$footnote = $this->runBlockGamut("$footnote\n");
				$footnote = preg_replace_callback(
					'{F\x1Afn:(.*?)\x1A:}',
					array(&$this, '_appendFootnotes_callback'),
					$footnote
				);

				$attr = str_replace("%%", ++$num, $attr);
				$note_id = $this->encodeAttribute($note_id);

				// Add backlink to last paragraph; create new paragraph if needed.
				$backlink = "<a href=\"#fnref:$note_id\"$attr>&#8617;</a>";

				if (preg_match('{</p>$}', $footnote))
				{
					$footnote = substr($footnote, 0, -4) . "&#160;$backlink</p>";
				}
				else
				{
					$footnote .= "\n\n<p>$backlink</p>";
				}

				$text .= "<li id=\"fn:$note_id\">\n";
				$text .= $footnote . "\n";
				$text .= "</li>\n\n";
			}

			$text .= "</ol>\n";
			$text .= "</div>";
		}

		return $text;
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _appendFootnotes_callback($matches)
	{
		$node_id = $matches[1];

		// Create footnote marker only if it has a corresponding footnote *and*
		// the footnote hasn't been used by another marker.
		if (isset($this->footnotes[$node_id]))
		{
			// Transfert footnote content to the ordered list.
			$this->orderedFootnotes[$node_id] = $this->footnotes[$node_id];
			unset($this->footnotes[$node_id]);

			$num = $this->footnoteCounter++;
			$attr = " rel=\"footnote\"";
			$attr = str_replace("%%", $num, $attr);
			$node_id = $this->encodeAttribute($node_id);

			return
			"<sup id=\"fnref:$node_id\">" .
			"<a href=\"#fn:$node_id\"$attr>$num</a>" .
			"</sup>";
		}

		return "[^" . $matches[1] . "]";
	}

	/**
	 * Strips abbreviations from text, stores titles in hash references.
	 *
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function stripAbbreviations($text)
	{
		$less_than_tab = static::TAB_WIDTH - 1;

		// Link defs are in the form: [id]*: url "optional title"
		$text = preg_replace_callback(
			'{
				^[ ]{0,' . $less_than_tab . '}\*\[(.+?)\][ ]?:	# abbr_id = $1
				(.*)					# text = $2 (no blank lines allowed)
			}xm',
			array(&$this, '_stripAbbreviations_callback'),
			$text
		);

		return $text;
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _stripAbbreviations_callback($matches)
	{
		$abbr_word = $matches[1];
		$abbr_desc = $matches[2];
		if ($this->abbrWordsRegex)
		{
			$this->abbrWordsRegex .= '|';
		}

		$this->abbrWordsRegex .= preg_quote($abbr_word);
		$this->abbrDescriptions[$abbr_word] = trim($abbr_desc);

		return ''; # String that will replace the block
	}

	/**
	 * Find defined abbreviations in text and wrap them in <abbr> elements.
	 *
	 * @param   string  $text
	 *
	 * @return  string
	 */
	public function doAbbreviations($text)
	{
		if ($this->abbrWordsRegex)
		{
			// cannot use the /x modifier because abbr_word_re may
			// contain significant spaces:
			$text = preg_replace_callback(
				'{' .
					'(?<![\w\x1A])' .
					'(?:' . $this->abbrWordsRegex . ')' .
					'(?![\w\x1A])' .
				'}',
				array(&$this, '_doAbbreviations_callback'),
				$text
			);
		}

		return $text;
	}

	/**
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	public function _doAbbreviations_callback($matches)
	{
		$abbr = $matches[0];

		if (isset($this->abbrDescriptions[$abbr]))
		{
			$desc = $this->abbrDescriptions[$abbr];
			if (empty($desc))
			{
				return $this->hashPart("<abbr>$abbr</abbr>");
			}
			else
			{
				$desc = $this->encodeAttribute($desc);
				return $this->hashPart("<abbr title=\"$desc\">$abbr</abbr>");
			}
		}
		else
		{
			return $matches[0];
		}
	}
}