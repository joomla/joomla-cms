<?php
/**
 * @version		$Id: crosswords.php 01 2011-08-13 11:37:09Z maverick $
 * @package		CoreJoomla.Crosswords
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2011 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

defined('DS') or define('DS', DIRECTORY_SEPARATOR);

require_once JPATH_COMPONENT.DS.'controller.php';
require_once JPATH_ROOT.DS.'components'.DS.'com_cjlib'.DS.'framework.php';

CJLib::import('corejoomla.framework.core');
CJLib::import('corejoomla.ui.bootstrap', true);

$app = JFactory::getApplication();
$task = $app->input->getCmd('task', null);

JSubMenuHelper::addEntry(JText::_('COM_CJLIB_DASHBOARD'), 'index.php?option=com_cjlib', $task == '');
JSubMenuHelper::addEntry(JText::_('COM_CJLIB_EMAIL_QUEUE'), 'index.php?option=com_cjlib&amp;task=queue', $task == 'queue');
JSubMenuHelper::addEntry(JText::_('COM_CJLIB_COUNTRIES'), 'index.php?option=com_cjlib&amp;task=countries', $task == 'countries');

$document = JFactory::getDocument();
$document->addStyleSheet(CJLIB_URI.'/framework/assets/cj.framework.css');
$document->addStyleSheet(JURI::base(true).'/components/com_cjlib/assets/css/styles.css');
$document->addScript(JURI::base(true).'/components/com_cjlib/assets/js/cj.lib.min.js');

JToolBarHelper::preferences('com_cjlib');

$controller = new CjLibController();
$controller->execute( $task );

$controller->redirect();
?>