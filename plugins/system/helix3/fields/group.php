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

class JFormFieldGroup extends JFormField {
  protected $type = 'Group';
  public function getInput() {
    $text   = (string) $this->element['title'];
    $subtitle  	= (!empty($this->element['subtitle'])) ? '<span>' . JText::_($this->element['subtitle']) . '</span>':'';
    $group = ($this->element['group']=='no')?'no_group':'in_group';
    return '<div class="group_separator '.$group.'" title="'. JText::_($this->element['desc']) .'">' . JText::_($text) . $subtitle . '</div>';
  }

  public function getLabel(){
    return false;
  }
}
