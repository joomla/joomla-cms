<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_postinstall
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if (!defined('FOF_INCLUDED'))
{
	require_once JPATH_LIBRARIES . '/fof/include.php';
}

$option		= JFactory::getApplication()->input->get('option', 'com_cpanel', 'CMD');

$eid	= ($option == 'com_cpanel') ? 700 : JComponentHelper::getComponent($option)->id;
$messages_model = FOFModel::getAnInstance('Messages', 'PostinstallModel', array('input' => array('eid' => $eid)));
$messages = $messages_model->getItemList();

if (!count($messages))
{
	return;
}

require JModuleHelper::getLayoutPath('mod_postinstall', $params->get('layout', 'default'));
