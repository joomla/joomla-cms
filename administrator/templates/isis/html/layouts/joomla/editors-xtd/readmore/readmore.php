<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.modal');

$button = $displayData;


JFactory::getDocument()->addScriptDeclaration(
	"
		function insertReadmore(editor)
	{
		var content = $button->content;
		if (content.match(/<hr\s+id=(\"|')system-readmore(\"|')\s*\/*>/i))
		{
			alert('$button->present');
			return false;
		} else {
			jInsertEditorText('<hr id=\"system-readmore\" />', editor);
		}
	}"
);

?>

<a href="#" class="<?php echo $button->class; ?>" role="button" title="<?php echo $button->text; ?>" onclick="<?php echo $button->onclick; ?>"><i class="icon-<?php echo $button->name; ?>"></i> <?php echo $button->text; ?></a>
