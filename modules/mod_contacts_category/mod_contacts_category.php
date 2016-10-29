<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_contacts_category
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the helper functions only once
JLoader::register('ModContactsCategoryHelper', __DIR__ . '/helper.php');

$input = JFactory::getApplication()->input;

// Prep for Normal or Dynamic Modes
$mode   = $params->get('mode', 'normal');
$idbase = null;

switch ($mode)
{
	case 'dynamic' :
		$option = $input->get('option');
		$view   = $input->get('view');

		if ($option === 'com_contact')
		{
			switch ($view)
			{
				case 'category' :
					$idbase = $input->getInt('id');
					break;
				case 'categories' :
					$idbase = $input->getInt('id');
					break;
				case 'contact' :
					if ($params->get('show_on_contact_page', 1))
					{
						$idbase = $input->getInt('catid');
					}
					break;
			}
		}
		break;
	case 'normal' :
	default:
		$idbase = $params->get('catid');
		break;
}

$cacheid = md5(serialize(array ($idbase, $module->module, $module->id)));

$cacheparams               = new stdClass;
$cacheparams->cachemode    = 'id';
$cacheparams->class        = 'ModContactsCategoryHelper';
$cacheparams->method       = 'getList';
$cacheparams->methodparams = $params;
$cacheparams->modeparams   = $cacheid;

$list = JModuleHelper::moduleCache($module, $params, $cacheparams);

if (!empty($list))
{
	$grouped                    = false;
	$contact_grouping           = $params->get('contact_grouping', 'none');
	$contact_grouping_direction = $params->get('contact_grouping_direction', 'ksort');
	$moduleclass_sfx            = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');
	$item_heading               = $params->get('item_heading');

	if ($contact_grouping !== 'none')
	{
		$grouped = true;

		switch ($contact_grouping)
		{
			case 'category_title' :
				$list = ModArticlesCategoryHelper::groupBy($list, $contact_grouping, $contact_grouping_direction);
				break;
			default:
				break;
		}
	}

	require JModuleHelper::getLayoutPath('mod_contacts_category', $params->get('layout', 'default'));
}
