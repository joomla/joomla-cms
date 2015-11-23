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
<div id="cj-wrapper" class="profile-activities<?php echo $this->pageclass_sfx;?>">
	<?php echo JLayoutHelper::render($layout.'.activities_list', array('activities'=>$this->items, 'params'=>$this->params, 'pagination'=>$this->pagination, 'limit'=>3, 'start'=>0));?>
</div>