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
 * A simple token or the beginning of a block token.
 *
 * @since  __DEPLOY_VERSION__
 */
class JStringTokenBegin extends JStringToken
{
	/**
	 * Constructor.
	 *
	 * @param   string                  $name             Name of the token.
	 * @param   JStringTokenDefinition  $tokenDefinition  Token definition object.
	 * @param   array                   $params           String representing parameters.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct($name, JStringTokenDefinition $tokenDefinition, array $params)
	{
		$this->name = $name;
		$this->tokenDefinition = $tokenDefinition;
		$this->params = $params;
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
		$value    = $this->tokenDefinition->bound;
		$callback = $this->tokenDefinition->callback;
		$layout   = $this->tokenDefinition->layout;

		if (is_callable($callback))
		{
			$value = $callback($this, $content, $value);
		}

		if ($layout instanceof JLayoutFile)
		{
			$value = $layout->render($value);
		}

		return $value;
	}

	/**
	 * Is this token simple or the beginning of a block?
	 *
	 * @return  boolean
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function isSimple()
	{
		return (boolean) $this->tokenDefinition->isSimple();
	}
}

