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
use Joomla\CMS\Toolbar\ToolbarButton;

/**
 * Renders a standard button
 *
 * @since  3.0
 */
class GroupButton extends BasicButton
{
	/**
	 * Get the HTML to render the button
	 *
	 * @param   array &$definition Parameters to be passed
	 *
	 * @return  string
	 *
	 * @since   3.0
	 *
	 * @throws \Exception
	 */
	public function render(&$definition = null)
	{
		$childToolbar = $this->getChildToolbar();
		$hasChildren = count($childToolbar->getItems()) > 0;

		if (!$hasChildren)
		{
			return '';
		}

		$buttons = $childToolbar->getItems();

		/** @var ToolbarButton $button */
		$button = array_shift($buttons);

		$childToolbar->setItems($buttons);

		$button->child = $childToolbar;

		return $button->render($definition);
	}

}
