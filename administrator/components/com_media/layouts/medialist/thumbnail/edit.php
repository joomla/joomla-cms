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

$link = 'index.php?option=com_media&amp;task=file.edit&amp;tmpl=index&amp;';
$link .= '&amp;folder=' . $displayData['folder'];
$link .= '&amp;' . JSession::getFormToken() . '=1';
$link .= '&amp;rm[]='. $item->name;

$allowEdit = $user->authorise('core.edit', 'com_media');

if (is_dir(JPATH_ADMINISTRATOR . '/components/com_media/views/image') == false)
{
	$allowEdit = false;
}
?>
<?php if ($allowEdit) : ?>
	<a class="pull-right" target="_top" href="<?php echo $link; ?>" title="<?php echo $item->name; ?>" class="preview">
		<span class="icon-pencil" style="padding-left: 5px;"></span>
	</a>
<?php endif; ?>
