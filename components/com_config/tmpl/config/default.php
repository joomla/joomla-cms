<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// Load tooltips behavior
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('script', 'com_config/config-default.js', ['version' => 'auto', 'relative' => true]);
?>

<form action="<?php echo Route::_('index.php?option=com_config'); ?>" id="application-form" method="post" name="adminForm" class="form-validate">

	<div class="btn-toolbar" role="toolbar" aria-label="<?php echo Text::_('JTOOLBAR'); ?>">
		<div class="btn-group">
			<button type="button" class="btn btn-primary" data-submit-task="config.apply">
				<span class="fas fa-check" aria-hidden="true"></span>
				<?php echo Text::_('JSAVE') ?>
			</button>
		</div>
		<div class="btn-group">
			<button type="button" class="btn btn-danger" data-submit-task="config.cancel">
				<span class="fas fa-times" aria-hidden="true"></span>
				<?php echo Text::_('JCANCEL') ?>
			</button>
		</div>
	</div>

	<hr>

	<div id="page-site" class="tab-pane active">
		<div class="row">
			<div class="col-md-12">
				<?php echo $this->loadTemplate('site'); ?>
			</div>
			<div class="col-md-12">
				<?php echo $this->loadTemplate('seo'); ?>
			</div>
			<div class="col-md-12">
				<?php echo $this->loadTemplate('metadata'); ?>
			</div>
		</div>
	</div>

	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>

</form>
