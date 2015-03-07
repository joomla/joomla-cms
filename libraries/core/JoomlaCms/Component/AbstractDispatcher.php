<?php
/**
 * Joomla! CMS Component package
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace JoomlaCMS\Component;

/**
 * Abstract base component dispatcher
 */
abstract class AbstractDispatcher implements  DispatcherInterface
{
	/**
	 * Application object
	 *
	 * @var  \JApplicationBase
	 */
	protected $application;

	/**
	 * Name of the component being dispatched
	 *
	 * @var  string
	 */
	protected $name;

	/**
	 * Constructor
	 *
	 * @param  \JApplicationBase  $application  Application object
	 */
	public function __construct(\JApplicationBase $application)
	{
		$this->application = $application;

		if (!$this->name)
		{
			$this->name = str_replace('Dispatcher', '', get_called_class());
		}

		$this->boot();
	}

	/**
	 * {@inheritdoc}
	 */
	public function boot()
	{
		// In the simplest implementation, we just return
		return;
	}

	/**
	 * {@inheritdoc}
	 */
	public function execute()
	{
		ob_start();
		$controller = \JControllerLegacy::getInstance($this->getName());
		$controller->execute($this->application->input->get('task'));
		$controller->redirect();

		$contents = ob_get_clean();

		return $contents;
	}

	/**
	 * Get the name of the component
	 *
	 * @return  string
	 */
	public function getName()
	{
		if (!$this->name)
		{
			throw new \InvalidArgumentException('The component\'s name has not been set.');
		}

		return $this->name;
	}
}
