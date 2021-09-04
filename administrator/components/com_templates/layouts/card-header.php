<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

extract($displayData);
?>

<div class="card-header d-flex flex-row align-items-center">
	<h4 class="card-title flex-grow-1 align-self-center">
		<?php if ($canEdit) : ?>
			<a class="ms-1" href="<?php echo Route::_('index.php?option=com_templates&task=template.edit&id=' . (int) $item->extensionId); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $item->templateName; ?>">
				<?php echo ucfirst($this->escape($item->templateName)); ?>
			</a>
		<?php else : ?>
			<?php echo ucfirst($this->escape($item->templateName)); ?>
		<?php endif; ?>
		<?php if ($version = $item->version) : ?>
			<small class="template-version small text-muted ms-1">v<?php echo $this->escape($version); ?></small>
		<?php endif; ?>
	</h4>
	<div class="card-header-right">
		<?php if (!(bool) $item->inheritable || $item->parent !== '') : ?>
			<small class="small text-muted ms-2"><?php echo Text::_('COM_TEMPLATES_LEGACY'); ?></small>
		<?php endif; ?>

		<?php if ($canCreate  || $canDelete) : ?>
			<div class="dropdown d-inline-block">
				<button class="btn btn-link dropdown-toggle" type="button" id="template-actions-<?php echo (int) $item->extensionId; ?>" data-bs-toggle="dropdown" aria-expanded="false">
					<span class="icon-cog" aria-hidden="true"></span>
					<span class="visually-hidden"><?php echo Text::_('COM_TEMPLATES_EDIT'); ?></span>
				</button>
				<ul class="dropdown-menu" aria-labelledby="template-actions-<?php echo (int) $item->extensionId; ?>">
					<li>
						<a href="<?php echo Route::_('index.php?option=com_templates&view=template&id=' . (int) $item->extensionId . '&' . Session::getFormToken() . '=1'); ?>" class="btn btn-link">
							<span class="icon-pencil" aria-hidden="true"></span>
							<span class="ms-1"><?php echo Text::_('COM_TEMPLATE_EDIT_FILES'); ?></span>
						</a>
					</li>
					<?php if ($canCreate) : ?>
						<li>
							<button class="js-action-exec btn btn-link" type="button" data-task="templates.forkTemplate" data-item="<?php echo (int) $item->extensionId; ?>">
								<span class="icon-copy" aria-hidden="true"></span>
								<span class="ms-1"><?php echo Text::_('COM_TEMPLATES_FORK'); ?></span>
							</button>
						</li>
					<?php endif; ?>
					<?php if ($canCreate) : ?>
						<li>
							<button class="js-action-exec-modal btn btn-link" type="button" data-task="templates.getExtensionLayouts" data-client="1" data-item="<?php echo (int) $item->extensionId; ?>" data-token="<?php echo Session::getFormToken(); ?>">
								<span class="icon-copy" aria-hidden="true"></span>
								<span class="ms-1"><?php echo Text::_('COM_TEMPLATES_NEW_OVERRIDE'); ?></span>
							</button>
						</li>
					<?php endif; ?>
					<?php if ($canDelete) : ?>
						<li>
							<button class="js-action-exec-uninstall btn btn-link" type="button" data-task="templates.uninstallTemplate" data-item="<?php echo (int) $item->extensionId; ?>" data-client="<?php echo (int) $item->clientId; ?>" data-name="<?php echo $this->escape($item->templateName); ?>">
								<span class="icon-trash" aria-hidden="true"></span>
								<span class="ms-1"><?php echo Text::_('COM_TEMPLATES_UNINSTALL'); ?></span>
							</button>
						</li>
					<?php endif; ?>
				</ul>
			</div>
		<?php endif; ?>

		<div class="dropdown d-inline-block">
			<button class="btn btn-link dropdown-toggle" type="button" id="template-info-<?php echo (int) $item->extensionId; ?>" data-bs-toggle="dropdown" aria-expanded="false">
				<span class="icon-info-circle" aria-hidden="true"></span>
				<span class="visually-hidden"><?php echo Text::_('COM_TEMPLATES_PREVIEW'); ?></span>
			</button>
			<ul class="dropdown-menu" aria-labelledby="template-info-<?php echo (int) $item->extensionId; ?>">
				<li class="list-group-item">
					<span class="text-muted"><?php echo Text::_('COM_TEMPLATES_CREATED'); ?>: </span> <?php echo $this->escape($item->creationDate); ?>
				</li>

				<?php if ($author = $item->author) : ?>
					<li class="list-group-item">
						<span class="text-muted"><?php echo Text::_('COM_TEMPLATES_AUTHOR'); ?>: </span><?php echo $this->escape($author); ?>
					</li>
				<?php endif; ?>

				<?php if ($email = $item->authorEmail) : ?>
					<li class="list-group-item">
						<span class="text-muted"><?php echo Text::_('COM_TEMPLATES_AUTHOR_EMAIL'); ?>: </span>
						<a href="mailto: <?php echo $this->escape($email); ?>">
							<?php echo $this->escape($email); ?>
						</a>
					</li>
				<?php endif; ?>

				<?php if ($email = $item->authorUrl) : ?>
					<li class="list-group-item">
						<span class="text-muted"><?php echo Text::_('COM_TEMPLATES_AUTHOR_EMAIL'); ?>: </span> <?php echo $this->escape($email); ?>
					</li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
</div>
