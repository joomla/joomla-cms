<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright   Copyright (C) 2010 - 2015 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * Generic field header, without any filters
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFFormHeaderField extends FOFFormHeader
{
	/**
	 * Get the header
	 *
	 * @return  string  The header HTML
	 */
	protected function getHeader()
	{
		$sortable = ($this->element['sortable'] != 'false');

		$label = $this->getLabel();

		if ($sortable)
		{
			$view = $this->form->getView();

			return JHTML::_('grid.sort', $label, $this->name,
				$view->getLists()->order_Dir, $view->getLists()->order,
				$this->form->getModel()->task
			);
		}
		else
		{
			return JText::_($label);
		}
	}
}
