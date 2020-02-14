<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined('_JEXEC') or die();

$doc = \JFactory::getDocument();

$data = $displayData;

$output ='';
$output .= '<main id="sp-component" class="' . $data->settings->className . '" role="main">';
$output .= '<div class="sp-column ' . ($data->settings->custom_class) . '">';
$output .= '<jdoc:include type="message" />';

if($doc->countModules('content-top'))
{
    $output .= '<div class="sp-module-content-top clearfix">';
    $output .= '<jdoc:include type="modules" name="content-top" style="sp_xhtml" />';
    $output .= '</div>';
}

$output .= '<jdoc:include type="component" />';

if($doc->countModules('content-bottom'))
{
    $output .= '<div class="sp-module-content-bottom clearfix">';
    $output .= '<jdoc:include type="modules" name="content-bottom" style="sp_xhtml" />';
    $output .= '</div>';
}

$output .= '</div>';
$output .= '</main>';

echo $output;
