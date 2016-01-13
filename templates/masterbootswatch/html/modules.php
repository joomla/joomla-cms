<?php 
	/*------------------------------------------------------------------------
# author    Gonzalo Suez
# copyright Copyright (C) 2013 gsuez.cl. All rights reserved.
# @license  http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Website   http://www.gsuez.cl
-------------------------------------------------------------------------*/
	defined('_JEXEC') or die;
	/**
 * This is a file to add template specific chrome to module rendering.  To use it you would
 * set the style attribute for the given module(s) include in your template to use the style
 * for each given modChrome function.
 *
 * eg.  To render a module mod_test in the submenu style, you would use the following include:
 * <jdoc:include type="module" name="test" style="submenu" />
 *
 * This gives template designers ultimate control over how modules are rendered.
 *
 * NOTICE: All chrome wrapping methods should be named: modChrome_{STYLE} and take the same
 * two arguments.
 *//*
 * Module chrome for rendering the module in a submenu
 */	function modChrome_block($module, &$params, &$attribs){
		if (!empty ($module->content)) :
		?>
           <div class="block <?php  if ($params->get('moduleclass_sfx')!='') : ?><?php  echo $params->get('moduleclass_sfx'); ?><?php  endif; ?>">
           	<div class="moduletable">           	
	           	<?php  if ($module->showtitle != 0) : ?>
			<div class="module-title">
	                		<h3 class="title"><?php  echo $module->title; ?></h3>
			</div>
	                	<?php  endif; ?>
	                	<div class="module-content">
	                		<?php  echo $module->content; ?>
	                	</div>
              </div>             	
           </div>
	<?php 
		endif;
	}
	function modChrome_panel($module, &$params, &$attribs){
		if (!empty ($module->content)) :
		?>
           <div class="block <?php  if ($params->get('moduleclass_sfx')!='') : ?><?php  echo $params->get('moduleclass_sfx'); ?><?php  endif; ?>">
           	<div class="moduletable panel -<?php  if ($params->get('moduleclass_sfx')!='') : ?><?php  echo $params->get('moduleclass_sfx'); ?><?php  endif; ?>">
	           	<?php  if ($module->showtitle != 0) : ?>
			<div class="panel-heading">
	                		<h3 class="panel-title"><?php  echo $module->title; ?></h3>
			</div>
	                	<?php  endif; ?>
	                	<div class="panel-body">
	                		<?php  echo $module->content; ?>
	                	</div>
              </div>             	
           </div>
	<?php 
		endif;
	}
	?>