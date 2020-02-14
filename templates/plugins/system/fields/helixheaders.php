<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die ();

jimport('joomla.form.formfield');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class JFormFieldHelixheaders extends JFormField
{
    protected $type = 'Helixheaders';

    protected function getInput()
    {
        $input  = JFactory::getApplication()->input;
        $id = $input->get('id',NULL,'INT');
        $template = $this->getTemplateName($id);

        $headers_src = JPATH_ROOT .'/templates/'. $template .'/headers';
        $thumb_url = JURI::root() .'templates/'. $template .'/headers';

        $html = '';

        if(JFolder::exists($headers_src))
        {
            $headers = JFolder::folders($headers_src);

            if(count($headers))
            {
                $html = '<div class="helix-ultimate-predefined-headers">';
                $html .= '<ul class="helix-ultimate-header-list clearfix" data-name="'. $this->name .'">';
                foreach($headers as $header)
                {
                    $html .= '<li class="helix-ultimate-header-item'.(($this->value == $header)?' active':'').'" data-style="'.$header.'">';
                    if(file_exists($headers_src . '/' . $header . '/thumb.svg'))
                    {
                        $html .= '<span><img src="'. $thumb_url . '/' . $header .'/thumb.svg" alt="'. $header .'"</span>';
                    }
                    else
                    {
                        $html .= '<span><img src="'. $thumb_url . '/' . $header .'/thumb.jpg" alt="'. $header .'"</span>';
                    }
                    $html .= '</li>';
                }
                $html .= '<input type="hidden" name="' . $this->name .'" value=\''. $this->value .'\' id="'. $this->id .'">';
                $html .= '</div>';
            }

        }
        
        return $html;
    }

    private function getTemplateName($id = 0)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__template_styles'));
        $query->where($db->quoteName('client_id') . ' = 0');
        $query->where($db->quoteName('id') . ' = ' . $db->quote( $id ));
        $db->setQuery($query);
        $result = $db->loadObject();

        if($result){
            return $result->template;
        }

        return;
    }
}