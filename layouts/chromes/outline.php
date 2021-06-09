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
	->registerAndUseStyle('layouts.chromes.outline', 'layouts/chromes/outline.css');

$module = $displayData['module'];

// Place Modules Button
$showModuleButton = $app->input->getBool('pm');

// Attributes of Select Position Tag for Placing Modules
$menuId = $app->getMenu()->getActive();
$action = 'href="administrator/index.php?option=com_modules&task=module.selectPosition&position=' . $module->position . "&menu=" . $menuId->id . '"';
$tag = "a";

// True for Backend Edit Module Position's Modal Iframe
if ($showModuleButton && $app->input->getBool('edit'))
{
	$tag = "button";
	$action = 'onclick="selectPosition(' . "'" . $module->position . "'" . ')"';
}
?>
<div class="mod-preview">
	<div class="mod-preview-info">
		<div class="mod-preview-position">
			<?php echo Text::sprintf('JGLOBAL_PREVIEW_POSITION', $module->position); ?>
		</div>
		<div class="mod-preview-style">
			<?php echo Text::sprintf('JGLOBAL_PREVIEW_STYLE', $module->style); ?>
		</div>
		<?php if ($showModuleButton): ?>
			<div class="mod-preview-position">
				<<?php echo $tag; ?> class="btn btn-sm btn-info" <?php echo $action; ?>>
					<?php echo Text::sprintf('JGLOBAL_PREVIEW_PLACE_MODULE'); ?>
					<span class="visually-hidden">
						<?php echo Text::sprintf('JGLOBAL_PREVIEW_PLACE_MODULE_POSITION', $module->position); ?>
					</span>
				</<?php echo $tag; ?>>
			</div>
		<?php endif; ?>
	</div>
	<div class="mod-preview-wrapper">
		<?php echo $module->content; ?>
	</div>
</div>
