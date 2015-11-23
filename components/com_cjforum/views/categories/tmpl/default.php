<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

$avatar = $this->params->get('avatar_component', 'cjforum');
$profile = $this->params->get('profile_component', 'cjforum');
$layout = $this->params->get('layout', 'default');
?>

<div id="cj-wrapper" class="category-list<?php echo $this->pageclass_sfx;?>">
	<?php 
	echo JLayoutHelper::render($layout.'.toolbar', array('params'=>$this->params, 'state'=>$this->state));
	echo JLayoutHelper::render($layout.'.header', array('params'=>$this->params, 'state'=>$this->state));
		
	if (count($this->items[$this->parent->id]) > 0 && $this->maxLevelcat != 0)
	{ 
		$num = 1;
		foreach($this->items[$this->parent->id] as $id => $item)
		{
			echo JLayoutHelper::render($layout.'.category_list', array('category'=>$item, 'params'=>$this->params, 'maxlevel'=>$this->maxLevelcat, 'section_num'=>$num));
			$num++;
		}
	}
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