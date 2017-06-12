<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$app = JFactory::getApplication();
$form = $displayData->getForm();

$fields = $displayData->get('fields') ?: [
	'publish_up',
	'publish_down',
	['created', 'created_time'],
	['created_by', 'created_user_id'],
	'created_by_alias',
	['modified', 'modified_time'],
	['modified_by', 'modified_user_id'],
	'version',
	'hits',
	'id'
];

$hiddenFields = $displayData->get('hidden_fields') ?: [];

foreach ($fields as $field)
{
	foreach ((array) $field as $f)
	{
		if ($form->getField($f))
		{
			if (in_array($f, $hiddenFields))
			{
				$form->setFieldAttribute($f, 'type', 'hidden');
			}

			echo $form->renderField($f);
			break;
		}
	}
}
