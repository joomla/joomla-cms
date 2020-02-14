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

class JFormFieldLayout extends JFormField {

  protected $type = 'Layout';

  public function getInput()
  {
    $helix_layout_path = JPATH_SITE.'/plugins/system/helix3/layout/';

    $json = json_decode($this->value);

    if(!empty($json)) {
      $value = $json;
    } else {
      $layout_file = JFile::read( JPATH_SITE . '/templates/' . $this->getTemplate() . '/layout/default.json' );
      $value = json_decode($layout_file);
    }

    $htmls = $this->generateLayout($helix_layout_path, $value);
    $htmls .= '<input type="hidden" id="'.$this->id.'" name="'.$this->name.'">';
    return $htmls;
  }


  private function generateLayout($path,$layout_data = null)
  {

    ob_start();
    include_once( $path.'generated.php' );
    $items = ob_get_contents();
    ob_end_clean();

    return $items;

  }


  public function getLabel()
  {
    return false;
  }

  //Get template name
  private static function getTemplate() {

    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select($db->quoteName(array('template')));
    $query->from($db->quoteName('#__template_styles'));
    $query->where($db->quoteName('id') . ' = '. $db->quote( JRequest::getVar('id') ));
    $db->setQuery($query);

    return $db->loadResult();
  }
}
