<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die ();

jimport('joomla.form.formfield');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class JFormFieldHelixpresets extends JFormField
{

    protected $type = 'Helixpresets';

    protected $presetfiled = '';
    protected $presetList ='';

    protected function getInput()
    {
        $children = $this->element->children();
        $html = '<div class="helix-ultimate-presets clearfix">';
    
        foreach ($children as $child)
        {
            $preset = json_decode($this->value);

            $class = '';

            if(isset($preset->preset) && $preset->preset == $child['name'])
            {
                $class = ' active';
            }

            $childName = $child->getName();

            if ($childName == 'preset')
            {
                $html_data_attr = 'data-preset="'. $child['name'] .'"';
                
                foreach ( $child->children() as $preset )
                {
                    $html_data_attr .= ' data-'. $preset['name'] .'="'. $preset['value'] .'"';
                }

                $html .='<div class="helix-ultimate-preset'. $class .'" style="background-color: '. $child['default'] .'" '. $html_data_attr .'  class="helix-ultimate-preset">';
                $html .='<div class="helix-ultimate-preset-title">'. $child['label'] .'</div>';
                $html .='<div class="helix-ultimate-preset-contents">';
                $html .='</div>';
                $html .='</div>';
            }
            else
            {
                throw new UnexpectedValueException(sprintf('Unsupported element %s in JFormFieldGroupedList', $child->getName()), 500);
            }
        }

        $html .= '<input id="'. $this->id .'" type="hidden" name="'. $this->name .'" class="helix-ultimate-input-preset" value="'. $this->value .'" />';
        $html .= '</div>';
            
        return $html; 
    }

    public function getLabel()
    {
        return false;
    }
}
