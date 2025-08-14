<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Note
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

$displayFrontend = $field->params->get('display_frontend', 0);
if (!$displayFrontend) {
    return;
}

$html  = [];
$class = [];

if (!empty($field->fieldparams->get('class', ''))) {
    $class[] = $field->fieldparams->get('class', '');
}

if ($close = (string) $field->fieldparams->get('close', '')) {
    HTMLHelper::_('bootstrap.alert');
    $close   = $close === 'true' ? 'alert' : $close;
    $html[]  = '<button type="button" class="btn-close" data-bs-dismiss="' . $close . '"></button>';
    $class[] = 'alert-dismissible show';
}

$class       = $class ? ' class="' . implode(' ', $class) . '"' : '';
$title       = $field->fieldparams->get('label', '');
$heading     = $field->fieldparams->get('heading', 'h4');
$description = (string) $field->fieldparams->get('description', '');
$html[]      = !empty($title) ? '<' . $heading . '>' . Text::_($title) . '</' . $heading . '>' : '';
$html[]      = !empty($description) ? Text::_($description) : '';

echo '<div ' . $class . '>' . implode('', $html) . '</div>';
