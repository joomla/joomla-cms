<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * JDocument head renderer
 *
 * @since  3.5
 */
class JDocumentRendererHtmlHead extends JDocumentRenderer
{
	/**
	 * Renders the document head and returns the results as a string
	 *
	 * @param   string  $head     (unused)
	 * @param   array   $params   Associative array of values
	 * @param   string  $content  The script
	 *
	 * @return  string  The output of the script
	 *
	 * @since   3.5
	 */
	public function render($head, $params = array(), $content = null)
	{
		return $this->fetchHead($this->_doc);
	}

	/**
	 * Generates the head HTML and return the results as a string
	 *
	 * @param   JDocumentHtml  $document  The document for which the head will be created
	 *
	 * @return  string  The head hTML
	 *
	 * @since   3.5
	 * @deprecated  4.0  Method code will be moved into the render method
	 */
	public function fetchHead($document)
	{
		// Convert the tagids to titles
		if (isset($document->_metaTags['name']['tags']))
		{
			$tagsHelper = new JHelperTags;
			$document->_metaTags['name']['tags'] = implode(', ', $tagsHelper->getTagNames($document->_metaTags['name']['tags']));
		}

		if ($document->getScriptOptions())
		{
			JHtml::_('behavior.core');
		}

		// Trigger the onBeforeCompileHead event
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeCompileHead');

		// Get line endings
		$lnEnd        = $document->_getLineEnd();
		$tab          = $document->_getTab();
		$tagEnd       = ' />';
		$buffer       = '';
		$mediaVersion = $document->getMediaVersion();

		// Generate charset when using HTML5 (should happen first)
		if ($document->isHtml5())
		{
			$buffer .= $tab . '<meta charset="' . $document->getCharset() . '">' . $lnEnd;
		}

		// Generate base tag (need to happen early)
		$base = $document->getBase();

		if (!empty($base))
		{
			$buffer .= $tab . '<base href="' . $base . '">' . $lnEnd;
		}

		// Generate META tags (needs to happen as early as possible in the head)
		foreach ($document->_metaTags as $type => $tag)
		{
			foreach ($tag as $name => $content)
			{
				if ($type == 'http-equiv' && !($document->isHtml5() && $name == 'content-type'))
				{
					$buffer .= $tab . '<meta http-equiv="' . $name . '" content="' . htmlspecialchars($content, ENT_COMPAT, 'UTF-8') . '">' . $lnEnd;
				}
				elseif ($type != 'http-equiv' && !empty($content))
				{
					$buffer .= $tab . '<meta ' . $type . '="' . $name . '" content="' . htmlspecialchars($content, ENT_COMPAT, 'UTF-8') . '">' . $lnEnd;
				}
			}
		}

		// Don't add empty descriptions
		$documentDescription = $document->getDescription();

		if ($documentDescription)
		{
			$buffer .= $tab . '<meta name="description" content="' . htmlspecialchars($documentDescription, ENT_COMPAT, 'UTF-8') . '">' . $lnEnd;
		}

		// Don't add empty generators
		$generator = $document->getGenerator();

		if ($generator)
		{
			$buffer .= $tab . '<meta name="generator" content="' . htmlspecialchars($generator, ENT_COMPAT, 'UTF-8') . '">' . $lnEnd;
		}

		$buffer .= $tab . '<title>' . htmlspecialchars($document->getTitle(), ENT_COMPAT, 'UTF-8') . '</title>' . $lnEnd;

		// Generate link declarations
		foreach ($document->_links as $link => $linkAtrr)
		{
			$buffer .= $tab . '<link href="' . $link . '" ' . $linkAtrr['relType'] . '="' . $linkAtrr['relation'] . '"';

			if (is_array($linkAtrr['attribs']))
			{
				if ($temp = ArrayHelper::toString($linkAtrr['attribs']))
				{
					$buffer .= ' ' . $temp;
				}
			}

			$buffer .= '>' . $lnEnd;
		}

		$defaultCssMimes       = array('text/css');
		$enumarateInlineStyles = 0;

		// Generate stylesheet links
		foreach ($document->styleSheets as $src => $attribs)
		{
			// Check if stylesheet uses IE conditional statements.
			$conditional = isset($attribs['options']) && isset($attribs['options']['conditional']) ? $attribs['options']['conditional'] : null;

			// Check if script uses media version.
			if (isset($attribs['options']['version']) && $attribs['options']['version'] && strpos($src, '?') === false
				&& ($mediaVersion || $attribs['options']['version'] !== 'auto'))
			{
				$src .= '?' . ($attribs['options']['version'] === 'auto' ? $mediaVersion : $attribs['options']['version']);
			}

			$buffer .= $tab;

			// This is for IE conditional statements support.
			if (!is_null($conditional))
			{
				$buffer .= '<!--[if ' . $conditional . ']>';
			}

			$buffer .= $src !== 'inlineStyles_' . $enumarateInlineStyles ? '<link href="' . $src . '" rel="stylesheet"' : '<style>';

			// Add script tag attributes.
			foreach ($attribs as $attrib => $value)
			{
				// Don't add the 'options' attribute. This attribute is for internal use (version, conditional, etc).
				if ($attrib === 'options')
				{
					continue;
				}

				// Don't add type attribute if document is HTML5 and it's a default mime type. 'mime' is for B/C.
				if (in_array($attrib, array('type', 'mime')) && $document->isHtml5() && in_array($value, $defaultCssMimes))
				{
					continue;
				}

				// Don't add type attribute if document is HTML5 and it's a default mime type. 'mime' is for B/C.
				if ($attrib === 'mime')
				{
					$attrib = 'type';
				}

				// Add attribute to script tag output.
				$buffer .= $src !== 'inlineStyles_' . $enumarateInlineStyles ? ' ' . htmlspecialchars($attrib, ENT_COMPAT, 'UTF-8') : '';

				// Json encode value if it's an array.
				$value = !is_scalar($value) ? json_encode($value) : $value;

				$buffer .= $src !== 'inlineStyles_' . $enumarateInlineStyles ? '="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"' : htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
			}

			$buffer .= $src !== 'inlineStyles_' . $enumarateInlineStyles ? $tagEnd : $lnEnd . $tab . '</style>';

			// Bump the number of inline style
			$enumarateInlineStyles = preg_match('#inlineStyles_#', $src) ? $enumarateInlineStyles + 1 : $enumarateInlineStyles;

			// This is for IE conditional statements support.
			if (!is_null($conditional))
			{
				$buffer .= '<![endif]-->';
			}

			$buffer .= $lnEnd;
		}

		// Generate scripts options
		$scriptOptions = $document->getScriptOptions();

		if (!empty($scriptOptions))
		{
			$buffer .= $tab . '<script type="application/json" class="joomla-script-options new">';

			$prettyPrint = (JDEBUG && defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : false);
			$jsonOptions = json_encode($scriptOptions, $prettyPrint);
			$jsonOptions = $jsonOptions ? $jsonOptions : '{}';

			$buffer .= $jsonOptions;
			$buffer .= '</script>' . $lnEnd;
		}

		$defaultJsMimes         = array('text/javascript', 'application/javascript', 'text/x-javascript', 'application/x-javascript');
		$html5NoValueAttributes = array('defer', 'async');
		$mediaVersion           = $document->getMediaVersion();
		$enumarateInlineScripts = 0;

		// Generate script file links
		foreach ($document->scripts as $src => $attribs)
		{
			// Check if script uses IE conditional statements.
			$conditional = isset($attribs['options']) && isset($attribs['options']['conditional']) ? $attribs['options']['conditional'] : null;

			// Check if script uses media version.
			if (isset($attribs['options']['version']) && $attribs['options']['version'] && strpos($src, '?') === false
				&& ($mediaVersion || $attribs['options']['version'] !== 'auto'))
			{
				$src .= '?' . ($attribs['options']['version'] === 'auto' ? $mediaVersion : $attribs['options']['version']);
			}

			$buffer .= $tab;

			// This is for IE conditional statements support.
			if (!is_null($conditional))
			{
				$buffer .= '<!--[if ' . $conditional . ']>';
			}

			$buffer .= $src !== 'inlineScript_' . $enumarateInlineScripts ? '<script src="' . $src . '"' : '<script>';

			// Add script tag attributes.
			foreach ($attribs as $attrib => $value)
			{
				// Don't add the 'options' attribute. This attribute is for internal use (version, conditional, etc).
				if ($attrib === 'options')
				{
					continue;
				}

				// Don't add type attribute if document is HTML5 and it's a default mime type. 'mime' is for B/C.
				if (in_array($attrib, array('type', 'mime')) && $document->isHtml5() && in_array($value, $defaultJsMimes))
				{
					continue;
				}

				// B/C: If defer and async is false or empty don't render the attribute.
				if (in_array($attrib, array('defer', 'async')) && !$value)
				{
					continue;
				}

				// Don't add type attribute if document is HTML5 and it's a default mime type. 'mime' is for B/C.
				if ($attrib === 'mime')
				{
					$attrib = 'type';
				}
				// B/C defer and async can be set to yes when using the old method.
				elseif (in_array($attrib, array('defer', 'async')) && $value === true)
				{
					$value = $attrib;
				}

				// Add attribute to script tag output.
				$buffer .= $src !== 'inlineScript_' . $enumarateInlineScripts ? ' ' . htmlspecialchars($attrib, ENT_COMPAT, 'UTF-8') : '';

				if (!($document->isHtml5() && in_array($attrib, $html5NoValueAttributes)))
				{
					// Json encode value if it's an array.
					$value = !is_scalar($value) ? json_encode($value) : $value;

					$buffer .= $src !== 'inlineScript_' . $enumarateInlineScripts ? '="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"' : $lnEnd . $tab . $value;
				}
			}

			$buffer .= $src !== 'inlineScript_' . $enumarateInlineScripts ? '></script>' : $lnEnd . $tab . '</script>';

			// Bump the number of inline script
			$enumarateInlineScripts = preg_match('#inlineScript_#', $src) ? $enumarateInlineScripts + 1 : $enumarateInlineScripts;

			// This is for IE conditional statements support.
			if (!is_null($conditional))
			{
				$buffer .= '<![endif]-->';
			}

			$buffer .= $lnEnd;
		}

		// Output the custom tags - array_unique makes sure that we don't output the same tags twice
		foreach (array_unique($document->_custom) as $custom)
		{
			$buffer .= $tab . $custom . $lnEnd;
		}

		return ltrim($buffer, $tab);
	}
}
