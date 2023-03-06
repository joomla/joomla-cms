<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$app = Factory::getApplication();

$function  = $app->input->getCmd('function');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('com_modules.admin-module-search');

if ($function) :
    $wa->useScript('com_modules.admin-select-modal');
endif;

?>

<div class="d-none" id="comModulesSelectSearchContainer">
    <div class="d-flex mt-2">
        <div class="m-auto">
            <label class="visually-hidden" for="comModulesSelectSearch">
                <?php echo Text::_('COM_MODULES_TYPE_CHOOSE'); ?>
            </label>
            <div class="input-group mb-3 me-sm-2">
                <input type="text" value=""
                    class="form-control" id="comModulesSelectSearch"
                    placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>"
                >
                <div class="input-group-text">
                    <span class="icon-search" aria-hidden="true"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="new-modules-list">
    <div class="new-modules">
        <div class="modules-alert alert alert-info d-none">
            <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
            <?php echo Text::_('COM_MODULES_MSG_MANAGE_NO_MODULES'); ?>
        </div>
        <h2 class="pb-3 ms-3" id="comModulesSelectTypeHeader">
            <?php echo Text::_('COM_MODULES_TYPE_CHOOSE'); ?>
        </h2>
        <div class="main-card card-columns p-4" id="comModulesSelectResultsContainer">
            <?php foreach ($this->items as &$item) : ?>
                <?php // Prepare variables for the link. ?>
                <?php $link = 'index.php?option=com_modules&task=module.add&client_id=' . $this->state->get('client_id', 0) . $this->modalLink . '&eid=' . $item->extension_id; ?>
                <?php $name = $this->escape($item->name); ?>
                <?php $desc = HTMLHelper::_('string.truncate', $this->escape(strip_tags($item->desc)), 200); ?>
                <a href="<?php echo Route::_($link); ?>" class="new-module mb-3 comModulesSelectCard"
                    <?php echo !empty($function) ? 'data-function="' . $this->escape($function) . '"' : ''; ?>
                    aria-label="<?php echo Text::sprintf('COM_MODULES_SELECT_MODULE', $name); ?>">
                    <div class="new-module-details">
                        <h3 class="new-module-title"><?php echo $name; ?></h3>
                        <p class="card-body new-module-caption p-0">
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
