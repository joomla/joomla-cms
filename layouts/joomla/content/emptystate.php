<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$textPrefix = $displayData['textPrefix'] ?? '';

if (!$textPrefix) {
    $textPrefix = strtoupper(Factory::getApplication()->getInput()->get('option'));
}

$formURL    = $displayData['formURL'] ?? '';
$createURL  = $displayData['createURL'] ?? '';
$helpURL    = $displayData['helpURL'] ?? '';
$title      = $displayData['title'] ?? Text::_($textPrefix . '_EMPTYSTATE_TITLE');
$content    = $displayData['content'] ?? Text::_($textPrefix . '_EMPTYSTATE_CONTENT');
$icon       = $displayData['icon'] ?? 'icon-copy article';
$append     = $displayData['formAppend'] ?? '';
$btnadd     = $displayData['btnadd'] ?? Text::_($textPrefix . '_EMPTYSTATE_BUTTON_ADD');
?>

<form action="<?php echo Route::_($formURL); ?>" method="post" name="adminForm" id="adminForm">

    <div class="px-4 py-5 my-5 text-center">
        <span class="fa-8x mb-4 <?php echo $icon; ?>" aria-hidden="true"></span>
        <h1 class="display-5 fw-bold"><?php echo $title; ?></h1>
        <div class="col-lg-6 mx-auto">
            <p class="lead mb-4">
                <?php echo $content; ?>
            </p>
            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                <?php if ($createURL && Factory::getApplication()->getInput()->get('tmpl') !== 'component') : ?>
                    <a href="<?php echo Route::_($createURL); ?>"
                     id="confirmButton" class="btn btn-primary btn-lg px-4 me-sm-3 emptystate-btnadd"><?php echo $btnadd; ?></a>
                <?php endif; ?>
                <?php if ($helpURL) : ?>
                    <a href="<?php echo $helpURL; ?>" target="_blank"
                       class="btn btn-outline-secondary btn-lg px-4"><?php echo Text::_('JGLOBAL_LEARN_MORE'); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php
        // Allow appending any modals (Eg: Bulk Import on com_redirect).
        echo $append;
    ?>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
