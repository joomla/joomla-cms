<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * View class for a list of articles.
 *
 * @since  3.7.0
 */
class AssociationsViewAssociation extends JViewLegacy
{
	/**
	 * An array of items
	 *
	 * @var    array
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
	 * @since   3.7.0
	 */
	public function display($tpl = null)
	{
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);

			return false;
		}

		$this->app  = JFactory::getApplication();
		$this->form = $this->get('Form');
		$input      = $this->app->input;

		$this->referenceId = $input->get('id', 0, 'int');

		list($extensionName, $typeName) = explode('.', $input->get('itemtype'));

		$this->extensionName = $extensionName;
		$this->typeName      = $typeName;
		$this->itemtype      = $extensionName . '.' . $typeName;

		$languageField = AssociationsHelper::getTypeFieldName($extensionName, $typeName, 'language');
		$referenceId   = $input->get('id', 0, 'int');
		$reference     = ArrayHelper::fromObject(AssociationsHelper::getItem($extensionName, $typeName, $referenceId));

		$this->referenceLanguage = $reference[$languageField];

		$options = array(
			'option'    => $typeName === 'category' ? 'com_categories' : $extensionName,
			'view'      => $typeName,
			'extension' => $extensionName,
			'tmpl'      => 'component',
		);

		// Reference and target edit links.
		$this->editUri = 'index.php?' . http_build_query($options);

		// Get target language.
		$this->targetId         = '0';
		$this->targetLanguage   = '';
		$this->defaultTargetSrc = '';
		$this->targetAction     = '';

		if ($target = $input->get('target', '', 'string'))
		{
			$matches = preg_split("#[\:]+#", $target);
			$this->targetAction     = $matches[2];
			$this->targetId         = $matches[1];
			$this->targetLanguage   = $matches[0];
			$task                   = $typeName . '.' . $this->targetAction;
			$this->defaultTargetSrc = JRoute::_($this->editUri . '&task= ' . $task . ' &id=' . (int) $this->targetId);
			$this->form->setValue('itemlanguage', '', $this->targetLanguage . ':' . $this->targetId . ':' . $this->targetAction);
		}

		/*
		* @todo Review later
		*/

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
			$this->sidebar = JHtmlSidebar::render();
		}
		else
		{
			// In article associations modal we need to remove language filter if forcing a language.
			// We also need to change the category filter to show show categories with All or the forced language.
			if ($forcedLanguage = JFactory::getApplication()->input->get('forcedLanguage', '', 'CMD'))
			{
				// If the language is forced we can't allow to select the language, so transform the language selector filter into an hidden field.
				$languageXml = new SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage . '" />');
				$this->filterForm->setField($languageXml, 'filter', true);

				// Also, unset the active language filter so the search tools is not open by default with this filter.
				unset($this->activeFilters['language']);

				// One last changes needed is to change the category filter to just show categories with All language or with the forced language.
				$this->filterForm->setFieldAttribute('category_id', 'language', '*,' . $forcedLanguage, 'filter');
			}
		}

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	protected function addToolbar()
	{
		// Hide main menu.
		JFactory::getApplication()->input->set('hidemainmenu', 1);

		$helper = AssociationsHelper::getExtensionHelper($this->extensionName);
		$title  = $helper->getTypeTitle($this->typeName);

		$languageKey = strtoupper($this->extensionName . '_' . $title . 'S');

		if ($this->typeName === 'category')
		{
			$languageKey = strtoupper($this->extensionName) . '_CATEGORIES';
		}

		JToolbarHelper::title(JText::sprintf('COM_ASSOCIATIONS_TITLE_EDIT', JText::_($this->extensionName), JText::_($languageKey)), 'contract');

		$bar = JToolbar::getInstance('toolbar');

		$bar->appendButton(
			'Custom', '<button onclick="Joomla.submitbutton(\'reference\')"'
			. 'class="btn btn-small btn-success"><span class="icon-apply icon-white"></span>'
			. JText::_('COM_ASSOCIATIONS_SAVE_REFERENCE') . '</button>', 'reference'
		);

		$bar->appendButton(
			'Custom', '<button onclick="Joomla.submitbutton(\'target\')"'
			. 'class="btn btn-small btn-success"><span class="icon-apply icon-white"></span>'
			. JText::_('COM_ASSOCIATIONS_SAVE_TARGET') . '</button>', 'target'
		);

		JToolBarHelper::custom('copy', 'copy.png', '', 'COM_ASSOCIATIONS_COPY_REFERENCE', false);

		JToolbarHelper::cancel('association.cancel', 'JTOOLBAR_CLOSE');
		JToolbarHelper::help('JGLOBAL_HELP');

		JHtmlSidebar::setAction('index.php?option=com_associations');
	}
}
