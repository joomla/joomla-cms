<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of articles.
 *
 * @since  1.6
 */
class ContentViewArticles extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		if ($this->getLayout() !== 'modal')
		{
			ContentHelper::addSubmenu('articles');
		}

		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->authors       = $this->get('Authors');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
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

		$this->f_levels = $options;

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
			$this->sidebar = JHtmlSidebar::render();
		}

		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
		$assoc		= JLanguageAssociations::isEnabled();

		$app		= JFactory::getApplication();
		$user		= JFactory::getUser();
		$userId		= $user->get('id');
		$listOrder	= $this->escape($this->state->get('list.ordering'));
		$listDirn	= $this->escape($this->state->get('list.direction'));
		$archived	= $this->state->get('filter.published') == 2 ? true : false;
		$trashed	= $this->state->get('filter.published') == -2 ? true : false;
		$saveOrder	= $listOrder == 'a.ordering';

		$this->table = new JGrid(array('class' => 'table table-striped', 'id' => 'articleList'));
		$this->table->addColumn('ordering')
			->addColumn('checkbox')
			->addColumn('status')
			->addColumn('title')
			->addColumn('access');
		if ($assoc)
		{
			$this->table->addColumn('assoc');
		}
		$this->table->addColumn('author')
			->addColumn('language')
			->addColumn('date')
			->addColumn('hits')
			->addColumn('id');

		$this->table->addRow(array(), 1)
			->setRowCell('ordering', JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'), array('width' => '1%', 'class' => 'nowrap center hidden-phone'))
			->setRowCell('checkbox', JHtml::_('grid.checkall'), array('width' => '1%', 'class' => 'hidden-phone'))
			->setRowCell('status', JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder), array('width' => '1%', 'style' => 'min-width:55px', 'class' => 'nowrap center'))
			->setRowCell('title', JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder))
			->setRowCell('access', JHtml::_('searchtools.sort',  'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder), array('width' => '10%', 'class' => 'nowrap hidden-phone'));
		if ($assoc)
		{
			$this->table->setRowCell('assoc', JHtml::_('searchtools.sort', 'COM_CONTENT_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder), array('width' => '5%', 'class' => 'nowrap hidden-phone'));
		}
		$this->table->setRowCell('author', JHtml::_('searchtools.sort',  'JAUTHOR', 'a.created_by', $listDirn, $listOrder), array('width' => '10%', 'class' => 'nowrap hidden-phone'))
			->setRowCell('language', JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder), array('width' => '5%', 'class' => 'nowrap hidden-phone'))
			->setRowCell('date', JHtml::_('searchtools.sort', 'JDATE', 'a.created', $listDirn, $listOrder), array('width' => '10%', 'class' => 'nowrap hidden-phone'))
			->setRowCell('hits',JHtml::_('searchtools.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder), array('width' => '10%'))
			->setRowCell('id', JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder), array('width' => '1%', 'class' => 'nowrap hidden-phone'));

		foreach ($this->items as $i => $item) {
			$item->max_ordering = 0;
			$ordering   = ($listOrder == 'a.ordering');
			$canCreate  = $user->authorise('core.create',     'com_content.category.' . $item->catid);
			$canEdit    = $user->authorise('core.edit',       'com_content.article.' . $item->id);
			$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
			$canEditOwn = $user->authorise('core.edit.own',   'com_content.article.' . $item->id) && $item->created_by == $userId;
			$canChange  = $user->authorise('core.edit.state', 'com_content.article.' . $item->id) && $canCheckin;
			
			$this->table->addRow(array('class' => 'row' . ($i % 2), 'sortable-group-id' => $item->catid));
			$iconClass = '';
			if (!$canChange)
			{
				$iconClass = ' inactive';
			}
			elseif (!$saveOrder)
			{
				$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
			}
			$ordering = '<span class="sortable-handler' . $iconClass . '"><i class="icon-menu"></i></span>';
			if ($canChange && $saveOrder)
			{
				$ordering .= '<input type="text" style="display:none" name="order[]" size="5" value="' . $item->ordering . '" class="width-20 text-area-order " />';
			}
			$this->table->setRowCell('ordering', $ordering, array('class' => 'order nowrap center hidden-phone'))
				->setRowCell('checkbox', JHtml::_('grid.id', $i, $item->id), array('class' => 'center hidden-phone'));
			
			$status = '<div class="btn-group">';
			$status .= JHtml::_('jgrid.published', $item->state, $i, 'articles.', $canChange, 'cb', $item->publish_up, $item->publish_down);
			$status .= JHtml::_('contentadministrator.featured', $item->featured, $i, $canChange);

			// Create dropdown items
			$action = $archived ? 'unarchive' : 'archive';
			JHtml::_('actionsdropdown.' . $action, 'cb' . $i, 'articles');

			$action = $trashed ? 'untrash' : 'trash';
			JHtml::_('actionsdropdown.' . $action, 'cb' . $i, 'articles');

			// Render dropdown list
			$status .= JHtml::_('actionsdropdown.render', $this->escape($item->title));
			$status .= '</div>';
			$this->table->setRowCell('status', $status, array('class' => 'center'));
			
			$title = '<div class="pull-left">';
			if ($item->checked_out)
			{
				$title .= JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'articles.', $canCheckin);
			}
			if ($item->language == '*')
			{
				$language = JText::alt('JALL', 'language');
			}
			else
			{
				$language = $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED');
			}
			if ($canEdit || $canEditOwn)
			{
				$title .= '<a href="' . JRoute::_('index.php?option=com_content&task=article.edit&id=' . $item->id) . '" title="' . JText::_('JACTION_EDIT') . '">' . $this->escape($item->title) . '</a>';
			}
			else
			{
				$title .= '<span title="' . JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)) . '">' . $this->escape($item->title) . '</span>';
			}
			$title .= '<span class="small">' . JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)) . '</span>';
			$title .= '<div class="small">' . JText::_('JCATEGORY') . ": " . $this->escape($item->category_title) . '</div>';
			$title .= '</div>';
			$this->table->setRowCell('title', $title, array('class' => 'has-context'))
				->setRowCell('access', $this->escape($item->access_level), array('class' => 'small hidden-phone'));
			if ($assoc && $item->association)
			{
				$this->table->setRowCell('assoc', JHtml::_('contentadministrator.association', $item->id), array('class' => 'hidden-phone'));
			}

			$author = '<a href="' . JRoute::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->created_by) . '" title="' . JText::_('JAUTHOR') . '">'
				 . $this->escape($item->author_name) . '</a>';
			if ($item->created_by_alias)
			{
				$author .= '<p class="smallsub"> ' . JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->created_by_alias)) . '</p>';
			}
			$this->table->setRowCell('author', $author, array('class' => 'small hidden-phone'));
			if ($item->language == '*')
			{
				$this->table->setRowCell('language', JText::alt('JALL', 'language'), array('class' => 'small hidden-phone'));
			}
			else
			{
				$this->table->setRowCell('language', ($item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED')), array('class' => 'small hidden-phone'));
			}
			$this->table->setRowCell('date', JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')), array('class' => 'nowrap small hidden-phone'))
				->setRowCell('hits', (int) $item->hits, array('class' => 'center'))
				->setRowCell('id', (int) $item->id, array('class' => 'center hidden-phone'));
		}

		parent::display($tpl);
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
		$canDo = JHelperContent::getActions('com_content', 'category', $this->state->get('filter.category_id'));
		$user  = JFactory::getUser();

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('COM_CONTENT_ARTICLES_TITLE'), 'stack article');

		if ($canDo->get('core.create') || (count($user->getAuthorisedCategories('com_content', 'core.create'))) > 0 )
		{
			JToolbarHelper::addNew('article.add');
		}

		if (($canDo->get('core.edit')) || ($canDo->get('core.edit.own')))
		{
			JToolbarHelper::editList('article.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('articles.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('articles.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::custom('articles.featured', 'featured.png', 'featured_f2.png', 'JFEATURE', true);
			JToolbarHelper::custom('articles.unfeatured', 'unfeatured.png', 'featured_f2.png', 'JUNFEATURE', true);
			JToolbarHelper::archiveList('articles.archive');
			JToolbarHelper::checkin('articles.checkin');
		}

		// Add a batch button
		if ($user->authorise('core.create', 'com_content') && $user->authorise('core.edit', 'com_content') && $user->authorise('core.edit.state', 'com_content'))
		{
			JHtml::_('bootstrap.modal', 'collapseModal');
			$title = JText::_('JTOOLBAR_BATCH');

			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'articles.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('articles.trash');
		}

		if ($user->authorise('core.admin', 'com_content') || $user->authorise('core.options', 'com_content'))
		{
			JToolbarHelper::preferences('com_content');
		}

		JToolbarHelper::help('JHELP_CONTENT_ARTICLE_MANAGER');
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.ordering'     => JText::_('JGRID_HEADING_ORDERING'),
			'a.state'        => JText::_('JSTATUS'),
			'a.title'        => JText::_('JGLOBAL_TITLE'),
			'category_title' => JText::_('JCATEGORY'),
			'access_level'   => JText::_('JGRID_HEADING_ACCESS'),
			'a.created_by'   => JText::_('JAUTHOR'),
			'language'       => JText::_('JGRID_HEADING_LANGUAGE'),
			'a.created'      => JText::_('JDATE'),
			'a.id'           => JText::_('JGRID_HEADING_ID'),
			'a.featured'     => JText::_('JFEATURED')
		);
	}
}
