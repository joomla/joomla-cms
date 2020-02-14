<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined('JPATH_BASE') or die;

$selector = empty($displayData['selector']) ? '' : $displayData['selector'];
$id = empty($displayData['id']) ? '' : $displayData['id'];
$active = empty($displayData['active']) ? '' : $displayData['active'];
$title = empty($displayData['title']) ? '' : $displayData['title'];

$li = '<li class="nav-item"><a class="nav-link' . $active . '" href="#' . $id . '" data-toggle="tab">' . $title . '</a></li>';

echo 'jQuery(function($){ $(', json_encode('#' . $selector . 'Tabs'), ').append($(', json_encode($li), ')); });';
