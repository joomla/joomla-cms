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
use Joomla\CMS\Session\Session;

extract($displayData);

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
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

$wa->useScript('core')
    ->useScript('joomla.dialog-autocreate')
    ->useScript('webcomponent.toolbar-button');

$url = 'index.php?' . http_build_query([
    'option'                => 'com_contenthistory',
    'view'                  => 'history',
    'layout'                => 'modal',
    'tmpl'                  => 'component',
    'item_id'               => $itemId,
    Session::getFormToken() => 1,
]);


$dialogOptions = [
    'popupType'  => 'iframe',
    'src'        => $url,
    'textHeader' => $title ?? '',
];

?>
<joomla-toolbar-button id="toolbar-versions">
    <button
        class="btn btn-primary"
        data-joomla-dialog="<?php echo $this->escape(json_encode($dialogOptions, JSON_UNESCAPED_SLASHES)); ?>"
        type="button">
        <span class="icon-code-branch" aria-hidden="true"></span>
        <?php echo $title; ?>
    </button>
</joomla-toolbar-button>
