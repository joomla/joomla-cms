<?php
/**
 * @version		$Id: view.html.php 10206 2008-04-17 02:52:39Z instance $
 * @package		Joomla
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.view');

/**
 * @package		Joomla
 * @subpackage	Contacts
 */
class ContactdirectoryViewCategory extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $option;

		$user		= &JFactory::getUser();
		$uri		= &JFactory::getURI();
		$model		= &$this->getModel();
		$document	= &JFactory::getDocument();

		$pparams = &$mainframe->getParams('com_contactdirectory');
		$cparams =& JComponentHelper::getParams('com_media');

		$category = $model->getCategory();
		$contacts	= $model->getData();
		$fields = $model->getFields();
		$pagination = $model->getPagination();

		$alphabet	= $mainframe->getUserStateFromRequest( $option.'alphabet',		'alphabet',	'',	'string' );
		$search		= $mainframe->getUserStateFromRequest( $option.'search',		'search',	'',	'string' );
		$search		= JString::strtolower( $search );

		// search filter
		$lists['search']= $search;

		//add alternate feed link
		/*if($pparams->get('show_feed_link', 1) == 1)
		{
			$link	= '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
		}*/

		for($i=0; $i<count($contacts); $i++){
			$contacts[$i]->link = JRoute::_('index.php?option=com_contactdirectory&view=contact&catid='.$category->slug.'&id='.$contacts[$i]->slug);
			$contacts[$i]->fields = $fields[$i];
			$contacts[$i]->params = new JParameter($contacts[$i]->params);

			foreach($contacts[$i]->fields as $contacts[$i]->field){
				$contacts[$i]->field->params = new JParameter($contacts[$i]->field->params);

				if($contacts[$i]->field->type == 'image'){
					if($contacts[$i]->field->data){
						if($contacts[$i]->field->pos == 'right'){
							$contacts[$i]->field->data = JHTML::_('image', $cparams->get('image_path') . '/'.$contacts[$i]->field->data, JText::_( 'CONTACT' ), array('align' => 'right'));
						}else{
							$contacts[$i]->field->data = JHTML::_('image', $cparams->get('image_path') . '/'.$contacts[$i]->field->data, JText::_( 'CONTACT' ), array('align' => 'left'));
						}
					}
				}

				if($contacts[$i]->field->type == 'textarea'){
					$contacts[$i]->field->data = nl2br($contacts[$i]->field->data);
				}

				if($contacts[$i]->field->type == 'url'){
					if(!empty($contacts[$i]->field->data)){
						$contacts[$i]->field->data = '<a href="http://'.$contacts[$i]->field->data.'">'.$contacts[$i]->field->data.'</a>';
					}
				}

				// Handle email cloaking
				if($contacts[$i]->field->type == 'email' && $contacts[$i]->field->show_field) {
					jimport('joomla.mail.helper');
					$contacts[$i]->field->data = trim($contacts[$i]->field->data);
					if(!empty($contacts[$i]->field->data) && JMailHelper::isEmailAddress($contacts[$i]->field->data)) {
						$contacts[$i]->field->data = JHTML::_('email.cloak', $contacts[$i]->field->data);
					}else{
						$contacts[$i]->field->data = '';
					}
				}

				// Manage the display mode for the field title
				switch ($contacts[$i]->field->params->get('field_title'))
				{
					case 0 :
						// text
						$contacts[$i]->field->params->set('marker_title', 	JText::_($contacts[$i]->field->title).": ");
						break;
					case 1:
						//icon and text
						$image = JHTML::_('image.site', 'arrow.png', 	'/images/M_images/', $contacts[$i]->field->params->get('choose_icon'), 	'/images/M_images/', JText::_($contacts[$i]->field->title).": ");
						$contacts[$i]->field->params->set('marker_title', 	$image);
						break;
					case 2 :
						// icons
						$image = JHTML::_('image.site', 'arrow.png', 	'/images/M_images/', $contacts[$i]->field->params->get('choose_icon'), 	'/images/M_images/', JText::_($contacts[$i]->field->title).": ");
						$contacts[$i]->field->params->set('marker_title', 	$image." ".JText::_($contacts[$i]->field->title).": ");
						break;
					case 3 :
						// none
						$contacts[$i]->field->params->set('marker_title', 	'');
						break;
				}

				switch ($contacts[$i]->field->pos){
					case 'title':
						$contacts[$i]->pos_title[] = $contacts[$i]->field;
						break;
					case 'top':
						$contacts[$i]->pos_top[] = $contacts[$i]->field;
						break;
					case 'left':
						$contacts[$i]->pos_left[] = $contacts[$i]->field;
						break;
					case 'main':
						$contacts[$i]->pos_main[] = $contacts[$i]->field;
						break;
					case 'right':
						$contacts[$i]->pos_right[] = $contacts[$i]->field;
						break;
					case 'bottom':
						$contacts[$i]->pos_bottom[] = $contacts[$i]->field;
						break;
				}
			}
		}

		// Set the page title and pathway
		if ($category->title)
		{

		// Add the category breadcrumbs item
			$document->setTitle(JText::_('CONTACT').' - '.$category->title);
		} else {
			$document->setTitle(JText::_('CONTACT'));
		}

		// Prepare category description
		$category->description = JHTML::_('content.prepare', $category->description);

		JHTML::stylesheet('contactdirectory.css', 'components/com_contactdirectory/css/');

		$this->assignRef('contacts', $contacts);
		$this->assignRef('lists', $lists);
		$this->assignRef('pagination', $pagination);
		$this->assignRef('category', $category);
		$this->assignRef('params', $pparams);
		$this->assignRef('user', $user);
		$this->assignRef('cparams', $cparams);

		$this->assign('action', $uri->toString());

		parent::display($tpl);
	}
}
