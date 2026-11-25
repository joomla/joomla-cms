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

if (empty($field->params->get('display_frontend', 0))) {
    return;
}

$html  = [];
$class = [];

if (!empty($field->fieldparams->get('class', ''))) {
    $class[] = htmlspecialchars($field->fieldparams->get('class', ''), ENT_QUOTES, 'UTF-8');
}

if ($close = (string) $field->fieldparams->get('close', '')) {
    HTMLHelper::_('bootstrap.alert');
    $close   = $close === 'true' ? 'alert' : $close;
    $html[]  = '<button type="button" class="btn-close" data-bs-dismiss="' . htmlspecialchars($close, ENT_QUOTES, 'UTF-8') . '"></button>';
    $class[] = 'alert-dismissible show';
}

$class       = $class ? ' class="' . implode(' ', $class) . '"' : '';
$title       = (string) $field->fieldparams->get('label', ($field->label ? : ''));
$heading     = (string) $field->fieldparams->get('heading', 'h4');
$heading     = \in_array($heading, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true) ? $heading : 'h4';
$description = (string) $field->fieldparams->get('description', '');
$html[]      = !empty($title) ? '<' . $heading . '>' . Text::_($title) . '</' . $heading . '>' : '';
$html[]      = !empty($description) ? Text::_($description) : '';

echo '<div ' . $class . '>' . implode('', $html) . '</div>';
