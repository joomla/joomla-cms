<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$module  = $displayData['module'];
$params  = $displayData['params'];

if ($module->content) :
	$id = $module->id;

	// Permission checks
	$user    = Factory::getUser();
	$canEdit = $user->authorise('core.edit', 'com_modules.module.' . $id);

	$moduleTag   = $params->get('module_tag', 'div');
	$bootstrapSize  = (int) $params->get('bootstrap_size', 6);
	$moduleClass    = ($bootstrapSize) ? 'col-md-' . $bootstrapSize : 'col-md-12';
	$headerTag      = htmlspecialchars($params->get('header_tag', 'h2'));
	$moduleClassSfx = $params->get('moduleclass_sfx', '');

	// Temporarily store header class in variable
	$headerClass = $params->get('header_class');
	$headerClass = ($headerClass) ? ' ' . htmlspecialchars($headerClass) : '';
	?>
	<div class="<?php echo $moduleClass; ?> module-wrapper">
		<<?php echo $moduleTag; ?> class="card mb-3<?php echo $moduleClassSfx; ?>">
			<?php if ($canEdit) : ?>
				<?php $uri = Uri::getInstance(); ?>
				<?php $url = Route::_('index.php?option=com_modules&task=module.edit&id=' . $id . '&return=' . base64_encode($uri)); ?>

				<?php $dropdownPosition = Factory::getLanguage()->isRTL() ? 'left' : 'right'; ?>

				<div class="module-actions dropdown">
					<a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="dropdownMenuButton-<?php echo $id; ?>">
								<span class="fa fa-cog"><span class="sr-only">
									<?php echo Text::_('JACTION_EDIT') . ' ' . $module->title; ?>
								</span></span>
					</a>
					<div class="dropdown-menu dropdown-menu-<?php echo $dropdownPosition; ?>" aria-labelledby="dropdownMenuButton-<?php echo $id; ?>">
						<a class="dropdown-item" href="<?php echo $url; ?>"><?php echo Text::_('JACTION_EDIT'); ?></a>
						<a class="dropdown-item unpublish-module" data-module-id="<?php echo $id; ?>"><?php echo Text::_('JACTION_UNPUBLISH'); ?></a>
					</div>
				</div>
			<?php endif; ?>
			<?php if ($module->showtitle) : ?>
				<<?php echo $headerTag; ?> class="card-header<?php echo $headerClass; ?>"><?php echo $module->title; ?></<?php echo $headerTag; ?>>
			<?php endif; ?>
			<div class="module-body">
				<?php echo $module->content; ?>
			</div>
		</<?php echo $moduleTag; ?>>
	</div>
<?php endif; ?>
