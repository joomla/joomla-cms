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
class JViewCmslist extends JViewCms
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
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since	3.2
	 */
	protected function addToolbar()
	{
	}
	/**
	* Add the submenu.
	*
	* @return  void
	*
	* @since	3.2
	*/
	protected function addSubmenu()
	{
	}
}