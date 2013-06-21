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
				<a href="#"><i class="icon-folder-close">&nbsp;<?php echo $folder['name']?></i></a>
				<?php echo $this->listTree($this->level,$folder['id']);?>
					<ul class="nav nav-list">
						<?php foreach($this->listTreeFiles($folder['id']) as $file):?>
							<li>
								<a href="<?php echo JRoute::_('index.php?option=com_templates&task=source.edit&id='.$file->id);?>">
									<i class="icon-edit">&nbsp;<?php echo $file->name;?></i>
								</a>
							</li>
						<?php endforeach;?>
					</ul>
			<?php endif;?>	
		</li>
	<?php endforeach;?>
</ul>
