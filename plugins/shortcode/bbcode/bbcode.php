<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Shortcode.BBCode
 * *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * BBCode Shortcode Plugin.
 *
 * @package     Joomla.Plugin
 * @subpackage  Shortcode.BBCode
 * @since       3.2
 */
class PlgShortcodeBbcode extends PlgContentShortcodes
{
	/**
	 * Method to catch the onAfterDispatch event.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function onShortcodePrepare($context, &$article, &$params)
	{
		// Check that we are in the site application.
		if (JFactory::getApplication()->isAdmin())
		{
			return true;
		}

		$this->addShortcode('b', array($this, 'bTag'));
		$this->addShortcode('i', array($this, 'iTag'));
		$this->addShortcode('u', array($this, 'uTag'));
		$this->addShortcode('url', array($this, 'urlTag'));
		$this->addShortcode('img', array($this, 'imgTag'));
		$this->addShortcode('quote', array($this, 'quoteTag'));

		$article->text = $this->doShortcode($article->text);
	}

	/**
	 * <b> tag support for bbcode
	 *
	 * @param   string  $content  The contents between the [b]content[/b] tags.
	 *
	 * @return  string  Formatted string.
	 *
	 * @since   3.2
	 */
	public function bTag($content)
	{
		return '<b>' . $content . '</b>';
	}

	/**
	 * <i> tag support for bbcode
	 *
	 * @param   string  $content  The contents between the [i]content[/i] tags.
	 *
	 * @return  string  Formatted string.
	 *
	 * @since   3.2
	 */
	public function iTag($content)
	{
		return '<i>' . $content . '</i>';
	}

	/**
	 * <u> tag support for bbcode
	 *
	 * @param   string  $content  The contents between the [u]content[/u] tags.
	 *
	 * @return  string  Formatted string.
	 *
	 * @since   3.2
	 */
	public function uTag($content)
	{
		return '<u>' . $content . '</u>';
	}

	/**
	 * Url tag support for bbcode
	 *
	 * @param   string  $content  The contents between the [url]content[/url] tags.
	 *
	 * @return  string  Formatted string.
	 *
	 * @since   3.2
	 */
	public function urlTag($content)
	{
		return '<a href="' . $content . '">' . $content . '</a>';
	}

	/**
	 * <img> tag support for bbcode
	 *
	 * @param   string  $content  The contents between the [url]content[/url] tags.
	 *
	 * @return  string  Formatted string.
	 *
	 * @since   3.2
	 */
	public function imgTag($content)
	{
		return '<img src="' . $content . '" alt="' . $this->params->get('alt') . '" />';
	}

	/**
	 * <u> tag support for bbcode
	 *
	 * @param   string  $content  The contents between the [u]content[/u] tags.
	 *
	 * @return  string  Formatted string.
	 *
	 * @since   3.2
	 */
	public function quoteTag($content)
	{
		return '<blockquote><p>' . $content . '</p></blockquote>';
	}
}
