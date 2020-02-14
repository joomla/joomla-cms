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

class JFormFieldLayoutlist extends JFormField
{
  protected $type = 'Layoutlist';

  public function getInput()
  {
    $template  = self::getTemplate();
    $layoutPath = JPATH_SITE.'/templates/'.$template.'/layout/';
    $laoutlist = JFolder::files($layoutPath, '.json');

    $htmls = '<div class="layoutlist"><select id="'.$this->id.'" name="'.$this->name.'">';
    if ($laoutlist) {
      foreach ($laoutlist as $name) {
        $htmls .= '<option value="'.$name.'">'.str_replace('.json','',$name).'</option>';
      }
    }
    $htmls .= '</select></div>';
    $htmls .= '<div class="layout-button-wrap"><a href="#" class="btn btn-success layout-save-action" data-action="save">'. JText::_('HELIX_SAVE_COPY') .'</a>';
    $htmls .= '<a href="#" class="btn btn-danger layout-del-action" data-action="remove">'. JText::_('HELIX_DELETE') .'</a></div>';

    return $htmls;
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
