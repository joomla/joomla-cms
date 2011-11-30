<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Categories view class for the Category package.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 * @since		1.6
 */
class CategoriesViewCategories extends JView
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->state		= $this->get('State');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Preprocess the list of items to find ordering divisions.
		foreach ($this->items as &$item) {
			$this->ordering[$item->parent_id][] = $item->id;
		}

		// Levels filter.
		$options	= array();
		$options[]	= JHtml::_('select.option', '1', JText::_('J1'));
		$options[]	= JHtml::_('select.option', '2', JText::_('J2'));
		$options[]	= JHtml::_('select.option', '3', JText::_('J3'));
		$options[]	= JHtml::_('select.option', '4', JText::_('J4'));
		$options[]	= JHtml::_('select.option', '5', JText::_('J5'));
		$options[]	= JHtml::_('select.option', '6', JText::_('J6'));
		$options[]	= JHtml::_('select.option', '7', JText::_('J7'));
		$options[]	= JHtml::_('select.option', '8', JText::_('J8'));
		$options[]	= JHtml::_('select.option', '9', JText::_('J9'));
		$options[]	= JHtml::_('select.option', '10', JText::_('J10'));

		$this->assign('f_levels', $options);
		
		$this->prepareTable();

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Prepare the table of the view
	 * 
	 * @since 2.5
	 */
	protected function prepareTable()
	{
		$user		= JFactory::getUser();
		$userId		= $user->get('id');
		$extension	= $this->escape($this->state->get('filter.extension'));
		$listOrder	= $this->escape($this->state->get('list.ordering'));
		$listDirn	= $this->escape($this->state->get('list.direction'));
		$ordering 	= ($listOrder == 'a.lft');
		$saveOrder 	= ($listOrder == 'a.lft' && $listDirn == 'asc');

		jimport('joomla.html.grid');
		$table = new JGrid(array('class' => 'adminlist'));
		
		$table->addColumn('checkbox')
			->addColumn('title')
			->addColumn('status')
			->addColumn('ordering')
			->addColumn('access')
			->addColumn('language')
			->addColumn('id')
		;
		
		$table->addRow(array(), 1)
			->setRowCell('checkbox', '<input type="checkbox" name="checkall-toggle" value="" title="'.JText::_('JGLOBAL_CHECK_ALL').'" onclick="Joomla.checkAll(this)" />', array('width' => '1%'))
			->setRowCell('title', JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder))
			->setRowCell('status', JHtml::_('grid.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder), array('width' => '5%'))
			->setRowCell('ordering', JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', 'a.lft', $listDirn, $listOrder).
				($saveOrder ? JHtml::_('grid.order',  $this->items, 'filesave.png', 'categories.saveorder') : ''), array('width' => '10%'))
			->setRowCell('access', JHtml::_('grid.sort',  'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder), array('width' => '10%'))
			->setRowCell('language', JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $this->state->get('list.direction'), $this->state->get('list.ordering')), array('width' => '5%', 'class' => 'nowrap'))
			->setRowCell('id', JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder), array('width' => '1%', 'class' => 'nowrap'))
		;
		
		$table->addRow(array(), 2)
			->setRowCell('checkbox', $this->pagination->getListFooter(), array('colspan' => 15))
		;
		$originalOrders = array();
		
		foreach ($this->items as $i => $item) {
			$orderkey	= array_search($item->id, $this->ordering[$item->parent_id]);
			$canEdit	= $user->authorise('core.edit',			$extension.'.category.'.$item->id);
			$canCheckin	= $user->authorise('core.admin', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
			$canEditOwn	= $user->authorise('core.edit.own',		$extension.'.category.'.$item->id) && $item->created_user_id == $userId;
			$canChange	= $user->authorise('core.edit.state',	$extension.'.category.'.$item->id) && $canCheckin;
				
			$table->addRow(array('class' => 'row'.($i % 2)))
				->setRowCell('checkbox', JHtml::_('grid.id', $i, $item->id), array('class' => 'center'));
			$table->setRowCell('title', str_repeat('<span class="gi">|&mdash;</span>', $item->level-1));
			if ($item->checked_out) {
				$table->setRowCell('title', JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'categories.', $canCheckin), array(), false);
			} else {
				if ($canEdit || $canEditOwn) {
					$table->setRowCell('title', '<a href="'.JRoute::_('index.php?option=com_categories&task=category.edit&id='.$item->id.'&extension='.$extension).'">
								'.$this->escape($item->title).'</a>', array(), false);
				} else {
					$table->setRowCell('title', $this->escape($item->title), array(), false);
				}
				$table->setRowCell('title', '<p class="smallsub" title="'.$this->escape($item->path).'">
							'.str_repeat('<span class="gtr">|&mdash;</span>', $item->level-1), array(), false);
				if (empty($item->note)) {
					$table->setRowCell('title', JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)), array(), false);
				} else {
					$table->setRowCell('title', JText::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)), array(), false);
				}
				$table->setRowCell('title', '</p>', array(), false);
			}
			
			$table->setRowCell('status', JHtml::_('jgrid.published', $item->published, $i, 'categories.', $canChange), array('class' => 'center'));
			
			if ($canChange) {
				if ($saveOrder) {
					$table->setRowCell('ordering', '<span>'.$this->pagination->orderUpIcon($i, isset($this->ordering[$item->parent_id][$orderkey - 1]), 'categories.orderup', 'JLIB_HTML_MOVE_UP', $ordering).'</span>', array(), false);
					$table->setRowCell('ordering', '<span>'.$this->pagination->orderDownIcon($i, $this->pagination->total, isset($this->ordering[$item->parent_id][$orderkey + 1]), 'categories.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering).'</span>', array(), false);
				}
				$disabled = $saveOrder ?  '' : 'disabled="disabled"';
				$table->setRowCell('ordering', '<input type="text" name="order[]" size="5" value="'.($orderkey + 1).'" '.$disabled.' class="text-area-order" />', array(), false);
				$originalOrders[] = $orderkey + 1;
			} else {
				$table->setRowCell('ordering', $orderkey + 1, array(), false);
			}

			$table->setRowCell('ordering', '', array('class' => 'order'), false);
			
			$table->setRowCell('access', $this->escape($item->access_level), array('class' => 'center nowrap'))
				->setRowCell('language', ($item->language == '*') ? JText::alt('JALL','language') : ($item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED')), array('class' => 'center nowrap'))
				->setRowCell('id', '<span title="'.sprintf('%d-%d', $item->lft, $item->rgt).'">'.(int) $item->id.'</span>', array('class' => 'center'));
		}
		
		$this->originalOrders = $originalOrders;
		$this->table = $table;
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		// Initialise variables.
		$categoryId	= $this->state->get('filter.category_id');
		$component	= $this->state->get('filter.component');
		$section	= $this->state->get('filter.section');
		$canDo		= null;

		// Avoid nonsense situation.
		if ($component == 'com_categories') {
			return;
		}

		// Need to load the menu language file as mod_menu hasn't been loaded yet.
		$lang = JFactory::getLanguage();
			$lang->load($component, JPATH_BASE, null, false, false)
		||	$lang->load($component, JPATH_ADMINISTRATOR.'/components/'.$component, null, false, false)
		||	$lang->load($component, JPATH_BASE, $lang->getDefault(), false, false)
		||	$lang->load($component, JPATH_ADMINISTRATOR.'/components/'.$component, $lang->getDefault(), false, false);

 		// Load the category helper.
		require_once JPATH_COMPONENT.'/helpers/categories.php';

		// Get the results for each action.
		$canDo = CategoriesHelper::getActions($component, $categoryId);

		// If a component categories title string is present, let's use it.
		if ($lang->hasKey($component_title_key = strtoupper($component.($section?"_$section":'')).'_CATEGORIES_TITLE')) {
			$title = JText::_($component_title_key);
		}
		// Else if the component section string exits, let's use it
		elseif ($lang->hasKey($component_section_key = strtoupper($component.($section?"_$section":'')))) {
			$title = JText::sprintf( 'COM_CATEGORIES_CATEGORIES_TITLE', $this->escape(JText::_($component_section_key)));
		}
		// Else use the base title
		else {
			$title = JText::_('COM_CATEGORIES_CATEGORIES_BASE_TITLE');
		}

		// Load specific css component
		JHtml::_('stylesheet',$component.'/administrator/categories.css', array(), true);

		// Prepare the toolbar.
		JToolBarHelper::title($title, 'categories '.substr($component,4).($section?"-$section":'').'-categories');

		if ($canDo->get('core.create')) {
			 JToolBarHelper::addNew('category.add');
		}

		if ($canDo->get('core.edit' ) || $canDo->get('core.edit.own')) {
			JToolBarHelper::editList('category.edit');
			JToolBarHelper::divider();
		}

		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::publish('categories.publish', 'JTOOLBAR_PUBLISH', true);
			JToolBarHelper::unpublish('categories.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::divider();
			JToolBarHelper::archiveList('categories.archive');
		}

		if (JFactory::getUser()->authorise('core.admin')) {
			JToolBarHelper::checkin('categories.checkin');
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete', $component)) {
			JToolBarHelper::deleteList('', 'categories.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		else if ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('categories.trash');
			JToolBarHelper::divider();
		}

		if ($canDo->get('core.admin')) {
			JToolBarHelper::custom('categories.rebuild', 'refresh.png', 'refresh_f2.png', 'JTOOLBAR_REBUILD', false);
			JToolBarHelper::preferences($component);
			JToolBarHelper::divider();
		}

		// Compute the ref_key if it does exist in the component
		if (!$lang->hasKey($ref_key = strtoupper($component.($section?"_$section":'')).'_CATEGORIES_HELP_KEY')) {
			$ref_key = 'JHELP_COMPONENTS_'.strtoupper(substr($component,4).($section?"_$section":'')).'_CATEGORIES';
		}

		// Get help for the categories view for the component by
		// -remotely searching in a language defined dedicated URL: *component*_HELP_URL
		// -locally  searching in a component help file if helpURL param exists in the component and is set to ''
		// -remotely searching in a component URL if helpURL param exists in the component and is NOT set to ''
		if ($lang->hasKey($lang_help_url = strtoupper($component).'_HELP_URL')) {
			$debug = $lang->setDebug(false);
			$url = JText::_($lang_help_url);
			$lang->setDebug($debug);
		}
		else {
			$url = null;
		}
		JToolBarHelper::help($ref_key, JComponentHelper::getParams( $component )->exists('helpURL'), $url);
	}
}
