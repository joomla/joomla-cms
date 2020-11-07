<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\View\Profile;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class to allow users edit their own profile.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The form object
	 *
	 * @var    \Joomla\CMS\Form\Form
	 * @since  1.6
	 */
	protected $form;

	/**
	 * The item being viewed
	 *
	 * @var    \Joomla\CMS\Object\CMSObject
	 * @since  1.6
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var    \Joomla\CMS\Object\CMSObject
	 * @since  1.6
	 */
	protected $state;

	/**
	 * Configuration forms for all two-factor authentication methods
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $tfaform;

	/**
	 * Returns the one time password (OTP) – a.k.a. two factor authentication – configuration for the user.
	 *
	 * @var    \stdClass
	 * @since  4.0.0
	 */
	protected $otpConfig;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		/** @var \Joomla\Component\Admin\Administrator\Model\ProfileModel $model */
		$model = $this->getModel();

		$this->form      = $model->getForm();
		$this->item      = $model->getItem();
		$this->state     = $model->getState();
		$this->tfaform   = $model->getTwofactorform();
		$this->otpConfig = $model->getOtpConfig();

		// Check for errors.
		if ($errors = $model->getErrors())
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->form->setValue('password', null);
		$this->form->setValue('password2', null);

		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', 1);

		ToolbarHelper::title(Text::_('COM_ADMIN_VIEW_PROFILE_TITLE'), 'user user-profile');

		ToolbarHelper::apply('profile.apply');
		ToolbarHelper::divider();
		ToolbarHelper::save('profile.save');
		ToolbarHelper::divider();
		ToolbarHelper::cancel('profile.cancel', 'JTOOLBAR_CLOSE');
		ToolbarHelper::divider();
		ToolbarHelper::help('JHELP_ADMIN_USER_PROFILE_EDIT');
	}
}
