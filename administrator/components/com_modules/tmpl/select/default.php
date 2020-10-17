<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$app = Factory::getApplication();

$function  = $app->input->getCmd('function');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('com_modules.admin-module-search');

if ($function) :
	$wa->useScript('com_modules.admin-select-modal');
endif;

?>
<h2 class="mb-3"><?php echo Text::_('COM_MODULES_TYPE_CHOOSE'); ?></h2>

<div class="container d-none" id="comModulesSelectSearchContainer">
	<div class="row">
		<div class="col-sm-6 offset-sm-2 col-md-4 offset-sm-4 offset-md-5 offset-lg-4">
			<div class="form-inline">
				<label class="sr-only" for="comModulesSelectSearch">
					<?php echo Text::_('COM_MODULES_TYPE_CHOOSE'); ?>
				</label>
				<div class="input-group mb-5 mr-sm-2">
					<input type="text" value=""
						   class="form-control" id="comModulesSelectSearch"
						   placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>"
					>
					<div class="input-group-append" aria-hidden="true">
						<div class="input-group-text">
							<span class="fa fa-search"></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="new-modules-list">
	<div class="new-modules">
		<div class="card-columns">
			<?php foreach ($this->items as &$item) : ?>
				<div class="card mb-4 comModulesSelectCard">
					<?php // Prepare variables for the link. ?>
					<?php $link = 'index.php?option=com_modules&task=module.add&client_id=' . $this->state->get('client_id', 0) . $this->modalLink . '&eid=' . $item->extension_id; ?>
					<?php $name = $this->escape($item->name); ?>
					<?php $desc = HTMLHelper::_('string.truncate', $this->escape(strip_tags($item->desc)), 200); ?>

					<div class="card-header">
						<h3><?php echo $name; ?></h3>
					</div>

					<div class="card-body">
						<p class="text-muted">
							<?php echo $desc; ?>
						</p>
					</div>
					<a href="<?php echo Route::_($link); ?>" class="btn btn-primary <?php echo $function ? ' select-link" data-function="' . $this->escape($function) : ''; ?>" aria-label="<?php echo Text::sprintf('COM_MODULES_SELECT_MODULE', $name); ?>">
						<?php echo Text::_('JSELECT'); ?>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
