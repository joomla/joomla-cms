<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die;

/**
 * Ordering field header
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFFormHeaderOrdering extends FOFFormHeader
{
	/**
	 * Get the header
	 *
	 * @return  string  The header HTML
	 */
	protected function getHeader()
	{
		$sortable = ($this->element['sortable'] != 'false');

		$view = $this->form->getView();
		$model = $this->form->getModel();

		$hasAjaxOrderingSupport = $view->hasAjaxOrderingSupport();

		if (!$sortable)
		{
			// Non sortable?! I'm not sure why you'd want that, but if you insist...
			return JText::_('JGRID_HEADING_ORDERING');
		}

		if (!$hasAjaxOrderingSupport)
		{
			// Ye olde Joomla! 2.5 method
			$html = JHTML::_('grid.sort', 'JFIELD_ORDERING_LABEL', 'ordering', $view->getLists()->order_Dir, $view->getLists()->order, 'browse');
			$html .= JHTML::_('grid.order', $model->getList());

			return $html;
		}
		else
		{
			// The new, drag'n'drop ordering support
			return JHtml::_(
				'grid.sort',
				'<i class="icon-menu-2"></i>',
				'ordering',
				$view->getLists()->order_Dir,
				$view->getLists()->order,
				null,
				'asc',
				'JGRID_HEADING_ORDERING'
			);
		}
	}
}
