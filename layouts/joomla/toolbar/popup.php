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
use Joomla\Utilities\ArrayHelper;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   int     $id
 * @var   string  $name
 * @var   string  $doTask
 * @var   string  $class
 * @var   string  $text
 * @var   string  $btnClass
 * @var   string  $tagName
 * @var   bool    $listCheck
 * @var   string  $htmlAttributes
 * @var   string  $modalWidth
 * @var   string  $modalHeight
 * @var   string  $popupType
 * @var   string  $url
 * @var   string  $textHeader
 */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
Factory::getApplication()->getDocument()->getWebAssetManager()
    ->useScript('core')
    ->useScript('joomla.dialog-autocreate')
    ->useScript('webcomponent.toolbar-button');

$tagName    = $tagName ?? 'button';
$modalAttrs = [];

// Check for use of Joomla Dialog, otherwise fallback to BS Modal
if (!empty($popupType)) {
    $popupOptions = [
        'popupType'  => $popupType,
        'src'        => $url,
        'textHeader' => $textHeader ?? '',
        'width'      => $modalWidth ?? null,
        'height'     => $modalHeight ?? null,
    ];

    $modalAttrs['data-joomla-dialog'] = $this->escape(json_encode($popupOptions));
} else {
    // @TODO: Remove this fallback in Joomla 6. Deprecation already triggered in PopupButton class.
    $modalAttrs['data-bs-toggle'] = 'modal';
    $modalAttrs['data-bs-target'] = '#' . $selector;
}

$idAttr   = !empty($id)        ? ' id="' . $id . '"' : '';
$listAttr = !empty($listCheck) ? ' list-selection' : '';

?>
<joomla-toolbar-button <?php echo $idAttr . $listAttr; ?>>
<<?php echo $tagName; ?>
    value="<?php echo $doTask; ?>"
    class="<?php echo $btnClass; ?>"
    <?php echo $htmlAttributes; ?>
    <?php echo ArrayHelper::toString($modalAttrs); ?>
>
    <span class="<?php echo $class; ?>" aria-hidden="true"></span>
    <?php echo $text; ?>
</<?php echo $tagName; ?>>
</joomla-toolbar-button>
