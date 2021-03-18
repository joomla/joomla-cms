<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>
<div class="com-content-archive archive">
<?php if ($this->params->get('show_page_heading')) : ?>
	<div class="page-header">
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
	</div>
<?php endif; ?>

<form id="adminForm" action="<?php echo Route::_('index.php'); ?>" method="post" class="com-content-archive__form">
	<fieldset class="com-content-archive__filters filters">
	<div class="filter-search form-inline">
		<?php if ($this->params->get('filter_field') !== 'hide') : ?>
		<div class="mr-2">
			<label class="filter-search-lbl sr-only" for="filter-search"><?php echo Text::_('COM_CONTENT_TITLE_FILTER_LABEL') . '&#160;'; ?></label>
			<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->filter); ?>" class="inputbox col-md-2" onchange="document.getElementById('adminForm').submit();" placeholder="<?php echo Text::_('COM_CONTENT_TITLE_FILTER_LABEL'); ?>">
		</div>
		<?php endif; ?>

		<div class="mr-2">
		<?php echo $this->form->monthField; ?>
		</div>
		<div class="mr-2">
		<?php echo $this->form->yearField; ?>
		</div>
		<div class="mr-2">
		<?php echo $this->form->limitField; ?>
		</div>

		<button type="submit" class="btn btn-primary" style="vertical-align: top;"><?php echo Text::_('JGLOBAL_FILTER_BUTTON'); ?></button>
		<input type="hidden" name="view" value="archive">
		<input type="hidden" name="option" value="com_content">
		<input type="hidden" name="limitstart" value="0">
	</div>
	<br>
	</fieldset>
</form>
<?php echo $this->loadTemplate('items'); ?>
</div>
