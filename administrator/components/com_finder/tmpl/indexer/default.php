<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.core');
HTMLHelper::_('jquery.framework');
HTMLHelper::_('script', 'com_finder/indexer.min.js', array('version' => 'auto', 'relative' => true));
Factory::getDocument()->addScriptDeclaration('var msg = "' . Text::_('COM_FINDER_INDEXER_MESSAGE_COMPLETE') . '";');
?>

<div class="text-center">
	<h1 id="finder-progress-header m-t-2"><?php echo Text::_('COM_FINDER_INDEXER_HEADER_INIT'); ?></h1>
	<p id="finder-progress-message"><?php echo Text::_('COM_FINDER_INDEXER_MESSAGE_INIT'); ?></p>
	<div id="progress" class="progress">
  		<div id="progress-bar" class="progress-bar bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
	</div>
	<input id="finder-indexer-token" type="hidden" name="<?php echo Factory::getSession()->getFormToken(); ?>" value="1">
</div>
