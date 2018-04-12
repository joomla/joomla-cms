<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Console;

use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputDefinition;

/**
 * Interface defining console commands.
 *
 * @since  __DEPLOY_VERSION__
 */
interface CommandInterface
{
	/**
	 * Execute the command.
	 *
	 * @return  integer  The exit code for the command.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function execute(): int;

	/**
	 * Get the command's aliases.
	 *
	 * @return  string[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getAliases(): array;

	/**
	 * Get the application object.
	 *
	 * @return  Application  The application object.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \UnexpectedValueException if the application has not been set.
	 */
	public function getApplication(): Application;

	/**
	 * Get the command's input definition.
	 *
	 * @return  InputDefinition
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getDefinition(): InputDefinition;

	/**
	 * Get the command's description.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getDescription(): string;

	/**
	 * Get the command's help.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getHelp(): string;

	/**
	 * Get the command's input helper set.
	 *
	 * @return  HelperSet
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getHelperSet(): HelperSet;

	/**
	 * Get the command's name.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getName(): string;

	/**
	 * Returns the processed help for the command.
	 *
	 * This method is used to replace placeholders in commands with the real values.
	 * By default, this supports `%command.name%` and `%command.full_name`.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getProcessedHelp(): string;

	/**
	 * Get the command's synopsis.
	 *
	 * @param   boolean  $short  Flag indicating whether the short or long version of the synopsis should be returned
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getSynopsis(bool $short = false): string;

	/**
	 * Check if the command is enabled in this environment.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isEnabled(): bool;

	/**
	 * Check if the command is hidden from the command listing.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isHidden(): bool;

	/**
	 * Merges the definition from the application to this command.
	 *
	 * @param   InputDefinition  $definition  The InputDefinition from the application to be merged.
	 * @param   boolean          $mergeArgs   Flag indicating whether the application's definition arguments should be merged
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @internal  This method should not be relied on as part of the public API
	 */
	public function mergeApplicationDefinition(InputDefinition $definition, bool $mergeArgs = true);

	/**
	 * Set the command's aliases.
	 *
	 * @param   string[]  $aliases  The command aliases
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setAliases(array $aliases);

	/**
	 * Set the application object.
	 *
	 * @param   Application  $app  The application object.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setApplication(Application $app);

	/**
	 * Sets the input definition for the command.
	 *
	 * @param   array|InputDefinition  $definition  Either an InputDefinition object or an array of objects to write to the definition.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setDefinition($definition);

	/**
	 * Sets the description for the command.
	 *
	 * @param   string  $description  The description for the command
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setDescription(string $description);

	/**
	 * Sets the help for the command.
	 *
	 * @param   string  $help  The help for the command
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setHelp(string $help);

	/**
	 * Set the command's input helper set.
	 *
	 * @param   HelperSet  $helperSet  The helper set.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setHelperSet(HelperSet $helperSet);

	/**
	 * Set whether this command is hidden from the command listing.
	 *
	 * @param   boolean  $hidden  Flag if this command is hidden.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setHidden(bool $hidden);

	/**
	 * Set the command's name.
	 *
	 * @param   string  $name  The command name
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setName(string $name);
}
