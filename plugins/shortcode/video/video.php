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
class PlgShortcodeVideo extends PlgContentShortcodes
{
	/**
	 * @var JApplication
	 */
	protected $app;

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
		if ($this->app->isAdmin())
		{
			return true;
		}

		// Register the callbacks to process the shortcodes.
		$this->addShortcode('vimeo', array($this, 'vimeo'));
		$this->addShortcode('youtube', array($this, 'youtube'));

		// Since $article is a reference, we manipulate the content directly.
		$article->text = $this->doShortcode($article->text);
	}

	/**
	 * Method to a YouTube video from the [youtube] shortcode.
	 *
	 * @param   string  $atts  User defined attributes in shortcode tag.
	 *
	 * @return  mixed
	 *
	 * @since   3.2
	 */
	public function vimeo()
	{
		$id = $this->params->get('id');

		if (empty($id))
		{
			return false;
		}

		return '<iframe src="//player.vimeo.com/video/' . $id . '?title=0&amp;portrait=0&amp;color=7a9d47" width="' .
			$this->params->get('width') . '" height="' . $this->params->get('height') .
			'" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
	}

	/**
	 * Method to a YouTube video from the [youtube] shortcode.
	 *
	 * @param   string  $atts  User defined attributes in shortcode tag.
	 *
	 * @return  mixed
	 *
	 * @since   3.2
	 */
	public function youtube()
	{
		$id = $this->params->get('id');

		if (empty($id))
		{
			return false;
		}

		return '<iframe title="YouTube Video Player" width="' . $this->params->get('width') .
			'" height="' . $this->params->get('height') . '" src="http://www.youtube.com/embed/' . $id .
			'" frameborder="0"  webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
	}
}
