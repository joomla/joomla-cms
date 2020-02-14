<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2015 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

$required_min_php_version = '5.4.0';

if (version_compare(PHP_VERSION,$required_min_php_version, '<')) {
  (include_once JPATH_SITE . '/administrator/components/com_sppagebuilder/views/phpversion.tmpl.php') or die('Your PHP version is too old for this component.');
  return;
}

require_once JPATH_COMPONENT.'/helpers/route.php';

jimport('joomla.application.component.controller');

$controller = JControllerLegacy::getInstance('Sppagebuilder');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
