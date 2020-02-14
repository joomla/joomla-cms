<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die ();

require_once __DIR__ . '/fa.php';

$current_menu_id = $this->form->getValue('id');
$JMenuSite = new JMenuSite;
$module_list = $this->getModuleNameById();

$mega_align = array(
    'left' => JText::_('HELIX_ULTIMATE_GLOBAL_LEFT'),
    'center' => JText::_('HELIX_ULTIMATE_GLOBAL_CENTER'),
    'right' => JText::_('HELIX_ULTIMATE_GLOBAL_RIGHT'),
    'full' => JText::_('HELIX_ULTIMATE_GLOBAL_FULL'),
);

$dropdown_list = array(
    'left' => JText::_('HELIX_ULTIMATE_GLOBAL_LEFT'),
    'right' => JText::_('HELIX_ULTIMATE_GLOBAL_RIGHT')
);

$menu_width = 600;
$align = 'right';
$layout = array();
$enable_megamenu = 0;
$show_title = 1;
$custom_class = '';
$faicon = '';
$dropdown = 'right';
$badge = '';
$badge_position = '';
$badge_bg_color = '';
$badge_text_color = '';
$display_class = '';
$dropdown_class = '';
$unique_menu_item_count = 0;


if (isset($menu_data->megamenu)) $enable_megamenu = $menu_data->megamenu;
if (isset($menu_data->width)) $menu_width = $menu_data->width;
if (isset($menu_data->menualign)) $align = $menu_data->menualign;
if (isset($menu_data->layout)) $layout = $menu_data->layout;
if (isset($menu_data->showtitle)) $show_title = $menu_data->showtitle;
if (isset($menu_data->customclass)) $custom_class = $menu_data->customclass;
if (isset($menu_data->faicon) && $menu_data->faicon) $faicon = $menu_data->faicon;
if (isset($menu_data->dropdown)) $dropdown = $menu_data->dropdown;
if (isset($menu_data->badge)) $badge = $menu_data->badge;
if (isset($menu_data->badge_position)) $badge_position = $menu_data->badge_position;
if (isset($menu_data->badge_bg_color)) $badge_bg_color = $menu_data->badge_bg_color;
if (isset($menu_data->badge_text_color)) $badge_text_color = $menu_data->badge_text_color;

if(!$enable_megamenu)
{
    $display_class = ' hide-menu-builder';
}
else
{
    $dropdown_class = ' hide-menu-builder';
}

$custom_class_label = JText::_('HELIX_ULTIMATE_MENU_CUSTOM_CLASS');

$badge_label = JText::_('HELIX_ULTIMATE_MENU_BADGE_TEXT');

$unique_menu_items = $this->uniqueMenuItems($current_menu_id, $layout);

if($unique_menu_items)
{
    $unique_menu_item_count = count($unique_menu_items);
}
?>

