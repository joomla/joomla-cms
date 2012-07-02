<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
$clientId	= $this->state->get('filter.client_id');
$state		= $this->state->get('filter.state');
$this->template		=  ModulesHelper::getTemplates($clientId, $state);
$this->items			=	ModulesHelper::getPositions($clientId);
?>
<select id="jform_position" name="jform[position]">
	<?php foreach ($this->template as $value=>$templates) : ?>
	<optgroup label="<?php echo JText::_($value);?>">
	  <?php foreach ($this->items as $templates) : ?>
		  	<?php foreach ($templates as $template):?>
	    		<option value="<?php echo $template;?>"><?php echo $template;?></option>
	    <?php endforeach; ?>
	  <?php endforeach; ?>
  	 </optgroup>
  	 <?php endforeach; ?>
</select>
