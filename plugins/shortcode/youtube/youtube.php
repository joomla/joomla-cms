<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Shortcode.YouTube
 * *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * YouTube Shortcode Plugin.
 *
 * @package     Joomla.Plugin
 * @subpackage  Shortcode.YouTube
 * @since       3.2
 */
class PlgShortcodeYoutube extends PlgContentShortcodes
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

		$this->addShortcode('youtube', array($this, 'youtube'));

		$article->text = $this->doShortcode($article->text);
	}

	/**
	 * Method to a YouTube video from the [youtube] shortcode.
	 *
	 * @param   string  $atts  User defined attributes in shortcode tag.
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public function youtube()
	{
		$id = $this->params->get('id');

		if (empty($id))
		{
			return;
		}

		return '<iframe title="YouTube video player" width="' . $this->params->get('width') . '" height="' . $this->params->get('height') . '" src="http://www.youtube.com/embed/' . $id . '" frameborder="0" allowfullscreen></iframe>';
	}
}
