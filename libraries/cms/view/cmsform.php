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
class JViewCmsform extends JViewCms
{
	public $state;
	/*
	 * The form object
	*
	* @var JForm
	*/
	public  $form;

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
			$form = $this->model->getForm();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}
var_dump($form);die;
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
	protected function editCheck()
	{
		return false;
	}
}