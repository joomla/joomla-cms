<?php
/**
* @package Helix3 Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2017 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

//no direct accees
defined ('_JEXEC') or die ('resticted aceess');

jimport('joomla.form.formfield');

class JFormFieldOptionio extends JFormField
{
	protected $type = 'optionio';

	protected function getInput()
	{
		$input = JFactory::getApplication()->input;
		$template_id = $input->get('id',0,'INT');

		$url_cureent =  "//$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

		$export_url = $url_cureent . '&helix3task=export';

		$output = '';
		$output .= '<div class="import-export clearfix" style="margin-bottom:30px;">';
		$output .= '<a class="btn btn-success" target="_blank" href="'. $export_url .'">'. JText::_("HELIX_SETTINGS_EXPORT") .'</a>';
		$output .= '</div>';
		$output .= '<div class="import-export clearfix">';
		$output .= '<textarea id="import-data" name="import-data" rows="5" style="margin-bottom:20px;"></textarea>';
		$output .= '<a id="import-settings" class="btn btn-primary" data-template_id="'. $template_id .'" target="_blank" href="#">'. JText::_("HELIX_SETTINGS_IMPORT") .'</a>';
		$output .= '</div>';

		return $output;
	}
}
