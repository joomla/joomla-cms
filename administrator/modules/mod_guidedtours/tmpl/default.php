<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_guidedtours
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$hideLinks = $app->getInput()->getBool('hidemainmenu');

if ($hideLinks || !$tours) {
    return;
}

// Load the Bootstrap Dropdown
$app->getDocument()
    ->getWebAssetManager()
    ->useScript('bootstrap.dropdown');

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
        <button type="button" class="dropdown-item text-center" data-bs-toggle="modal" data-bs-target="#modGuidedTours-modal">
            <?php echo Text::_('MOD_GUIDEDTOURS_SHOW_ALL'); ?>
        </button>
    </div>
</div>
<?php

$modalParams = [
    'title'  => Text::_('MOD_GUIDEDTOURS_START_TOUR'),
    'footer' => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
        . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
];

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

$modalCode = HTMLHelper::_('bootstrap.renderModal', 'modGuidedTours-modal', $modalParams, $modalBody);

// We have to attach the modal to the body, otherwise we have problems with the backdrop
$app->getDocument()->getWebAssetManager()->addInlineScript("
document.addEventListener('DOMContentLoaded', function() {
    document.body.insertAdjacentHTML('beforeend', " . json_encode($modalCode) . ");
    const modal = document.getElementById('modGuidedTours-modal');

    // add all the elements inside modal which you want to make focusable
    const focusableElements = 'button, [href]';
    const firstFocusableElement = modal.querySelectorAll(focusableElements)[0]; // get first element to be focused inside modal
    const focusableContent = modal.querySelectorAll(focusableElements);
    const lastFocusableElement = focusableContent[focusableContent.length - 1]; // get last element to be focused inside modal

    document.addEventListener('keydown', function(e) {
      let isTabPressed = e.key === 'Tab' || e.keyCode === 9;

      if (!isTabPressed) {
        return;
      }

      if (e.shiftKey) { // if shift key pressed for shift + tab combination
        if (document.activeElement === firstFocusableElement) {
          lastFocusableElement.focus(); // add focus for the last focusable element
          e.preventDefault();
        }
      } else { // if tab key is pressed
        if (document.activeElement === lastFocusableElement) { // if focused has reached to last focusable element then focus first focusable element after pressing tab
          firstFocusableElement.focus(); // add focus for the first focusable element
          e.preventDefault();
        }
      }
    });

    firstFocusableElement.focus();
});
");
