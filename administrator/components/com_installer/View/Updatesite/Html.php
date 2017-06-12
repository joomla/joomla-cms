<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Installer\Administrator\View\Updatesite;

defined('_JEXEC') or die;

use Joomla\CMS\View\HtmlView;
use Joomla\CMS\Helper\ContentHelper;

/**
 * View to edit a contact.
 *
 * @since  __DEPLOY_VERSION__
 */
class Html extends HtmlView
{
	/**
	 * The \JForm object
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @var  \JForm
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @var  object
	 */
	protected $item;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function display($tpl = null)
	{
		// Initialise variables.
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function addToolbar()
	{
		\JFactory::getApplication()->input->set('hidemainmenu', true);

		$user       = \JFactory::getUser();
		$userId     = $user->id;
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);

		// Since we don't track these assets at the item level, use the category id.
		$canDo = ContentHelper::getActions('com_installer', 'updatesite');

		\JToolbarHelper::title(\JText::_('COM_INSTALLER_UPDATESITE_EDIT_TITLE'), 'address contact');

		// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
		$itemEditable = $canDo->get('core.edit');

		$toolbarButtons = [];

		// Can't save the record if it's checked out and editable
		if (!$checkedOut && $itemEditable)
		{
			$toolbarButtons[] = ['apply', 'updatesite.apply'];
			$toolbarButtons[] = ['save', 'updatesite.save'];
		}

		\JToolbarHelper::saveGroup(
			$toolbarButtons,
			'btn-success'
		);

		\JToolbarHelper::cancel('updatesite.cancel', 'JTOOLBAR_CLOSE');
	}
}
