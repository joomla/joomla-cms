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
 * An end token.
 *
 * @since  __DEPLOY_VERSION__
 */
class JStringTokenEnd extends JStringToken
{
	/**
	 * Constructor.
	 *
	 * @param   JStringTokenDefinition  $tokenDefinition  Token definition object.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct(JStringTokenDefinition $tokenDefinition)
	{
		$this->tokenDefinition = $tokenDefinition;
	}

	/**
	 * Return the name of the token.
	 *
	 * @return  string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function getName()
	{
		return $this->tokenDefinition->name;
	}
}

