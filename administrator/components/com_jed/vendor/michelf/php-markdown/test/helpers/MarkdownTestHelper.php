<?php

use PHPUnit\Framework\TestCase;

class MarkdownTestHelper
{
	/**
	 * Takes an input directory containing .text and .(x)html files, and returns an array
	 * of .text files and the corresponding output xhtml or html file. Can be used in a unit test data provider.
	 *
	 * @param string $directory Input directory
	 *
	 * @return array
	 */
	public static function getInputOutputPaths($directory) {
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
		$regexIterator = new RegexIterator(
			$iterator,
			'/^.+\.text$/',
			RecursiveRegexIterator::GET_MATCH
		);

		$dataValues = array();

		foreach ($regexIterator as $inputFiles) {
			foreach ($inputFiles as $inputMarkdownPath) {
				$xhtml = true;
				$expectedHtmlPath = substr($inputMarkdownPath, 0, -4) . 'xhtml';
				if (!file_exists($expectedHtmlPath)) {
					$expectedHtmlPath = substr($inputMarkdownPath, 0, -4) . 'html';
					$xhtml = false;
				}
				$dataValues[] = array($inputMarkdownPath, $expectedHtmlPath, $xhtml);
			}
		}

		return $dataValues;
	}

	/**
	 * Applies PHPUnit's assertSame after normalizing both strings (e.g. ignoring whitespace differences).
	 * Uses logic found originally in MDTest.
	 *
	 * @param string $string1
	 * @param string $string2
	 * @param string $message Positive message to print when test fails (e.g. "String1 matches String2")
	 * @param bool $xhtml
	 */
	public static function assertSameNormalized($string1, $string2, $message, $xhtml = true) {

		$t_result = $string1;
		$t_output = $string2;

		// DOMDocuments
		if ($xhtml) {
			$document = new DOMDocument();
			$doc_result = $document->loadXML('<!DOCTYPE html>' .
				"<html xmlns='http://www.w3.org/1999/xhtml'>" .
				"<body>$t_result</body></html>");

			$document2 = new DOMDocument();
			$doc_output = $document2->loadXML('<!DOCTYPE html>' .
				"<html xmlns='http://www.w3.org/1999/xhtml'>" .
				"<body>$t_output</body></html>");

			if ($doc_result) {
				static::normalizeElementContent($document->documentElement, false);
				$n_result = $document->saveXML();
			} else {
				$n_result = '--- Expected Result: XML Parse Error ---';
			}
			if ($doc_output) {
				static::normalizeElementContent($document2->documentElement, false);
				$n_output = $document2->saveXML();
			} else {
				$n_output = '--- Output: XML Parse Error ---';
			}
		} else {

			// '@' suppressors used because some tests have invalid HTML (multiple elements with the same id attribute)
			// Perhaps isolate to a separate test and remove this?

			$document = new DOMDocument();
			$doc_result = @$document->loadHTML($t_result);

			$document2 = new DOMDocument();
			$doc_output = @$document2->loadHTML($t_output);

			if ($doc_result) {
				static::normalizeElementContent($document->documentElement, false);
				$n_result = $document->saveHTML();
			} else {
				$n_result = '--- Expected Result: HTML Parse Error ---';
			}

			if ($doc_output) {
				static::normalizeElementContent($document2->documentElement, false);
				$n_output = $document2->saveHTML();
			} else {
				$n_output = '--- Output: HTML Parse Error ---';
			}
		}

		$n_result = preg_replace('{^.*?<body>|</body>.*?$}is', '', $n_result);
		$n_output = preg_replace('{^.*?<body>|</body>.*?$}is', '', $n_output);

		$c_result = $n_result;
		$c_output = $n_output;

		$c_result = trim($c_result) . "\n";
		$c_output = trim($c_output) . "\n";

		// This will throw a test exception if the strings don't exactly match
		TestCase::assertSame($c_result, $c_output, $message);
	}

