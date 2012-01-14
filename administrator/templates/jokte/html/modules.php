<?php
/** 
 * @package     Minima
 * @author      Marco Barbosa
 * @copyright   Copyright (C) 2010 Marco Barbosa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/*
 * Module chrome for rendering the box in the dashboard
 */
function modChrome_widget($module, &$params, &$attribs)
{
    if ($module->content)
    {
        ?>
        <div id="widget-<?php echo $module->id ?>" class="box expand">
            <div class="box-top">
                <span class="handle"><?php echo $module->title; ?></span>
                <!--<nav>
                    <span class="box-icon"></span>                
                    <ul>
                        <li><a class="nav-settings" href="javascript:void(0);">Settings</a></li>
                        <li><a class="nav-hide" href="javascript:void(0);">Hide</a></li>
                    </ul>
                </nav>-->
            </div>
            <div class="box-content"><?php echo $module->content; ?></div>
        </div>
        <?php
    }
}
?>
