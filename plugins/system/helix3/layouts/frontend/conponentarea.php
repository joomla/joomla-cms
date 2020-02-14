<?php
/**
* @package Helix3 Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2017 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/
defined('_JEXEC') or die('Restricted Access');

//Helix3
helix3::addLess('frontend-edit', 'frontend-edit');
helix3::addJS('frontend-edit.js');

$data = $displayData;

$output ='';

$output .= '<div id="sp-component" class="' . $data->className . '">';

$output .= '<div class="sp-column ' . ($data->settings->custom_class) . '">';
$output .= '<jdoc:include type="message" />';
$output .= '<jdoc:include type="component" />';
$output .= '</div>';

$output .= '</div>';


echo $output;
