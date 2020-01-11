<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Utility\Utility;

?>

<div class="alert alert-info">
	<span class="fa fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
	<?php echo Text::_('COM_INSTALLER_VIEW_DEFAULT_IMPORT_INTRO'); ?>
</div>

<fieldset class="importform options-grid-form options-grid-form-full">
	<legend><?php echo Text::_('COM_INSTALLER_IMPORT_TITLE'); ?></legend>
	<table class="table">
		<tbody>
		<tr>
			<td>
				<?php echo Text::_('COM_INSTALLER_FILE_IMPORTER_TEXT'); ?>
			</td>
			<td>
				<input class="form-control-file" id="zip_file" name="zip_file" type="file" accept="application/zip" size="57">
				<?php $maxSize = HTMLHelper::_('number.bytes', Utility::getMaxUploadSize()); ?>
				<small class="form-text text-muted"><?php echo Text::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', '&#x200E;' . $maxSize); ?></small>
			</td>
		</tr>
		</tbody>
		<tfoot>
		<tr>
			<td>&nbsp;</td>
			<td>
				<button id="importButton" class="btn btn-primary" type="button" onclick="Joomla.submitbutton('database.import');"><?php echo Text::_('COM_INSTALLER_IMPORT_BUTTON'); ?></button>
			</td>
		</tr>
		</tfoot>
	</table>
</fieldset>
<?php echo HTMLHelper::_('form.token'); ?>
