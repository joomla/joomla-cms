<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Fields\Administrator\View\Select\HtmlView $this */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('com_fields.admin-field-select-search');

$context = $this->escape($this->state->get('filter.context'));
?>

<div class="d-none" id="comFieldsSelectSearchContainer">
    <div class="d-flex mt-2">
        <div class="m-auto">
            <label class="visually-hidden" for="comFieldsSelectSearch">
                <?php echo Text::_('COM_FIELDS_TYPE_CHOOSE'); ?>
            </label>
            <div class="input-group mb-3 me-sm-2">
                <input type="text" value=""
                    class="form-control" id="comFieldsSelectSearch"
                    placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>"
                >
                <div class="input-group-text">
                    <span class="icon-search" aria-hidden="true"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="new-fields-list">
    <div class="new-modules">
        <div class="fields-alert alert alert-info d-none">
            <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
            <?php echo Text::_('COM_FIELDS_MSG_NO_FIELD_TYPES'); ?>
        </div>
        <h2 class="pb-3 ms-3" id="comFieldsSelectTypeHeader">
            <?php echo Text::_('COM_FIELDS_TYPE_CHOOSE'); ?>
        </h2>
        <div class="main-card card-columns p-4" id="comFieldsSelectResultsContainer">
            <?php foreach ($this->items as $item) : ?>
                <?php // Prepare variables for the link. ?>
                <?php $link = 'index.php?option=com_fields&task=field.add&context=' . $context . '&type=' . $item->type; ?>
                <?php $name = $this->escape($item->label); ?>
                <?php $desc = HTMLHelper::_('string.truncate', $this->escape(strip_tags($item->description)), 200); ?>
                <a href="<?php echo Route::_($link); ?>" class="new-module mb-3 comFieldsSelectCard"
                    aria-label="<?php echo Text::sprintf('COM_FIELDS_SELECT_FIELD_TYPE', $name); ?>">
                    <div class="new-module-details">
                        <h3 class="new-module-title"><?php echo $name; ?></h3>
                        <p class="new-module-caption p-0">
                            <?php echo $desc; ?>
                        </p>
                    </div>
                    <span class="new-module-link">
                        <span class="icon-plus" aria-hidden="true"></span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
