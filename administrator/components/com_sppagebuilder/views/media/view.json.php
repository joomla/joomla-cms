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

require_once JPATH_ROOT . '/administrator/components/com_sppagebuilder/helpers/language.php';

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

		$input 			= JFactory::getApplication()->input;
		$layout         = $input->get('layout', 'browse', 'STRING');
		$this->date 	= $input->post->get('date', NULL, 'STRING');
		$this->start 	= $input->post->get('start', 0, 'INT');
		$this->search   = $input->post->get('search', NULL, 'STRING');
		$this->limit 	= 18;

		$model 			= $this->getModel();

		if(($layout == 'browse') || ($layout == 'modal')) {
			$this->items = $model->getItems();
			$this->filters = $model->getDateFilters($this->date, $this->search);
			$this->total = $model->getTotalMedia($this->date, $this->search);
			$this->categories = $model->getMediaCategories();
		} else {
			$this->media = $model->getFolders();
		}

		parent::display($tpl);
	}
}
