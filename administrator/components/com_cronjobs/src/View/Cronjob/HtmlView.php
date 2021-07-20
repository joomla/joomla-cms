<?php
/**
 * Declares the MVC View for CronjobModel.
 *
 * @package       Joomla.Administrator
 * @subpackage    com_cronjobs
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GPL v3
 */

namespace Joomla\Component\Cronjobs\Administrator\View\Cronjob;

// Restrict direct access
\defined('_JEXEC') or die;

use Exception;
use JObject;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * The MVC View AddCronjob
 *
 * @package    Joomla.Administrator
 * @subpackage com_cronjobs
 *
 * @since      __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The Form object
	 *
	 * @var   \JForm
	 * @since __DEPLOY__VERSION__
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var   object
	 * @since __DEPLOY__VERSION__
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var   JObject
	 * @since __DEPLOY__VERSION__
	 */
	protected $state;

	/**
	 * The actions the user is authorised to perform
	 *
	 * @var    JObject
	 * @since  4.0.0
	 */
	protected $canDo;


	/**
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	public function display($tpl = null): void
	{
		/*
		 * Will call the getForm() method of CronjobModel
		 */
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');

		$this->state = $this->get('State');
		$this->canDo = ContentHelper::getActions('com_cronjobs', 'cronjob', $this->item->id);

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Adds the page title and toolbar
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	protected function addToolbar(): void
	{
		$app = Factory::getApplication();

		/** @noinspection SpellCheckingInspection */
		$app->getInput()->set('hidemainmenu', true);
		$user = $app->getIdentity();
		$userId = $user->id;
		$isNew = ($this->item->id == 0);
		$canDo = $this->canDo;

		// TODO : icon?
		ToolbarHelper::title($isNew ? Text::_('COM_CRONJOBS_MANAGER_CRONJOB_NEW') : Text::_('COM_CRONJOBS_MANAGER_CRONJOB_EDIT'), 'tags');

		// Goes into ToolbarHelper::saveGroup()
		$toolbarButtons = [];

		// For a new cronjob, check if user has 'core.create' access
		if ($isNew && $canDo->get('core.create'))
		{
			// The cronjob.apply task maps to the save() method in CronjobController
			ToolbarHelper::apply('cronjob.apply');

			$toolbarButtons[] = ['save', 'cronjob.save'];
		}
		else
		{
			if (!$isNew && $canDo->get('core.edit'))
			{
				ToolbarHelper::apply('cronjob.apply');
				$toolbarButtons[] = ['save', 'cronjob.save'];

				// TODO | ? : Do we need save2new and save2copy? If yes, need to support in the Model,
				// 			  here and the Controller.
			}
		}

		ToolbarHelper::saveGroup(
			$toolbarButtons,
			'btn-success'
		);

		ToolbarHelper::cancel('cronjob.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}

}
