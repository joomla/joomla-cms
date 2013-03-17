<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JHtml::_('behavior.framework');
JHtml::_('behavior.keepalive');
?>

<div id="finder-indexer-container">
	<br /><br />
	<h1 id="finder-progress-header"><?php echo JText::_('COM_FINDER_INDEXER_HEADER_INIT'); ?></h1>

	<p id="finder-progress-message"><?php echo JText::_('COM_FINDER_INDEXER_MESSAGE_INIT'); ?></p>

	<form id="finder-progress-form"></form>

	<div id="finder-progress-container"></div>

	<input id="finder-indexer-token" type="hidden" name="<?php echo JFactory::getSession()->getFormToken(); ?>" value="1" />
</div>