	/**
	 * @param DOMElement $element Modifies this element by reference
	 * @param bool $whitespace_preserve Preserve Whitespace
	 * @return void
	 */
	protected static function normalizeElementContent($element, $whitespace_preserve) {
		#
		# Normalize content of HTML DOM $element. The $whitespace_preserve
		# argument indicates that whitespace is significant and shouldn't be
		# normalized; it should be used for the content of certain elements like
		# <pre> or <script>.
		#
		$node_list = $element->childNodes;
		switch (strtolower($element->nodeName)) {
			case 'body':
			case 'div':
			case 'blockquote':
			case 'ul':
			case 'ol':
			case 'dl':
			case 'h1':
			case 'h2':
			case 'h3':
			case 'h4':
			case 'h5':
			case 'h6':
				$whitespace = "\n\n";
				break;

			case 'table':
				$whitespace = "\n";
				break;

			case 'pre':
			case 'script':
			case 'style':
			case 'title':
				$whitespace_preserve = true;
				$whitespace = "";
				break;

			default:
				$whitespace = "";
				break;
		}
		foreach ($node_list as $node) {
			switch ($node->nodeType) {
				case XML_ELEMENT_NODE:
					/** @var DOMElement $node */
					static::normalizeElementContent($node, $whitespace_preserve);
					static::normalizeElementAttributes($node);

					switch (strtolower($node->nodeName)) {
						case 'p':
						case 'div':
						case 'hr':
						case 'blockquote':
						case 'ul':
						case 'ol':
						case 'dl':
						case 'li':
						case 'address':
						case 'table':
						case 'dd':
						case 'pre':
						case 'h1':
						case 'h2':
						case 'h3':
						case 'h4':
						case 'h5':
						case 'h6':
							$whitespace = "\n\n";
							break;

						case 'tr':
						case 'td':
						case 'dt':
							$whitespace = "\n";
							break;

						default:
							$whitespace = "";
							break;
					}

					if (($whitespace === "\n\n" || $whitespace === "\n") &&
						$node->nextSibling &&
						$node->nextSibling->nodeType != XML_TEXT_NODE) {
						$element->insertBefore(new DOMText($whitespace), $node->nextSibling);
					}
					break;

				case XML_TEXT_NODE:
					/** @var DOMText $node */
					if (!$whitespace_preserve) {
						if (trim($node->data) === "") {
							$node->data = $whitespace;
						}
						else {
							$node->data = preg_replace('{\s+}', ' ', $node->data);
						}
					}
					break;
			}
		}
		if (!$whitespace_preserve &&
			($whitespace === "\n\n" || $whitespace === "\n")) {
			if ($element->firstChild) {
				if ($element->firstChild->nodeType == XML_TEXT_NODE) {
					$element->firstChild->data = // @phpstan-ignore-line
						preg_replace('{^\s+}', "\n", $element->firstChild->data ?? '');
				}
				else {
					$element->insertBefore(new DOMText("\n"), $element->firstChild);
				}
			}
			if ($element->lastChild) {
				if ($element->lastChild->nodeType == XML_TEXT_NODE) {
					$element->lastChild->data = // @phpstan-ignore-line
						preg_replace('{\s+$}', "\n", $element->lastChild->data ?? '');
				}
				else {
					$element->insertBefore(new DOMText("\n"), null);
				}
			}
		}
	}

	/**
	 * @param DOMElement $element Modifies this element by reference
	 */
	protected static function normalizeElementAttributes (DOMElement $element)
	{
		#
		# Sort attributes by name.
		#
		// Gather the list of attributes as an array.
		$attr_list = array();
		foreach ($element->attributes as $attr_node) {
			$attr_list[$attr_node->name] = $attr_node;
		}

		// Sort attribute list by name.
		ksort($attr_list);

		// Remove then put back each attribute following sort order.
		foreach ($attr_list as $attr_node) {
			$element->removeAttributeNode($attr_node);
			$element->setAttributeNode($attr_node);
		}
	}
}
