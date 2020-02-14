<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die ();

jimport('joomla.form.formfield');

class JFormFieldHelixexportimport extends JFormField
{
	protected $type = 'Helixexportimport';

	protected function getInput()
	{
		$input = JFactory::getApplication()->input;
		$template_id = $input->get('id',0,'INT');
		$export_url = 'index.php?option=com_ajax&helix=ultimate&task=export&id=' . $template_id;

		$output  = '<a class="btn btn-success" id="btn-helix-ultimate-export-settings" target="_blank" href="'. $export_url .'">'. JText::_("HELIX_ULTIMATE_SETTINGS_EXPORT") .'</a>';
		$output .= '<textarea id="input-helix-ultimate-settings" rows="5"></textarea>';
		$output .= '<a id="btn-helix-ultimate-import-settings" class="btn btn-primary" data-template_id="'. $template_id .'" target="_blank" href="#">'. JText::_("HELIX_ULTIMATE_SETTINGS_IMPORT") .'</a>';

		return $output;
	}
}
