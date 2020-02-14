<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die ();

jimport('joomla.form.formfield');

require_once dirname(__DIR__) . '/platform/helper.php';

use HelixUltimate\Helper\Helper as Helper;

class JFormFieldHelixlayout extends JFormField
{

    protected $type = 'Helixlayout';

    public function getInput()
    {
        $input  = \JFactory::getApplication()->input;
        $style_id = (int) $input->get('id', 0, 'INT');
        $style = Helper::getTemplateStyle($style_id);

        $helix_layout_path = \JPATH_SITE.'/plugins/system/helixultimate/layout/';

        $json = json_decode($this->value);

        if(!empty($json))
        {
            $rows = $json;
        }
        else
        {
            $layout_file = \JFile::read( \JPATH_SITE . '/templates/' . $style->template . '/options.json' );
            $value = json_decode($layout_file);
            $rows = json_decode($value->layout);
        }

        $htmls = $this->generateLayout($helix_layout_path, $rows);
        $htmls .= '<input type="hidden" id="'.$this->id.'" name="'.$this->name.'">';
        return $htmls;
    }


    private function generateLayout($path,$layout_data = null)
    {
        $GLOBALS['tpl_layout_data'] = $layout_data;

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
}
