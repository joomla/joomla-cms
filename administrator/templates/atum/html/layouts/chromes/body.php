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

// Permission checks
$user    = Factory::getUser();
$canEdit = $user->authorise('core.edit', 'com_modules.module.' . $module->id);

$moduleTag   = $params->get('module_tag', 'div');
$bootstrapSize  = (int) $params->get('bootstrap_size', 6);
$moduleClass    = ($bootstrapSize) ? 'col-md-' . $bootstrapSize : 'col-md-12';
$headerTag      = htmlspecialchars($params->get('header_tag', 'h2'));
$moduleClassSfx = $params->get('moduleclass_sfx', '');

// Temporarily store header class in variable
$headerClass    = $params->get('header_class');
$headerClass    = ($headerClass) ? ' ' . htmlspecialchars($headerClass) : '';

if ($module->content) : ?>
	<div class="<?php echo $moduleClass; ?>">
		<<?php echo $moduleTag; ?> class="card mb-3<?php echo $moduleClassSfx; ?>">
			<?php if ($canEdit) : ?>
				<div class="module-actions">
					<a href="<?php echo Route::_('index.php?option=com_modules&task=module.edit&id=' . (int) $module->id); ?>">
						<span class="fa fa-cog"><span class="sr-only"><?php echo Text::_('JACTION_EDIT') . " " . $module->title; ?></span></span>
					</a>
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
