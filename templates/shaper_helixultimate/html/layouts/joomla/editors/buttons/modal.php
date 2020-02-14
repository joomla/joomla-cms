<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

$button = $displayData;

if (!$button->get('modal'))
{
	return;
}

$class    = ($button->get('class')) ? $button->get('class') : null;
$class   .= ($button->get('modal')) ? ' modal-button' : null;
$href     = '#' . str_replace(' ', '', $button->get('text')) . 'Modal';
$link     = ($button->get('link')) ? JUri::base() . $button->get('link') : null;
$onclick  = ($button->get('onclick')) ? ' onclick="' . $button->get('onclick') . '"' : '';
$title    = ($button->get('title')) ? $button->get('title') : $button->get('text');
$options  = is_array($button->get('options')) ? $button->get('options') : array();

// Create the modal
echo JHtml::_(
	'bootstrap.renderModal',
	str_replace(' ', '', $button->get('text')) . 'Modal',
	array(
		'url'    => $link,
		'title'  => $title,
		'height' => array_key_exists('height', $options) ? $options['height'] : '400px',
		'width'  => array_key_exists('width', $options) ? $options['width'] : '800px',
		'bodyHeight'  => array_key_exists('wibodyHeightdth', $options) ? $options['bodyHeight'] : '70',
		'modalWidth'  => array_key_exists('modalWidth', $options) ? $options['modalWidth'] : '80',
		'footer' => '<button class="btn btn-secondary" data-dismiss="modal" aria-hidden="true">'
			. JText::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</button>'
	)
);
