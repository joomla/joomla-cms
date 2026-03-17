<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if (!array_key_exists('field', $displayData)) {
    return;
}

$field = $displayData['field'];

// Do nothing when not in mail context, like that the default rendering is used
if ($field->context !== 'com_contact.mail') {
    return;
}

// Prepare the value for the contact form mail
$value = html_entity_decode($field->value);

echo ($field->params->get('showlabel') ? Text::_($field->label) . ': ' : '') . $value . "\r\n";
