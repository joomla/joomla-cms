<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Document\Renderer\Html;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Document\DocumentRenderer;

/**
 * JDocument styles renderer
 *
 * @since  4.0.0
 */
class StylesRenderer extends DocumentRenderer
{
	/**
	 * Renders the document stylesheets and style tags and returns the results as a string
	 *
	 * @param   string  $head     (unused)
	 * @param   array   $params   Associative array of values
	 * @param   string  $content  The script
	 *
	 * @return  string  The output of the script
	 *
	 * @since   4.0.0
	 */
	public function render($head, $params = array(), $content = null)
	{
		// Get line endings
		$lnEnd        = $this->_doc->_getLineEnd();
		$tab          = $this->_doc->_getTab();
		$tagEnd       = ' />';
		$buffer       = '';
		$mediaVersion = $this->_doc->getMediaVersion();

		$defaultCssMimes = array('text/css');

		// Generate stylesheet links
		foreach ($this->_doc->_styleSheets as $src => $attribs)
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

			$buffer .= '<link href="' . $src . '" rel="stylesheet"';

			// Add script tag attributes.
			foreach ($attribs as $attrib => $value)
			{
				// Don't add the 'options' attribute. This attribute is for internal use (version, conditional, etc).
				if ($attrib === 'options')
				{
					continue;
				}

				// Don't add type attribute if document is HTML5 and it's a default mime type. 'mime' is for B/C.
				if (in_array($attrib, array('type', 'mime')) && $this->_doc->isHtml5() && in_array($value, $defaultCssMimes))
				{
					continue;
				}

				// Don't add type attribute if document is HTML5 and it's a default mime type. 'mime' is for B/C.
				if ($attrib === 'mime')
				{
					$attrib = 'type';
				}

				// Add attribute to script tag output.
				$buffer .= ' ' . htmlspecialchars($attrib, ENT_COMPAT, 'UTF-8');

				// Json encode value if it's an array.
				$value = !is_scalar($value) ? json_encode($value) : $value;

				$buffer .= '="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"';
			}

			$buffer .= $tagEnd;

			// This is for IE conditional statements support.
			if (!is_null($conditional))
			{
				$buffer .= '<![endif]-->';
			}

			$buffer .= $lnEnd;
		}

		// Generate stylesheet declarations
		foreach ($this->_doc->_style as $type => $contents)
		{
			$buffer .= $tab . '<style';

			if (!is_null($type) && (!$this->_doc->isHtml5() || !in_array($type, $defaultCssMimes)))
			{
				$buffer .= ' type="' . $type . '"';
			}

			$buffer .= '>' . $lnEnd;

			// This is for full XHTML support.
			if ($this->_doc->_mime != 'text/html')
			{
				$buffer .= $tab . $tab . '/*<![CDATA[*/' . $lnEnd;
			}

			$buffer .= $contents . $lnEnd;

			// See above note
			if ($this->_doc->_mime != 'text/html')
			{
				$buffer .= $tab . $tab . '/*]]>*/' . $lnEnd;
			}

			$buffer .= $tab . '</style>' . $lnEnd;
		}

		// Generate scripts options
		$scriptOptions = $this->_doc->getScriptOptions();

		if (!empty($scriptOptions))
		{
			$buffer .= $tab . '<script type="application/json" class="joomla-script-options new">';

			$prettyPrint = (JDEBUG && defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : false);
			$jsonOptions = json_encode($scriptOptions, $prettyPrint);
			$jsonOptions = $jsonOptions ? $jsonOptions : '{}';

			$buffer .= $jsonOptions;
			$buffer .= '</script>' . $lnEnd;
		}

		return ltrim($buffer, $tab);
	}
}
