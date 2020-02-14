<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die ();

require_once dirname(__DIR__) . '/platform/helper.php';

use HelixUltimate\Helper\Helper as Helper;

class JFormFieldHelixpositions extends JFormField
{
  protected $type = 'Helixpositions';

  protected function getInput()
  {
    $html = array();
    $attr = '';
    $input  = \JFactory::getApplication()->input;
    $style_id = (int) $input->get('id', 0, 'INT');
    $style = Helper::getTemplateStyle($style_id);

    $db = \JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select($db->quoteName('position'));
    $query->from($db->quoteName('#__modules'));
    $query->where($db->quoteName('client_id') . ' = 0');
    $query->where($db->quoteName('published') . ' = 1');
    $query->group('position');
    $query->order('position ASC');
    $db->setQuery($query);
    $dbpositions = $db->loadObjectList();

    $templateXML = \JPATH_SITE.'/templates/'.$style->template.'/templateDetails.xml';
    $template = simplexml_load_file( $templateXML );
    $options = array();

    foreach($dbpositions as $positions)
    {
      $options[] = $positions->position;
    }

    foreach($template->positions[0] as $position)
    {
      $options[] =  (string) $position;
    }

    ksort($options);

    $opts = array_unique($options);

    $options = array();

    foreach ($opts as $opt) {
      $options[$opt] = $opt;
    }

    $html[] = \JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);

    return implode($html);
  }
}
