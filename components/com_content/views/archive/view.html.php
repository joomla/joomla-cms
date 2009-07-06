<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Content component
 *
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since 1.5
 */
class ContentViewArchive extends JView
{
	function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$option = JRequest::getCmd('option');

		if (empty($layout))
		{
			// degrade to default
			$layout = 'list';
		}

		// Initialize some variables
		$user		= &JFactory::getUser();
		$pathway	= &$app->getPathway();
		$document	= &JFactory::getDocument();

		// Get the page/component configuration
		$params = &$app->getParams('com_content');

		// Request variables
		$task 		= JRequest::getCmd('task');
		$limit		= JRequest::getVar('limit', $params->get('display_num', 20), '', 'int');
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
		$month		= JRequest::getInt('month');
		$year		= JRequest::getInt('year');
		$filter		= JRequest::getString('filter');

		// Get some data from the model
		$state = & $this->get('state');
		$items = & $this->get('data' );
		$total = & $this->get('total');

		// Add item to pathway
		$pathway->addItem(JText::_('Archive'), '');

		$params->def('filter',			1);
		$params->def('filter_type',		'title');

		jimport('joomla.html.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);

		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();

		// because the application sets a default page title, we need to get it
		// right from the menu item itself
		if (is_object($menu)) {
			$menu_params = new JParameter($menu->params);
			if (!$menu_params->get('page_title')) {
				$params->set('page_title',	JText::_('Archives'));
			}
		} else {
			$params->set('page_title',	JText::_('Archives'));
		}
		$document->setTitle($params->get('page_title'));

		$form = new stdClass();
		// Month Field
		$months = array(
			'' => JText::_('Month'),
			'01' => JText::_('JANUARY_SHORT'),
			'02' => JText::_('FEBRUARY_SHORT'),
			'03' => JText::_('MARCH_SHORT'),
			'04' => JText::_('APRIL_SHORT'),
			'05' => JText::_('MAY_SHORT'),
			'06' => JText::_('JUNE_SHORT'),
			'07' => JText::_('JULY_SHORT'),
			'08' => JText::_('AUGUST_SHORT'),
			'09' => JText::_('SEPTEMBER_SHORT'),
			'10' => JText::_('OCTOBER_SHORT'),
			'11' => JText::_('NOVEMBER_SHORT'),
			'12' => JText::_('DECEMBER_SHORT')
		);
		$form->monthField = JHtml::_(
			'select.genericlist',
			$months,
			'month',
			array(
				'list.attr' => 'size="1" class="inputbox"',
				'list.select' => $month,
				'option.key' => null
			)
		);
		// Year Field
		$years = array();
		$years[] = JHtml::_('select.option', null, JText::_('Year'));
		for ($i = 2000; $i <= 2010; $i++) {
			$years[] = JHtml::_('select.option', $i, $i);
		}
		$form->yearField = JHtml::_(
			'select.genericlist',
			$years,
			'year',
			array('list.attr' => 'size="1" class="inputbox"', 'list.select' => $year)
		);
		$form->limitField = $pagination->getLimitBox();

		$this->assign('filter', $filter);
		$this->assign('year', $year);
		$this->assign('month', $month);

		$this->assignRef('form', $form);
		$this->assignRef('items', $items);
		$this->assignRef('params', $params);
		$this->assignRef('user', $user);
		$this->assignRef('pagination', $pagination);

		parent::display($tpl);
	}
}
?>
