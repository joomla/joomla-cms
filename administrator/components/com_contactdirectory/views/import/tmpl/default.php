<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	ContactDirectory
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.tooltip');

JToolBarHelper::title(JText::_('IMPORT_CONTACTS'));
JToolBarHelper::custom('import', 'save.png', 'save_f2.png', JText::_('SAVE'), false, true);
JToolBarHelper::cancel();
?>

<form action="index.php" method="post" name="adminForm" enctype="multipart/form-data">
	<label for="import_file"><?php echo JText::_('CONTACTS_IMPORT_FROM_FILE'); ?></label><br />
	<input type="file" name="importFile" id="import_file" size="50" />
	<br /><br />
	<label for="import_string"><?php echo JText::_('CONTACTS_IMPORT_FROM_STRING'); ?></label><br />
	<textarea name="importString" rows="15" cols="100" id="import_string"></textarea>

	<input type="hidden" name="controller" value="contact" />
	<input type="hidden" name="option" value="com_contactdirectory" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