<div class="helix-ultimate-row">
    <div class="helix-ultimate-col-sm-9">
        
        <div class="helix-ultimate-megamenu-wrap">

            <div class="helix-ultimate-megamenu-actions">
                <?php
                    if ($menu_item->parent_id == 1)
                    {
                        echo $this->switchFieldHTML('toggler', JText::_('HELIX_ULTIMATE_MENU_ENABLED'), $enable_megamenu);
                        
                        echo $this->textFieldHTML('width', JText::_('HELIX_ULTIMATE_MENU_SUB_WIDTH'), 400, $menu_width, 'number', $display_class);
                        
                        echo $this->selectFieldHTML('alignment', JText::_('HELIX_ULTIMATE_MENU_SUB_ALIGNMENT'), $mega_align, $align, $display_class);
                    }

                    echo $this->switchFieldHTML('title-toggler', JText::_('HELIX_ULTIMATE_MENU_SHOW_TITLE'), $show_title);

                    echo $this->selectFieldHTML('dropdown', 'Dropdown Position', $dropdown_list, $dropdown, $dropdown_class);

                    echo $this->selectFieldHTML('fa-icon', JText::_('HELIX_ULTIMATE_MENU_ICON'), $fa_list, $faicon);

                    echo $this->textFieldHTML('custom-class', $custom_class_label, '', $custom_class);

                    echo $this->textFieldHTML('menu-badge', $badge_label, '', $badge);

                    echo $this->selectFieldHTML('badge-position', 'Badge Position', $dropdown_list, $badge_position);

                    echo $this->colorFieldHTML('bg-color', 'Background Color', '#333333', $badge_bg_color);

                    echo $this->colorFieldHTML('text-color', 'Text Color', '#ffffff', $badge_text_color);
                ?>
            </div>

            <div id="helix-ultimate-megamenu-layout" class="helix-ultimate-megamenu-layout helix-ultimate-megamenu-field-control<?php echo ($enable_megamenu != 1)?' hide-menu-builder':''?>" data-megamenu="<?php echo $enable_megamenu; ?>" data-width="<?php echo $menu_width; ?>" data-menualign="<?php echo $align; ?>" data-dropdown="<?php echo $dropdown; ?>" data-showtitle="<?php echo $show_title; ?>" data-customclass="<?php echo $custom_class; ?>" data-faicon="<?php echo $faicon; ?>" data-dropdown="<?php echo $dropdown; ?>" data-badge="<?php echo $badge; ?>" data-badge_position="<?php echo $badge_position; ?>" data-badge_bg_color="<?php echo $badge_bg_color; ?>" data-badge_text_color="<?php echo $badge_text_color; ?>">
                
                <?php if ($layout) { $col_number = 0; ?>
                    <?php foreach ($layout as $key => $row) { ?>

                        <div class="helix-ultimate-megamenu-row">
                            <div class="helix-ultimate-megamenu-row-actions clearfix">
                                <div class="helix-ultimate-action-move-row"> <span class="fa fa-sort"></span> Row</div>
                                <a href="#" class="helix-ultimate-action-detele-row"><span class="fa fa-trash-o"></span></a>
                            </div>

                            <div class="helix-ultimate-row">

                                <?php if (! empty($row->attr) ) { ?>
                                    <?php foreach ($row->attr as $col_key => $col) { ?>

                                        <div class="helix-ultimate-megmenu-col helix-ultimate-col-sm-<?php echo $col->colGrid; ?>" data-grid="<?php echo $col->colGrid; ?>">
                                            <div class="helix-ultimate-megamenu-column">

                                                <div class="helix-ultimate-megamenu-column-actions">
                                                    <span class="helix-ultimate-action-move-column"><span class="fa fa-arrows"></span> Column</span>
                                                </div>

                                                <?php
                                                    $col_list = '<div class="helix-ultimate-megamenu-item-list">';
                                                    if ( isset($col->items) && count($col->items))
                                                    {
                                                        foreach ($col->items as $item)
                                                        {
                                                            if ($item->type === 'module')
                                                            {
                                                                $modules = $this->getModuleNameById($item->item_id);
                                                                $title = $modules->title . '<a href="javascript:;" class="helix-ultimate-megamenu-remove-module"><span class="fa fa-remove"></span></a>';
                                                            }
                                                            elseif ($item->type === 'menu_item')
                                                            {
                                                                $title = $JMenuSite->getItem($item->item_id)->title;
                                                            }

                                                            $col_list .= '<div class="helix-ultimate-megamenu-item" data-mod_id="'. $item->item_id .'" data-type="'. $item->type .'">';
                                                            $col_list .= '<div class="helix-ultimate-megamenu-item-module">';
                                                            $col_list .= '<div class="helix-ultimate-megamenu-item-module-title">' . $title . '</div>';
                                                            $col_list .= '</div>';
                                                            $col_list .= '</div>';
                                                        }
                                                    }

                                                    if ($unique_menu_item_count && $col_number == 0)
                                                    {
                                                        $col_number++;
                                                    
                                                        foreach ($unique_menu_items as $key => $item_id)
                                                        {
                                                            $col_list .= '<div class="helix-ultimate-megamenu-item" data-mod_id="' . $item_id .'" data-type="menu_item">';
                                                            $col_list .= '<div class="helix-ultimate-megamenu-item-module">';
                                                            $col_list .= '<div class="helix-ultimate-megamenu-item-module-title">' . $JMenuSite->getItem($item_id)->title .'</div>';
                                                            $col_list .= '</div>';
                                                            $col_list .= '</div>';
                                                        }
                                                    }
                                                    $col_list .= '</div>';

                                                    echo $col_list;
                                                ?>

                                            </div>
                                        </div>

                                    <?php } ?>
                                <?php } ?>

                            </div>
                        </div>

                    <?php } ?>
                <?php } ?>

            </div>

        </div>

        <div class="helix-ultimate-megamenu-add-row helix-ultimate-megamenu-field-control clearfix<?php echo ($enable_megamenu != 1)?' hide-menu-builder':''?>">
            <button id="helix-ultimate-choose-megamenu-layout" class="helix-ultimate-choose-megamenu-layout"><span class="fa fa-plus-circle"></span> Add New Row</button>
            <div class="helix-ultimate-megamenu-modal" id="helix-ultimate-megamenu-layout-modal" style="display: none;" >
                <div class="helix-ultimate-row">

                <?php foreach ($this->row_layouts as $row_layout) { $col_grids = explode('+',$row_layout); ?>

                    <div class="helix-ultimate-col-sm-4">
                        <div class="helix-ultimate-megamenu-grids" data-layout="<?php echo $row_layout; ?>">
                            <div class="helix-ultimate-row">

                                <?php foreach ($col_grids as $col_grid) { ?>
                                    <div class="helix-ultimate-col-sm-<?php echo $col_grid; ?>"><div><?php echo $col_grid; ?></div></div>
                                <?php } ?>

                            </div>
                        </div>
                    </div>

                <?php } ?>

                </div>
            </div>
        </div> <!-- End of Row Layout Structure -->

    </div>

    <?php if ($menu_item->parent_id == 1 && $module_list) { ?>
        <div class="helix-ultimate-col-sm-3">
            <div class="helix-ultimate-megamenu-sidebar <?php echo ($enable_megamenu != 1)?' hide-menu-builder':''?>">
                <h3><span class="fa fa-bars"></span> <?php echo JText::_('HELIX_ULTIMATE_MENU_MODULE_LIST'); ?></h3>
                <div class="helix-ultimate-megamenu-module-list">
                    <?php foreach($module_list as $module) { ?>

                        <div class="helix-ultimate-megamenu-draggable-module" data-mod_id="<?php echo $module->id; ?>" data-type="module"><span class="fa fa-arrows"></span> <?php echo $module->title; ?></div>
                    
                    <?php } ?>
                </div>
            </div>
        </div> <!-- End of Module List -->
    <?php } ?>

</div>
