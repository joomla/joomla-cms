<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for all Social Icon JLayouts
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       1.5
 */
abstract class JHtmlSocial
{
	/**
	 * A function that calls the facebook JLayout and then returns the string
	 *
	 * @param   array  $array  The array of variables to control the JLayout.
	 *
	 * @return  string   The facebook like button.
	 *
	 * @since   3.2
	 */
	public static function facebook($array = array())
	{
		$layout = new JLayoutFile('social.facebook');

		return $layout->render($array);
	}

	/**
	 * A function that calls the Google+ +1 JLayout and then returns the string
	 *
	 * @param   array  $array  The array of variables to control the JLayout.
	 *
	 * @return  string   The Google+ +1 button.
	 *
	 * @since   3.2
	 */
	public static function google($array = array())
	{
		$layout = new JLayoutFile('social.google');

		return $layout->render($array);
	}

	/**
	 * A function that calls the twitter follow JLayout and then returns the string
	 *
	 * @param   array  $array  The array of variables to control the JLayout.
	 *
	 * @return  string   The twitter follow button.
	 *
	 * @since   3.2
	 */
	public static function follow($array = array('user' => 'joomla'))
	{
		$layout = new JLayoutFile('social.twitter.follow');

		return $layout->render($array);
	}

	/**
	 * A function that calls the twitter hashtag JLayout and then returns the string
	 *
	 * @param   array  $array  The array of variables to control the JLayout.
	 *
	 * @return  string   The twitter hashtag button.
	 *
	 * @since   3.2
	 */
	public static function hashtag($array = array('hashtag' => 'jpositiv'))
	{
		$layout = new JLayoutFile('social.twitter.hashtag');

		return $layout->render($array);
	}

	/**
	 * A function that calls the twitter mention JLayout and then returns the string
	 *
	 * @param   array  $array  The array of variables to control the JLayout.
	 *
	 * @return  string   The twitter mention button.
	 *
	 * @since   3.2
	 */
	public static function mention($array = array('user' => 'joomla'))
	{
		$layout = new JLayoutFile('social.twitter.mention');

		return $layout->render($array);
	}

	/**
	 * A function that calls the twitter share JLayout and then returns the string
	 *
	 * @param   array  $array  The array of variables to control the JLayout.
	 *
	 * @return  string   The twitter share button.
	 *
	 * @since   3.2
	 */
	public static function share($array = array())
	{
		$layout = new JLayoutFile('social.twitter.share');

		return $layout->render($array);
	}
}
