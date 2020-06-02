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

if ($function) :
	HTMLHelper::_('script', 'com_modules/admin-select-modal.js', ['version' => 'auto', 'relative' => true]);
endif;
?>
<h2 class="mb-3"><?php echo Text::_('COM_MODULES_TYPE_CHOOSE'); ?></h2>
<div id="new-modules-list">
	<div class="new-modules">
		<div class="card-columns">
			<?php foreach ($this->items as &$item) : ?>
				<div class="card mb-4">
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
