<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   © 2013 Open Source Matters, Inc. <https://www.joomla.org/contribute-to-joomla.html>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$title = JText::_('COM_MEDIA_CREATE_NEW_FOLDER');
?>
<button data-toggle="collapse" data-target="#collapseFolder" class="toolbar">
	<span class="icon-folder" title="<?php echo $title; ?>"></span> <?php echo $title; ?>
</button>
