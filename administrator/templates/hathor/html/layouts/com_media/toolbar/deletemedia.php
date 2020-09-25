<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$title = JText::_('JTOOLBAR_DELETE');
?>
<button onclick="MediaManager.submit('folder.delete')" class="toolbar">
	<span class="icon-32-delete" title="<?php echo $title; ?>"></span> <?php echo $title; ?>
</button>
