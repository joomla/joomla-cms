<?php
/**
* @package Helix3 Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2017 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/
defined('_JEXEC') or die('Restricted Access');

//helper & model
$menu_class   = JPATH_ROOT . '/plugins/system/helix3/core/classes/helix3.php';

if (file_exists($menu_class)) {
    require_once($menu_class);
}

$data = $displayData;

$output ='';

    $output .= '<div id="sp-' . JFilterOutput::stringURLSafe($data->settings->name) . '" class="' . $data->className . '">';

        $output .= '<div class="sp-column ' . ($data->settings->custom_class) . '">';

        $features = (Helix3::hasFeature($data->settings->name))? helix3::getInstance()->loadFeature[$data->settings->name] : array();

            foreach ($features as $key => $feature){
                if (isset($feature['feature']) && $feature['load_pos'] == 'before' ) {
                    $output .= $feature['feature'];
                }
            }

            $output .= '<jdoc:include type="modules" name="' . $data->settings->name . '" style="sp_xhtml" />';

            foreach ($features as $key => $feature){
                if (isset($feature['feature']) && $feature['load_pos'] != 'before' ) {
                    $output .= $feature['feature'];
                }
            }

        $output .= '</div>'; //.sp-column

    $output .= '</div>'; //.sp-


echo $output;
