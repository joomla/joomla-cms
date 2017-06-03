<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('FinderIndexerParser', dirname(__DIR__) . '/parser.php');

/**
 * HTML Parser class for the Finder indexer package.
 *
 * @since  2.5
 */
class FinderIndexerParserHtml extends FinderIndexerParser
{
	/**
	 * Method to parse input and extract the plain text. Because this method is
	 * called from both inside and outside the indexer, it needs to be able to
	 * batch out its parsing functionality to deal with the inefficiencies of
	 * regular expressions. We will parse recursively in 2KB chunks.
	 *
	 * @param   string  $input  The input to parse.
	 *
	 * @return  string  The plain text input.
	 *
	 * @since   2.5
	 */
	public function parse($input)
	{
		// Strip invalid UTF-8 characters.
		$input = iconv('utf-8', 'utf-8//IGNORE', $input);

		// Remove anything between <head> and </head> tags.  Do this first
		// because there might be <script> or <style> tags nested inside.
		$input = $this->removeBlocks($input, '<head>', '</head>');

		// Convert <style> and <noscript> tags to <script> tags
		// so we can remove them efficiently.
		$search = array(
			'<style', '</style',
			'<noscript', '</noscript',
		);
		$replace = array(
			'<script', '</script',
			'<script', '</script',
		);
		$input = str_replace($search, $replace, $input);

		// Strip all script blocks.
		$input = $this->removeBlocks($input, '<script', '</script>');

		// Decode HTML entities.
		$input = html_entity_decode($input, ENT_QUOTES, 'UTF-8');

		// Convert entities equivalent to spaces to actual spaces.
		$input = str_replace(array('&nbsp;', '&#160;'), ' ', $input);

		// This fixes issues such as '<h1>Title</h1><p>Paragraph</p>'
		// being transformed into 'TitleParagraph' with no space.
		$input = str_replace('>', '> ', $input);

		// Strip HTML tags.
		$input = strip_tags($input);

		return parent::parse($input);
	}

	/**
	 * Method to process HTML input and extract the plain text.
	 *
	 * @param   string  $input  The input to process.
	 *
	 * @return  string  The plain text input.
	 *
	 * @since   2.5
	 */
	protected function process($input)
	{
		// Replace any amount of white space with a single space.
		return preg_replace('#\s+#u', ' ', $input);
	}

	/**
	 * Method to remove blocks of text between a start and an end tag.
	 * Each block removed is effectively replaced by a single space.
	 *
	 * Note: The start tag and the end tag must be different.
	 * Note: Blocks must not be nested.
	 * Note: This method will function correctly with multi-byte strings.
	 *
	 * @param   string  $input     String to be processed.
	 * @param   string  $startTag  String representing the start tag.
	 * @param   string  $endTag    String representing the end tag.
	 *
	 * @return  string with blocks removed.
	 */
	private function removeBlocks($input, $startTag, $endTag)
	{
		$return = '';
		$offset = 0;
		$startTagLength = strlen($startTag);
		$endTagLength = strlen($endTag);

		// Find the first start tag.
		$start = stripos($input, $startTag);

		// If no start tags were found, return the string unchanged.
		if ($start === false)
		{
			return $input;
		}

		// Look for all blocks defined by the start and end tags.
		while ($start !== false)
		{
			// Accumulate the substring up to the start tag.
			$return .= substr($input, $offset, $start - $offset) . ' ';

			// Look for an end tag corresponding to the start tag.
			$end = stripos($input, $endTag, $start + $startTagLength);

			// If no corresponding end tag, leave the string alone.
			if ($end === false)
			{
				// Fix the offset so part of the string is not duplicated.
				$offset = $start;
				break;
			}

			// Advance the start position.
			$offset = $end + $endTagLength;

			// Look for the next start tag and loop.
			$start = stripos($input, $startTag, $offset);
		}

		// Add in the final substring after the last end tag.
		$return .= substr($input, $offset);

		return $return;
	}
}
