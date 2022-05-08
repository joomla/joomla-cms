<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Administrator\View\Group;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/**
 * Group View
 *
 * @since  3.7.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * @var  \JForm
	 *
	 * @since  3.7.0
	 */
	protected $form;

	/**
	 * @var  \JObject
	 *
	 * @since  3.7.0
	 */
	protected $item;

	/**
	 * @var  \JObject
	 *
	 * @since  3.7.0
	 */
	protected $state;

	/**
	 * The actions the user is authorised to perform
	 *
	 * @var  \JObject
	 *
	 * @since  3.7.0
	 */
	protected $canDo;


	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @see     JViewLegacy::loadTemplate()
	 * @since   3.7.0
	 */
	public function display($tpl = null)
	{
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');

		$component = '';
		$parts     = FieldsHelper::extract($this->state->get('filter.context'));

		if ($parts)
		{
			$component = $parts[0];
		}

		$this->canDo = ContentHelper::getActions($component, 'fieldgroup', $this->item->id);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		Factory::getApplication()->input->set('hidemainmenu', true);

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Adds the toolbar.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	protected function addToolbar()
	{
		$component = '';
		$parts     = FieldsHelper::extract($this->state->get('filter.context'));

		if ($parts)
		{
			$component = $parts[0];
		}

		$userId    = Factory::getUser()->get('id');
		$canDo     = $this->canDo;

		$isNew      = ($this->item->id == 0);
		$checkedOut = !(is_null($this->item->checked_out) || $this->item->checked_out == $userId);

		// Avoid nonsense situation.
		if ($component == 'com_fields')
		{
			return;
		}

		// Load component language file
		$lang = Factory::getLanguage();
		$lang->load($component, JPATH_ADMINISTRATOR)
		|| $lang->load($component, Path::clean(JPATH_ADMINISTRATOR . '/components/' . $component));

		$title = Text::sprintf('COM_FIELDS_VIEW_GROUP_' . ($isNew ? 'ADD' : 'EDIT') . '_TITLE', Text::_(strtoupper($component)));

		// Prepare the toolbar.
		ToolbarHelper::title(
			$title,
			'puzzle-piece field-' . ($isNew ? 'add' : 'edit') . ' ' . substr($component, 4) . '-group-' .
			($isNew ? 'add' : 'edit')
		);

		$toolbarButtons = [];

		// For new records, check the create permission.
		if ($isNew)
		{
			ToolbarHelper::apply('group.apply');

			ToolbarHelper::saveGroup(
				[
					['save', 'group.save'],
					['save2new', 'group.save2new']
				],
				'btn-success'
			);

			ToolbarHelper::cancel('group.cancel');
		}
		else
		{
			// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
			$itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);

			$toolbarButtons = [];

			// Can't save the record if it's checked out and editable
			if (!$checkedOut && $itemEditable)
			{
				ToolbarHelper::apply('group.apply');

				$toolbarButtons[] = ['save', 'group.save'];

				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create'))
				{
					$toolbarButtons[] = ['save2new', 'group.save2new'];
				}
			}

			// If an existing item, can save to a copy.
			if ($canDo->get('core.create'))
			{
				$toolbarButtons[] = ['save2copy', 'group.save2copy'];
			}

			ToolbarHelper::saveGroup(
				$toolbarButtons,
				'btn-success'
			);

			ToolbarHelper::cancel('group.cancel', 'JTOOLBAR_CLOSE');
		}

		ToolbarHelper::help('JHELP_COMPONENTS_FIELDS_FIELD_GROUPS_EDIT');
	}
}
