<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$link = 'index.php?option=com_contenthistory&amp;view=history&amp;layout=modal&amp;tmpl=component&amp;item_id='
	. (int) $displayData['itemId'] . '&amp;type_id=' . $displayData['typeId'] . '&amp;type_alias='
	. $displayData['typeAlias'] . '&amp;' . JSession::getFormToken() . '=1';

if (JFactory::getApplication()->isAdmin()) {
	echo JHtmlBootstrap::renderModal('versionsModal', array( 'url' => $link, 'title' => JText::_('COM_CONTENTHISTORY_MODAL_TITLE'),'height' => '600px', 'width' => '800px'), '');
}
?>
<?php if (JFactory::getApplication()->isAdmin()) : ?>
<button onclick="jQuery('#versionsModal').modal('show')" class="btn btn-small" data-toggle="modal" title="<?php echo $displayData['title']; ?>">
<span class="icon-archive"></span><?php echo $displayData['title']; ?></button>
<?php endif; ?>

<?php if (JFactory::getApplication()->isSite()) : ?>
<a rel="{handler: 'iframe', size: {x: <?php echo $displayData['height']; ?>, y: <?php echo $displayData['width']; ?>}}"
	href="index.php?option=com_contenthistory&amp;view=history&amp;layout=modal&amp;tmpl=component&amp;item_id=<?php echo (int) $displayData['itemId']; ?>&amp;type_id=<?php echo $displayData['typeId']; ?>&amp;type_alias=<?php echo $displayData['typeAlias']; ?>&amp;<?php echo JSession::getFormToken(); ?>=1"
	title="<?php echo $displayData['title']; ?>" class="btn btn-small modal_jform_contenthistory">
	<i class="icon-archive"></i> <?php echo $displayData['title']; ?>
</a>
<?php endif; ?>
