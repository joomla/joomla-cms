<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$module  = $displayData['module'];
$params  = $displayData['params'];
$attribs = $displayData['attribs'];

if ((string) $module->content === '') {
    return;
}

$id = $module->id;

// Permission checks
$user      = Factory::getUser();
$canEdit   = $user->authorise('core.edit', 'com_modules.module.' . $id) && $user->authorise('core.manage', 'com_modules');
$canChange = $user->authorise('core.edit.state', 'com_modules.module.' . $id) && $user->authorise('core.manage', 'com_modules');

$moduleTag      = $params->get('module_tag', 'div');
$bootstrapSize  = (int) $params->get('bootstrap_size', 12);
$moduleClass    = $bootstrapSize ? 'col-md-' . $bootstrapSize : 'col-md-12';
$headerTag      = htmlspecialchars($params->get('header_tag', 'h2'), ENT_QUOTES, 'UTF-8');
$moduleClassSfx = $params->get('moduleclass_sfx', '');

// Add class from attributes if any
if (!empty($attribs['class'])) {
    $moduleClass .= ' ' . htmlspecialchars($attribs['class'], ENT_QUOTES, 'UTF-8');
}

// Temporarily store header class in variable
$headerClass = $params->get('header_class') ? ' class="' . htmlspecialchars($params->get('header_class'), ENT_QUOTES, 'UTF-8') . '"' : '';

// Get the module icon
$headerIcon = $params->get('header_icon') ? '<span class="' . htmlspecialchars($params->get('header_icon'), ENT_QUOTES, 'UTF-8') . '" aria-hidden="true"></span>' : '';

?>
<div class="<?php echo $moduleClass; ?> module-wrapper">
    <<?php echo $moduleTag; ?> class="card mb-3 <?php echo $moduleClassSfx; ?>">
        <?php if ($canEdit || $canChange || $headerIcon || $module->showtitle) : ?>
            <div class="card-header">
                <?php if ($canEdit || $canChange) : ?>
                    <?php $dropdownPosition = Factory::getLanguage()->isRtl() ? 'start' : 'end'; ?>
                    <div class="module-actions dropdown">
                        <button type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-haspopup="true" aria-expanded="false" class="btn" id="dropdownMenuButton-<?php echo $id; ?>">
                            <span class="icon-cogs" aria-hidden="true"></span>
                            <span class="visually-hidden"><?php echo Text::sprintf('JACTION_EDIT_MODULE', $module->title); ?></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-<?php echo $dropdownPosition; ?>" aria-labelledby="dropdownMenuButton-<?php echo $id; ?>">
                            <?php if ($canEdit) : ?>
                                <?php $uri = Uri::getInstance(); ?>
                                <?php $url = Route::_('index.php?option=com_modules&task=module.edit&id=' . $id . '&return=' . base64_encode($uri)); ?>
                                <a class="dropdown-item" href="<?php echo $url; ?>"><?php echo Text::_('JACTION_EDIT'); ?></a>
                            <?php endif; ?>
                            <?php if ($canChange) : ?>
                                <button type="button" class="dropdown-item unpublish-module" data-module-id="<?php echo $id; ?>"><?php echo Text::_('JACTION_UNPUBLISH'); ?></button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($module->showtitle) : ?>
                    <<?php echo $headerTag; ?><?php echo $headerClass; ?>>
                        <?php echo $headerIcon; ?>
                        <?php echo htmlspecialchars($module->title, ENT_QUOTES, 'UTF-8'); ?>
                    </<?php echo $headerTag; ?>>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="card-body">
            <?php echo $module->content; ?>
        </div>
    </<?php echo $moduleTag; ?>>
</div>
