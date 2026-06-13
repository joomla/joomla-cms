<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_healthcheck
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('core')
    ->useScript('bootstrap.dropdown');

// Register and use the filter and async assets. The async script fills any quick-icon carrying a
// data-url from com_ajax; mod_quickicon's script is intentionally not used here as it expects a
// different response shape and would render "undefined" for these icons.
$wa->registerAndUseScript('mod_healthcheck.filter', 'mod_healthcheck/healthcheck-filter.js', [], ['defer' => true])
    ->registerAndUseScript('mod_healthcheck.async', 'mod_healthcheck/healthcheck-async.js', [], ['defer' => true])
    ->registerAndUseStyle('mod_healthcheck.general', 'mod_healthcheck/healthcheck.css');

$gauges_html  = HTMLHelper::_('healthchecks.gauges', $gauges);
$buttons_html = HTMLHelper::_('healthchecks.buttons', $buttons);
$lists_html   = HTMLHelper::_('healthchecks.lists', $lists);
$tables_html  = HTMLHelper::_('healthchecks.tables', $tables);
$leading_html = HTMLHelper::_('healthchecks.leadings', $leading);
$footer_html  = HTMLHelper::_('healthchecks.footers', $footer);

$has_data = !empty($gauges_html) || !empty($buttons_html) || !empty($lists_html) || !empty($tables_html) || !empty($reports_html);
?>
<?php if ($has_data) : ?>
    <!-- Filter Buttons -->
    <div class="healthcheck-filters p-3 d-flex align-items-center justify-content-between">
        <div class="btn-group btn-group-sm" role="group" aria-label="<?php echo Text::_('MOD_HEALTHCHECK_FILTER_LABEL'); ?>">
            <button type="button" class="btn btn-outline-primary active healthcheck-filter-btn" data-filter="all">
                <?php echo Text::_('MOD_HEALTHCHECK_FILTER_ALL'); ?>
            </button>
            <button type="button" class="btn btn-outline-success healthcheck-filter-btn" data-filter="healthy">
                <?php echo Text::_('MOD_HEALTHCHECK_FILTER_HEALTHY'); ?>
            </button>
            <button type="button" class="btn btn-outline-warning healthcheck-filter-btn" data-filter="warning">
                <?php echo Text::_('MOD_HEALTHCHECK_FILTER_WARNING'); ?>
            </button>
            <button type="button" class="btn btn-outline-danger healthcheck-filter-btn" data-filter="critical">
                <?php echo Text::_('MOD_HEALTHCHECK_FILTER_CRITICAL'); ?>
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-primary">
            <i class="icon-refresh"></i>
        </button>
    </div>
<?php endif; ?>
<?php if (!empty($leading_html)) : ?>
    <div class="health-checks module-leadingtext px-3 pb-3">
        <?php echo $leading_html; ?>
    </div>
<?php endif; ?>
<nav class="quick-icons health-checks px-3 pb-3" aria-label="<?php echo Text::_('MOD_HEALTHCHECK_NAV_LABEL') . ' ' . htmlspecialchars($module->title, ENT_QUOTES, 'UTF-8'); ?>">
    <?php if ($has_data) : ?>
        <!-- show gauges -->
        <?php if (!empty($gauges_html)) : ?>
            <ul class="nav flex-wrap">
                <?php echo $gauges_html; ?>
            </ul>
        <?php endif; ?>

        <!-- show buttons (icons) -->
        <?php if (!empty($buttons_html)) : ?>
            <ul class="nav flex-wrap">
                <?php echo $buttons_html; ?>
            </ul>
        <?php endif; ?>

        <!-- show lists -->
        <?php if (!empty($lists_html)) : ?>
            <ul class="mt-3">
                <?php echo $lists_html; ?>
            </ul>
        <?php endif; ?>

        <!-- show tabular data -->
        <?php if (!empty($tables_html)) : ?>
            <div class="mt-3">
                <?php echo $tables_html; ?>
            </div>
        <?php endif; ?>

        <!-- show reports, links -->
        <?php if (!empty($reports_html)) : ?>
            <ul class="nav flex-wrap">
                <?php echo $reports_html; ?>
            </ul>
        <?php endif; ?>
    <?php else : ?>
        <?php echo LayoutHelper::render('joomla.content.emptystate_module', [
            'textPrefix' => 'MOD_HEALTHCHECK',
            'icon'       => 'icon-heart',
            'title'      => 'MOD_HEALTHCHECK_NO_MATCHING_RESULTS'
        ]); ?>
    <?php endif; ?>
</nav>
<?php if (!empty($footer_html)) : ?>
    <div class="health-checks module-footertext px-3 pb-3">
        <?php echo $footer_html; ?>
    </div>
<?php endif; ?>
