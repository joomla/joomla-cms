<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * @package		Joomla.Site
 * @subpackage	Contacts
 */
class ContactViewCategory extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $option;

		$user	  = &JFactory::getUser();
		$uri 	  = &JFactory::getURI();
		$model	  = &$this->getModel();
		$document = &JFactory::getDocument();

		$pparams = &$mainframe->getParams('com_contact');

		// Selected Request vars
		$categoryId			= JRequest::getVar('catid',				0,				'', 'int');
		$limitstart			= JRequest::getVar('limitstart',		0,				'', 'int');
		$filter_order		= JRequest::getVar('filter_order',		'cd.ordering',	'', 'cmd');
		$filter_order_Dir	= JRequest::getVar('filter_order_Dir',	'ASC',			'', 'word');

		$pparams->def('display_num', $mainframe->getCfg('list_limit'));
		$default_limit = $pparams->def('display_num', 20);

		$limit = $mainframe->getUserStateFromRequest('com_contact.'.$this->getLayout().'.limit', 'limit', $default_limit, 'int');

		// query options
		$options['category_id']	= $categoryId;
		$options['limit']		= $limit;
		$options['limitstart']	= $limitstart;
		$options['order by']	= "$filter_order $filter_order_Dir, cd.ordering";

		$categories	= $model->getCategories($options);
		$contacts	= $model->getContacts($options);
		$total 		= $model->getContactCount($options);

		//add alternate feed link
		if ($pparams->get('show_feed_link', 1) == 1)
		{
			$link	= '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
		}

		//prepare contacts
		if ($pparams->get('show_email', 0) == 1) {
			jimport('joomla.mail.helper');
		}

		$k = 0;
		for($i = 0; $i <  count($contacts); $i++)
		{
			$contact = &$contacts[$i];

			$contact->link = JRoute::_('index.php?option=com_contact&view=contact&id='.$contact->slug.'&catid='.$contact->catslug);
			if ($pparams->get('show_email', 0) == 1) {
				$contact->email_to = trim($contact->email_to);
				if (!empty($contact->email_to) && JMailHelper::isEmailAddress($contact->email_to)) {
					$contact->email_to = JHtml::_('email.cloak', $contact->email_to);
				} else {
					$contact->email_to = '';
				}
			}

			$contact->odd	= $k;
			$contact->count = $i;
			$k = 1 - $k;
		}

		// find current category
		// TODO: Move to model
		$category = null;
		foreach ($categories as $i => $_cat)
		{
			if ($_cat->id == $categoryId) {
				$category = &$categories[$i];
				break;
			}
		}
		if ($category == null) {
			$db = &JFactory::getDbo();
			$category = &JTable::getInstance('category');
		}

		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();

		// because the application sets a default page title, we need to get it
		// right from the menu item itself
		if (is_object($menu)) {
			$menu_params = new JParameter($menu->params);
			if (!$menu_params->get('page_title')) {
				$pparams->set('page_title',	$category->title);
			}
		} else {
			$pparams->set('page_title',	$category->title);
		}
		$document->setTitle($pparams->get('page_title'));

		// Prepare category description
		$category->description = JHtml::_('content.prepare', $category->description);

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;
		$selected = '';

		jimport('joomla.html.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);

		$this->assignRef('items',		$contacts);
		$this->assignRef('lists',		$lists);
		$this->assignRef('pagination',	$pagination);
		//$this->assignRef('data',		$data);
		$this->assignRef('category',	$category);
		$this->assignRef('params',		$pparams);

		$this->assign('action',		str_replace('&', '&amp;', $uri->toString()));

		parent::display($tpl);
	}

	function getItems()
	{

	}
}
