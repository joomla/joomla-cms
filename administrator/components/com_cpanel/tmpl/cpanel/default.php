<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;

$user = Factory::getUser();
?>

<div class="row">
	<?php $iconmodules = ModuleHelper::getModules('icon');
	if ($iconmodules) : ?>
        <?php HTMLHelper::_('bootstrap.framework'); ?>
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
	<?php
	$cols = 0;
	foreach ($this->modules as $module)
	{
		echo ModuleHelper::renderModule($module, array('style' => 'well'));
	}
	?>
</div>
