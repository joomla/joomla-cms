<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_postinstall
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Postinstall\Administrator\View\Messages;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
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
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function display($tpl = null)
	{
		/** @var MessagesModel $model */
		$model = $this->getModel();

		$this->items = $model->getItems();

		if (!\count($this->items))
		{
			$this->setLayout('emptystate');
		}

		$this->joomlaFilesExtensionId = $model->getJoomlaFilesExtensionId();
		$this->eid                    = (int) $model->getState('eid', $this->joomlaFilesExtensionId);

		if (empty($this->eid))
		{
			$this->eid = $this->joomlaFilesExtensionId;
		}

		$this->toolbar();

		$this->token = Factory::getSession()->getFormToken();
		$this->extension_options = $model->getComponentOptions();

		ToolbarHelper::title(Text::sprintf('COM_POSTINSTALL_MESSAGES_TITLE', $model->getExtensionName($this->eid)), 'bell');

		parent::display($tpl);
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
		$toolbar = Toolbar::getInstance('toolbar');

		if (!empty($this->items))
		{
			$toolbar->unpublish('message.hideAll', 'COM_POSTINSTALL_HIDE_ALL_MESSAGES');
		}

		// Options button.
		if (Factory::getUser()->authorise('core.admin', 'com_postinstall'))
		{
			$toolbar->preferences('com_postinstall');
			$toolbar->help('Post-installation_Messages_for_Joomla_CMS');
		}
	}
}
