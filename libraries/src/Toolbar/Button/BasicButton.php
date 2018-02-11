<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar\Button;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Toolbar\ToolbarButton;

/**
 * Renders a standard button
 *
 * @since  3.0
 */
class BasicButton extends ToolbarButton
{
	/**
	 * Property layout.
	 *
	 * @var  string
	 */
	protected $layout = 'joomla.toolbar.basic';

	/**
	 * Fetch the HTML for the button
	 *
	 * @param   string  $type  Unused string.
	 *
	 * @return  string  HTML string for the button
	 *
	 * @since   3.0
	 *
	 * @deprecated  5.0 Use render() instead.
	 */
	public function fetchButton($type = 'Basic')
	{
		throw new \LogicException('This is a new button in 4.0, please use redner() instead.');
	}
}
