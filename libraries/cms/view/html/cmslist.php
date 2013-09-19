<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  view
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View for the global configuration
 *
 * @package     Joomla.Libraries
 * @subpackage  view
 * @since       3.2
 */
class JViewHtmlCmslist extends JViewHtmlCms
{
	public $state;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  Layout
	 *
	 * @return  void
	 *
	 */
	public function render()
	{
		$lang = JFactory::getLanguage();
		$this->state = $this->model->getState();
		$this->items = $this->model->getItems();
		$this->pagination = $this->model->getPagination();

		$this->addToolbar();
		$this->addSubmenu();

		return parent::render();
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array();
	}
}