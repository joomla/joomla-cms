<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

Text::script('COM_FINDER_INDEXER_MESSAGE_COMPLETE');
Text::script('COM_FINDER_AN_ERROR_HAS_OCCURRED');
Text::script('COM_FINDER_MESSAGE_RETURNED');
Text::script('JLIB_JS_AJAX_ERROR_OTHER');
Text::script('JLIB_JS_AJAX_ERROR_PARSE');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useStyle('com_finder.indexer')
    ->useScript('com_finder.indexer');

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
