<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

require_once JPATH_COMPONENT .'/builder/classes/ajax.php';
jimport( 'joomla.filesystem.file' );
jimport( 'joomla.filesystem.folder' );
$input = JFactory::getApplication()->input;
$action = $input->get('callback', '', 'STRING');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/ajax.php';
