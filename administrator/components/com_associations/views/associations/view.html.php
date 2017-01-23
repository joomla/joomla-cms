<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of articles.
 *
 * @since  3.7.0
 */
class AssociationsViewAssociations extends JViewLegacy
{
	/**
	 * An array of items
	 *
	 * @var   array
	 *
	 * @since  3.7.0
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var    JPagination
	 *
	 * @since  3.7.0
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var    object
	 *
	 * @since  3.7.0
	 */
	protected $state;

	/**
	 * Selected item type properties.
	 *
	 * @var    Registry
	 *
	 * @since  3.7.0
	 */
	public $itemType = null;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since  3.7.0
	 */
	public function display($tpl = null)
	{
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if (!JLanguageAssociations::isEnabled())
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_ASSOCIATIONS_ERROR_NO_ASSOC'), 'warning');
		}
		elseif ($this->state->get('itemtype') == '' || $this->state->get('language') == '')
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_ASSOCIATIONS_NOTICE_NO_SELECTORS'), 'notice');
		}
		else
		{
			$type = null;

			list($extensionName, $typeName) = explode('.', $this->state->get('itemtype'));

			$extension = AssociationsHelper::getSupportedExtension($extensionName);

			$types = $extension->get('types');

			if (array_key_exists($typeName, $types))
			{
				$type = $types[$typeName];
			}

			$this->itemType = $type;

			if (is_null($type))
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_ASSOCIATIONS_ERROR_NO_TYPE'), 'warning');
			}
			else
			{
				$this->extensionName = $extensionName;
				$this->typeName      = $typeName;
				$this->typeSupports  = array();
				$this->typeFields    = array();

				$details = $type->get('details');

				if (array_key_exists('support', $details))
				{
					$support = $details['support'];
					$this->typeSupports = $support;
				}

				if (array_key_exists('fields', $details))
				{
					$fields = $details['fields'];
					$this->typeFields = $fields;
				}

				// Dynamic filter form.
				// This selectors doesn't have to activate the filter bar.
				unset($this->activeFilters['itemtype']);
				unset($this->activeFilters['language']);

				// Remove filters options depending on selected type.
				if (empty($support['state']))
				{
					unset($this->activeFilters['state']);
					$this->filterForm->removeField('state', 'filter');
				}
				if ($type !== 'category')
				{
					unset($this->activeFilters['category_id']);
					$this->filterForm->removeField('category_id', 'filter');
				}
				if ($extensionName !== 'com_menus')
				{
					unset($this->activeFilters['menutype']);
					$this->filterForm->removeField('menutype', 'filter');
				}
				if (!in_array($extensionName, array('com_categories', 'com_menus')))
				{
					unset($this->activeFilters['level']);
					$this->filterForm->removeField('level', 'filter');
				}
				if (empty($support['acl']))
				{
					unset($this->activeFilters['access']);
					$this->filterForm->removeField('access', 'filter');
				}

				// Add extension attribute to category filter.
				if (empty($support['catid']))
				{
					$this->filterForm->setFieldAttribute('category_id', 'extension', $extensionName, 'filter');
				}

				$this->items      = $this->get('Items');
				$this->pagination = $this->get('Pagination');

				$linkParameters = array(
					'layout'     => 'edit',
					'itemtype'   => $extensionName . '.' . $typeName,
					'task'       => 'association.edit',
				);

				$this->editUri = 'index.php?option=com_associations&view=association&' . http_build_query($linkParameters);
			}
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		// Will add sidebar if needed $this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since  3.7.0
	 */
	protected function addToolbar()
	{
		$user = JFactory::getUser();

		if (isset($this->typeName) && isset($this->extensionName))
		{
			$helper = AssociationsHelper::getExtensionHelper($this->extensionName);
			$title  = $helper->getTypeTitle($this->typeName);

			$languageKey = strtoupper($this->extensionName . '_' . $title . 'S');

			if ($this->typeName === 'category')
			{
				$languageKey = strtoupper($this->extensionName) . '_CATEGORIES';
			}

			JToolbarHelper::title(
				JText::sprintf(
					'COM_ASSOCIATIONS_TITLE_LIST', JText::_($this->extensionName), JText::_($languageKey)
				), 'contract'
			);
		}
		else
		{
			JToolbarHelper::title(JText::_('COM_ASSOCIATIONS_TITLE_LIST_SELECT'), 'contract');
		}
	
		if ($user->authorise('core.admin', 'com_associations') || $user->authorise('core.options', 'com_associations'))
		{
			if (!isset($this->typeName))
			{
				JToolbarHelper::custom('associations.purge', 'purge', 'purge', 'COM_ASSOCIATIONS_PURGE', false, false);
				JToolbarHelper::custom('associations.clean', 'refresh', 'refresh', 'COM_ASSOCIATIONS_DELETE_ORPHANS', false, false);
			}
			JToolbarHelper::preferences('com_associations');
		}

		/*
		 * @todo Help page
		*/
		JToolbarHelper::help('JGLOBAL_HELP');
	}
}
