<?php
/**
* @package Helix3 Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2017 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

//no direct accees
defined ('_JEXEC') or die ('resticted aceess');

class JFormFieldSpgallery extends JFormField {

  protected $type = 'Spgallery';

  protected function getInput()
  {
    $doc = JFactory::getDocument();

    JHtml::_('jquery.framework');
    JHtml::_('jquery.ui', array('core', 'sortable'));

    $plg_path = JURI::root(true) . '/plugins/system/helix3';
    $doc->addScript($plg_path . '/assets/js/spgallery.js');
    $doc->addStyleSheet($plg_path . '/assets/css/spgallery.css');

    $values = json_decode($this->value);

    if($values) {
      $images = $this->element['name'] . '_images';
      $values = $values->$images;
    } else {
      $values = array();
    }

    $output  = '<div class="sp-gallery-field">';
    $output .= '<ul class="sp-gallery-items clearfix">';

    if(is_array($values) && $values) {
      foreach ($values as $key => $value) {

        $data_src = $value;

        $src = JURI::root(true) . '/' . $value;

        $basename = basename($src);

        $thumbnail = JPATH_ROOT . '/' . dirname($value) . '/' . JFile::stripExt($basename) . '_thumbnail.' . JFile::getExt($basename);
        if(file_exists($thumbnail)) {
          $src = JURI::root(true) . '/' . dirname($value) . '/' . JFile::stripExt($basename) . '_thumbnail.' . JFile::getExt($basename);
        }

        $small_size = JPATH_ROOT . '/' . dirname($value) . '/' . JFile::stripExt($basename) . '_small.' . JFile::getExt($basename);
        if(file_exists($small_size)) {
          $src = JURI::root(true) . '/' . dirname($value) . '/' . JFile::stripExt($basename) . '_small.' . JFile::getExt($basename);
        }

        $output .= '<li data-src="' . $data_src . '"><a href="#" class="btn btn-mini btn-danger btn-remove-image">Delete</a><img src="'. $src .'" alt=""></li>';
      }
    }

    $output .= '</ul>';

    $output .= '<input type="file" class="sp-gallery-item-upload" accept="image/*" style="display:none;">';
    $output .= '<a class="btn btn-default btn-large btn-sp-gallery-item-upload" href="#"><i class="fa fa-plus"></i> Upload Images</a>';


    $output .= '<input type="hidden" name="'. $this->name .'" data-name="'. $this->element['name'] .'_images" id="' . $this->id . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8')
    . '"  class="form-field-spgallery">';
    $output .= '</div>';

    return $output;
  }
}