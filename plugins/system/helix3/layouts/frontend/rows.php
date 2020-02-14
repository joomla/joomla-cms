<?php
/**
* @package Helix3 Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2017 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/
defined('_JEXEC') or die('Restricted Access');

//helper & model
$helix3_class   = JPATH_ROOT . '/plugins/system/helix3/core/classes/helix3.php';

if (file_exists($helix3_class)) {
    require_once($helix3_class);
}

$template       = JFactory::getApplication()->getTemplate();
$themepath      = JPATH_THEMES . '/' . $template;
$carea_file     = $themepath . '/html/layouts/helix3/frontend/conponentarea.php';
$module_file    = $themepath . '/html/layouts/helix3/frontend/modules.php';
$lyt_thm_path   = $themepath . '/html/layouts/helix3/';

$layout_path_carea  = (file_exists($carea_file)) ? $lyt_thm_path : JPATH_ROOT .'/plugins/system/helix3/layouts';
$layout_path_module = (file_exists($module_file)) ? $lyt_thm_path : JPATH_ROOT .'/plugins/system/helix3/layouts';

$data = $displayData;

$output ='';

$output .= '<div class="row">';

foreach ($data['rowColumns'] as $key => $column){

    //Responsive Utilities
    if (isset($column->settings->xs_col) && $column->settings->xs_col) {
        $column->className = $column->settings->xs_col . ' ' . $column->className;
    }

    if (isset($column->settings->sm_col) && $column->settings->sm_col) {
        $column->className = preg_replace('/col-sm-\d*/', $column->settings->sm_col, $column->className);
    }

    if (isset($column->settings->hidden_md) && $column->settings->hidden_md) {
        $column->className = $column->className . ' hidden-md hidden-lg';
    }

    if (isset($column->settings->hidden_sm) && $column->settings->hidden_sm) {
        $column->className = $column->className . ' hidden-sm';
    }

    if (isset($column->settings->hidden_xs) && $column->settings->hidden_xs) {
        $column->className = $column->className . ' hidden-xs';
    }
    //End Responsive Utilities

    if ($column->settings->column_type){ //Component
        $getLayout = new JLayoutFile('frontend.conponentarea', $layout_path_carea );
        $output .= $getLayout->render($column);
    }
    else { // Module

        $getLayout = new JLayoutFile('frontend.modules', $layout_path_module );
        $output .= $getLayout->render($column);
    }
}

$output .= '</div>'; //.row

echo $output;
