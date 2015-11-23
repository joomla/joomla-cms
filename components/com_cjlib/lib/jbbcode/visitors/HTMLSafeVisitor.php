<?php

namespace JBBCode\visitors;

/**
 * This visitor escapes html content of all strings and attributes
 *
 * @author Alexander Polyanskikh
 */
class HTMLSafeVisitor implements \JBBCode\NodeVisitor
{
	public function visitDocumentElement(\JBBCode\DocumentElement $documentElement)
	{
		foreach ($documentElement->getChildren() as $child) {
			$child->accept($this);
		}
	}

	public function visitTextNode(\JBBCode\TextNode $textNode)
	{
		$textNode->setValue($this->htmlSafe($textNode->getValue()));
	}

	public function visitElementNode(\JBBCode\ElementNode $elementNode)
	{
		$attrs = $elementNode->getAttribute();
		if (is_array($attrs))
		{
			foreach ($attrs as &$el)
				$el = $this->htmlSafe($el);

			$elementNode->setAttribute($attrs);
		}

		foreach ($elementNode->getChildren() as $child) {
			$child->accept($this);
		}
	}

	protected function htmlSafe($str, $options = null)
	{
		if (is_null($options))
		{
			if (defined('ENT_DISALLOWED'))
				$options = ENT_QUOTES | ENT_DISALLOWED | ENT_HTML401; // PHP 5.4+
			else
				$options = ENT_QUOTES;  // PHP 5.3
		}

		return htmlspecialchars($str, $options, 'UTF-8');
	}
}
