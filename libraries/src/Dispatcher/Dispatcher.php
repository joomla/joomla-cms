<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\MVC\Factory\MVCFactoryFactoryInterface;
use Joomla\Input\Input;

/**
 * Base class for a Joomla Dispatcher
 *
 * @since  4.0.0
 */
abstract class Dispatcher implements DispatcherInterface
{
	/**
	 * The application instance
	 *
	 * @var    CMSApplication
	 * @since  4.0.0
	 */
	protected $app;

	/**
	 * The input instance
	 *
	 * @var    Input
	 * @since  4.0.0
	 */
	protected $input;

	/**
	 * The MVC factory
	 *
	 * @var  MVCFactoryFactoryInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private $mvcFactoryFactory;

	/**
	 * Constructor for Dispatcher
	 *
	 * @param   CMSApplication              $app                The application instance
	 * @param   Input                       $input              The input instance
	 * @param   MVCFactoryFactoryInterface  $mvcFactoryFactory  The MVC factory instance
	 *
	 * @since   4.0.0
	 */
	public function __construct(CMSApplication $app, Input $input, MVCFactoryFactoryInterface $mvcFactoryFactory)
	{
		$this->app               = $app;
		$this->input             = $input;
		$this->mvcFactoryFactory = $mvcFactoryFactory;
	}

	/**
	 * The application the dispatcher is working with.
	 *
	 * @return  CMSApplication
	 *
	 * @since   4.0.0
	 */
	protected function getApplication(): CMSApplication
	{
		return $this->app;
	}

	/**
	 * The MVC Factory.
	 *
	 * @return  MVCFactoryFactoryInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getMvcFactoryFactory(): MVCFactoryFactoryInterface
	{
		return $this->mvcFactoryFactory;
	}
}
