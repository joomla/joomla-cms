<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.multiselect');

?>

<div id="installer-database" class="clearfix">
	<form enctype="multipart/form-data" action="<?php echo Route::_('index.php?option=com_installer&view=database'); ?>" method="post" name="adminForm" id="adminForm">
				<?php echo HTMLHelper::_('uitab.startTabSet', 'database-tabs', array('active' => 'update-structure')); ?>

				<?php echo HTMLHelper::_('uitab.addTab', 'database-tabs', 'update-structure', Text::_('COM_INSTALLER_VIEW_DEFAULT_TAB_FIX')); ?>
				<?php echo $this->loadTemplate('update'); ?>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>

				<?php echo HTMLHelper::_('uitab.addTab', 'database-tabs', 'upload-import', Text::_('COM_INSTALLER_VIEW_DEFAULT_TAB_IMPORT')); ?>
				<?php echo $this->loadTemplate('import'); ?>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>

				<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	</form>
</div>
