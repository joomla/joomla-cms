<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2015 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

// import Joomla view library
jimport('joomla.application.component.view');

if(!class_exists('SppagebuilderHelperSite')) {
	require_once JPATH_ROOT . '/components/com_sppagebuilder/helpers/helper.php';
}

class SppagebuilderViewPage extends JViewLegacy {

	protected $item;

	function display( $tpl = null ) {

		$this->item = $this->get('Item');

		if (count($errors = $this->get('Errors'))) {
			JLog::add(implode('<br />',$errors),JLog::WARNING,'jerror');
			return false;
		}

		if ($this->item->access_view == false) {
			JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		$model = $this->getModel();
		$model->hit();

		$this->_prepareDocument($this->item->title);
		SppagebuilderHelperSite::loadLanguage();
		parent::display($tpl);
	}

	protected function _prepareDocument($title = '') {
		$config = JFactory::getConfig();
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$menus = $app->getMenu();
		$menu = $menus->getActive();

		//Title
		if (isset($meta['title']) && $meta['title']) {
			$title = $meta['title'];
		} else {
			if ($menu) {
				if($menu->params->get('page_title', '')) {
					$title = $menu->params->get('page_title');
				} else {
					$title = $menu->title;
				}
			}
		}

		//Include Site title
		$sitetitle = $title;
		if($config->get('sitename_pagetitles')==2) {
			$sitetitle = JText::sprintf('JPAGETITLE', $sitetitle, $app->get('sitename'));
		} elseif ($config->get('sitename_pagetitles')==1) {
			$sitetitle = JText::sprintf('JPAGETITLE', $app->get('sitename'), $sitetitle);
		}
		$doc->setTitle($sitetitle);

		$og_title = $this->item->og_title;
		if ($og_title) {
			$this->document->addCustomTag('<meta content="'.$og_title.'" property="og:title" />');
		} else {
			$doc->addCustomTag('<meta content="' . $title . '" property="og:title" />');
		}

		$this->document->addCustomTag('<meta content="website" property="og:type"/>');
		$this->document->addCustomTag('<meta content="'.JURI::current().'" property="og:url" />');

		$og_image = $this->item->og_image;
		if ($og_image) {
			$this->document->addCustomTag('<meta content="'.JURI::root().$og_image.'" property="og:image" />');
			$this->document->addCustomTag('<meta content="1200" property="og:image:width" />');
			$this->document->addCustomTag('<meta content="630" property="og:image:height" />');
		}

		$og_description = $this->item->og_description;
		if ($og_description) {
			$this->document->addCustomTag('<meta content="'.$og_description.'" property="og:description" />');
		}

		if ($menu) {

			if ($menu->params->get('menu-meta_description')) {
				$this->document->setDescription($menu->params->get('menu-meta_description'));
			}

			if ($menu->params->get('menu-meta_keywords')) {
				$this->document->setMetadata('keywords', $menu->params->get('menu-meta_keywords'));
			}

			if ($menu->params->get('robots'))
			{
				$this->document->setMetadata('robots', $menu->params->get('robots'));
			}

		}
	}
}
