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

<?php if ($this->quickicons) : ?>
<div class="row">
	<div class="col-md-12">
    	<?php
		// Display the submenu position modules
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
</div>
