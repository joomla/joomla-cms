<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined('_JEXEC') or die();

$settings = $displayData;
$colSettings = 'data-grid_size="12" data-column_type="0" data-name="none"';
if(isset($settings->grid_size) && $settings->grid_size){
    $colSettings = RowColumnSettings::getSettings($settings);
}

$output = '<div class="helix-ultimate-layout-column col-md-' . ((isset($settings->grid_size) && $settings->grid_size)? $settings->grid_size :12) .'" ' . $colSettings .'>';
$output .= '<div class="helix-ultimate-column' . ((isset($settings->column_type) && $settings->column_type) ? ' helix-ultimate-column-component' : '') . ' clearfix">';

if (isset($settings->column_type) && $settings->column_type)
{
    $output .= '<span class="helix-ultimate-column-title">Component</span>';
}
else
{
    if (isset($settings->name))
    {
        $output .= '<span class="helix-ultimate-column-title">'. $settings->name .'</span>';
    }
    else
    {
        $output .= '<span class="helix-ultimate-column-title">None</span>';
    }
}

$output .= '<a class="helix-ultimate-column-options" href="#" ><i class="fa fa-gear fa-fw"></i></a>';
$output .= '</div>';
$output .= '</div>';

echo $output;