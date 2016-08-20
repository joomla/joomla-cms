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
 * Token type definition.
 *
 * @since __DEPLOY_VERSION__
 */
class JStringTokenDefinition
{
	/**
	 * Name of the token.  Example "loadposition".
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public $name = '';

	/**
	 * Function that will be called to translate the token.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public $callback = null;

	/**
	 * Flag which is set to indicate that this token is simple.
	 *
	 * A simple token looks like {name} and will be replaced in its entirety.
	 * A block token has a matching end block token which looks like {/name}.
	 * The begin and end block tokens and everything in between will be replaced.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public $simple = true;

	/**
	 * Constructor.
	 *
	 * @param   string    $name      Token name.
	 * @param   callable  $callback  Callable which will return the replacement string.
	 * @param   boolean   $simple    True if token is simple; false if token is block.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct($name, callable $callback, $simple = true)
	{
		$this->name = JString::strtolower($name);
		$this->callback = $callback;
		$this->simple = (boolean) $simple;
	}
}

