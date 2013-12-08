<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.Libraries
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
class JViewHtmlCmsform extends JViewHtmlCms
{
	public $state;

	/*
	 * The form object
	 *
	 * @var JForm
	 */
	public  $form;

	/*
	 * The form object
	*
	* @var JForm
	*/
	public  $item;

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
		try
		{
			$user = JFactory::getUser();
			$app  = JFactory::getApplication();
			$lang = JFactory::getLanguage();
			$this->state = $this->model->getState();
			$this->form = $this->model->getForm();
			$this->item = $this->model->getItem();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		$this->addToolbar();
		$this->addSubmenu();

		return parent::render();
	}

	/**
	 * Checks if a user can edit this data
	 *
	 * @return  boolean  True if edit is allowed
	 *
	 * @since   3.0
	 */
	protected function editCheck()
	{
		return false;
	}
}