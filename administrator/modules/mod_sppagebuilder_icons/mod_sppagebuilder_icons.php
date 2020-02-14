<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted access');

$mod_name = 'mod_sppagebuilder_icons';

$document 	= JFactory::getDocument();
$input 		= JFactory::getApplication()->input;

$document->addStyleSheet(JURI::base(true).'/modules/'.$mod_name.'/tmpl/css/pagebuilder-style.css');

require JModuleHelper::getLayoutPath($mod_name,$params->get('layout','default'));