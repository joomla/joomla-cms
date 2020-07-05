<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.framework');

?>
<a rel="{handler: 'iframe', size: {x: <?php echo $displayData['height']; ?>, y: <?php echo $displayData['width']; ?>}}"
	href="index.php?option=com_contenthistory&amp;view=history&amp;layout=modal&amp;tmpl=component&amp;item_id=<?php echo (int) $displayData['itemId']; ?>&amp;type_id=<?php echo $displayData['typeId']; ?>&amp;type_alias=<?php echo $displayData['typeAlias']; ?>&amp;<?php echo JSession::getFormToken(); ?>=1"
	title="<?php echo $displayData['title']; ?>" class="btn btn-small modal_jform_contenthistory">
	<span class="icon-archive" aria-hidden="true"></span> <?php echo $displayData['title']; ?>
</a>
