<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  content.imagelazyload
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Plugin adds the lazyloading attribute on the fly
 *
 * @since  1.0
 */
class PlgContentImageLazyload extends CMSPlugin
{
	/**
	 * Plugin that adds on the fly the loading=lazy attribute to image tags
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   object   &$row     The article object.
	 * @param   mixed    &$params  The article params
	 * @param   integer  $page     The 'page' number
	 *
	 * @return  mixed  Always returns void or true
	 *
	 * @since   4.0.0
	 */
	public function onContentPrepare($context, &$row, &$params, $page = 0)
	{
		if (strpos($row->text, '<img') === false)
		{
			return;
		}

		if (!preg_match_all('/<img\s[^>]+>/', $row->text, $matches))
		{
			return;
		}

		foreach ($matches[0] as $image)
		{
			// Make sure we have a src but no loading attribute
			if (strpos($image, ' src=') !== false && strpos($image, ' loading=') === false)
			{
				$lazyloadImage = str_replace('<img ', '<img loading="lazy" ', $image);
				$row->text = str_replace($image, $lazyloadImage, $row->text);
			}
		}
	}
}
