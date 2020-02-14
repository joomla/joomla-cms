<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined('_JEXEC') or die();

$data = $displayData;
$options = $data->settings;

$output ='';
$output .= '<'.$data->sematic.' id="sp-' . JFilterOutput::stringURLSafe($options->name) . '" class="'. $options->className .'">';
$output .= '<div class="sp-column ' . ($options->custom_class) . '">';
$features = (isset($data->hasFeature[$options->name]) && $data->hasFeature[$options->name])? $data->hasFeature[$options->name] : array();

foreach ($features as $key => $feature)
{
    if (isset($feature['feature']) && $feature['load_pos'] == 'before' )
    {
        $output .= $feature['feature'];
    }
}

$output .= '<jdoc:include type="modules" name="' . $options->name . '" style="sp_xhtml" />';

foreach ($features as $key => $feature)
{
    if (isset($feature['feature']) && $feature['load_pos'] != 'before' )
    {
        $output .= $feature['feature'];
    }
}

$output .= '</div>';
$output .= '</'.$data->sematic.'>';

echo $output;
