<?php
/**
 * @package     Joomla.Cms
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\View;

defined('JPATH_PLATFORM') or die;

/**
 * Base class for a Joomla Form View
 *
 * Class holding methods for displaying presentation data.
 *
 * @since  2.5.5
 */
class FormView extends HtmlView
{
	/**
	 * The \JForm object
	 *
	 * @var  \JForm
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var  object
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var  object
	 */
	protected $state;

	/**
	 * The actions the user is authorised to perform
	 *
	 * @var  \JObject
	 */
	protected $canDo;

	/**
	 * The toolbar title
	 *
	 * @var string
	 */
	protected $toolbarTitle;

	/**
	 * The preview link
	 *
	 * @var string
	 */
	protected $previewLink;

	/**
	 * The help link
	 *
	 * @var string
	 */
	protected $helpLink;

	/**
	 * Constructor
	 *
	 * @param   array   $config  An optional associative array of configuration settings.
	 */
	public function __construct(array $config)
	{
		parent::__construct($config);

		// Set class properties from config data passed in constructor
		if (isset($config['canDo']))
		{
			$this->canDo = $config['canDo'];
		}

		if (isset($config['help_link']))
		{
			$this->helpLink = $config['help_link'];
		}
	}

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return   mixed
	 */
	public function display($tpl = null)
	{
		// Prepare view data
		$this->initializeView();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		// Build toolbar
		$this->addToolbar();

		// Render the view
		return parent::display($tpl);
	}

	/**
	 * Prepare view data
	 *
	 * @return  void
	 */
	protected function initializeView()
	{
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');

		// Property $canDo should be built by the child class before, if not, generate default value
		if (!empty($this->canDo))
		{
			$this->canDo = new \JObject;
		}
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
		\JFactory::getApplication()->input->set('hidemainmenu', true);

		$user       = \JFactory::getUser();
		$userId     = $user->id;
		$isNew      = ($this->item->id == 0);
		$viewName   = $this->getName();
		$checkedOut = $this->getModel()->isCheckedOut($this->item);
		$canDo      = $this->canDo;

		if (empty($this->toolbarTitle))
		{
			$langKey = strtoupper($this->option . '_PAGE_' . ($checkedOut ? 'VIEW_' . $viewName : ($isNew ? 'ADD_' . $viewName : 'EDIT_' . $viewName)));
			$this->toolbarTitle = \JText::_($langKey);
		}

		\JToolbarHelper::title(
			$this->toolbarTitle,
			'pencil-2 ' . $viewName . '-add'
		);

		// For new records, check the create permission.
		if ($isNew && $canDo->get('core.create'))
		{
			\JToolbarHelper::saveGroup(
				[
					['apply', $viewName . '.apply'],
					['save', $viewName . '.save'],
					['save2new', $viewName . '.save2new']
				],
				'btn-success'
			);

			\JToolbarHelper::cancel($viewName . '.cancel');
		}
		else
		{
			// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
			if (property_exists($this->item, 'created_by'))
			{
				$itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);
			}
			else
			{
				$itemEditable = $canDo->get('core.edit');
			}

			$toolbarButtons = [];

			// Can't save the record if it's checked out and editable
			if (!$checkedOut && $itemEditable)
			{
				$toolbarButtons[] = ['apply', $viewName . '.apply'];
				$toolbarButtons[] = ['save', $viewName . '.save'];

				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create'))
				{
					$toolbarButtons[] = ['save2new', $viewName . '.save2new'];
				}
			}

			// If checked out, we can still save
			if ($canDo->get('core.create'))
			{
				$toolbarButtons[] = ['save2copy', $viewName . '.save2copy'];
			}

			\JToolbarHelper::saveGroup(
				$toolbarButtons,
				'btn-success'
			);

			if (\JComponentHelper::isEnabled('com_contenthistory') && $this->state->params->get('save_history', 0) && $itemEditable)
			{
				\JToolbarHelper::versions($this->option . '.' . $viewName, $this->item->id);
			}

			if (!$isNew && $this->previewLink)
			{
				\JToolbarHelper::preview($this->previewLink, \JText::_('JGLOBAL_PREVIEW'), 'eye', 80, 90);
			}

			\JToolbarHelper::cancel($viewName . '.cancel', 'JTOOLBAR_CLOSE');
		}

		\JToolbarHelper::divider();

		if ($this->helpLink)
		{
			\JToolbarHelper::help($this->helpLink);
		}
	}
}
