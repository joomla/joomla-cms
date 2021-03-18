<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Administrator\View\Application;

\defined('_JEXEC') or die;

use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Config\Administrator\Helper\ConfigHelper;

/**
 * View for the global configuration
 *
 * @since  3.2
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The model state
	 *
	 * @var    \Joomla\CMS\Object\CMSObject
	 * @since  3.2
	 */
	public $state;

	/**
	 * The form object
	 *
	 * @var    \Joomla\CMS\Form\Form
	 * @since  3.2
	 */
	public $form;

	/**
	 * The data to be displayed in the form
	 *
	 * @var   array
	 * @since 3.2
	 */
	public $data;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @see     \JViewLegacy::loadTemplate()
	 * @since   3.0
	 */
	public function display($tpl = null)
	{
		$form = null;
		$data = null;

		try
		{
			// Load Form and Data
			$form = $this->get('form');
			$data = $this->get('data');
			$user = Factory::getUser();
		}
		catch (\Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		// Bind data
		if ($form && $data)
		{
			$form->bind($data);
		}

		// Get the params for com_users.
		$usersParams = ComponentHelper::getParams('com_users');

		// Get the params for com_media.
		$mediaParams = ComponentHelper::getParams('com_media');

		// Load settings for the FTP layer.
		$ftp = ClientHelper::setCredentialsFromRequest('ftp');

		$this->form        = &$form;
		$this->data        = &$data;
		$this->ftp         = &$ftp;
		$this->usersParams = &$usersParams;
		$this->mediaParams = &$mediaParams;
		$this->components  = ConfigHelper::getComponentsWithConfig();
		ConfigHelper::loadLanguageForComponents($this->components);

		$this->userIsSuperAdmin = $user->authorise('core.admin');

		$this->addToolbar();

		return parent::display($tpl);
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
		ToolbarHelper::title(Text::_('COM_CONFIG_GLOBAL_CONFIGURATION'), 'equalizer config');
		ToolbarHelper::apply('application.apply');
		ToolbarHelper::divider();
		ToolbarHelper::save('application.save');
		ToolbarHelper::divider();
		ToolbarHelper::cancel('application.cancel', 'JTOOLBAR_CLOSE');
		ToolbarHelper::divider();
		ToolbarHelper::help('JHELP_SITE_GLOBAL_CONFIGURATION');
	}
}
