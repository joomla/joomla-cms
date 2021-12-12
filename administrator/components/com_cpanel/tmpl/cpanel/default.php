<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// Load JavaScript message titles
Text::script('ERROR');
Text::script('WARNING');
Text::script('NOTICE');
Text::script('MESSAGE');

Text::script('COM_CPANEL_UNPUBLISH_MODULE_SUCCESS');
Text::script('COM_CPANEL_UNPUBLISH_MODULE_ERROR');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('com_cpanel.admin-cpanel')
	->useScript('com_cpanel.admin-addmodule');

$user = Factory::getUser();

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
		'footer'      => '<button type="button" class="button-cancel btn btn-danger" data-bs-dismiss="modal" data-bs-target="#closeBtn">'
			. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
			. '<button type="button" id="btnModalSaveAndClose" class="button-save btn btn-success hidden" data-bs-target="#saveBtn">'
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
		<?php if ($user->authorise('core.manage', 'com_modules')) : ?>
			<div class="module-wrapper">
				<div class="card">
					<button type="button" data-bs-toggle="modal" data-bs-target="#moduleDashboardAddModal" class="cpanel-add-module">
						<div class="cpanel-add-module-icon">
							<span class="icon-plus-square" aria-hidden="true"></span>
						</div>
						<span><?php echo Text::_('COM_CPANEL_ADD_DASHBOARD_MODULE'); ?></span>
					</button>
				</div>
			</div>
		<?php endif; ?>
		</div>
	</div>
</div>
