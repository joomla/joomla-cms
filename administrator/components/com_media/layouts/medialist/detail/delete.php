<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$user = JFactory::getUser();

$item = $displayData['item'];

$link = 'index.php?option=com_media&amp;tmpl=index&amp;';
$link .= '&amp;task=' . $displayData['task'];
$link .= '&amp;folder=' . $displayData['folder'];
$link .= '&amp;' . JSession::getFormToken() . '=1';
$link .= '&amp;rm[]='. $item->name;
?>
<?php if ($user->authorise('core.delete', 'com_media')):?>
	<td>
		<a class="delete-item" target="_top" href="<?php echo $link; ?>" rel="<?php echo $item->name; ?>">
			<i class="icon-remove hasTooltip" title="<?php echo JHtml::tooltipText('JACTION_DELETE');?>"></i>
		</a>
		<input type="checkbox" name="rm[]" value="<?php echo $item->name; ?>" onclick="Joomla.isChecked(this.checked);" />
	</td>
<?php endif;?>
