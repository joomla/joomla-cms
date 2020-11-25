<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document\Renderer\Html;

use Joomla\CMS\Factory;

\defined('JPATH_PLATFORM') or die;

/**
 * Trait that fixes assets
 *
 * @since  __DEPLOY_VERSION__
 */
trait FixAssets
{
	/**
	 * Function that will enforce nonce and defer to scripts to use the API.
	 *
	 * @param   string   $asset    The array of the html tags
	 * @param   boolean  $inPlace  Flag to fix in place or not
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function fixAsset($asset, $inPlace = false)
	{
		$dom = new \DOMDocument;
		$wa  = Factory::getDocument()->getWebAssetManager();

		\libxml_use_internal_errors(true);
		$dom->loadHtml($asset);
		\libxml_clear_errors();

		$script = $dom->getElementsByTagName('script')->item(0);
		$style  = $dom->getElementsByTagName('style')->item(0);

		// Case script
		if ($script)
		{
			$src = $script->getAttribute('src');

			if ($src)
			{
				$attrs = [];

				for ($i = 0; $i < $script->attributes->length; ++$i)
				{
					$node = $script->attributes->item($i);

					if ($node->nodeName !== 'src')
					{
						$attrs[$node->nodeName] = $node->nodeValue;
					}
				}

				// Defer by default
				if (!isset($attrs['defer']) && (!isset($attrs['type']) || $attrs['type'] !== 'module') && !isset($attrs['data-joomla-no-defer']))
				{
					$attrs['defer'] = '';
				}

				// Add missing nonce
				if (!isset($attrs['nonce']))
				{
					$attrs['nonce'] = $this->_doc->nonce;
				}

				if (!$inPlace)
				{
					$wa->registerAndUseScript(
						md5($src),
						$src,
						[],
						$attrs
					);

					return '';
				}

				return $dom->saveXML();
			}
			else
			{
				$type = $script->getAttribute('type');
				$noDefer = $script->hasAttribute('data-joomla-no-defer');

				if ($noDefer || $type === 'module')
				{
					// Add missing nonce
					if (!isset($attrs['nonce']))
					{
						$attrs['nonce'] = $this->_doc->nonce;
					}

					return $dom->saveXML();
				}

				// Add missing nonce
				if (!isset($attrs['nonce']))
				{
					$attrs['nonce'] = $this->_doc->cspNonce;
				}

				if (in_array($type, ["application/javascript", "text/javascript", '']))
				{
					if (!$inPlace)
					{
						$wa->addInlineScript(
							$script->nodeValue,
							['name' => md5($script->nodeValue)],
							[],
							[]
						);

						return '';
					}

					return $dom->saveXML();
				}
			}

			return '';
		}

		// Case inline style
		if ($style)
		{
			// Add missing nonce
			if (!isset($attrs['nonce']))
			{
				$attrs['nonce'] = $this->_doc->nonce;
			}

			if ($style->hasAttribute('data-joomla-no-defer'))
			{
				return $dom->saveXML();
			}

			if (!$inPlace)
			{
				$wa->addInlineStyle(
					$style->nodeValue,
					['name' => md5($style->nodeValue)],
					[],
					[]
				);

				return '';
			}

			return $dom->saveXML();
		}
	}

	/**
	 * Method that fixes the head assets inserted with addCustomTag
	 *
	 * @return void
	 */
	public function fixCustom()
	{
		foreach ($this->_doc->_custom as $id => $custom)
		{
			$result = $this->fixAsset($custom, false);

			if (!$result)
			{
				// Remove from the array
				unset($this->_doc->_custom[$id]);
			}
		}
	}

	/**
	 * Method that fixes the static assets in an html fragment
	 *
	 * @param   string  $content  The html fragment
	 *
	 * @return string
	 */
	public function fixAssets($content)
	{
		preg_match_all('/<script(.|\s)*?<\/script>/i', $content, $scripts);
		preg_match_all('/<style(.|\s)*?<\/style>/i', $content, $styles);

		// Bail quickly
		if (count($scripts[0]) === 0 && count($styles[0]) === 0)
		{
			return $content;
		}

		if (count($scripts[0]))
		{
			foreach ($scripts[0] as $script)
			{
				$newSrcipt = $this->fixAsset($script, true);

				if ($newSrcipt !== '')
				{
					preg_replace($script, $newSrcipt, $content);
				}
			}
		}

		if (count($styles[0]))
		{
			foreach ($styles[0] as $style)
			{
				$newStyle = $this->fixAsset($style, true);

				if ($newStyle !== '')
				{
					preg_replace($style, $newStyle, $content);
				}
			}
		}

		return $content;
	}
}
