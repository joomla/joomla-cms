<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die ();

class JFormFieldHeliximage extends JFormField
{

	protected $type = 'Heliximage';

	protected function getInput()
	{
		$doc = JFactory::getDocument();

		JHtml::_('jquery.framework');

		$plg_path = JURI::root(true) . '/plugins/system/helixultimate';

		$class = ' helix-ultimate-image-field-empty';

		if($this->value) {
			$class = ' helix-ultimate-image-field-has-image';
		}

		$output  = '<div class="helix-ultimate-image-field' . $class . ' clearfix">';
		$output .= '<div class="helix-ultimate-image-upload-wrapper">';

		if($this->value) {
			$data_src = $this->value;
			$src = JURI::root(true) . '/' . $data_src;

			$basename = basename($data_src);
			$thumbnail = JPATH_ROOT . '/' . dirname($data_src) . '/' . JFile::stripExt($basename) . '_thumbnail.' . JFile::getExt($basename);

			if(file_exists($thumbnail)) {
				$src = JURI::root(true) . '/' . dirname($data_src) . '/' . JFile::stripExt($basename) . '_thumbnail.' . JFile::getExt($basename);
			}

			$output .= '<img src="'. $src .'" data-src="' . $data_src . '" alt="">';
		}

		$output .= '</div>';

		$output .= '<input type="file" class="helix-ultimate-image-upload" accept="image/*" style="display:none;">';
		$output .= '<a class="btn btn-primary btn-helix-ultimate-image-upload" href="#"><i class="fa fa-plus"></i> '. \JText::_('HELIX_ULTIMATE_UPLOAD_IMAGE') .'</a>';
		$output .= '<a class="btn btn-danger btn-helix-ultimate-image-remove" href="#"><i class="fa fa-minus-circle"></i> '. JText::_('HELIX_ULTIMATE_REMOVE_IMAGE') .'</a>';

		$output .= '<input type="hidden" name="'. $this->name .'" id="' . $this->id . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8')
				. '"  class="form-field-helix-ultimate-image">';
		$output .= '</div>';

		return $output;
	}
}
