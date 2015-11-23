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

$layout = $this->params->get('layout');
?>
<div id="cj-wrapper" class="category-details<?php echo $this->pageclass_sfx;?>">
	<?php 
	echo JLayoutHelper::render($layout.'.toolbar', array('params'=>$this->params, 'state'=>$this->state));
	echo JLayoutHelper::render($layout.'.header', array('params'=>$this->params, 'state'=>$this->state));
	echo JLayoutHelper::render($layout.'.topic_list', array('topics'=>$this->items, 'state'=>$this->state, 
			'params'=>$this->params, 'pagination'=>$this->pagination, 'heading'=>$this->heading, 'viewName'=>$this->viewName));
	?>
	<div class="panel panel-default">
		<div class="panel-body">
		<?php
		echo JLayoutHelper::render($layout.'.online_users', array('params'=>$this->params, 'state'=>$this->state));
		echo JLayoutHelper::render($layout.'.footer', array('params'=>$this->params, 'state'=>$this->state));
		?>
		</div>
	</div>
	<?php echo JLayoutHelper::render($layout.'.credits', array('params'=>$this->params));?>
</div>
