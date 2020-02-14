<?php
/**
* @package Helix3 Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2017 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

//no direct accees
defined ('_JEXEC') or die ('resticted aceess');

JFormHelper::loadFieldClass('text');

/**
* Supports a modal article picker.
*
* @package		Joomla.Administrator
* @subpackage	com_modules
* @since		1.6
*/
class JFormFieldModPos extends JFormFieldText
{
  /**
  * The form field type.
  *
  * @var		string
  * @since	1.6
  */
  protected $type = 'ModPos';

  /**
  * Method to get the field input markup.
  *
  * @return	string	The field input markup.
  * @since	1.6
  */
  protected function getInput()
  {
    $db = JFactory::getDBO();
    $query = 'SELECT `position` FROM `#__modules` WHERE  `client_id`=0 AND ( `published` !=-2 AND `published` !=0 ) GROUP BY `position` ORDER BY `position` ASC';

    $db->setQuery($query);
    $dbpositions = (array) $db->loadAssocList();


    $template = $this->form->getValue('template');
    $templateXML = JPATH_SITE.'/templates/'.$template.'/templateDetails.xml';
    $template = simplexml_load_file( $templateXML );
    $options = array();

    foreach($dbpositions as $positions) $options[] = $positions['position'];

    foreach($template->positions[0] as $position)  $options[] =  (string) $position;

    $options = array_unique($options);

    $selectOption = array();
    sort($selectOption);

    foreach($options as $option) $selectOption[] = JHTML::_( 'select.option',$option,$option );

    return JHTML::_('select.genericlist', $selectOption, 'jform[params]['.$this->element['name'].']', 'class="'.$this->element['class'].'"', 'value', 'text', $this->value, 'jform_params_helix_'.$this->element['name']);
  }
}
