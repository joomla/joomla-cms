<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

Text::script('COM_FINDER_INDEXER_MESSAGE_COMPLETE', true);

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('script', 'com_finder/indexer.min.js', array('version' => 'auto', 'relative' => true));
?>

<div class="text-center">
	<h1 id="finder-progress-header" class="m-t-2" aria-live="assertive"><?php echo Text::_('COM_FINDER_INDEXER_HEADER_INIT'); ?></h1>
	<p id="finder-progress-message" aria-live="polite"><?php echo Text::_('COM_FINDER_INDEXER_MESSAGE_INIT'); ?></p>
	<div id="progress" class="progress">
		<div id="progress-bar" class="progress-bar bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
	</div>
	<?php if (JDEBUG) : ?>
	<dl id="finder-debug-data" class="row">
	</dl>
	<?php endif; ?>
	<input id="finder-indexer-token" type="hidden" name="<?php echo Factory::getSession()->getFormToken(); ?>" value="1">
</div>
