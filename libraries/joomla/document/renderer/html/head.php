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
		$lnEnd  = $document->_getLineEnd();

		// Get the meta tags (also base, title, etc.)
		$buffer = $this->fetchMetaTags($document);

		// Get (non-stylesheet) link tags if any
		if (!empty($document->_links))
		{
			$buffer = array_merge($buffer, $this->fetchLinkTags($document));
		}

		// Get the stylesheets
		if (!empty($document->_styleSheets))
		{
			$buffer = array_merge($buffer, $this->fetchStyleSheets($document));
		}

		// Get the style declarations
		if (!empty($document->_style))
		{
			$buffer = array_merge($buffer, $this->fetchStyleDeclarations($document));
		}

		// Get scripts (loaded as external files)
		if (!empty($document->_scripts))
		{
			$buffer = array_merge($buffer, $this->fetchScripts($document));
		}

		// Get script options
		$scriptOptions = $document->getScriptOptions();

		if (!empty($scriptOptions))
		{
			$buffer = array_merge($buffer, $this->fetchScriptOptions($document));
		}

		// Get script declarations
		if (!empty($document->_script))
		{
			$buffer = array_merge($buffer, $this->fetchScriptDeclarations($document));
		}

		// Get translation strings for javascript
		$script = JText::script();

		if (!empty($script))
		{
			$buffer = array_merge($buffer, $this->fetchScriptLanguageDeclarations($document));
		}

		// Get custom tags
		if (!empty($document->_custom))
		{
			$buffer = array_merge($buffer, $this->fetchCustomTags($document));
		}

		return implode($lnEnd, $buffer);
	}

	/**
	 * Gets the meta, title, and base tags as an array.
	 *
	 * @param   JDocumentHtml  $document  The document for which the head will be created
	 *
	 * @return  array  The tags
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function fetchMetaTags($document)
	{
		$tab     = $document->_getTab();
		$isHtml5 = $document->isHtml5();
		$tagEnd  = $isHtml5 ? '>' : '/>';
		$buffer  = array();

		// Generate charset when using HTML5 (should happen first)
		if ($isHtml5)
		{
			$buffer[] = $tab . '<meta charset="' . $document->getCharset() . '"' . $tagEnd;
		}

		// Generate base tag (need to happen early)
		$base = $document->getBase();

		if (!empty($base))
		{
			$buffer[] = $tab . '<base href="' . $base . '"' . $tagEnd;
		}

		// Generate META tags (needs to happen as early as possible in the head)
		foreach ($document->_metaTags as $type => $tag)
		{
			foreach ($tag as $name => $content)
			{
				// Html5 doesn't need content-type and we don't need empty meta tags
				if (($isHtml5 && $type == 'http-equiv' && $name == 'content-type') || empty($content))
				{
					continue;
				}

				$buffer[] = $tab . '<meta ' . $type . '="' . $name . '" content="' . htmlspecialchars($content, ENT_COMPAT, 'UTF-8') . '"' . $tagEnd;
			}
		}

		// Don't add empty descriptions
		$documentDescription = $document->getDescription();

		if ($documentDescription)
		{
			$buffer[] = $tab . '<meta name="description" content="' . htmlspecialchars($documentDescription, ENT_COMPAT, 'UTF-8') . '"' . $tagEnd;
		}

		// Don't add empty generators
		$generator = $document->getGenerator();

		if ($generator)
		{
			$buffer[] = $tab . '<meta name="generator" content="' . htmlspecialchars($generator, ENT_COMPAT, 'UTF-8') . '"' . $tagEnd;
		}

		$buffer[] = $tab . '<title>' . htmlspecialchars($document->getTitle(), ENT_COMPAT, 'UTF-8') . '</title>';

		return $buffer;
	}

	/**
	 * Gets the (non-stylesheet) link tags as an array.
	 *
	 * @param   JDocumentHtml  $document  The document for which the head will be created
	 *
	 * @return  array  The tags
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function fetchLinkTags($document)
	{
		$tab     = $document->_getTab();
		$isHtml5 = $document->isHtml5();
		$tagEnd  = $isHtml5 ? '>' : '/>';
		$buffer  = array();

		// Generate link declarations
		foreach ($document->_links as $link => $linkAtrr)
		{
			$attr = array(
				'href="' . $link . '"',
				$linkAtrr['relType'] . '="' . $linkAtrr['relation'] . '"'
			);

			if (is_array($linkAtrr['attribs']))
			{
				$attr[] = ArrayHelper::toString($linkAtrr['attribs']);
			}

			$buffer[] = $tab . '<link ' . implode(' ', $attr) . $tagEnd;
		}

		return $buffer;
	}

	/**
	 * Gets the (stylesheet) link tags as an array.
	 *
	 * @param   JDocumentHtml  $document  The document for which the head will be created
	 *
	 * @return  array  The tags
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function fetchStyleSheets($document)
	{
		$tab          = $document->_getTab();
		$isHtml5      = $document->isHtml5();
		$mediaVersion = $document->getMediaVersion();
		$tagEnd       = $isHtml5 ? ' >' : ' />';
		$buffer       = array();

		$defaultCssMimes = array('text/css');

		// Generate stylesheet links
		foreach ($document->_styleSheets as $src => $attribs)
		{
			$conditional = null;
			$attr = array('rel="stylesheet"');

			if (isset($attribs['mime']) && (!$isHtml5 || !in_array($attribs['mime'], $defaultCssMimes)))
			{
				$attr[] = 'type="' . $attribs['mime'] . '"';
			}

			if (isset($attribs['options']) && is_array($attribs['options']))
			{
				$options = $attribs['options'];

 				$conditional = isset($options['conditional']) ? $options['conditional'] : null;

				if (isset($options['version']) && $options['version'] && strpos($src, '?') === false
					&& ($mediaVersion || $options['version'] !== 'auto'))
  				{
  					$src .= '?' . ($options['version'] === 'auto' ? $mediaVersion : $options['version']);
				}
			}

			$attr[] = 'href="' . $src . '"';

			$buffer[] = $tab;

			// This is for IE conditional statements support.
			if (!is_null($conditional))
			{
				$attr[] = 'media="' . $attribs['media'] . '"';
			}

			if (isset($attribs['attribs']) && is_array($attribs['attribs']) && !empty($attribs['attribs']))
			{
				$attr[] = ArrayHelper::toString($attribs['attribs']);
			}

			$buffer[] = $tab . '<link ' . implode(' ', $attr) . $tagEnd;
		}

		return $buffer;
	}

	/**
	 * Gets the style tags as an array.
	 *
	 * @param   JDocumentHtml  $document  The document for which the head will be created
	 *
	 * @return  array  The tags
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function fetchStyleDeclarations($document)
	{
		$tab    = $document->_getTab();
		$buffer = array();

		$defaultCssMimes = array('text/css');

		// Generate stylesheet declarations
		foreach ($document->_style as $type => $content)
		{
			$openTag = $tab . '<style';

			if (!is_null($type) && (!$document->isHtml5() || !in_array($type, $defaultCssMimes)))
			{
				$openTag .= ' type="' . $type . '"';
			}

			$openTag .= '>';

			$buffer[] = $openTag;

			// This is for full XHTML support.
			if ($document->_mime != 'text/html')
			{
				$buffer[] = $tab . $tab . '/*<![CDATA[*/';
			}

			$buffer[] = $content;

			// See above note
			if ($document->_mime != 'text/html')
			{
				$buffer[] = $tab . $tab . '/*]]>*/';
			}

			$buffer[] = $tab . '</style>';
		}

		return $buffer;
	}

	/**
	 * Gets the external file loading script tags as an array.
	 *
	 * @param   JDocumentHtml  $document  The document for which the head will be created
	 *
	 * @return  array  The tags
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function fetchScripts($document)
	{
		$tab     = $document->_getTab();
		$isHtml5 = $document->isHtml5();
		$buffer  = array();

		$defaultJsMimes         = array('text/javascript', 'application/javascript', 'text/x-javascript', 'application/x-javascript');
		$html5NoValueAttributes = array('defer', 'async');
		$mediaVersion           = $document->getMediaVersion();

		// Generate script file links
		foreach ($document->_scripts as $src => $attribs)
		{
			// Check if script uses media version.
			if (isset($attribs['options']['version']) && $attribs['options']['version'] && strpos($src, '?') === false
				&& ($mediaVersion || $attribs['options']['version'] !== 'auto'))
			{
				$src .= '?' . ($attribs['options']['version'] === 'auto' ? $mediaVersion : $attribs['options']['version']);
			}

			$attr = array('src="' . $src . '"');

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
				$attribValue = htmlspecialchars($attrib, ENT_COMPAT, 'UTF-8');

				if (!($document->isHtml5() && in_array($attrib, $html5NoValueAttributes)))
				{
					// Json encode value if it's an array.
					$value = !is_scalar($value) ? json_encode($value) : $value;

					$attribValue .= '="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"';
				}

				$attr[] = $attribValue;
			}

			// Check if script uses IE conditional statements.
			$conditional = isset($attribs['options']) && isset($attribs['options']['conditional']) ? $attribs['options']['conditional'] : null;

			$buffer[] = $tab .
				(is_null($conditional) ? '' : '<!--[if ' . $conditional . ']>') .
				'<script ' . implode(' ', $attr) . '></script>' .
				(is_null($conditional) ? '' : '<![endif]-->');
		}

		return $buffer;
	}

	/**
	 * Gets a script tag containing the Joomla options storage.
	 *
	 * @param   JDocumentHtml  $document  The document for which the head will be created
	 *
	 * @return  array  The tags
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function fetchScriptOptions($document)
	{
		// Generate scripts options
		$scriptOptions = $document->getScriptOptions();

		if (empty($scriptOptions))
		{
			return array();
		}

		$tab     = $document->_getTab();

		$prettyPrint  = (JDEBUG && defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : false);
		$jsonOptions = json_encode($scriptOptions, $prettyPrint);
		$jsonOptions = $jsonOptions ? $jsonOptions : '{}';

		$buffer = $tab .
			'<script type="application/json" class="joomla-script-options new">' .
			$jsonOptions .
			'</script>';

		return array($buffer);
	}

	/**
	 * Gets a script tag containing javascript declarations as an array.
	 *
	 * @param   JDocumentHtml  $document  The document for which the head will be created
	 *
	 * @return  array  The tags
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function fetchScriptDeclarations($document)
	{
		$tab     = $document->_getTab();
		$isHtml5 = $document->isHtml5();
		$buffer  = array();

		$defaultJsMimes = array('text/javascript', 'application/javascript', 'text/x-javascript', 'application/x-javascript');

		// Generate script declarations
		foreach ($document->_script as $type => $content)
		{
			$openTag = $tab . '<script';

			if (!is_null($type) && (!$isHtml5 || !in_array($type, $defaultJsMimes)))
			{
				$openTag .= ' type="' . $type . '"';
			}

			$openTag .= '>';

			$buffer[] = $openTag;

			// This is for full XHTML support.
			if ($document->_mime != 'text/html')
			{
				$buffer[] = $tab . $tab . '//<![CDATA[';
			}

			$buffer[] = $content;

			// See above note
			if ($document->_mime != 'text/html')
			{
				$buffer[] = $tab . $tab . '//]]>';
			}

			$buffer[] = $tab . '</script>';
		}

		return $buffer;
	}

	/**
	 * Gets a script tag that loads translation strings.
	 *
	 * @param   JDocumentHtml  $document  The document for which the head will be created
	 *
	 * @return  array  The tags
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function fetchScriptLanguageDeclarations($document)
	{
		$script = JText::script();

		if (empty($script))
		{
			return array();
		}

		$tab     = $document->_getTab();
		$isHtml5 = $document->isHtml5();
		$buffer  = array();

		// Generate script language declarations.
		$openTag = $tab . '<script';

		if (!$document->isHtml5())
		{
			$openTag .= ' type="text/javascript"';
		}

		$openTag .= '>';

		$buffer[] = $openTag;

		if ($document->_mime != 'text/html')
		{
			$buffer[] = $tab . $tab . '//<![CDATA[';
		}

		// Why is this inside of a closure?
		$buffer[] = $tab . $tab . '(function() {';
		$buffer[] = $tab . $tab . $tab . 'Joomla.JText.load(' . json_encode($script) . ');';
		$buffer[] = $tab . $tab . '})();';

		if ($document->_mime != 'text/html')
		{
			$buffer[] = $tab . $tab . '//]]>';
		}

		$buffer[] = $tab . '</script>';

		return $buffer;
	}

	/**
	 * Gets the custom tags as an array.
	 *
	 * @param   JDocumentHtml  $document  The document for which the head will be created
	 *
	 * @return  array  The tags
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function fetchCustomTags($document)
	{
		$tab     = $document->_getTab();
		$buffer = array();

		// Output the custom tags - array_unique makes sure that we don't output the same tags twice
		foreach (array_unique($document->_custom) as $custom)
		{
			$buffer[] = $tab . $custom;
		}

		return ltrim($buffer, $tab);
	}
}
