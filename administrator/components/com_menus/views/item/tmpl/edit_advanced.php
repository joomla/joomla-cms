<?php
/**
 * @version		$Id: edit_options.php 12812 2009-09-22 03:58:25Z dextercowley $
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
			
	
								
	<?php echo $this->form->getLabel('published'); ?>
	<?php echo $this->form->getInput('published'); ?>
	
	<?php echo $this->form->getLabel('access'); ?>
	<?php echo $this->form->getInput('access'); ?>
									
	<?php echo $this->form->getLabel('menutype'); ?>
	<?php echo $this->form->getInput('menutype'); ?>
	
	<?php echo $this->form->getLabel('parent_id'); ?>
	<?php echo $this->form->getInput('parent_id'); ?>
				
	<?php if ($this->item->type !=='url'){ ?>
		<?php echo $this->form->getLabel('link'); ?>
		<?php echo $this->form->getInput('link'); ?>
	<?php } ?>

	<?php echo $this->form->getLabel('browserNav'); ?>
	<?php echo $this->form->getInput('browserNav'); ?>
	
	<?php echo $this->form->getLabel('home'); ?>
	<?php echo $this->form->getInput('home'); ?>
				
	<?php echo $this->form->getLabel('template_id'); ?>
	<?php echo $this->form->getInput('template_id'); ?>

	<div class="paramrow" />
		
	</div>							

