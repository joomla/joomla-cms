<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_guidedtours
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$hideLinks = $app->getInput()->getBool('hidemainmenu');

if ($hideLinks || !$tours) {
    return;
}

// Load the Bootstrap Dropdown
$app->getDocument()
    ->getWebAssetManager()
    ->useScript('bootstrap.dropdown')
    ->useScript('dialog')
;

$lang       = $app->getLanguage();
$extension  = $app->getInput()->get('option');
$listTours  = [];
$allTours   = [];
$toursCount = $params->get('tourscount', 7);

foreach ($tours as $tour) :
    if ($toursCount > 0 && count(array_intersect(['*', $extension], $tour->extensions))) :
        $listTours[] = $tour;
        $toursCount--;
    endif;

    $uri = new Uri($tour->url);

    // We assume the url is the starting point
    $key = $uri->getVar('option') ?? Text::_('MOD_GUIDEDTOURS_GENERIC_TOUR');

    if (!isset($allTours[$key])) :
        $lang->load("$key.sys", JPATH_ADMINISTRATOR)
        || $lang->load("$key.sys", JPATH_ADMINISTRATOR . '/components/' . $key);

        $allTours[$key] = [];
    endif;

    $allTours[$key][] = $tour;
endforeach;

$popupId      = 'guidedtours-popup-content' . $module->id;
$popupOptions = json_encode([
    'src'        => '#' . $popupId,
    'width'      => '800px',
    'height'     => 'fit-content',
    'textHeader' => Text::_('MOD_GUIDEDTOURS_START_TOUR'),
]);

?>
<div class="header-item-content dropdown header-tours d-none d-sm-block">
    <button class="dropdown-toggle d-flex align-items-center ps-0 py-0" data-bs-toggle="dropdown" type="button" title="<?php echo Text::_('MOD_GUIDEDTOURS_MENU'); ?>">
        <div class="header-item-icon">
            <span class="icon-map-signs" aria-hidden="true"></span>
        </div>
        <div class="header-item-text">
            <?php echo Text::_('MOD_GUIDEDTOURS_MENU'); ?>
        </div>
        <span class="icon-angle-down" aria-hidden="true"></span>
    </button>
    <div class="dropdown-menu dropdown-menu-end">
        <?php foreach ($listTours as $tour) : ?>
            <button type="button" class="button-start-guidedtour dropdown-item" data-id="<?php echo $tour->id ?>">
                <span class="icon-map-signs" aria-hidden="true"></span>
                <?php echo $tour->title; ?>
            </button>
        <?php endforeach; ?>
        <button type="button" class="dropdown-item text-center" data-joomla-dialog="<?php echo htmlspecialchars($popupOptions); ?>">
            <?php echo Text::_('MOD_GUIDEDTOURS_SHOW_ALL'); ?>
        </button>
    </div>
</div>
<?php

$modalHtml = [];
$modalHtml[] = '<div class="p-3">';
$modalHtml[] = '<div class="row">';
foreach ($allTours as $extension => $tours) :
    $modalHtml[] = '<div class="col-lg-6">';
    $modalHtml[] = '<h4>' . Text::_($extension) . '</h4>';
    $modalHtml[] = '<ul class="list-unstyled">';
    foreach ($tours as $tour) :
        $modalHtml[] = '<li>';
        $modalHtml[] = '<a href="#" role="button" class="button-start-guidedtour" data-id="' . (int) $tour->id . '">' . htmlentities($tour->title) . '</a>';
        $modalHtml[] = '</li>';
    endforeach;
    $modalHtml[] = '</ul>';
    $modalHtml[] = '</div>';
endforeach;
$modalHtml[] = '</div>';
$modalHtml[] = '</div>';

$modalBody = implode($modalHtml);

?>
<template id="<?php echo $popupId; ?>"><?php echo $modalBody; ?></template>
