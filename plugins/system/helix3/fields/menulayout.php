<?php
/**
* @package Helix3 Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2017 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

//no direct accees
defined ('_JEXEC') or die ('resticted aceess');

$current_menu_id = $this->form->getValue('id');

function create_menu($current_menu_id)
{
  $items = menuItems();
  $menus = new JMenuSite;

  if (isset($items[$current_menu_id]))
  {
    $item = $items[$current_menu_id];
    foreach ($item as $key => $item_id)
    {
      echo '<li>';
      echo $menus->getItem($item_id)->title;
      echo '</li>';
    }
  }
}

function menuItems()
{
  $menus = new JMenuSite;
  $menus = $menus->getMenu();
  $new = array();
  foreach ($menus as $item) {
    $new[$item->parent_id][] = $item->id;
  }
  return $new;
}

function getModuleNameId($id = 'all')
{
  $db = JFactory::getDBO();

  if ($id == 'all') {
    $query = 'SELECT id, title FROM `#__modules` WHERE ( `published` !=-2 AND `published` !=0 ) AND client_id = 0';
  } else {
    $query = 'SELECT id, title FROM `#__modules` WHERE ( `published` !=-2 AND `published` !=0 ) AND id = ' . $id;
  }

  $db->setQuery($query);

  return $db->loadObjectList();
}

$modules = getModuleNameId();
?>

<?php
$menu_width = 600;
$align = 'right';
$layout = '';

if (isset($menu_data->width))
{
  $menu_width = $menu_data->width;
}

if (isset($menu_data->menuAlign))
{
  $align = $menu_data->menuAlign;
}

if (isset($menu_data->layout))
{
  $layout = $menu_data->layout;
}
?>

<?php
$items = menuItems();
$item = array();
if (isset($items[$current_menu_id]) && !empty($items[$current_menu_id])) {
  $item = $items[$current_menu_id];
}

$menuItems = new JMenuSite;

$no_child = true;
$count = 0;
$x_key = 0;
$y_key = 0;
$check_child = 0;
$item_array = array();

foreach ($item as $key => $id)
{
  $status = 0;
  if (isset($items[$id]) && is_array($items[$id]))
  {
    $no_child = false;
    $count = $count + 1;
    $check_child = $check_child+1;
    $status = 1;
  }

  if ($check_child === 2)
  {
    $y_key = 0;
    $x_key = $x_key + 1;
    $check_child = 1;
  }

  $item_array[$x_key][$y_key] = array($id,$status);
  $y_key = $y_key + 1;
}

if ($no_child === true)
{
  $count = 1;
}

if($count > 4 && $count != 6)
{
  $count = 4;
}
?>


<div class="row-fluid">

  <div class="span2">
    <h3 class="sidebar-title"><?php echo JText::_('HELIX_MENU_DRAG_MODULE'); ?></h3>
    <div class="modules-list">
      <?php
      $modules = getModuleNameId();
      if($modules) {
        foreach($modules as $module){
          echo '<div class="draggable-module" data-mod_id="' . $module->id . '">' . $module->title . '<i class="fa fa-remove"></i><i class="fa fa-arrows"></i></div>';
        }
      }?>
    </div>
  </div>

  <div class="span10">

    <div class="action-bar">
      <ul>
        <li>
          <strong><?php echo JText::_('HELIX_MENU_SUB_WIDTH'); ?></strong> <input type="number" id="menuWidth" name="width" value="<?php echo $menu_width; ?>">
        </li>
        <li id="sizeShape"><a href="#" class="add-layout btn btn-primary"><i class="fa fa-plus"></i> <?php echo JText::_('HELIX_MENU_MANAGE_LAYOUT'); ?></a></li>
        <li class="btn-group">
          <a class="alignment btn <?php echo ($align == 'left')?'active':''; ?>" data-al_flag="left" href="#"><?php echo JText::_('HELIX_GLOBAL_LEFT'); ?></a>
          <a class="alignment btn <?php echo ($align == 'center')?'active':''; ?>" data-al_flag="center" href="#"><?php echo JText::_('HELIX_GLOBAL_CENTER'); ?></a>
          <a class="alignment btn <?php echo ($align == 'right')?'active':''; ?>" data-al_flag="right" href="#"><?php echo JText::_('HELIX_GLOBAL_RIGHT'); ?></a>
          <a class="alignment btn <?php echo ($align == 'full')?'active':''; ?>" data-al_flag="full" href="#"><?php echo JText::_('HELIX_GLOBAL_FULL'); ?></a>
        </li>
        <li class="btn-group">
          <a class="layout-reset btn btn-success"href="#" data-current_item="<?php echo $current_menu_id; ?>"><i class="fa fa-refresh"></i> <?php echo JText::_('HELIX_GLOBAL_RESET'); ?></a>
        </li>
      </ul>
    </div>

    <div id="megamenulayout" style="width:<?php echo $menu_width; ?>px;" data-width="<?php echo $menu_width; ?>" data-menu_item="<?php echo $count; ?>" data-menu_align="<?php echo $align; ?>">

      <?php

      if ($layout) {

        foreach ($layout as $key => $row) {

          ?>

          <div class="menu-section">
            <span class="row-move"><i class="fa fa-bars"></i></span>
            <div class="spmenu sp-row">

              <?php foreach ($row->attr as $key => $column){ ?>

                <div class="column sp-col-sm-<?php echo $column->colGrid; ?>" data-column="<?php echo $column->colGrid; ?>">
                  <div class="column-items-wrap">

                    <?php
                    $menus_id = $column->menuParentId;
                    $modId = $column->moduleId;

                    if ( $menus_id )
                    {
                      $menu_id_array = explode(',',$menus_id);
                      foreach ($menu_id_array as $menuId) {
                        ?>
                        <?php if(in_array( $menuId , $item)) { ?>

                          <h4 data-current_child="<?php echo $menuId; ?>" ><?php echo $menuItems->getItem($menuId)->title; ?></h4>
                        <?php }else if($current_menu_id != $menuId){ ?>
                          <h4 style="display:none" data-current_child="<?php echo $menuId; ?>" ><?php echo $menuItems->getItem($current_menu_id)->title; ?></h4>
                        <?php }else if (isset($menuId)) { ?>
                          <h4 style="display:none" data-current_child="<?php echo $menuId; ?>" ><?php echo $menuItems->getItem($menuId)->title; ?></h4>
                        <?php } ?>
                        <?php if (isset($items[$menuId])) {?>

                          <ul class="child-menu-items">
                            <?php echo create_menu($menuId); ?>
                          </ul>
                        <?php } ?>
                        <?php
                      }
                    }

                    ?>

                    <div class="modules-container"><?php if ($modId){
                      $modArray = explode(',',$modId);
                      foreach ($modArray as $mod_id)
                      {
                        $modules = getModuleNameId($mod_id);

                        if ($modules) {
                          $module = $modules[0];
                          ?>
                          <div class='draggable-module' data-mod_id="<?php echo $module->id; ?>"><?php echo $module->title; ?><i class="fa fa-remove"></i><i class="fa fa-arrows"></i></div>
                          <?php
                        }
                      }
                    }?></div>

                  </div>
                </div>

              <?php } ?>

            </div>
          </div>

          <?php
        }
      }
      else if($no_child === true)
      {
        echo '<div class="menu-section">';
        echo '<span class="row-move"><i class="fa fa-bars"></i></span>';
        echo '<div class="spmenu sp-row">';
        echo '<div class="column sp-col-md-12" data-column="12">';
        echo '<div class="column-items-wrap">';
        echo '<h4 style="display:none" data-current_child="'.$current_menu_id.'" >'.$menuItems->getItem($current_menu_id)->title.'</h4>';
        echo '<ul class="child-menu-items">';

        foreach ($item as $key => $id)
        {
          echo '<li>'.$menuItems->getItem($id)->title.'</li>';
        }
        echo '</ul>';
        echo '<div class="modules-container">';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
      }
      else
      {
        echo '<div class="menu-section">';
        echo '<span class="row-move"><i class="fa fa-bars"></i></span>';
        echo '<div class="spmenu sp-row">';

        $columnNumber = 12 / $count;
        foreach ($item_array as $key => $item_array)
        {
          echo '<div class="column sp-col-md-'.$columnNumber.'" data-column="'.$columnNumber.'">';
          echo '<div class="column-items-wrap">';

          foreach ($item_array as $key => $item)
          {
            $id = $item[0];
            echo '<h4 data-current_child="'.$id.'" >'.$menuItems->getItem($id)->title.'</h4>';

            if ($item[1])
            {
              echo '<ul class="child-menu-items">';
              echo create_menu($id);
              echo '</ul>';
            }

          }

          echo '<div class="modules-container"></div>';
          echo '</div>';
          echo '</div>';
        }

        echo '</div>';
        echo '</div>';
      } ?>

    </div>
  </div>
</div>

<div class="sp-modal" id="layout-modal" tabindex="-1" role="dialog" aria-labelledby="modal-label" aria-hidden="true">
  <div class="sp-modal-dialog">
    <div class="sp-modal-content">
      <div class="sp-modal-header">
        <button type="button" class="close" data-dismiss="spmodal" aria-hidden="true">&times;</button>
        <h3 class="sp-modal-title" id="modal-label"><?php echo JText::_('HELIX_MENU_CHOOSE_LAYOUT'); ?></h3>
      </div>
      <div class="sp-modal-body">
        <ul class="menu-layout-list clearfix">
          <li><a href="#" class="layout12" data-layout="12" data-design="layout12"><img src="<?php echo JURI::root(true) . '/plugins/system/helix3/assets/images/megamenu/12.png'; ?>" alt="12"></a></li>
          <li><a href="#" class="layout66" data-layout="6,6" data-design="layout66"><img src="<?php echo JURI::root(true) . '/plugins/system/helix3/assets/images/megamenu/6-6.png'; ?>" alt="6+6"></a></li>
          <li><a href="#" class="layout444" data-layout="4,4,4" data-design="layout444"><img src="<?php echo JURI::root(true) . '/plugins/system/helix3/assets/images/megamenu/4-4-4.png'; ?>" alt="4+4+4"></a></li>
          <li><a href="#" class="layout3333" data-layout="3,3,3,3" data-design="layout3333"><img src="<?php echo JURI::root(true) . '/plugins/system/helix3/assets/images/megamenu/3-3-3-3.png'; ?>" alt="3+3+3+3"></a></li>
          <li><a href="#" class="layout222222" data-layout="2,2,2,2,2,2" data-design="layout222222"><img src="<?php echo JURI::root(true) . '/plugins/system/helix3/assets/images/megamenu/2-2-2-2-2-2.png'; ?>" alt="2+2+2+2+2+2"></a></li>
          <li><a href="#" class="layout57" data-layout="5,7" data-design="layout57"><img src="<?php echo JURI::root(true) . '/plugins/system/helix3/assets/images/megamenu/5-7.png'; ?>" alt="5+7"></a></li>
          <li><a href="#" class="layout48" data-layout="4,8" data-design="layout48"><img src="<?php echo JURI::root(true) . '/plugins/system/helix3/assets/images/megamenu/4-8.png'; ?>" alt="4+8"></a></li>
          <li><a href="#" class="layout39" data-layout="3,9" data-design="layout39"><img src="<?php echo JURI::root(true) . '/plugins/system/helix3/assets/images/megamenu/3-9.png'; ?>" alt="3+9"></a></li>
          <li><a href="#" class="layout44412" data-layout="4,4,4,12" data-design="layout44412"><img src="<?php echo JURI::root(true) . '/plugins/system/helix3/assets/images/megamenu/4-4-4-12.png'; ?>" alt="4+4+4+12"></a></li>
          <li><a href="#" class="layout333312" data-layout="3,3,3,3,12" data-design="layout333312"><img src="<?php echo JURI::root(true) . '/plugins/system/helix3/assets/images/megamenu/3-3-3-3-12.png'; ?>" alt="3+3+3+3+12"></a></li>
          <li><a href="#" class="layout6612" data-layout="6,6,12" data-design="layout6612"><img src="<?php echo JURI::root(true) . '/plugins/system/helix3/assets/images/megamenu/6-6-12.png'; ?>" alt="6+6+12"></a></li>
          <li><a href="#" class="layout44466" data-layout="4,4,4,6,6" data-design="layout44466"><img src="<?php echo JURI::root(true) . '/plugins/system/helix3/assets/images/megamenu/4-4-4-6-6.png'; ?>" alt="4+4+4+6+6"></a></li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="menu-layout">

  <div class="layout-design" id="layout12">
    <div class="menu-section">
      <span class="row-move"><i class="fa fa-bars"></i></span>
      <div class="spmenu sp-row">
        <div class="column sp-col-sm-12" data-column="12">
          <div class="column-items-wrap">{0}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="layout-design" id="layout66">
    <div class="menu-section">
      <span class="row-move"><i class="fa fa-bars"></i></span>
      <div class="spmenu sp-row">
        <div class="column sp-col-sm-6" data-column="6">
          <div class="column-items-wrap">{0}</div>
        </div>
        <div class="column sp-col-sm-6" data-column="6">
          <div class="column-items-wrap">{1}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="layout-design" id="layout444">
    <div class="menu-section">
      <span class="row-move"><i class="fa fa-bars"></i></span>
      <div class="spmenu sp-row">
        <div class="column sp-col-sm-4" data-column="4">
          <div class="column-items-wrap">{0}</div>
        </div>
        <div class="column sp-col-sm-4" data-column="4">
          <div class="column-items-wrap">{1}</div>
        </div>
        <div class="column sp-col-sm-4" data-column="4">
          <div class="column-items-wrap">{2}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="layout-design" id="layout3333">
    <div class="menu-section">
      <span class="row-move"><i class="fa fa-bars"></i></span>
      <div class="spmenu sp-row">
        <div class="column sp-col-sm-3" data-column="3">
          <div class="column-items-wrap">{0}</div>
        </div>
        <div class="column sp-col-sm-3" data-column="3">
          <div class="column-items-wrap">{1}</div>
        </div>
        <div class="column sp-col-sm-3" data-column="3">
          <div class="column-items-wrap">{2}</div>
        </div>
        <div class="column sp-col-sm-3" data-column="3">
          <div class="column-items-wrap">{3}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="layout-design" id="layout222222">
    <div class="menu-section">
      <span class="row-move"><i class="fa fa-bars"></i></span>
      <div class="spmenu sp-row">
        <div class="column sp-col-sm-2" data-column="2">
          <div class="column-items-wrap">{0}</div>
        </div>
        <div class="column sp-col-sm-2" data-column="2">
          <div class="column-items-wrap">{1}</div>
        </div>
        <div class="column sp-col-sm-2" data-column="2">
          <div class="column-items-wrap">{2}</div>
        </div>
        <div class="column sp-col-sm-2" data-column="2">
          <div class="column-items-wrap">{3}</div>
        </div>
        <div class="column sp-col-sm-2" data-column="2">
          <div class="column-items-wrap">{4}</div>
        </div>
        <div class="column sp-col-sm-2" data-column="2">
          <div class="column-items-wrap">{5}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="layout-design" id="layout57">
    <div class="menu-section">
      <span class="row-move"><i class="fa fa-bars"></i></span>
      <div class="spmenu sp-row">
        <div class="column sp-col-sm-5" data-column="5">
          <div class="column-items-wrap">{0}</div>
        </div>
        <div class="column sp-col-sm-7" data-column="7">
          <div class="column-items-wrap">{1}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="layout-design" id="layout48">
    <div class="menu-section">
      <span class="row-move"><i class="fa fa-bars"></i></span>
      <div class="spmenu sp-row">
        <div class="column sp-col-sm-4" data-column="4">
          <div class="column-items-wrap">{0}</div>
        </div>
        <div class="column sp-col-sm-8" data-column="8">
          <div class="column-items-wrap">{1}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="layout-design" id="layout39">
    <div class="menu-section">
      <span class="row-move"><i class="fa fa-bars"></i></span>
      <div class="spmenu sp-row">
        <div class="column sp-col-sm-3" data-column="3">
          <div class="column-items-wrap">{0}</div>
        </div>
        <div class="column sp-col-sm-9" data-column="9">
          <div class="column-items-wrap">{1}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="layout-design" id="layout44412">
    <div class="menu-section">
      <span class="row-move"><i class="fa fa-bars"></i></span>
      <div class="spmenu sp-row">
        <div class="column sp-col-sm-4" data-column="4">
          <div class="column-items-wrap">{0}</div>
        </div>
        <div class="column sp-col-sm-4" data-column="4">
          <div class="column-items-wrap">{1}</div>
        </div>
        <div class="column sp-col-sm-4" data-column="4">
          <div class="column-items-wrap">{2}</div>
        </div>
      </div>
    </div>
    <div class="menu-section">
      <span class="row-move"><i class="fa fa-bars"></i></span>
      <div class="spmenu sp-row">
        <div class="column sp-col-sm-12" data-column="12">
          <div class="column-items-wrap">{3}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="layout-design" id="layout333312">
    <div class="menu-section">
      <span class="row-move"><i class="fa fa-bars"></i></span>
      <div class="spmenu sp-row">
        <div class="column sp-col-sm-3" data-column="3">
          <div class="column-items-wrap">{0}</div>
        </div>
        <div class="column sp-col-sm-3" data-column="3">
          <div class="column-items-wrap">{1}</div>
        </div>
        <div class="column sp-col-sm-3" data-column="3">
          <div class="column-items-wrap">{2}</div>
        </div>
        <div class="column sp-col-sm-3" data-column="3">
          <div class="column-items-wrap">{3}</div>
        </div>
      </div>
    </div>
    <div class="menu-section">
      <span class="row-move"><i class="fa fa-bars"></i></span>
      <div class="spmenu sp-row">
        <div class="column sp-col-sm-12" data-column="12">
          <div class="column-items-wrap">{4}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="layout-design" id="layout6612">
    <div class="menu-section">
      <span class="row-move"><i class="fa fa-bars"></i></span>
      <div class="spmenu sp-row">
        <div class="column sp-col-sm-6" data-column="6">
          <div class="column-items-wrap">{0}</div>
        </div>
        <div class="column sp-col-sm-6" data-column="6">
          <div class="column-items-wrap">{1}</div>
        </div>
      </div>
    </div>
    <div class="menu-section">
      <span class="row-move"><i class="fa fa-bars"></i></span>
      <div class="spmenu sp-row">
        <div class="column sp-col-sm-12" data-column="12">
          <div class="column-items-wrap">{2}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="layout-design" id="layout44466">
    <div class="menu-section">
      <span class="row-move"><i class="fa fa-bars"></i></span>
      <div class="spmenu sp-row">
        <div class="column sp-col-sm-4" data-column="4">
          <div class="column-items-wrap">{0}</div>
        </div>
        <div class="column sp-col-sm-4" data-column="4">
          <div class="column-items-wrap">{1}</div>
        </div>
        <div class="column sp-col-sm-4" data-column="4">
          <div class="column-items-wrap">{2}</div>
        </div>
      </div>
    </div>
    <div class="menu-section">
      <span class="row-move"><i class="fa fa-bars"></i></span>
      <div class="spmenu sp-row">
        <div class="column sp-col-sm-6" data-column="6">
          <div class="column-items-wrap">{3}</div>
        </div>
        <div class="column sp-col-sm-6" data-column="6">
          <div class="column-items-wrap">{4}</div>
        </div>
      </div>
    </div>
  </div>
</div>
