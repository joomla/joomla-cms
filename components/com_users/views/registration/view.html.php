<?php
/**
 * @version		$Id: view.html.php 21367 2011-05-18 12:29:19Z chdemko $
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Registration view class for Users.
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @since		1.6
 */
class UsersViewRegistration extends JView
{
	protected $data;
	protected $form;
	protected $params;
	protected $state;
	protected $article_id;

	/**
	 * Method to display the view.
	 *
	 * @param	string	The template file to include
	 * @since	1.6
	 */
	public function display($tpl = null)
	{
		// Get the view data.
		$this->data		= $this->get('Data');
		$this->form		= $this->get('Form');
		$this->state	= $this->get('State');
		$this->params	= $this->state->get('params');
		

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		// Check for layout override
		$active = JFactory::getApplication()->getMenu()->getActive();
		if (isset($active->query['layout'])) {
			$this->setLayout($active->query['layout']);
		}

		//Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

		$this->prepareDocument();
		
		
		/******** Customzation by joomlashowroom on 27-12-11 **************/
		// get profile plugin parameters
		jimport( 'joomla.html.parameter' );
		$user_plugins =	JPluginHelper::getPlugin('user');
		foreach($user_plugins as $key=>$value)
		{		
			if($user_plugins[$key]->name == "profile") {
				$pluginParams    = new JParameter( $user_plugins[$key]->params);
				$user_plugins[$key]->params = $pluginParams;
				//echo $user_plugins[$key]->params->get('profile-require_tos_page','');
				$link = 'index.php?option=com_content&view=article&layout=modal&tmpl=component&id=' . $user_plugins[$key]->params->get('profile-require_tos_page','');
				
				// Load the modal behavior script.
				JHtml::_('behavior.modal', 'a.modal_article');
				$html = '		<a class="modal_article" title="' . JText::_('JLIB_FORM_CHANGE_USER') . '"' . ' href="' . $link . '"'
				. ' rel="{handler: \'iframe\', size: {x: 800, y: 500}}"> Terms of service </a>';												
			}
		}
		$this->article_id = $html;
		//echo "<pre>"; print_r($user_plugins); exit;
		// end
		/******** END ******/
		

		parent::display($tpl);
	}

	/**
	 * Prepares the document.
	 *
	 * @since	1.6
	 */
	protected function prepareDocument()
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu();
		$title 		= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->def('page_heading', JText::_('COM_USERS_REGISTRATION'));
		}

		$title = $this->params->get('page_title', '');
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
