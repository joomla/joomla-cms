<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$button = $displayData;

if (!$button->get('modal'))
{
	return;
}

$class    = ($button->get('class')) ? $button->get('class') : null;
$class   .= ($button->get('modal')) ? ' modal-button' : null;
$href     = '#' . str_replace(' ', '', $button->get('text')) . 'Modal';
$link     = ($button->get('link')) ? Uri::base() . $button->get('link') : null;
$onclick  = ($button->get('onclick')) ? ' onclick="' . $button->get('onclick') . '"' : '';
$title    = ($button->get('title')) ? $button->get('title') : $button->get('text');
$options  = is_array($button->get('options')) ? $button->get('options') : array();

$confirm = '';

if (is_array($button->get('options')) && isset($options['confirmText']) && isset($options['confirmCallback']))
{
	$confirm = '<a class="btn btn-success" data-dismiss="modal" aria-hidden="true" onclick="' . $options['confirmCallback'] . '">'
		. $options['confirmText'] . '</a>';
}
// Create the modal
echo HTMLHelper::_(
	'bootstrap.renderModal',
	str_replace(' ', '', $button->get('text')) . 'Modal',
	array(
		'url'    => $link,
		'title'  => $title,
		'height' => array_key_exists('height', $options) ? $options['height'] : '400px',
		'width'  => array_key_exists('width', $options) ? $options['width'] : '800px',
		'bodyHeight'  => array_key_exists('wibodyHeightdth', $options) ? $options['bodyHeight'] : '70',
		'modalWidth'  => array_key_exists('modalWidth', $options) ? $options['modalWidth'] : '80',
		'footer' => $confirm . '<button class="btn btn-secondary" data-dismiss="modal" aria-hidden="true">'
			. Text::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</button>'
	)
);
