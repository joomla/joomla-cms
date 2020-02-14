<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die ();

jimport('joomla.form.formfield');

class JFormFieldHelixmegamenu extends JFormField
{
    protected $type = "Helixmegamenu";

    private $row_layouts = array('12', '6+6', '4+4+4', '3+3+3+3', '2+2+2+2+2+2', '5+7', '4+8','3+9','2+10');

    public function getInput()
    {
        $html  = '<div>';
        $html .= $this->getMegaSettings();
        $html .= '<input type="hidden" name="'.$this->name.'" id="'.$this->id.'" value="'.$this->value.'">';
        $html .= '</div>';
        
        return $html;
    }

    public function getMegaSettings()
    {
        $mega_menu_path = \JPATH_SITE.'/plugins/system/helixultimate/fields/';
        $menu_data = json_decode($this->value);
        $menu_item = $this->form->getData()->toObject();

        ob_start();
        include_once dirname(__DIR__) . '/core/lib/helixmenuhelper.php';
        $html = ob_get_clean();

        return $html;
    }

    private function getModuleNameById($id = 'all')
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('id','title')));
        $query->from($db->quoteName('#__modules'));
        $query->where($db->quoteName('published').'= 1');
        $query->where($db->quoteName('client_id').'= 0');

        if($id != 'all')
        {
            $query->where($db->quoteName('id').'=' . $db->quote($id));
        }
        $db->setQuery($query);

        if($id != 'all')
        {
            return $db->loadObject();
        }

        return $db->loadObjectList();
    }

    private function uniqueMenuItems($current_menu_id, $layout = array())
    {
        $saved_menu_items = array();

        $items = $this->menuItems();
        $children = isset($items[$current_menu_id])?$items[$current_menu_id]:array();

        if(!$layout) {
            return $children;
        }

        foreach ($layout as $key => $row)
        {
            foreach ($row->attr as $col_key => $col)
            {
                if ($col->items)
                {
                    foreach ($col->items as $item)
                    {
                        if ($item->type === 'menu_item')
                        {
                            unset($children[$item->item_id]);
                        }
                    }
                }
            }
        }

        return $children;
    }

    private function menuItems()
    {
        $menus = new JMenuSite;
        $menus = $menus->getMenu();
        $new = array();
        foreach ($menus as $item)
        {
            $new[$item->parent_id][$item->id] = $item->id;
        }
        return $new;
    }


    private function selectFieldHTML($name, $label, $list, $default, $display_class = '')
    {
        $view_class = '';
        if($name == 'alignment')
        {
            $view_class = 'helix-ultimate-megamenu-field-control' . $display_class;
        }
        elseif($name == 'dropdown')
        {
            $view_class = 'helix-ultimate-dropdown-field-control' . $display_class;
        }

        $html  = '';
        $html .= '<div class="'. $view_class .'">';
        $html .= '<span class="helix-ultimate-megamenu-label">' . $label .'</span>';
        $html .= '<select id="helix-ultimate-megamenu-'. $name .'">';

        if($name == 'fa-icon')
        {
            $html .= '<option value="">' . JText::_('HELIX_ULTIMATE_GLOBAL_SELECT') . '</option>';
            foreach($list as $each)
            {
                $html .= '<option value="'.$each.'"'. (($default == $each) ? 'selected' : '').'>'. str_replace('fa-', '', $each) .'</option>';
            }
        }
        else
        {
            foreach($list as $key => $each)
            {
                $html .= '<option value="'.$key.'"'. (($default == $key) ? 'selected' : '').'>'. $each .'</option>';
            }
        }

        
        
        $html .= '</select>';
        $html .= '</div>';

        return $html;
    }

    private function colorFieldHTML($name, $label, $placeholder, $value)
    {
        $html  = '';
        $html .= '<div>';
        $html .= '<span class="helix-ultimate-megamenu-label">'. $label .'</span>';
        $html .= '<input type="text" class="minicolors" id="helix-ultimate-menu-badge-'.$name.'" placeholder="'. $placeholder .'" value="' . $value .'" />';
        $html .= '</div>';

        return $html;
    }

    private function textFieldHTML($name, $label, $placeholder, $value, $type = 'text', $display_class = '')
    {
        if($type == 'number')
        {
            $display_class = 'helix-ultimate-megamenu-field-control' . $display_class;
        }

        $html  = '';
        $html .= '<div class="'. $display_class .'">';
        $html .= '<span class="helix-ultimate-megamenu-label">'. $label .'</span>';
        $html .= '<input type="'.$type.'" id="helix-ultimate-megamenu-'. $name .'" placeholder="' .$placeholder. '" value="'. $value .'" />';
        $html .= '</div>';

        return $html;
    }

    private function switchFieldHTML($name, $label, $value)
    {
        $html  = '';
        $html .= '<div>';
        $html .= '<span class="helix-ultimate-megamenu-label">'. $label .'</span>';
        $html .= '<input type="checkbox" class="helix-ultimate-checkbox" id="helix-ultimate-megamenu-'. $name .'" '. (($value) ? 'checked' : ''). '/>';
        $html .= '</div>';

        return $html;
    }

    
}
