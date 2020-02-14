<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die ();

jimport('joomla.form.formfield');

class JFormFieldHelixmedia extends JFormField
{

	protected $type = 'Helixmedia';

	public function getInput()
	{
		
		$output = '<div class="helix-ultimate-image-holder">';
		if($this->value != '')
		{
			$output .= '<img src="'. \JURI::root() . $this->value .'" alt="">';
		}
		$output .= '</div>';

		$output .= '<input type="hidden" name="'. $this->name .'" id="'. $this->id .'" value="'. $this->value .'">';
		$output .= '<a href="#" class="helix-ultimate-media-picker btn btn-primary btn-sm" data-id="'. $this->id .'"><span class="fa fa-picture-o"></span> Select Media</a>';
		$output .= '<a href="#" class="helix-ultimate-media-clear btn btn-secondary btn-sm"><span class="fa fa-times"></span> Clear</a>';

		return $output;

	}

}
