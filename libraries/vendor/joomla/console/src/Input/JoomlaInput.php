<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Console\Input;

use Joomla\Input\Input;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;

/**
 * ArgvInput handles binding the bound input definition to the Joomla application input.
 *
 * @since  __DEPLOY_VERSION__
 */
class JoomlaInput extends ArgvInput
{
	/**
	 * The application's input object.
	 *
	 * @var    Input
	 * @since  __DEPLOY_VERSION__
	 */
	private $input;

	/**
	 * Constructor.
	 *
	 * @param   Input                 $input       The application's input object
	 * @param   array|null            $argv        An array of parameters from the CLI (in the argv format)
	 * @param   InputDefinition|null  $definition  A InputDefinition instance
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(Input $input, array $argv = null, InputDefinition $definition = null)
	{
		parent::__construct($argv, $definition);

		$this->input = $input;
	}

	/**
	 * Binds the current Input instance with the given arguments and options.
	 *
	 * @param   InputDefinition  $definition  A InputDefinition instance
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function bind(InputDefinition $definition)
	{
		parent::bind($definition);

		// Bind arguments to the input
		foreach ($this->getArguments() as $key => $value)
		{
			$this->input->def($key, $value);
		}

		// Bind options to the input
		foreach ($this->getOptions() as $key => $value)
		{
			$this->input->def($key, $value);

			// If this option has shortcuts, register those too
			if ($shortcut = $definition->getOption($key)->getShortcut())
			{
				foreach (explode('|', $shortcut) as $shortcutKey)
				{
					$this->input->def($shortcutKey, $value);
				}
			}
		}
	}
}
