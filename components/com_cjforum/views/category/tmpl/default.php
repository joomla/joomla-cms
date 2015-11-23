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

$layout = $this->params->get('layout', 'default');
?>
<div id="cj-wrapper" class="category-details<?php echo $this->pageclass_sfx;?>">
	<?php 
	echo JLayoutHelper::render($layout.'.toolbar', array('category'=>$this->category, 'params'=>$this->params, 'state'=>$this->state));
	echo JLayoutHelper::render($layout.'.header', array('params'=>$this->params, 'state'=>$this->state));
	echo JLayoutHelper::render($layout.'.category_list', array('category'=>$this->category, 'params'=>$this->params, 'maxlevel'=>$this->params->get('maxLevel'), 'section_num'=>1));
	echo JLayoutHelper::render($layout.'.topic_list', array('category'=>$this->category, 'topics'=>$this->items, 'params'=>$this->params, 'pagination'=>$this->pagination));
	echo JLayoutHelper::render($layout.'.credits', array('params'=>$this->params));
	?>
</div>
