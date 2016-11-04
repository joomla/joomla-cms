<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.core');
JHtml::_('jquery.framework');
JHtml::_('script', 'com_search/indexer.js', false, true);
JFactory::getDocument()->addScriptDeclaration('var msg = "' . JText::_('COM_SEARCH_INDEXER_MESSAGE_COMPLETE') . '";');
?>

<div id="search-indexer-container">
	<br /><br />
	<h1 id="search-progress-header"><?php echo JText::_('COM_SEARCH_INDEXER_HEADER_INIT'); ?></h1>

	<p id="search-progress-message"><?php echo JText::_('COM_SEARCH_INDEXER_MESSAGE_INIT'); ?></p>

	<div id="progress" class="progress progress-striped active">
		<div id="progress-bar" class="bar bar-success" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
	</div>

	<input id="search-indexer-token" type="hidden" name="<?php echo JFactory::getSession()->getFormToken(); ?>" value="1" />
</div>
