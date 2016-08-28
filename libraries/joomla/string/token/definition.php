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
 * @since  __DEPLOY_VERSION__
 */
abstract class JStringTokenDefinition
{
	/**
	 * Bound variable.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public $bound = null;

	/**
	 * Callback.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public $callback = null;

	/**
	 * JLayoutFile object.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public $layout = null;

	/**
	 * Constructor.
	 *
	 * @param   mixed  $bound  An optional value or callable to bind to the token.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct($bound = null)
	{
		$this->bound	= $bound;
	}

	/**
	 * Assign a callback function.
	 *
	 * @param   callable  $callback  An optional callback function.
	 *
	 * @return  This object for method chaining.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function callback(callable $callback = null)
	{
		$this->callback = $callback;

		return $this;
	}

	/**
	 * Is this token simple or the beginning of a block?
	 *
	 * @return  boolean
	 *
	 * @since __DEPLOY_VERSION__
	 */
	abstract public function isSimple();

	/**
	 * Assign a layout function.
	 *
	 * @param   JLayout  $layout  An optional layout to bind to the token.
	 *
	 * @return  This object for method chaining.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function layout(JLayout $layout = null)
	{
		$this->layout = $layout;

		return $this;
	}
}

