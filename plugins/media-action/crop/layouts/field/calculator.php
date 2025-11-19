<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Media-Action.crop
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var  string  $id  The field ID
 */

// Load the calculator JavaScript
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('plg_media-action_crop');
$wa->useScript('plg_media-action_crop.calculator');

// Prepare data for JavaScript
$copiedIcon = LayoutHelper::render('joomla.icon.iconclass', ['icon' => 'icon-check']);
$copiedText = Text::_('PLG_MEDIA-ACTION_CROP_RATIO_CALCULATOR_COPY_DONE_LABEL');
?>
<div class="card mb-3">
    <div class="card-header">
        <h2 id="aspect-ratio-calculator" class="card-title mb-0">
            <?php echo LayoutHelper::render('joomla.icon.iconclass', ['icon' => 'icon-wand']); ?>
            <?php echo Text::_('PLG_MEDIA-ACTION_CROP_RATIO_CALCULATOR_LABEL'); ?>
        </h2>
    </div>
    <div class="card-body">
        <p class="mb-3">
            <?php echo Text::_('PLG_MEDIA-ACTION_CROP_RATIO_CALCULATOR_DESC'); ?>
        </p>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="calc-width" class="form-label fw-bold">
                    <?php echo Text::_('PLG_MEDIA-ACTION_CROP_RATIO_CALCULATOR_WIDTH_LABEL'); ?>
                </label>
                <input type="number" id="calc-width" min="1" step="1" value="16" class="form-control" />
            </div>
            <div class="col-md-6">
                <label for="calc-height" class="form-label fw-bold">
                    <?php echo Text::_('PLG_MEDIA-ACTION_CROP_RATIO_CALCULATOR_HEIGHT_LABEL'); ?>
                </label>
                <input type="number" id="calc-height" min="1" step="1" value="9" class="form-control" />
            </div>
        </div>
        <div class="alert alert-success d-flex align-items-center justify-content-between" role="alert">
            <div>
                <strong class="me-2">
                    <?php echo Text::_('PLG_MEDIA-ACTION_CROP_RATIO_CALCULATOR_OUTPUT_LABEL'); ?>
                </strong>
                <output id="calc-output" for="aspect-ratio-calculator" class="font-monospace fs-5 text-success fw-bold user-select-all" style="cursor: pointer;" title="<?php echo Text::_('PLG_MEDIA-ACTION_CROP_RATIO_CALCULATOR_OUTPUT_TITLE_LABEL'); ?>">1.7777777777777777</output>
            </div>
            <button type="button" id="copy-calc-value" class="btn btn-success btn-sm"
                    data-copied-icon="<?php echo htmlspecialchars($copiedIcon, ENT_QUOTES, 'UTF-8'); ?>"
                    data-copied-text="<?php echo htmlspecialchars($copiedText, ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo LayoutHelper::render('joomla.icon.iconclass', ['icon' => 'icon-copy']); ?>
                <?php echo Text::_('PLG_MEDIA-ACTION_CROP_RATIO_CALCULATOR_COPY_LABEL'); ?>
            </button>
        </div>
    </div>
</div>
