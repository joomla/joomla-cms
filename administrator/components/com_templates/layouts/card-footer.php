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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

extract($displayData);
?>
<div class="card-footer">
	<h3>Styles:</h3>
	<?php foreach ($item->styles as $style) : ?>
	<div class="list-group d-flex">
		<div class="d-flex justify-content-between">
			<a href="<?php echo Route::_('index.php?option=com_templates&task=style.edit&id=' . (int) $style->id); ?>" class="btn btn-link">
				<?php echo $style->title; ?>
			</a>

			<div>
			<?php if ($style->home == '0') : ?>
				<button class="js-action-exec btn btn-link" type="button" data-task="templates.setDefault" data-item="<?php echo (int) $style->id; ?>">
					<span class="icon-star me-2" aria-hidden="true"></span>
					<span class="visually-hidden"><?php echo Text::_('COM_TEMPLATES_STYLE_IS_DEFAULT'); ?></span>
				</button>
			<?php elseif ($style->home == '1'):?>
				<strong class="text-success btn btn-transparent">
					<span class="icon-star icon-md me-2" aria-hidden="true"></span>
					<span class="visually-hidden"><?php echo Text::_('COM_TEMPLATES_STYLE_IS_DEFAULT'); ?></span>
				</strong>
			<?php elseif ($canChange):?>
				<a href="<?php echo Route::_('index.php?option=com_templates&task=templates.unsetDefault&cid[]=' . $style->id . '&' . Session::getFormToken() . '=1'); ?>" class="btn btn-link">
					<?php echo Text::sprintf('COM_TEMPLATES_GRID_UNSET_LANGUAGE', $style->language_title); ?>
				</a>
			<?php else : ?>
			<span class="btn btn-transparent">
				<?php echo Text::sprintf('COM_TEMPLATES_STYLES_PAGES_ALL_LANGUAGE', $this->escape($style->language_title)); ?>
			</span>
			<?php endif; ?>
			<?php if (((int) $style->home !== 0 && (int) $style->home !== 1) && $style->image):?>
				<small class="ms-2">
					<?php echo HTMLHelper::_('image', 'mod_languages/' . $style->image . '.gif', $style->language_title, array('title' => Text::sprintf('COM_TEMPLATES_STYLES_PAGES_ALL_LANGUAGE', $style->language_title)), true); ?>
				</small>
			<?php elseif ((int) $style->assigned > 0) :  // $style->assigned > 0?>
				<small class="ms-2">
					<span class="icon-check-circle" aria-hidden="true"></span>
					<span class="visually-hidden"><?php echo Text::sprintf('COM_TEMPLATES_STYLES_PAGES_SELECTED', $style->assigned); ?></span>
				</small>
			<?php endif; ?>

			<?php if ($clientId === 0) : // Preview ?>
				<a href="<?php echo Route::_( Uri::root() . 'index.php?tp=1&templateStyle=' . (int) $style->id); ?>" target="_blank" class="btn btn-link">
					<span class="visually-hidden"><?php echo Text::_('COM_TEMPLATES_PREVIEW'); ?></span>
				</a>
			<?php endif; ?>
			<?php if($canCreate  || $canDelete) : ?>
				<div class="dropdown d-inline-block">
					<button class="btn btn-link dropdown-toggle" type="button" id="dropdown-<?php echo $style->title; ?>" data-bs-toggle="dropdown" aria-expanded="false">
						<?php echo Text::_('JTOOLBAR_CHANGE_STATUS'); ?>
					</button>
					<ul class="dropdown-menu" aria-labelledby="dropdown-<?php echo $style->title; ?>">
						<li>
							<a href="<?php echo Route::_('index.php?option=com_templates&task=style.edit&id=' . (int) $style->id . '&' . Session::getFormToken() . '=1'); ?>" class="btn btn-link">
								<span class="icon-pencil" aria-hidden="true"></span>
								<span class="ms-1"><?php echo Text::_('COM_TEMPLATES_STYLE_EDIT'); ?></span>
							</a>
						</li>
						<?php if($canCreate) : ?>
						<li>

							<button class="js-action-exec-new btn btn-link"
									type="button"
									data-task="<?php $task = $style-inheritable === 1 && $style->parent === '' ? 'templates.createChild' : 'templates.duplicate' ?>"
									data-item="<?php echo (int) $style->id; ?>">
								<span class="icon-copy" aria-hidden="true"></span>
								<span class="ms-1"><?php echo Text::_('COM_TEMPLATES_STYLE_DUPLICATE'); ?></span>
							</button>
						</li>
						<?php endif; ?>
						<?php if($canDelete) : ?>
						<li>
							<button class="js-action-exec btn btn-link" type="button" data-task="templates.delete" data-item="<?php echo (int) $style->id; ?>">
								<span class="icon-trash" aria-hidden="true"></span>
								<span class="ms-1"><?php echo Text::_('COM_TEMPLATES_STYLE_DELETE'); ?></span>
							</button>
						</li>
						<?php endif; ?>
					</ul>
				</div>
			<?php endif; ?>
			</div>
		</div>
	</div>
	<?php endforeach; ?>

</div>
