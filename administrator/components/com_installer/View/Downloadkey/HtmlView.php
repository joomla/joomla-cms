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

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\Input\Input;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\User\User;
use Joomla\Component\Installer\Administrator\Model\DownloadkeyModel;
use Joomla\Component\Installer\Administrator\View\Installer\HtmlView as InstallerViewDefault;

/**
 * View to edit a download key.
 *
 * @since  __DEPLOY_VERSION__
 */
class HtmlView extends InstallerViewDefault
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
	 * Set if we are in a modal
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $modal = '';

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @throws  Exception
	 */
	public function display($tpl = null)
	{
		/** @var DownloadkeyModel $model */
		$model       = $this->getModel();
		$this->form  = $model->getForm();
		$this->item  = $model->getItem();
		$this->state = $model->getState();

		/** @var Input $input */
		$input       = Factory::getApplication()->input;
		$this->modal = $input->getString('tmpl', '');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @throws  Exception
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		$user       = User::getInstance();
		$userId     = $user->id;
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);

		$canDo = ContentHelper::getActions('com_installer', 'downloadkey');

		ToolbarHelper::title(Text::_('COM_INSTALLER_DOWNLOADKEY_EDIT_TITLE'), 'bookmark downloadkeys');

		$toolbarButtons = [];

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit')))
		{
			$toolbarButtons[] = ['apply', 'downloadkey.apply'];
			$toolbarButtons[] = ['save', 'downloadkey.save'];
		}

		ToolbarHelper::saveGroup(
			$toolbarButtons,
			'btn-success'
		);

		if (empty($this->item->update_site_id))
		{
			ToolbarHelper::cancel('downloadkey.cancel');
		}
		else
		{
			if (ComponentHelper::isEnabled('com_contenthistory')
				&& $this->state->params->get('save_history', 0)
				&& $canDo->get('core.edit')
			)
			{
				ToolbarHelper::versions('com_installers.downloadkey', $this->item->id);
			}

			ToolbarHelper::cancel('downloadkey.cancel', 'JTOOLBAR_CLOSE');
		}

		ToolbarHelper::divider();
		ToolbarHelper::help('JHELP_COMPONENTS_BANNERS_BANNERS_EDIT');
	}
}
