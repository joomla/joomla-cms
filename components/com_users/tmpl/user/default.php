<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Load Custom Fields for this User
JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');
$fields = FieldsHelper::getFields('com_users.user', $this->item, true);

if ($fields)
{
	foreach ($fields as $field)
	{
		$this->item->{$field->label} = $field->value;
	}
}
?>
    <h1>
		<?php echo $this->escape($this->item->name); ?>
    </h1>
