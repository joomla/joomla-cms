<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$button = $displayData;

$class    = ($button->get('class')) ? $button->get('class') : null;
$class   .= ($button->get('modal')) ? ' modal-button' : null;
$href     = '#' . str_replace(' ', '', $button->get('text')) . 'Modal';
$link     = ($button->get('link')) ? JUri::base() . $button->get('link') : null;
$onclick  = ($button->get('onclick')) ? ' onclick="' . $button->get('onclick') . '"' : '';
$title    = ($button->get('title')) ? $button->get('title') : $button->get('text');

// Load modal popup behavior
if ($button->get('modal'))
{
	echo JHtml::_(
		'bootstrap.renderModal',
		str_replace(' ', '', $button->get('text')) . 'Modal',
		array(
			'url'    => $link,
			'title'  => $title,
			'height' => '300px',
			'width'  => '800px',
			'footer' => '<button class="btn" data-dismiss="modal" aria-hidden="true">'
				. JText::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</button>'
		)
	);
}
