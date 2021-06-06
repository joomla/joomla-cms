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
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;

$app = Factory::getApplication();
$app->getDocument()
	->getWebAssetManager()
	->registerAndUseStyle('layouts.chromes.outline', 'layouts/chromes/outline.css');

$module = $displayData['module'];

// Place Modules Button
$showModuleButton = $app->input->getBool('pm') && ContentHelper::getActions('com_modules')->get('core.create');

// Redirect URL Params for Placing Modules
$menuId = $app->getMenu()->getActive();
$templateId = $app->getTemplate(true)->id;
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
				<a class="btn btn-sm btn-info" href="administrator/index.php?option=com_modules&task=module.setPosition&position=<?php echo $module->position;?>&menu=<?php echo $menuId->id;?>&template=<?php echo $templateId;?>">
					<?php echo Text::sprintf('JGLOBAL_PREVIEW_ADD_MODULE', $module->position); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
	<div class="mod-preview-wrapper">
		<?php echo $module->content; ?>
	</div>
</div>
