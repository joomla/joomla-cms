<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('script', 'com_content/admin-articles-default-batch-footer.js', ['relative' => true, 'version' => 'auto']);
?>
<a class="btn btn-secondary" type="button" data-dismiss="modal">
	<?php echo Text::_('JCANCEL'); ?>
</a>
<button id='batch-submit-button-id' class="btn btn-success" type="submit" data-submit-task='article.batch'>
	<?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>
