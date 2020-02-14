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

class SppagebuilderViewIntegrations extends JViewLegacy {

	protected $items;
	protected $pagination;
	protected $state;

	public function display( $tpl = null ) {
		$this->items = $this->get('Items');

		if(count( $errors = $this->get('Errors'))) {
			JError::raiseError(500,implode('<br />',$errors));
		}

		$this->addToolbar();
		$this->addSubmenu('integrations');
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
			$vName == 'categories'
		);

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
		JToolBarHelper::title( JText::_('COM_SPPAGEBUILDER') . ' - ' . JText::_('COM_SPPAGEBUILDER_INTEGRATIONS'));
	}

}
