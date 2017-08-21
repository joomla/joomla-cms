<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$item = isset($displayData['item']) ? $displayData['item'] : null;
$context = isset($displayData['context']) ? $displayData['context'] : null;

if (empty($item) || !($item instanceof stdClass) || empty($context))
{
	return;
}

JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

$fields = !empty($displayData['fields']) ? $displayData['fields'] : array();

if (empty($fields))
{
	$fields = $item->jcfields ?: FieldsHelper::getFields($context, $item, true);
}

if (empty($fields))
{
	return;
}

// Check if we have mail context in first element
$isMail = (reset($fields)->context === 'com_contact.mail');

?>
<?php if (!$isMail) : ?>
<dl class="fields-container contact-fields dl-horizontal">
<?php endif; ?>
<?php foreach ($fields as $field) : ?>
	<?php if (empty($field->value) && !$isMail) :
		continue;
	endif; ?>
	<?php echo FieldsHelper::render($context, 'field.render', array('field' => $field)); ?>
<?php endforeach; ?>
<?php if (!$isMail) : ?>
</dl>
<?php endif; ?>
