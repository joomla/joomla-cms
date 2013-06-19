<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<ul class="nav nav-list">
	<?php foreach($this->tree as $folder):?>
		<li>
			<?php if($folder['parent'] == $this->level):?>
				<a href="index.php?option=com_templates&task=template.files&folderid=<?php echo $folder['id'];?>"><i class="icon-folder-2">&nbsp;<?php echo $folder['name']?></i></a>
				<?php echo $this->listTree($this->level,$folder['id']);?>
			<?php endif;?>	
		</li>
	<?php endforeach;?>
</ul>
