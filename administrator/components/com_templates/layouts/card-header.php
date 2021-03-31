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
		<?php if (!$item->inheritable || !$item->parent) : ?>
			<small class="small text-muted ms-2"><?php echo Text::_('COM_TEMPLATES_LEGACY'); ?></small>
		<?php endif; ?>
		<div class="dropdown">
			<button class="btn btn-link dropdown-toggle" type="button" id="template-info-<?php echo $item->id; ?>" data-bs-toggle="dropdown" aria-expanded="false">
				<span class="icon-info-circle" aria-hidden="true"></span>
				<span class="visually-hidden"><?php echo Text::_('COM_TEMPLATES_PREVIEW'); ?></span>
			</button>
			<ul class="dropdown-menu" aria-labelledby="template-info-<?php echo $item->id; ?>">
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
