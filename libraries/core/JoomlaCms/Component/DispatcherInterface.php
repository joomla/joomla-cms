<?php
/**
 * Joomla! CMS Component package
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace JoomlaCMS\Component;

/**
 * Interface to define a component dispatcher
 */
interface DispatcherInterface
{
	/**
	 * Bootup sequence for the component
	 *
	 * This method could be used to check component dependencies or register services, for example.
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException if the component cannot be booted
	 */
	public function boot();

	/**
	 * Execute the component.
	 *
	 * @return  string  The component output
	 */
	public function execute();
}
