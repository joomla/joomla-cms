<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Installer\Administrator\View\Downloadkey;

defined('_JEXEC') or die;

use Joomla\CMS\View\HtmlView;

/**
 * View to edit a download key.
 *
 * @since  __DEPLOY_VERSION__
 */
class Html extends HtmlView
{
	/**
	 * The \JForm object
	 *
	 * @var  \JForm
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
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function display($tpl = null)
	{
		// Initialise variables.
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');

		$jinput = \JFactory::getApplication()->input;
		$this->modal = $jinput->getString('tmpl', '');

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
		$canDo = \JHelperContent::getActions('com_installer', 'downloadkey');

		\JToolbarHelper::title(\JText::_('COM_INSTALLER_DOWNLOADKEY_EDIT_TITLE'), 'bookmark downloadkeys');

		$toolbarButtons = [];

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit')))
		{
			$toolbarButtons[] = ['apply', 'downloadkey.apply'];
			$toolbarButtons[] = ['save', 'downloadkey.save'];
		}

		\JToolbarHelper::saveGroup(
			$toolbarButtons,
			'btn-success'
		);

		if (empty($this->item->id))
		{
			\JToolbarHelper::cancel('downloadkey.cancel');
		}
		else
		{
			if (\JComponentHelper::isEnabled('com_contenthistory') && $this->state->params->get('save_history', 0) && $canDo->get('core.edit'))
			{
				\JToolbarHelper::versions('com_installers.downloadkey', $this->item->id);
			}

			\JToolbarHelper::cancel('banner.cancel', 'JTOOLBAR_CLOSE');
		}

		\JToolbarHelper::divider();
		\JToolbarHelper::help('JHELP_COMPONENTS_BANNERS_BANNERS_EDIT');
	}
}
