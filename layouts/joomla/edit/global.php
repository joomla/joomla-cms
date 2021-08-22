<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app       = JFactory::getApplication();
$form      = $displayData->getForm();
$input     = $app->input;
$component = $input->getCmd('option', 'com_content');

if ($component === 'com_categories')
{
	$extension = $input->getCmd('extension', 'com_content');
	$parts     = explode('.', $extension);
	$component = $parts[0];
}

$saveHistory = JComponentHelper::getParams($component)->get('save_history', 0);

$fields = $displayData->get('fields') ?: array(
	array('parent', 'parent_id'),
	array('published', 'state', 'enabled'),
	array('category', 'catid'),
	'featured',
	'sticky',
	'access',
	'id',
	'language',
	'tags',
	'note',
	'version_note',
);

$hiddenFields   = $displayData->get('hidden_fields') ?: array();
$hiddenFields[] = 'id';

if (!$saveHistory)
{
	$hiddenFields[] = 'version_note';
}

$html   = array();
$html[] = '<fieldset class="form-vertical">';

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

			$html[] = $form->renderField($f);
			break;
		}
	}
}

$html[] = '</fieldset>';

echo implode('', $html);
