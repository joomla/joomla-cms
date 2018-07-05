<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar;

defined('_JEXEC') or die;

use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;

/**
 * Default factory for creating toolbar objects
 *
 * @since  4.0.0
 */
class ContainerAwareToolbarFactory implements ToolbarFactoryInterface, ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * Creates a new toolbar button.
	 *
	 * @param   Toolbar  $toolbar  The Toolbar instance to attach to the button
	 * @param   string   $type     Button Type
	 *
	 * @return  ToolbarButton
	 *
	 * @since   3.8.0
	 * @throws  \InvalidArgumentException
	 */
	public function createButton(Toolbar $toolbar, string $type): ToolbarButton
	{
		$normalisedType = ucfirst($type);
		$buttonClass    = $this->loadButtonClass($normalisedType);

		if (!$buttonClass)
		{
			$dirs = $toolbar->getButtonPath();

			$file = \JFilterInput::getInstance()->clean(str_replace('_', DIRECTORY_SEPARATOR, strtolower($type)) . '.php', 'path');

			jimport('joomla.filesystem.path');

			if ($buttonFile = \JPath::find($dirs, $file))
			{
				include_once $buttonFile;
			}
			else
			{
				\JLog::add(\JText::sprintf('JLIB_HTML_BUTTON_NO_LOAD', $buttonClass, $buttonFile), \JLog::WARNING, 'jerror');

				throw new \InvalidArgumentException(\JText::sprintf('JLIB_HTML_BUTTON_NO_LOAD', $buttonClass, $buttonFile));
			}
		}

		if (!class_exists($buttonClass))
		{
			throw new \InvalidArgumentException(sprintf('Class `%1$s` does not exist, could not create a toolbar button.', $buttonClass));
		}

		// Check for a possible service from the container otherwise manually instantiate the class
		if ($this->getContainer()->has($buttonClass))
		{
			return $this->getContainer()->get($buttonClass);
		}

		/** @var ToolbarButton $button */
		$button = new $buttonClass($normalisedType);

		return $button->setParent($toolbar);
	}

	/**
	 * Creates a new Toolbar object.
	 *
	 * @param   string  $name  The toolbar name.
	 *
	 * @return  Toolbar
	 *
	 * @since   4.0.0
	 */
	public function createToolbar(string $name = 'toolbar'): Toolbar
	{
		return new Toolbar($name, $this);
	}

	/**
	 * Load the button class including the deprecated ones.
	 *
	 * @param   string  $type  Button Type (normalized)
	 *
	 * @return  string|null
	 *
	 * @since   4.0.0
	 */
	private function loadButtonClass(string $type)
	{
		$buttonClasses = [
			'Joomla\\CMS\\Toolbar\\Button\\' . $type . 'Button',
			// @deprecated 5.0
			'JToolbarButton' . $type,
		];

		foreach ($buttonClasses as $buttonClass)
		{
			if (!class_exists($buttonClass))
			{
				continue;
			}

			return $buttonClass;
		}

		return null;
	}
}
