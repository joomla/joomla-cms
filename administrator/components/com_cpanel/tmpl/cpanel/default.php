<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

$user = Factory::getUser();
?>

<div class="row">
	<?php $iconmodules = ModuleHelper::getModules('icon');
	if ($iconmodules) : ?>
		<div class="col-md-12">
			<?php
			// Display the submenu position modules
			foreach ($iconmodules as $iconmodule)
			{
				echo ModuleHelper::renderModule($iconmodule);
			}
			?>
		</div>
	<?php endif; ?>
</div>
<div class="row">
    <?php if ($this->cpanel && $this->menuitem->hasChildren()) : ?>
        <?php foreach ($this->menuitem->getChildren() as $item) : ?>
            <?php if ($item->hasChildren()) : ?>
            <h3><?php echo $item->title; ?></h3>
            <ul>
                <?php foreach ($item->getChildren() as $child) : ?>
                    <li><a href="<?php echo $child->link; ?>"><?php echo $child->title; ?></a>
                        <?php if($child->hasChildren()) : ?>
                        <ul>
		                    <?php foreach ($child->getChildren() as $subchild) : ?>
                            <li><a href="<?php echo $subchild->link; ?>"><?php echo $subchild->title; ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
	<?php
	$cols = 0;
	foreach ($this->modules as $module)
	{
		echo ModuleHelper::renderModule($module, array('style' => 'well'));
	}
	?>
</div>
