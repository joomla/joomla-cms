<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Site\View\Config;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Config\Administrator\Controller\RequestController;

/**
 * View for the global configuration
 *
 * @since  3.2
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The form object
	 *
	 * @var   \JForm
	 * @since 3.2
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
	 * Is the current user a super administrator?
	 *
	 * @var   boolean
	 * @since 3.2
	 */
	protected $userIsSuperAdmin;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   3.2
	 */
	public function display($tpl = null)
	{
		$user = \JFactory::getUser();
		$this->userIsSuperAdmin = $user->authorise('core.admin');

		// Access backend com_config
		$requestController = new RequestController;

		// Execute backend controller
		$serviceData = json_decode($requestController->getJson(), true);

		$form = $this->getForm();

		if ($form)
		{
			$form->bind($serviceData);
		}

		$this->form = $form;
		$this->data = $serviceData;

		return parent::display($tpl);
	}
}
