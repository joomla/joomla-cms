<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die ();

class JFormFieldHelixgallery extends JFormField
{

	protected $type = 'Helixgallery';

	protected function getInput()
	{
		$doc = \JFactory::getDocument();

		\JHtml::_('jquery.framework');
		\JHtml::_('jquery.ui', array('core', 'sortable'));

		$plg_path = \JURI::root(true) . '/plugins/system/helixultimate';

		$values = json_decode($this->value);

		if($values) {
			$images = $this->element['name'] . '_images';
			$values = $values->$images;
		} else {
			$values = array();
		}

		$output  = '<div class="helix-ultimate-gallery-field">';
		$output .= '<ul class="helix-ultimate-gallery-items clearfix">';

		if(is_array($values) && $values) {
			foreach ($values as $key => $value) {

				$data_src = $value;

				$src = \JURI::root(true) . '/' . $value;

				$basename = basename($src);

				$thumbnail = \JPATH_ROOT . '/' . dirname($value) . '/' . \JFile::stripExt($basename) . '_thumbnail.' . \JFile::getExt($basename);
				$small_size = \JPATH_ROOT . '/' . dirname($value) . '/' . \JFile::stripExt($basename) . '_small.' . \JFile::getExt($basename);
				
				if(file_exists($thumbnail))
				{
					$src = \JURI::root(true) . '/' . dirname($value) . '/' . \JFile::stripExt($basename) . '_thumbnail.' . \JFile::getExt($basename);
				}
				else if(file_exists($small_size))
				{
					$src = \JURI::root(true) . '/' . dirname($value) . '/' . \JFile::stripExt($basename) . '_small.' . \JFile::getExt($basename);
				}

				$output .= '<li class="helix-ultimate-gallery-item" data-src="' . $data_src . '"><a href="#" class="btn btn-mini btn-danger btn-helix-ultimate-remove-gallery-image"><span class="fa fa-times"></span></a><img src="'. $src .'" alt=""></li>';
			}
		}

		$output .= '</ul>';

		$output .= '<input type="file" id="helix-ultimate-gallery-item-upload" accept="image/*" multiple="multiple" style="display:none;">';
		$output .= '<a class="btn btn-default btn-secondary btn-helix-ultimate-gallery-item-upload" href="#"><i class="fa fa-plus"></i> '. \JText::_('HELIX_ULTIMATE_UPLOAD_IMAGES') .'</a>';


		$output .= '<input type="hidden" name="'. $this->name .'" data-name="'. $this->element['name'] .'_images" id="' . $this->id . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8')
				. '"  class="form-field-helix-ultimate-gallery">';
		$output .= '</div>';

		return $output;
	}
}
