<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2017 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

$font = $displayData['font'];

$system = array(
  'Arial',
  'Tahoma',
  'Verdana',
  'Helvetica',
  'Times New Roman',
  'Trebuchet MS',
  'Georgia'
);

if(!in_array($font, $system)) {
  $google_font = '//fonts.googleapis.com/css?family=' . str_replace(' ', '+', $font) . ':100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic';
  JFactory::getDocument()->addStylesheet($google_font);
}
