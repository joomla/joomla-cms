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
 * Token interface.
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class JStringToken
{
	/**
	 * Name of the token..
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $name = '';

	/**
	 * Token definition object.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $tokenDefinition = null;

	/**
	 * String content.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $content = '';

	/**
	 * Parameters.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $params = array();

	/**
	 * Return the name of the token.
	 *
	 * @return  string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Return the parameters associated with the token.
	 *
	 * @return  array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function getParams()
	{
		return $this->params;
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
		return $content;
	}
}

