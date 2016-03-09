<?php
/**
 * @package    Fields
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2015 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

$controller = JControllerLegacy::getInstance('Fields');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
