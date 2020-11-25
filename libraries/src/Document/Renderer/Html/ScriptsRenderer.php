<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Document\Renderer\Html;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Document\DocumentRenderer;
use Joomla\CMS\WebAsset\WebAssetItemInterface;

/**
 * JDocument head renderer
 *
 * @since  4.0.0
 */
class ScriptsRenderer extends DocumentRenderer
{
	/**
	 * List of already rendered src
	 *
	 * @var array
	 *
	 * @since   4.0.0
	 */
	private $renderedSrc = [];

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
		$wam          = $this->_doc->getWebAssetManager();
		$assets       = $wam->getAssets('script', true);

		// Get a list of inline assets and their relation with regular assets
		$inlineAssets   = $wam->filterOutInlineAssets($assets);
		$inlineRelation = $wam->getInlineRelation($inlineAssets);

		// Merge with existing scripts, for rendering
		$assets = array_merge(array_values($assets), $this->_doc->_scripts);

		// Add Script Options as inline asset
		$scriptOptions = $this->_doc->getScriptOptions();

		if ($scriptOptions)
		{
			$prettyPrint = (JDEBUG && \defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : false);
			$jsonOptions = json_encode($scriptOptions, $prettyPrint);
			$jsonOptions = $jsonOptions ? $jsonOptions : '{}';
			$nonce       = $this->_doc->cspNonce ? ' nonce="' . $this->_doc->cspNonce . '"' : '';

			$buffer .= '<script type="application/json" class="joomla-script-options new"' . $nonce . '>' . $jsonOptions . '</script>';
		}

		$buffer .= '<script type=module' . $nonce . '>!function(e,t,n){!("noModule"in(t=e.createElement("script")))&&"onbeforeload"in t&&(n=!1,e.addEventListener("beforeload",function(e){if(e.target===t)n=!0;else if(!e.target.hasAttribute("nomodule")||!n)return;e.preventDefault()},!0),t.type="module",t.src=".",e.head.appendChild(t),t.remove())}(document)</script>';

		// Generate script file links
		foreach ($assets as $key => $item)
		{
			// Check whether we have an Asset instance, or old array with attributes
			$asset = $item instanceof WebAssetItemInterface ? $item : null;

			// Add src attribute for non Asset item
			if (!$asset)
			{
				$item['src'] = $key;
			}

			// Check for inline content "before"
			if ($asset && !empty($inlineRelation[$asset->getName()]['before']))
			{
				foreach ($inlineRelation[$asset->getName()]['before'] as $itemBefore)
				{
					$buffer .= $this->renderInlineElement($itemBefore);

					// Remove this item from inline queue
					unset($inlineAssets[$itemBefore->getName()]);
				}
			}

			$buffer .= $this->renderElement($item);

			// Check for inline content "after"
			if ($asset && !empty($inlineRelation[$asset->getName()]['after']))
			{
				foreach ($inlineRelation[$asset->getName()]['after'] as $itemBefore)
				{
					$buffer .= $this->renderInlineElement($itemBefore);

					// Remove this item from inline queue
					unset($inlineAssets[$itemBefore->getName()]);
				}
			}
		}

		// Generate script declarations for assets
		foreach ($inlineAssets as $item)
		{
			$buffer .= $this->renderInlineElement($item);
		}

		// Generate script declarations for old scripts
		foreach ($this->_doc->_script as $type => $contents)
		{
			// Test for B.C. in case someone still store script declarations as single string
			if (\is_string($contents))
			{
				$contents = [$contents];
			}

			foreach ($contents as $content)
			{
				$buffer .= $this->renderInlineElement(
					[
						'type' => $type,
						'content' => $content,
					]
				);
			}
		}

