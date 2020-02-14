<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die ();

jimport('joomla.form.formfield');

class JFormFieldHelixdetails extends JFormField
{
    protected $type = 'Helixdetails';

    protected function getInput()
    {
        \JHtml::_('jquery.framework');
        $doc = \JFactory::getDocument();
		$plg_path = \JURI::root(true) . '/plugins/system/helixultimate';
		$doc->addScript($plg_path . '/assets/js/admin/details.js');
		$doc->addStyleSheet($plg_path . '/assets/css/admin/details.css');

        $app = \JFactory::getApplication();
        $id  = (int) $app->input->get('id', 0,'INT');

        $url = \JRoute::_('index.php?option=com_ajax&helix=ultimate&id=' . $id);
        $html = '<a href="'. $url .'" class="helix-ultimate-options"><i class="icon-options"></i> Template Options</a>';

        return $html;
    }

    public function getLabel()
    {
        return false;
    }
}
