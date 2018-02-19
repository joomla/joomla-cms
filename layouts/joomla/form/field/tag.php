<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Helper\UserGroupsHelper;


extract($displayData);

// Get some system objects.
$document = Factory::getDocument();

/**
 * Layout variables
 * -----------------
 * @var   string   $autocomplete    Autocomplete attribute for the field.
 * @var   boolean  $autofocus       Is autofocus enabled?
 * @var   string   $class           Classes for the input.
 * @var   string   $description     Description of the field.
 * @var   boolean  $disabled        Is this field disabled?
 * @var   string   $group           Group the field belongs to. <fields> section in form XML.
 * @var   boolean  $hidden          Is this field hidden in the form?
 * @var   string   $hint            Placeholder for the field.
 * @var   string   $id              DOM id of the field.
 * @var   string   $label           Label of the field.
 * @var   string   $labelclass      Classes to apply to the label.
 * @var   boolean  $multiple        Does this field support multiple values?
 * @var   string   $name            Name of the input field.
 * @var   array    $options         Options available for this field.
 */

// Add Javascript for permission change
HTMLHelper::_('webcomponent', ['joomla-field-tag' => 'system/webcomponents/joomla-field-tag.min.js'], array('version' => 'auto', 'relative' => true));
// pattern="^([a-zA-Z0-9\.\-+]{2,20},?)+$"
?>
<joomla-field-tags>
	<input id="<?php echo $id; ?>" type="hidden"
			value="<?php echo htmlspecialchars(json_encode($displayData['options'])); ?>">
</joomla-field-tags>
