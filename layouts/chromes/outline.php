<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$app = Factory::getApplication();
$app->getDocument()
	->getWebAssetManager()
	->registerAndUseStyle('layouts.chromes.outline', 'layouts/chromes/outline.css')
	->registerAndUseScript('layouts.chromes.outline', 'layouts/chromes/outline.js');

$module = $displayData['module'];

// Place Modules Button
$showPlaceModuleButton = $app->input->getBool('pm') && ($app->getName() == 'site');

// Attributes of Select Position Tag for Placing Modules
$menuId = $app->getMenu()->getActive();
$action = isset($menuId->id) ? 'href="administrator/index.php?option=com_modules&task=module.selectPosition&position=' . $module->position . "&menu=" . $menuId->id . '"' : '';
$tag = "a";

// True for Backend Edit Module Position's Modal Iframe
if ($showPlaceModuleButton && $app->input->getBool('edit'))
{
	$tag = "button";
	$action = 'data-position="' . $module->position . '"';
}
?>
<div class="mod-preview">
	<div class="mod-preview-info">
		<div class="mod-preview-position">
			<?php echo Text::sprintf('JGLOBAL_PREVIEW_POSITION', $module->position); ?>
		</div>
		<?php if ($showPlaceModuleButton): ?>
			<div class="mod-preview-position">
				<<?php echo $tag; ?> class="btn btn-sm btn-info jmod-position-select" <?php echo $action; ?>>
					<?php echo Text::sprintf('JGLOBAL_PREVIEW_PLACE_MODULE'); ?>
					<span class="visually-hidden">
						<?php echo Text::sprintf('JGLOBAL_PREVIEW_PLACE_MODULE_POSITION', $module->position); ?>
					</span>
				</<?php echo $tag; ?>>
			</div>
		<?php else: ?>
			<div class="mod-preview-style">
				<?php echo Text::sprintf('JGLOBAL_PREVIEW_STYLE', $module->style); ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="mod-preview-wrapper">
		<?php echo $module->content; ?>
	</div>
</div>