		return ltrim($buffer, $tab);
	}

	/**
	 * Renders the element
	 *
	 * @param   WebAssetItemInterface|array  $item  The element
	 *
	 * @return  string  The resulting string
	 *
	 * @since   4.0.0
	 */
	private function renderElement($item) : string
	{
		$buffer = '';
		$asset  = $item instanceof WebAssetItemInterface ? $item : null;
		$src    = $asset ? $asset->getUri() : ($item['src'] ?? '');

		// Make sure we have a src, and it not already rendered
		if (!$src || !empty($this->renderedSrc[$src]) || ($asset && $asset->getOption('webcomponent')))
		{
			return '';
		}

		$lnEnd        = $this->_doc->_getLineEnd();
		$tab          = $this->_doc->_getTab();

		// Get the attributes and other options
		if ($asset)
		{
			$attribs     = $asset->getAttributes();
			$version     = $asset->getVersion();

			// Add an asset info for debugging
			if (JDEBUG)
			{
				$attribs['data-asset-name'] = $asset->getName();

				if ($asset->getDependencies())
				{
					$attribs['data-asset-dependencies'] = implode(',', $asset->getDependencies());
				}
			}
		}
		else
		{
			$attribs     = $item;
			$version     = isset($attribs['options']['version']) ? $attribs['options']['version'] : '';
		}

		// To prevent double rendering
		$this->renderedSrc[$src] = true;

		return $this->checkEsAlternative($src, $attribs, $version);

		// Check if script uses media version.
//		if ($version && strpos($src, '?') === false && ($mediaVersion || $version !== 'auto'))
//		{
//			$src .= '?' . ($version === 'auto' ? $mediaVersion : $version);
//		}
//
//		// Render the element with attributes
//		$buffer .= $tab . '<script src="' . htmlspecialchars($src) . '"';
//		$buffer .= $this->renderAttributes($attribs, true);
//		$buffer .= '></script>';
//
//		$buffer .= $lnEnd;

		return $buffer;
	}

	/**
	 * Renders the inline element
	 *
	 * @param   WebAssetItemInterface|array  $item  The element
	 *
	 * @return  string  The resulting string
	 *
	 * @since   4.0.0
	 */
	private function renderInlineElement($item) : string
	{
		if ($item instanceof WebAssetItemInterface)
		{
			$attribs = $item->getAttributes();
			$content = $item->getOption('content');
		}
		else
		{
			$attribs = $item;
			$content = $item['content'] ?? '';

			unset($attribs['content']);
		}

		// Do not produce empty elements
		if (!$content)
		{
			return '';
		}

		// Add "nonce" attribute if exist
		if ($this->_doc->cspNonce)
		{
			$attribs['nonce'] = $this->_doc->cspNonce;
		}

		$finalAttribs = $this->renderAttributes($attribs, true);
		$attribs['src'] = 'data:text/javascript;base64,' . base64_encode($content);
		$content = '';

		$finalSrc = !empty($attribs['src']) ? ' src="' . $attribs['src'] . '"' : '';

		return $this->_doc->_getTab() . '<script ' . $finalAttribs . $finalSrc . '>' . $content . '</script>' . $this->_doc->_getLineEnd();
	}

	/**
	 * Renders the element attributes
	 *
	 * @param array $attributes The element attributes
	 * @param bool  $forceDefer Force the defer attribute
	 *
	 * @return  string  The attributes string
	 *
	 * @since   4.0.0
	 */
	private function renderAttributes(array $attributes, bool $forceDefer) : string
	{
		$buffer = '';

		$defaultJsMimes         = array('text/javascript', 'application/javascript', 'text/x-javascript', 'application/x-javascript');
		$html5NoValueAttributes = array('defer', 'async');

		foreach ($attributes as $attrib => $value)
		{
			// Don't add the 'options' attribute. This attribute is for internal use (version, conditional, etc).
			if ($attrib === 'options' || $attrib === 'src')
			{
				continue;
			}

			// Don't add type attribute if document is HTML5 and it's a default mime type. 'mime' is for B/C.
			if (\in_array($attrib, array('type', 'mime')) && $this->_doc->isHtml5() && \in_array($value, $defaultJsMimes))
			{
				continue;
			}

			// B/C: If defer and async is false or empty don't render the attribute.
			if (\in_array($attrib, array('defer', 'async')) && !$value)
			{
				continue;
			}

			// Don't add type attribute if document is HTML5 and it's a default mime type. 'mime' is for B/C.
			if ($attrib === 'mime')
			{
				$attrib = 'type';
			}
			// B/C defer and async can be set to yes when using the old method.
			elseif (\in_array($attrib, array('defer', 'async')) && $value === true)
			{
				$value = $attrib;
			}

			// Add attribute to script tag output.
			$buffer .= ' ' . htmlspecialchars($attrib, ENT_COMPAT, 'UTF-8');

			if (!($this->_doc->isHtml5() && \in_array($attrib, $html5NoValueAttributes)))
			{
				// Json encode value if it's an array.
				$value = !is_scalar($value) ? json_encode($value) : $value;

				$buffer .= '="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"';
			}
		}

		if ($forceDefer && !isset($attributes['async']) && !isset($attributes['defer']))
		{
			$buffer .= ' defer';
		}

		return $buffer;
	}

	/**
	 * @param   string  $src  The source string
	 *
	 * @return  string|string[]
	 */
	public function checkEsAlternative(string $src, $attribs, $version)
	{
		$mediaVersion = $this->_doc->getMediaVersion();

		if (strpos('http://', $src) || strpos('https://', $src)  || strpos('//', $src))
		{
			return '';
		}

		$testx = '';

		// The given file IS ES6
		if (strpos('.es6.', $src))
		{
			if (!strpos('.min.js', $src))
			{
				$newSrc = str_replace('.es6.js', '.js', $src);
			}
			else
			{
				$newSrc = str_replace('.es6.min.js', '.min.js', $src);
			}

			if (!empty($newSrc) && is_file(JPATH_ROOT . '/' . $newSrc))
			{
				// Check if script uses media version.
				if ($version && strpos($src, '?') === false && ($mediaVersion || $version !== 'auto'))
				{
					$newSrc .= '?' . ($version === 'auto' ? $mediaVersion : $version);
				}

				$attribs['nomodule'] = true;

				// Render the element with attributes
				$testx = '<script src="' . htmlspecialchars($newSrc) . '"';
				$testx .= $this->renderAttributes($attribs, true);
				$testx .= '></script>';
			}
		}

		// The given file IS ES5
		if (!strpos($src, '.es6.'))
		{
			if (!strpos($src, '.min.'))
			{
				$newSrc = str_replace('.js', '.es6.js', $src);
			}
			else
			{
				$newSrc = str_replace('.min.js', '.es6.min.js', $src);
			}

			if (!empty($newSrc) && is_file(JPATH_ROOT .  $newSrc))
			{
				// Check if script uses media version.
				if ($version && strpos($newSrc, '?') === false && ($mediaVersion || $version !== 'auto'))
				{
					$newSrc .= '?' . ($version === 'auto' ? $mediaVersion : $version);
				}

				$attribs['type'] = 'module';

				// Render the element with attributes
				$testx .= '<script src="' . htmlspecialchars($newSrc) . '"';
				$testx .= $this->renderAttributes($attribs, false);
				$testx .= '></script>';
			}

			$noModule = '';
			if (isset($attribs['type']))
			{
				unset($attribs['type']);
				$noModule = ' nomodule';
			}


			// Check if script uses media version.
			if ($version && strpos($src, '?') === false && ($mediaVersion || $version !== 'auto'))
			{
				$src .= '?' . ($version === 'auto' ? $mediaVersion : $version);
			}

			// Render the element with attributes
			$testx .= '<script src="' . htmlspecialchars($src) . '"';
			$testx .= $this->renderAttributes($attribs, true);
			$testx .= $noModule. '></script>';

		}

		return $testx;
	}
}
