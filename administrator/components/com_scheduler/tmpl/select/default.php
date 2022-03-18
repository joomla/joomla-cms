<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/** The SelectView default layout template. */

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Scheduler\Administrator\View\Select\HtmlView;

/** @var  HtmlView  $this */

$app = $this->app;

$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_scheduler.admin-view-select-task-css');
$wa->useScript('com_scheduler.admin-view-select-task-search');

?>

<!-- Tasks search box on below the toolbar begins -->
<div class="d-none" id="comSchedulerSelectSearchContainer">
	<div class="d-flex mt-2">
		<div class="ms-auto me-auto">
			<label class="visually-hidden" for="comSchedulerSelectSearch">
				<?php echo Text::_('COM_SCHEDULER_TYPE_CHOOSE'); ?>
			</label>
			<div class="input-group mb-3 me-sm-2">
				<input type="text" value=""
					   class="form-control" id="comSchedulerSelectSearch"
					   placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>"
				>
				<div class="input-group-text">
					<span class="icon-search" aria-hidden="true"></span>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Search box and related elements end -->

<div id="new-tasks-list">
	<div class="new-tasks">
		<!-- Hidden alert div -->
		<div class="tasks-alert alert alert-info d-none">
			<span class="icon-info-circle" aria-hidden="true"></span><span
					class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
			<?php echo Text::_('COM_SCHEDULER_MSG_MANAGE_NO_TASK_PLUGINS'); ?>
		</div>
		<h2 class="pb-3 ms-3" id="comSchedulerSelectTypeHeader">
			<?php echo Text::_('COM_SCHEDULER_TYPE_CHOOSE'); ?>
		</h2>

		<!-- Parent card -->
		<div class="main-card card-columns p-4" id="comSchedulerSelectResultsContainer">

			<!-- Plugin task cards start below -->
			<?php foreach ($this->items as $item) : ?>
				<?php // Prepare variables for the link. ?>
				<?php $link = 'index.php?option=com_scheduler&task=task.add&type=' . $item->id; ?>
				<?php $name = $this->escape($item->title); ?>
				<?php $desc = HTMLHelper::_('string.truncate', $this->escape(strip_tags($item->desc)), 200); ?>
				<!-- The task card begins -->
				<a href="<?php echo Route::_($link); ?>" class="new-task mb-3 comSchedulerSelectCard"
				   data-function="' . $this->escape($function) : ''; ?>"
				   aria-label="<?php echo Text::sprintf('COM_SCHEDULER_SELECT_TASK_TYPE', $name); ?>">
					<div class="new-task-details">
						<h3 class="new-task-title"><?php echo $name; ?></h3>
						<p class="card-body new-task-caption p-0">
							<?php echo $desc; ?>
						</p>
					</div>
					<span class="new-task-link">
						<span class="icon-plus" aria-hidden="true"></span>
					</span>
				</a>
				<!-- The task card ends here -->
			<?php endforeach; ?>
		</div>
	</div>
</div>
