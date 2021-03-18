<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_postinstall
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Postinstall\Administrator\View\Messages;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Postinstall\Administrator\Model\MessagesModel;

/**
 * Model class to display postinstall messages
 *
 * @since  3.2
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Executes before rendering the page for the Browse task.
	 *
	 * @param   string  $tpl  Subtemplate to use
	 *
	 * @return  boolean  Return true to allow rendering of the page
	 *
	 * @since   3.2
	 */
	public function display($tpl = null)
	{
		/** @var MessagesModel $model */
		$model = $this->getModel();

		$this->items = $model->getItems();

		$this->joomlaFilesExtensionId = $model->getJoomlaFilesExtensionId();
		$this->eid                    = (int) $model->getState('eid', $this->joomlaFilesExtensionId, 'int');

		if (empty($this->eid))
		{
			$this->eid = $this->joomlaFilesExtensionId;
		}

		$this->toolbar();

		$this->token = Factory::getSession()->getFormToken();
		$this->extension_options = $model->getComponentOptions();

		ToolbarHelper::title(Text::sprintf('COM_POSTINSTALL_MESSAGES_TITLE', $model->getExtensionName($this->eid)));

		return parent::display($tpl);
	}

	/**
	 * displays the toolbar
	 *
	 * @return  void
	 *
	 * @since   3.6
	 */
	private function toolbar()
	{
		if (!empty($this->items))
		{
			ToolbarHelper::custom('message.hideAll', 'unpublish.png', 'unpublish_f2.png', 'COM_POSTINSTALL_HIDE_ALL_MESSAGES', false);
		}

		// Options button.
		if (Factory::getUser()->authorise('core.admin', 'com_postinstall'))
		{
			ToolbarHelper::preferences('com_postinstall', 550, 875);
			ToolbarHelper::help('JHELP_COMPONENTS_POST_INSTALLATION_MESSAGES');
		}
	}
}
