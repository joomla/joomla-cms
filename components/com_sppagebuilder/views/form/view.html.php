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

// if (!class_exists('SppagebuilderModelPage')) {
// 	require_once JPATH_ROOT . '/components/com_sppagebuilder/models/page.php';
// }

class SppagebuilderViewForm extends JViewLegacy
{
	protected $form;
	protected $item;

	function display( $tpl = null )
	{

		$user = JFactory::getUser();
		$app  = JFactory::getApplication();

		$this->item = $this->get('Item');
		$this->form = $this->get('Form');

		if ( !$user->id ) {
				$uri = JFactory::getURI();
				$pageURL = $uri->toString();
				$return_url = base64_encode($pageURL);
				$joomlaLoginUrl = 'index.php?option=com_users&view=login&return=' . $return_url;

				$app->redirect(JRoute::_($joomlaLoginUrl, false), JText::_('JERROR_ALERTNOAUTHOR'), 'message');
				return false;
		}

		$input 		 = JFactory::getApplication()->input;
		$pageid 	 = $input->get('id', '', 'INT');
		$item_info  = SppagebuilderModelPage::getPageInfoById($pageid);
		
		$authorised = $user->authorise('core.edit', 'com_sppagebuilder') || $user->authorise('core.edit', 'com_sppagebuilder.page.' . $pageid) || ($user->authorise('core.edit.own', 'com_sppagebuilder') && ($user->id == $item_info->created_by));

		if ($authorised !== true)
		{
			$app->enqueueMessage(JText::_('COM_SPPAGEBUILDER_ERROR_EDIT_PERMISSION'), 'error');
			$app->setHeader('status', 403, true);
			return false;
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));

			return false;
		}

		$this->_prepareDocument($this->item->title);
		SppagebuilderHelperSite::loadLanguage();
		parent::display($tpl);
	}

	protected function _prepareDocument($title = '')
	{
		$config 	= JFactory::getConfig();
		$app 		= JFactory::getApplication();
		$doc 		= JFactory::getDocument();
		$menus   	= $app->getMenu();
		$menu 		= $menus->getActive();

		if(isset($menu)) {
			if($menu->params->get('page_title', '')) {
				$title = $menu->params->get('page_title');
			} else {
				$title = $menu->title;
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

	}
}
