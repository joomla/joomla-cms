<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_sampledata
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$app->getDocument()->getWebAssetManager()
    ->registerAndUseScript('mod_sampledata', 'mod_sampledata/sampledata-process.js', [], ['type' => 'module'], ['core']);

Text::script('MOD_SAMPLEDATA_COMPLETED');
Text::script('MOD_SAMPLEDATA_CONFIRM_START');
Text::script('MOD_SAMPLEDATA_ITEM_ALREADY_PROCESSED');
Text::script('MOD_SAMPLEDATA_INVALID_RESPONSE');

$app->getDocument()->addScriptOptions(
    'sample-data',
    [
        'icon' => Uri::root(true) . '/media/system/images/ajax-loader.gif',
    ]
);
?>
<?php if ($items) : ?>
    <ul id="sample-data-wrapper" class="list-group list-group-flush sample-data">
        <?php foreach ($items as $i => $item) : ?>
            <li class="list-group-item sampledata-<?php echo $item->name; ?>">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="sample-data__title">
                        <span class="sample-data__icon icon-<?php echo $item->icon; ?> me-1" aria-hidden="true"></span>
                        <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm apply-sample-data" data-type="<?php echo $item->name; ?>" data-steps="<?php echo $item->steps; ?>">
                        <span class="icon-upload" aria-hidden="true"></span> <?php echo Text::_('JLIB_INSTALLER_INSTALL'); ?>
                        <span class="visually-hidden"><?php echo $item->title; ?></span>
                    </button>
                </div>
                <p class="sample-data__desc small mt-1"><?php echo $item->description; ?></p>
            </li>
            <?php // Progress bar ?>
            <li class="list-group-item sampledata-progress-<?php echo $item->name; ?> d-none">
                <div class="progress mb-3">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"></div>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
    <a href="index.php?option=com_plugins&filter[folder]=sampledata" class="btn btn-secondary btn-sm manage-sample-data float-end m-3">
        <span class="icon-tasks" aria-hidden="true"></span> <?php echo Text::_('MOD_SAMPLEDATA_MANAGE_SAMPLEDATA'); ?>
    </a>
<?php endif; ?>
