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
<div class="card-footer d-flex align-items-center p-4">
	<div class="list-group">
		<div class="list-group-item">
			<div class="d-flex">
				<div class="mr-auto">
					<?php if($canCreate  || $canDelete) : ?>
						<?php if($canCreate) : ?>
							<a class="btn btn-link" href="javascript:void(0);" onclick="return Joomla.listItemTask('cb<?php echo $i; ?>', 'styles.duplicate')">
								<span class="icon-copy" aria-hidden="true" title="<?php echo Text::_('COM_TEMPLATES_STYLE_DUPLICATE'); ?>"></span>
							</a>
						<?php endif; ?>

						<?php if($canDelete) : ?>
							<a class="btn btn-link" href="javascript:void(0);" onclick="return Joomla.listItemTask('cb<?php echo $i; ?>', 'styles.delete')">
								<span class="icon-trash" aria-hidden="true" title="<?php echo Text::_('COM_TEMPLATES_STYLE_DELETE'); ?>"></span>
							</a>
						<?php endif; ?>

					<?php endif; ?>
					<?php if ($clientId === 0) : ?>
						<a href="<?php echo Route::_( Uri::root() . 'index.php?tp=1&templateStyle=' . (int) $item->id); ?>" target="_blank" class="card-header-icon" title="<?php echo Text::_('COM_TEMPLATES_PREVIEW'); ?>">
							<span class="icon-eye-open icon-md" area-hidden="true"></span>
						</a>
					<?php endif; ?>
				</div>

				<div>
					<a href="<?php echo Route::_('index.php?option=com_templates&view=template&id=' . (int) $item->e_id); ?>" class="btn btn-link">
						<?php echo Text::_('COM_TEMPLATES_TEMPLATE_EDIT_FILES'); ?>
					</a>
					<a href="<?php echo Route::_('index.php?option=com_templates&task=style.edit&id=' . (int) $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $item->title; ?>" class="btn btn-link ml-3">
						<span class="icon-options-cog" area-hidden="true"></span> <?php echo Text::_('COM_TEMPLATES_STYLE_EDIT'); ?>
					</a>
				</div>
			</div>
		</div>


		<?php if ($item->home == '0') : ?>
			<a href="javascript:void(0);" onclick="return Joomla.listItemTask('cb<?php echo $i; ?>','styles.setDefault')" class="btn btn-secondary btn-block">
				<span class="icon-star" area-hidden="true"></span> <?php echo Text::_('COM_TEMPLATES_STYLE_SET_DEFAULT'); ?>
			</a>
		<?php elseif ($item->home == '1'):?>
			<strong class="text-success btn btn-transparent">
				<span class="icon-check-circle icon-md mr-2" area-hidden="true"></span> <?php echo Text::_('COM_TEMPLATES_STYLE_IS_DEFAULT'); ?>
			</strong>
		<?php elseif ($canChange):?>
			<a href="<?php echo Route::_('index.php?option=com_templates&task=styles.unsetDefault&cid[]=' . $item->id . '&' . Session::getFormToken() . '=1'); ?>" class="btn btn-secondary btn-block">
				<?php echo Text::sprintf('COM_TEMPLATES_GRID_UNSET_LANGUAGE', $item->language_title); ?>
			</a>
		<?php else : ?>
			<span class="btn btn-transparent">
					<?php echo Text::sprintf('COM_TEMPLATES_STYLES_PAGES_ALL_LANGUAGE', $this->escape($item->language_title)); ?>
				</span>
		<?php endif; ?>
		<?php if (((int) $item->home !== 0 && (int) $item->home !== 1) && $item->image):?>
			<small class="ml-2">
				<?php echo HTMLHelper::_('image', 'mod_languages/' . $item->image . '.gif', $item->language_title, array('title' => Text::sprintf('COM_TEMPLATES_STYLES_PAGES_ALL_LANGUAGE', $item->language_title)), true); ?>
			</small>
		<?php elseif ((int) $item->home === 1) :  // $item->assigned > 0?>
			<small class="ml-2">
				<span class="icon-check-circle" area-hidden="true"></span>
				<span class="visually-hidden"><?php echo Text::sprintf('COM_TEMPLATES_STYLES_PAGES_SELECTED', $item->assigned); ?></span>
			</small>
		<?php endif; ?>
	</div>

</div>
