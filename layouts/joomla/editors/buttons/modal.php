<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$button = $displayData;

if (!$button->get('modal')) {
    return;
}

$class    = ($button->get('class')) ? $button->get('class') : null;
$class   .= ($button->get('modal')) ? ' modal-button' : null;
$href     = '#' . $button->get('editor') . '_' . strtolower($button->get('name')) . '_modal';
$link     = ($button->get('link')) ? Uri::base() . $button->get('link') : null;
$onclick  = ($button->get('onclick')) ? ' onclick="' . $button->get('onclick') . '"' : '';
$title    = ($button->get('title')) ? $button->get('title') : $button->get('text');
$options  = is_array($button->get('options')) ? $button->get('options') : [];

$confirm = '';

if (is_array($button->get('options')) && isset($options['confirmText']) && isset($options['confirmCallback'])) {
    $confirm = '<button type="button" class="btn btn-success" data-bs-dismiss="modal" onclick="' . $options['confirmCallback'] . '">'
        . $options['confirmText'] . ' </button>';
}

if (null !== $button->get('id')) {
    $id = str_replace(' ', '', $button->get('id'));
} else {
    $id = $button->get('editor') . '_' . strtolower($button->get('name')) . '_modal';
}

// @todo: J4: Move Make buttons fullscreen on smaller devices per https://github.com/joomla/joomla-cms/pull/23091
// Create the modal
echo HTMLHelper::_(
    'bootstrap.renderModal',
    $id,
    [
        'url'    => $link,
        'title'  => $title,
        'height' => array_key_exists('height', $options) ? $options['height'] : '400px',
        'width'  => array_key_exists('width', $options) ? $options['width'] : '800px',
        'bodyHeight'  => array_key_exists('bodyHeight', $options) ? $options['bodyHeight'] : '70',
        'modalWidth'  => array_key_exists('modalWidth', $options) ? $options['modalWidth'] : '80',
        'footer' => $confirm . '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
            . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
    ]
);
