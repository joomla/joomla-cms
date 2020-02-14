<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die ();

jimport('joomla.form.formfield');

class JFormFieldHelixbutton extends JFormField
{
	protected $type = 'Helixbutton';	
	
	protected function getInput()
	{

		$url = !empty($this->element['url']) ? $this->element['url'] : '#';
		$class = !empty($this->element['class']) ? ' ' . $this->element['class'] : '';
		$text = !empty($this->element['text']) ? $this->element['text'] : 'Button';
		$target = !empty($this->element['target']) ? $this->element['target'] : '_self';
		
		return '<a id="'. $this->id .'" class="btn'. $class .'" href="'. $url .'" target="' . $target . '">'. JText::_($text) .'</a>';	
	}	
}