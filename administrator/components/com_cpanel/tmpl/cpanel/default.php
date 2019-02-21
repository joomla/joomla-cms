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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$user = Factory::getUser();
?>

<?php if ($this->quickicons) : ?>
<div class="row">
	<div class="col-md-12">
    	<?php
		// Display the icon position modules
		foreach ($this->quickicons as $iconmodule)
		{
			echo ModuleHelper::renderModule($iconmodule);
		}
		?>
	</div>
</div>
<?php endif; ?>
<div class="row">
	<?php
	foreach ($this->modules as $module)
	{
		echo ModuleHelper::renderModule($module, array('style' => 'well'));
	}
	?>
	<?php if ($user->authorise('core.create', 'com_modules')) : ?>
	<div class="col-md-6">
		<a href="<?php echo Route::_('index.php?option=com_cpanel&task=addModule&position=' . $this->escape($this->position)); ?>" class="cpanel-add-module text-center py-5 w-100 d-block">
			<div class="cpanel-add-module-icon text-center">
				<span class="fa fa-plus-square-o text-light mt-2"></span>
			</div>
			<span><?php echo Text::_('COM_CPANEL_ADD_DASHBOARD_MODULE'); ?></span>
		</a>
	</div>
	<?php endif; ?>
</div>
