<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined('_JEXEC') or die();

$layout_path_carea  = JPATH_ROOT .'/plugins/system/helixultimate/layouts';
$layout_path_module = JPATH_ROOT .'/plugins/system/helixultimate/layouts';

$data = $displayData;

$output ='';
$output .= '<div class="row">';

foreach ($data['rowColumns'] as $key => $column)
{
    if(isset($data['componentArea']) && $data['componentArea'])
    {
        $column->sematic = 'aside';
    }
    else
    {
        $column->sematic = 'div';
    }

    $column->hasFeature = $data['loadFeature'];
    if ($column->settings->column_type)
    {
        $getLayout = new JLayoutFile('frontend.conponentarea', $layout_path_carea );
        $output .= $getLayout->render($column);
    }
    else
    {
        $getLayout = new JLayoutFile('frontend.modules', $layout_path_module );
        $output .= $getLayout->render($column);
    }
}

$output .= '</div>';

echo $output;
