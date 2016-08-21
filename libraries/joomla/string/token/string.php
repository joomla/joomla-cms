<?php
/**
 * @package     Joomla.Platform
 * @subpackage  String
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * A string token.
 *
 * @since  __DEPLOY_VERSION__
 */
class JStringTokenString extends JStringToken
{
	/**
	 * Constructor.
	 *
	 * @param   string  $content  String to be represented as a token.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct($content)
	{
		$this->content = $content;
	}

	/**
	 * Return the translated value of the token.
	 *
	 * @param   string  $content  Possible content to be translated.
	 *
	 * @return  string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function getValue($content = '')
	{
		return $this->content;
	}
}

