<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

extract($displayData, EXTR_OVERWRITE);

/**
 * Layout variables
 * -----------------
 * @var   string  $id
 * @var   string  $itemId
 * @var   string  $typeId
 * @var   string  $typeAlias
 * @var   string  $title
 */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getDocument()->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_contenthistory');

$wa->useScript('core');
$wa->registerAndUseScript('joomla-modal', 'system/joomla-modal.min.js', [], ['type' => 'module'], []);
$wa->registerAndUseStyle('joomla-modal', 'system/joomla-modal.min.css', [], [], []);
$wa->useScript('com_contenthistory.admin-history-versions');

$url = 'index.php?' . http_build_query([
    'option' => 'com_contenthistory',
    'view' => 'history',
    'layout' => 'modal',
    'tmpl' => 'component',
    'item_id' => $itemId,
    Session::getFormToken() => 1
]);
?>
<joomla-modal-button id="jooml-modal-button-preview" title="<?= $title; ?>" url="<?= $url; ?>" close-text="<?= Text::_('JLIB_HTML_BEHAVIOR_CLOSE'); ?>" click-outside="true">
    <button class="btn btn-primary" type="button">
        <span class="icon-code-branch" aria-hidden="true"></span>
        <?php echo $title; ?>
    </button>
</joomla-modal-button>
