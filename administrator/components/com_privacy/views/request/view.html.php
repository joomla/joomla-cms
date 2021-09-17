<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Request view class
 *
 * @since  3.9.0
 */
class PrivacyViewRequest extends JViewLegacy
{
	/**
	 * The action logs for the item
	 *
	 * @var    array
	 * @since  3.9.0
	 */
	protected $actionlogs;

	/**
	 * The form object
	 *
	 * @var    JForm
	 * @since  3.9.0
	 */
	protected $form;

	/**
	 * The item record
	 *
	 * @var    JObject
	 * @since  3.9.0
	 */
	protected $item;

	/**
	 * The state information
	 *
	 * @var    JObject
	 * @since  3.9.0
	 */
	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @see     JViewLegacy::loadTemplate()
	 * @since   3.9.0
	 * @throws  Exception
	 */
	public function display($tpl = null)
	{
		// Initialise variables.
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');

		// Variables only required for the default layout
		if ($this->getLayout() === 'default')
		{
			/** @var ActionlogsModelActionlogs $logsModel */
			$logsModel = $this->getModel('actionlogs');

			$this->actionlogs = $logsModel->getLogsForItem('com_privacy.request', $this->item->id);

			// Load the com_actionlogs language strings for use in the layout
			$lang = JFactory::getLanguage();
			$lang->load('com_actionlogs', JPATH_ADMINISTRATOR, null, false, true)
				|| $lang->load('com_actionlogs', JPATH_ADMINISTRATOR . '/components/com_actionlogs', null, false, true);
		}

		// Variables only required for the edit layout
		if ($this->getLayout() === 'edit')
		{
			$this->form = $this->get('Form');
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	protected function addToolbar()
	{
		JFactory::getApplication('administrator')->set('hidemainmenu', true);

		// Set the title and toolbar based on the layout
		if ($this->getLayout() === 'edit')
		{
			JToolbarHelper::title(JText::_('COM_PRIVACY_VIEW_REQUEST_ADD_REQUEST'), 'lock');

			JToolbarHelper::apply('request.save');
			JToolbarHelper::cancel('request.cancel');
			JToolbarHelper::help('JHELP_COMPONENTS_PRIVACY_REQUEST_EDIT');
		}
		else
		{
			JToolbarHelper::title(JText::_('COM_PRIVACY_VIEW_REQUEST_SHOW_REQUEST'), 'lock');

			$bar = JToolbar::getInstance('toolbar');

			// Add transition and action buttons based on item status
			switch ($this->item->status)
			{
				case '0':
					$bar->appendButton('Standard', 'cancel-circle', 'COM_PRIVACY_TOOLBAR_INVALIDATE', 'request.invalidate', false);

					break;

				case '1':
					$return = '&return=' . base64_encode('index.php?option=com_privacy&view=request&id=' . (int) $this->item->id);

					$bar->appendButton('Standard', 'apply', 'COM_PRIVACY_TOOLBAR_COMPLETE', 'request.complete', false);
					$bar->appendButton('Standard', 'cancel-circle', 'COM_PRIVACY_TOOLBAR_INVALIDATE', 'request.invalidate', false);

					if ($this->item->request_type === 'export')
					{
						JToolbarHelper::link(
							JRoute::_('index.php?option=com_privacy&task=request.export&format=xml&id=' . (int) $this->item->id . $return),
							'COM_PRIVACY_ACTION_EXPORT_DATA',
							'download'
						);

						if (JFactory::getConfig()->get('mailonline', 1))
						{
							JToolbarHelper::link(
								JRoute::_(
									'index.php?option=com_privacy&task=request.emailexport&id=' . (int) $this->item->id . $return
									. '&' . JSession::getFormToken() . '=1'
								),
								'COM_PRIVACY_ACTION_EMAIL_EXPORT_DATA',
								'mail'
							);
						}
					}

					if ($this->item->request_type === 'remove')
					{
						$bar->appendButton('Standard', 'delete', 'COM_PRIVACY_ACTION_DELETE_DATA', 'request.remove', false);
					}

					break;

				// Item is in a "locked" state and cannot transition
				default:
					break;
			}

			JToolbarHelper::cancel('request.cancel', 'JTOOLBAR_CLOSE');
			JToolbarHelper::help('JHELP_COMPONENTS_PRIVACY_REQUEST');
		}
	}
}
