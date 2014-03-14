<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  hal
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('FOF_INCLUDED') or die;

/**
 * Interface for HAL document renderers
 *
 * @package  FrameworkOnFramework
 * @since    2.1
 */
interface FOFHalRenderInterface
{
	/**
	 * Render a HAL document into a representation suitable for consumption.
	 *
	 * @param   array  $options  Renderer-specific options
	 *
	 * @return  void
	 */
	public function render($options = array());
}
