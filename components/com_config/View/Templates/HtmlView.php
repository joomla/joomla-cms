<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Site\View\Templates;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Templates\Administrator\Model\StyleModel;
use Joomla\Component\Config\Administrator\Controller\RequestController;

/**
 * View to edit a template style.
 *
 * @since  3.2
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The data to be displayed in the form
	 *
	 * @var   array
	 * @since 3.2
	 */
	public $item;

	/**
	 * The form object
	 *
	 * @var   \JForm
	 * @since 3.2
	 */
	public $form;

	/**
	 * Is the current user a super administrator?
	 *
	 * @var   boolean
	 * @since 3.2
	 */
	protected $userIsSuperAdmin;

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   3.2
	 */
	public function display($tpl = null)
	{
		$user = \JFactory::getUser();
		$this->userIsSuperAdmin = $user->authorise('core.admin');

		$app   = \JFactory::getApplication();

		$app->input->set('id', $app->getTemplate(true)->id);

		$view = new \Joomla\Component\Templates\Administrator\View\Style\JsonView;

		// Get/Create the model
		$model = new StyleModel;
		$view->setModel($model, true);

		$view->document = \JFactory::getDocument();

		$json = $view->display();

		// Execute backend controller
		$serviceData = json_decode($json, true);

		// Access backend com_config
		$requestController = new RequestController;

		// Execute backend controller
		$configData = json_decode($requestController->getJson(), true);

		$data = array_merge($configData, $serviceData);

		/** @var \JForm $form */
		$form = $this->getForm();

		if ($form)
		{
			$form->bind($data);
		}

		$this->form = $form;

		$this->data = $serviceData;

		return parent::display($tpl);
	}
}
