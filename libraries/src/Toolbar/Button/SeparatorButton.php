<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar\Button;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Toolbar\ToolbarButton;

/**
 * Renders a button separator
 *
 * @since  3.0
 */
class SeparatorButton extends ToolbarButton
{
	/**
	 * Property layout.
	 *
	 * @var  string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $layout = 'joomla.toolbar.separator';

	/**
	 * Empty implementation (not required for separator)
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @deprecated  5.0 Use render() instead.
	 */
	public function fetchButton()
	{
	}
}
