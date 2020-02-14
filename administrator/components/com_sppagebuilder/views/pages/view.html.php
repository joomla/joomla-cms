<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

jimport('joomla.application.component.view');

class SppagebuilderViewPages extends JViewLegacy {

	protected $items;
	protected $pagination;
	protected $state;

	public function display( $tpl = null ) {
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');

		//Joomla Component Helper
		jimport('joomla.application.component.helper');
		$this->params = JComponentHelper::getParams('com_sppagebuilder');

		if(count( $errors = $this->get('Errors'))) {
			JError::raiseError(500,implode('<br />',$errors));
		}

		$this->addToolbar();
		$this->addSubmenu('pages');
		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	protected function addSubmenu ($vName) {
		JHtmlSidebar::addEntry(
			'<i class="fa fa-list-ul"></i> ' . JText::_('COM_SPPAGEBUILDER_PAGES'),
			'index.php?option=com_sppagebuilder&view=pages',
			$vName == 'pages'
		);

		JHtmlSidebar::addEntry(
			'<i class="fa fa-folder-o"></i> ' . JText::_('COM_SPPAGEBUILDER_CATEGORIES'),
			'index.php?option=com_categories&extension=com_sppagebuilder',
			$vName == 'categories');

		JHtmlSidebar::addEntry(
			'<i class="fa fa-plug"></i> ' . JText::_('COM_SPPAGEBUILDER_INTEGRATIONS'),
			'index.php?option=com_sppagebuilder&view=integrations',
			$vName == 'integrations'
		);

		JHtmlSidebar::addEntry(
			'<i class="fa fa-globe"></i> ' . JText::_('COM_SPPAGEBUILDER_LANGUAGES'),
			'index.php?option=com_sppagebuilder&view=languages',
			$vName == 'languages'
		);

		JHtmlSidebar::addEntry(
			'<i class="fa fa-info-circle"></i> ' . JText::_('COM_SPPAGEBUILDER_ABOUT_SPPB'),
			'index.php?option=com_sppagebuilder&view=about',
			$vName == 'about'
		);

		JHtmlSidebar::addEntry(
			'<i class="fa fa-picture-o"></i> ' . JText::_('COM_SPPAGEBUILDER_MEDIA'),
			'index.php?option=com_sppagebuilder&view=media',
			$vName == 'media'
		);

	}

	protected function addToolBar() {
		$state	= $this->get('State');
		$canDo	= JHelperContent::getActions('com_sppagebuilder');
		$user	= JFactory::getUser();
		$bar 	= JToolBar::getInstance('toolbar');

		JToolBarHelper::title( JText::_('COM_SPPAGEBUILDER') . ' - ' . JText::_('COM_SPPAGEBUILDER_PAGES'));

		if ($canDo->get('core.create')) {
			JToolBarHelper::preferences('com_sppagebuilder');
		}

		// new page button
		if ($canDo->get('core.admin')) {
			JToolbarHelper::addNew('page.add');
		}

		// edit button
		if ($canDo->get('core.edit')) {
			JToolbarHelper::editList('page.edit');
		}

		// publish and unpublish button
		if ($canDo->get('core.edit.state')) {
			JToolbarHelper::publish('pages.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('pages.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::checkin('pages.checkin');
		}

		// delete and trush button
		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
			JToolbarHelper::deleteList('', 'pages.delete', 'JTOOLBAR_EMPTY_TRASH');
		} elseif ($canDo->get('core.edit.state')) {
			JToolbarHelper::trash('pages.trash');
		}

    JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_PUBLISHED'),
			'filter_published',
			JHtml::_('select.options', JHtml::_('jgrid.publishedOptions',array('archived'=>false)), 'value', 'text', $this->state->get('filter.published'), true)
		);

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_CATEGORY'),
			'filter_category_id',
			JHtml::_('select.options', JHtml::_('category.options', 'com_sppagebuilder'), 'value', 'text', $this->state->get('filter.category_id'))
		);

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_ACCESS'),
			'filter_access',
			JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'))
		);

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_LANGUAGE'),
			'filter_language',
			JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language'))
		);
	}

	protected function getSortFields() {
		return array(
			'a.ordering'  	=> JText::_('JGRID_HEADING_ORDERING'),
			'a.published' 	=> JText::_('JSTATUS'),
			'a.title' 		=> JText::_('JGLOBAL_TITLE'),
			'a.access' 		=> JText::_('JGRID_HEADING_ACCESS'),
			'a.language' 	=> JText::_('JGRID_HEADING_LANGUAGE'),
			'a.id' 			=> JText::_('JGRID_HEADING_ID')
		);
	}
}
