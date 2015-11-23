<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
JHtml::_('behavior.caption');

$theme = $this->params->get('theme', 'default');
$layout = $this->params->get('layout', 'default');
$this->item->comments = array_reverse($this->item->comments);
?>
<div id="cj-wrapper" class="activity-details<?php echo $this->pageclass_sfx;?>">
	
	<?php echo JLayoutHelper::render($layout.'.toolbar', array('params'=>$this->params, 'state'=>$this->state));?>
	<?php echo JLayoutHelper::render($layout.'.header', array('params'=>$this->params, 'state'=>$this->state));?>
	
	<div class="activities">
		<?php echo JLayoutHelper::render($layout.'.activities_list', array('activities'=>array($this->item), 'params'=>$this->params, 'pagination'=>null, 'limit'=>10, 'start'=>1));?>
	</div>
	
	<?php echo JLayoutHelper::render($layout.'.credits', array('params'=>$this->params));?>
	
	<div style="display: none">
		<input id="cjforum_pageid" value="activity" type="hidden">
	</div>
</div>
