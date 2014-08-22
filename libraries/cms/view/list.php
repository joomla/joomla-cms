<?php
/**
 * View List class, used to render list of records from back-end of your component
 * 
 * @package     Joomla.JCms
 * @subpackage  ViewList
 * @author	Ossolution Team
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die();
class JCmsViewList extends JCmsViewHtml
{

	/**
	 * The model state
	 *
	 * @var JCmsModelState
	 */
	protected $state;

	/**
	 * Hold actions which can be performed
	 * @var JObject
	 */
	protected $canDo;

	/**
	 * List of records which will be displayed
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var JPagination
	 */
	protected $pagination;

	/**
	 * Method to instantiate the view.
	 *
	 * @param   array  $config The configuration data for the view
	 *
	 * @since  3.5
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Method to display a view
	 * 
	 * @see JCmsViewHtml::display()
	 */
	public function display()
	{
		$this->prepareView();
		$this->sidebar = JHtmlSidebar::render();
		parent::display();
	}

	/**
	 * Prepare the view before it is displayed
	 * 
	 */
	protected function prepareView()
	{
		$this->state = $this->model->getState();
		$this->items = $this->model->getData();
		$this->pagination = $this->model->getPagination();
		if ($this->isAdminView)
		{
			
			$helperClass = $this->classPrefix . 'Helper';
			if (is_callable($helperClass . '::addSubmenus'))
			{
				call_user_func(array($helperClass, 'addSubmenus'), $this->name);
			}
			else
			{
				call_user_func(array('JCmsComponentHelper', 'addSubmenus'), $this->name);
			}
			$this->getActions();
			$this->addTitle();
			$this->addToolbar();
			$this->addFilters();
		}
	}

	/**
	 * Get actions which users can perform
	 *
	 * @return void        	
	 */
	protected function getActions()
	{
		$helperClass = $this->classPrefix . 'Helper';
		if (is_callable($helperClass . '::getActions'))
		{
			$this->canDo = call_user_func(array($helperClass, 'getActions'), $this->name, $this->state);
		}
		else
		{
			$this->canDo = call_user_func(array('JCmsHelper', 'getActions'), $this->option, $this->name, $this->state);
		}
	}

	/**
	 * Method to add title to toolbar
	 * 
	 */
	protected function addTitle()
	{
		JToolBarHelper::title(JText::_(strtoupper($this->languagePrefix . '_MANAGER_' . $this->name)), 'link ' . $this->name);
	}

	/**
	 * Method to add sidebar filter
	 */
	protected function addFilters()
	{
		JHtmlSidebar::setAction('index.php?option=' . $this->option . '&view=' . $this->name);
		
		JHtmlSidebar::addFilter(JText::_('JOPTION_SELECT_PUBLISHED'), 'filter_state', 
			JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->filter_state, true));
		
		JHtmlSidebar::addFilter(JText::_('JOPTION_SELECT_ACCESS'), 'filter_access', 
			JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->filter_access));
		
		JHtmlSidebar::addFilter(JText::_('JOPTION_SELECT_LANGUAGE'), 'filter_language', 
			JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->filter_language));
		
		JHtmlSidebar::addFilter(JText::_('JOPTION_SELECT_TAG'), 'filter_tag', 
			JHtml::_('select.options', JHtml::_('tag.options', true, true), 'value', 'text', $this->state->filter_tag));
	}

	/**
	 * Method to add toolbar buttons
	 * 
	 */
	protected function addToolbar()
	{
		$canDo = $this->canDo;
		if ($canDo->get('core.create'))
		{
			JToolBarHelper::addNew('add', 'JTOOLBAR_NEW');
		}
		if ($canDo->get('core.edit') && isset($this->items[0]))
		{
			JToolBarHelper::editList('edit', 'JTOOLBAR_EDIT');
		}
		if ($canDo->get('core.delete') && isset($this->items[0]))
		{
			JToolBarHelper::deleteList(JText::_($this->languagePrefix . '_DELETE_CONFIRM'), 'delete');
		}
		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->published) || isset($this->items[0]->state))
			{
				JToolbarHelper::publish('publish', 'JTOOLBAR_PUBLISH', true);
				JToolbarHelper::unpublish('unpublish', 'JTOOLBAR_UNPUBLISH', true);
				
				JToolbarHelper::archiveList('archive');
				JToolbarHelper::checkin('checkin');
			}
		}
		
		if ($state->filter_state == -2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('trash');
		}
		
		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences($this->option);
		}
	}
}
