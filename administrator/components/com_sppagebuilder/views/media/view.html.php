<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

require_once JPATH_ROOT . '/administrator/components/com_sppagebuilder/helpers/language.php';

jimport('joomla.application.component.view');

class SppagebuilderViewMedia extends JViewLegacy {
	public function display( $tpl = null ) {
		$user = JFactory::getUser();
		$app  = JFactory::getApplication();

		$authorised = $user->authorise('core.edit', 'com_sppagebuilder') || $user->authorise('core.edit.own', 'com_sppagebuilder');
		if ($authorised !== true) {
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);

			return false;
		}

		$input = JFactory::getApplication()->input;
    $layout = $input->get('layout', 'browse', 'STRING');
    $this->date = $input->post->get('date', NULL, 'STRING');
    $this->start = $input->post->get('start', 0, 'INT');
    $this->search = $input->post->get('search', NULL, 'STRING');
    $this->limit = 18;

  	$model = $this->getModel();
    $this->items = $model->getItems();
    $this->filters = $model->getDateFilters($this->date, $this->search);
    $this->total = $model->getTotalMedia($this->date, $this->search);
    $this->categories = $model->getMediaCategories();

		JToolBarHelper::title(JText::_('SP Page Builder - Media'));

		$this->addSubmenu('media');
		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	protected function addSubmenu($vName) {
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
			'<i class="fa fa-picture-o"></i> ' . JText::_('COM_SPPAGEBUILDER_MEDIA'). '<span><i class="fa fa-chevron-down pull-right"></i></span>',
			'index.php?option=com_sppagebuilder&view=media',
			$vName == 'media'
		);
	}
}
