<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

/** @var \Joomla\Component\Modules\Administrator\View\Positions\HtmlView $this */

Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('com_modules.admin-modules-positions-modal');
?>
<div class="container-popup">
    <div class="p-3">
        <label for="comModulesPositionsSearch" class="visually-hidden">
            <?php echo $this->escape(Text::_('JSEARCH_FILTER')); ?>
        </label>
        <div class="input-group mb-4">
            <input
                type="text"
                id="comModulesPositionsSearch"
                class="form-control"
                placeholder="<?php echo $this->escape(Text::_('JSEARCH_FILTER')); ?>"
                autocomplete="off"
                aria-controls="comModulesPositionsList"
            >
            <div class="input-group-text">
                <span class="icon-search" aria-hidden="true"></span>
            </div>
        </div>

        <div id="comModulesPositionsNoResults" class="alert alert-info" hidden aria-live="polite">
            <span class="icon-info-circle" aria-hidden="true"></span>
            <span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
            <?php echo $this->escape(Text::_('JGLOBAL_NO_MATCHING_RESULTS')); ?>
        </div>

        <div id="comModulesPositionsList">
        <?php foreach ($this->positions as $key => $group) : ?>
            <?php if (empty($group['items'])) : ?>
                <?php continue; ?>
            <?php endif; ?>
            <?php $renderableItems = array_filter($group['items'], static fn($o) => !empty($o->value)); ?>
            <?php if (empty($renderableItems)) : ?>
                <?php continue; ?>
            <?php endif; ?>
            <details open class="positions-group mb-0" data-group="<?php echo $this->escape($key); ?>">
                <summary role="heading" aria-level="3" class="fs-3 mb-0">
                    <?php echo $this->escape($group['text']); ?>
                </summary>
                <div class="list-group list-group-flush">
                    <?php foreach ($group['items'] as $option) : ?>
                        <?php if (empty($option->value)) :
                            continue;
                        endif; ?>
                        <button
                            type="button"
                            class="position-select-link list-group-item list-group-item-action"
                            data-content-select
                            data-id="<?php echo $this->escape($option->value); ?>"
                            data-title="<?php echo $this->escape($option->text); ?>"
                            aria-label="<?php echo $this->escape(Text::_('JSELECT') . ': ' . $option->text); ?>"
                        >
                            <?php echo $this->escape($option->text); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </details>
        <?php endforeach; ?>
        </div>
    </div>
</div>
