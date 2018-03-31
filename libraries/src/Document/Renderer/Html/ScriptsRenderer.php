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
 * JDocument head renderer
 *
 * @since  4.0.0
 */
class ScriptsRenderer extends DocumentRenderer
{
	/**
	 * Renders the document script tags and returns the results as a string
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
		$buffer       = '';
		$mediaVersion = $this->_doc->getMediaVersion();

		$defaultJsMimes         = array('text/javascript', 'application/javascript', 'text/x-javascript', 'application/x-javascript');
		$html5NoValueAttributes = array('defer', 'async');

		// Generate script file links
		foreach ($this->_doc->_scripts as $src => $attribs)
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

			$buffer .= '<script src="' . $src . '"';

			// Add script tag attributes.
			foreach ($attribs as $attrib => $value)
			{
				// Don't add the 'options' attribute. This attribute is for internal use (version, conditional, etc).
				if ($attrib === 'options')
				{
					continue;
				}

				// Don't add type attribute if document is HTML5 and it's a default mime type. 'mime' is for B/C.
				if (in_array($attrib, array('type', 'mime')) && $this->_doc->isHtml5() && in_array($value, $defaultJsMimes))
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
				$buffer .= ' ' . htmlspecialchars($attrib, ENT_COMPAT, 'UTF-8');

				if (!($this->_doc->isHtml5() && in_array($attrib, $html5NoValueAttributes)))
				{
					// Json encode value if it's an array.
					$value = !is_scalar($value) ? json_encode($value) : $value;

					$buffer .= '="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"';
				}
			}

			$buffer .= '></script>';

			// This is for IE conditional statements support.
			if (!is_null($conditional))
			{
				$buffer .= '<![endif]-->';
			}

			$buffer .= $lnEnd;
		}

		// Generate script declarations
		foreach ($this->_doc->_script as $type => $contents)
		{
			$buffer .= $tab . '<script';

			if (!is_null($type) && (!$this->_doc->isHtml5() || !in_array($type, $defaultJsMimes)))
			{
				$buffer .= ' type="' . $type . '"';
			}

			$buffer .= '>' . $lnEnd;

			// This is for full XHTML support.
			if ($this->_doc->_mime != 'text/html')
			{
				$buffer .= $tab . $tab . '//<![CDATA[' . $lnEnd;
			}

			$buffer .= $contents . $lnEnd;

			// See above note
			if ($this->_doc->_mime != 'text/html')
			{
				$buffer .= $tab . $tab . '//]]>' . $lnEnd;
			}

			$buffer .= $tab . '</script>' . $lnEnd;
		}


		// Output the custom tags - array_unique makes sure that we don't output the same tags twice
		foreach (array_unique($this->_doc->_custom) as $custom)
		{
			$buffer .= $tab . $custom . $lnEnd;
		}

		return ltrim($buffer, $tab);
	}
}
