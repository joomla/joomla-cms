<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.core');
// Load JavaScript message titles
Text::script('ERROR');
Text::script('WARNING');
Text::script('NOTICE');
Text::script('MESSAGE');

Text::script('COM_CPANEL_UNPUBLISH_MODULE_SUCCESS');
Text::script('COM_CPANEL_UNPUBLISH_MODULE_ERROR');

HTMLHelper::_('script', 'com_cpanel/admin-cpanel-default.min.js', array('version' => 'auto', 'relative' => true));

$user = Factory::getUser();
HTMLHelper::_('script', 'com_cpanel/admin-add_module.js', ['version' => 'auto', 'relative' => true]);

// Set up the bootstrap modal that will be used for all module editors
echo HTMLHelper::_(
	'bootstrap.renderModal',
	'moduleDashboardAddModal',
	array(
		'title'       => Text::_('COM_CPANEL_ADD_MODULE_MODAL_TITLE'),
		'backdrop'    => 'static',
		'url'         => Route::_('index.php?option=com_cpanel&task=addModule&function=jSelectModuleType&position=' . $this->escape($this->position)),
		'bodyHeight'  => '70',
		'modalWidth'  => '80',
		'footer'      => '<button type="button" class="button-cancel btn btn-sm btn-danger" data-dismiss="modal" data-target="#closeBtn"><span class="fas fa-times" aria-hidden="true"></span>'
			. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
			. '<button type="button" class="button-save btn btn-sm btn-success hidden" data-target="#saveBtn"><span class="fas fa-copy" aria-hidden="true"></span>'
			. Text::_('JSAVE') . '</button>',
	)
);
?>
<div id="cpanel-modules">
	<div class="cpanel-modules <?php echo $this->position; ?>">
		<div class="card-columns">
		<?php if ($this->quickicons) :
			foreach ($this->quickicons as $iconmodule)
			{
				echo ModuleHelper::renderModule($iconmodule, array('style' => 'well'));
			}
		endif;
		foreach ($this->modules as $module)
		{
			echo ModuleHelper::renderModule($module, array('style' => 'well'));
		}
		?>
		</div>
	</div>
</div>

<?php if ($user->authorise('core.create', 'com_modules')) : ?>
<div class="row">
	<div class="col-md-6">
		<button type="button" data-toggle="modal" data-target="#moduleDashboardAddModal" class="cpanel-add-module text-center py-5 w-100 d-block">
			<div class="cpanel-add-module-icon text-center">
				<span class="fas fa-plus-square text-light mt-2"></span>
			</div>
			<span><?php echo Text::_('COM_CPANEL_ADD_DASHBOARD_MODULE'); ?></span>
		</button>
	</div>
</div>
<?php endif; ?>
