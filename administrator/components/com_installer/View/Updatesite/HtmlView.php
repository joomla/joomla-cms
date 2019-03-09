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

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Installer\Administrator\View\Installer\HtmlView as InstallerViewDefault;

/**
 * View to edit an update site.
 *
 * @since  __DEPLOY_VERSION__
 */
class HtmlView extends InstallerViewDefault
{
	/**
	 * The Form object
	 *
	 * @var  Form
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var  object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $item;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function display($tpl = null)
	{
		// Initialise variables.
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		$user       = Factory::getUser();
		$userId     = $user->id;
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);

		// Since we don't track these assets at the item level, use the category id.
		$canDo = ContentHelper::getActions('com_installer', 'updatesite');

		ToolbarHelper::title(Text::_('COM_INSTALLER_UPDATESITE_EDIT_TITLE'), 'address contact');

		// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
		$itemEditable = $canDo->get('core.edit');

		$toolbarButtons = [];

		// Can't save the record if it's checked out and editable
		if (!$checkedOut && $itemEditable)
		{
			$toolbarButtons[] = ['apply', 'updatesite.apply'];
			$toolbarButtons[] = ['save', 'updatesite.save'];
		}

		ToolbarHelper::saveGroup(
			$toolbarButtons,
			'btn-success'
		);

		ToolbarHelper::cancel('updatesite.cancel', 'JTOOLBAR_CLOSE');
	}
}
