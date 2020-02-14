<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

jimport('joomla.form.formfield');

class JFormFieldSpmedia extends JFormField
{
	protected $type = 'Spmedia';

	protected function getInput() {

		$media_format = $this->getAttribute('media_format', 'image');

		JText::script('COM_SPPAGEBUILDER_MEDIA_MANAGER_CONFIRM_DELETE');
		JText::script('COM_SPPAGEBUILDER_MEDIA_MANAGER_ENTER_DIRECTORY_NAME');

		$html = '';

		if($this->value) {
			if($media_format == 'image') {
				$html = '<img class="sp-pagebuilder-media-preview" src="' . JURI::root(true) . '/' . $this->value . '" alt="" />';
			}
		} else {
			if($media_format == 'image') {
				$html  = '<img class="sp-pagebuilder-media-preview sp-pagebuilder-media-no-image" alt="">';
			}
		}

		if($media_format == 'image') {
			$html .= '<input class="sp-media-input" type="hidden" name="'. $this->name .'" id="'. $this->id .'" value="'. $this->value .'">';
		} else {
			$html .= '<input class="sp-media-input" type="text" name="'. $this->name .'" id="'. $this->id .'" value="'. $this->value .'">';
		}

		$html .= '<a href="#" id="media-upload-button" class="sp-pagebuilder-btn sp-pagebuilder-btn-primary sp-pagebuilder-btn-media-manager" data-support="' . $media_format . '"><i class="fa fa-spinner fa-spin" style="margin-right: 5px; display: none;"></i>'. JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_UPLOAD_' . strtoupper($media_format)) .'</a> <a href="#" class="sp-pagebuilder-btn sp-pagebuilder-btn-danger sp-pagebuilder-btn-clear-media"><i class="fa fa-times"></i></a>';

		return $html;
	}
}
