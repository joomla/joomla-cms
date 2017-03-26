<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.core');
JHtml::_('jquery.framework');
JHtml::_('script', 'com_finder/indexer.js', array('version' => 'auto', 'relative' => true));
JFactory::getDocument()->addScriptDeclaration('var msg = "' . JText::_('COM_FINDER_INDEXER_MESSAGE_COMPLETE') . '";');
?>

<div id="finder-indexer-container">
	<h1 id="finder-progress-header m-t-2"><?php echo JText::_('COM_FINDER_INDEXER_HEADER_INIT'); ?></h1>
	<p id="finder-progress-message"><?php echo JText::_('COM_FINDER_INDEXER_MESSAGE_INIT'); ?></p>
	<div id="progress" class="progress progress-striped active">
		<div id="progress-bar" class="bar bar-success" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
	</div>
	<input id="finder-indexer-token" type="hidden" name="<?php echo JFactory::getSession()->getFormToken(); ?>" value="1">
</div>
