<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string   $autocomplete    Autocomplete attribute for the field.
 * @var   boolean  $autofocus       Is autofocus enabled?
 * @var   string   $class           Classes for the input.
 * @var   string   $description     Description of the field.
 * @var   boolean  $disabled        Is this field disabled?
 * @var   string   $group           Group the field belongs to. <fields> section in form XML.
 * @var   boolean  $hidden          Is this field hidden in the form?
 * @var   string   $hint            Placeholder for the field.
 * @var   string   $id              DOM id of the field.
 * @var   string   $label           Label of the field.
 * @var   string   $labelclass      Classes to apply to the label.
 * @var   boolean  $multiple        Does this field support multiple values?
 * @var   string   $name            Name of the input field.
 * @var   string   $onchange        Onchange attribute for the field.
 * @var   string   $onclick         Onclick attribute for the field.
 * @var   string   $pattern         Pattern (Reg Ex) of value of the form field.
 * @var   boolean  $readonly        Is this field read only?
 * @var   boolean  $repeat          Allows extensions to duplicate elements.
 * @var   boolean  $required        Is this field required?
 * @var   integer  $size            Size attribute of the input.
 * @var   boolean  $spellcheck      Spellcheck state for the form field.
 * @var   string   $validate        Validation rules to apply.
 * @var   string   $value           Value attribute of the field.
 * @var   string   $dataAttribute   Miscellaneous data attributes preprocessed for HTML output
 * @var   array    $dataAttributes  Miscellaneous data attribute for eg, data-*
 * @var   string   $valueTitle
 * @var   array    $canDo
 * @var   string[] $urls
 * @var   string[] $modalTitles
 * @var   string[] $buttonIcons
 */

// Prepare options for each Modal
$modalSelect = [
    'popupType'  => 'iframe',
    'src'        => empty($urls['select']) ? '' : Route::_($urls['select'], false),
    'textHeader' => Text::_($modalTitles['select'] ?? 'JSELECT'),
];
$modalNew = [
    'popupType'  => 'iframe',
    'src'        => empty($urls['new']) ? '' : Route::_($urls['new'], false),
    'textHeader' => Text::_($modalTitles['new'] ?? 'JACTION_CREATE'),
];
$modalEdit = [
    'popupType'  => 'iframe',
    'src'        => empty($urls['edit']) ? '' : Route::_($urls['edit'], false),
    'textHeader' => Text::_($modalTitles['edit'] ?? 'JACTION_EDIT'),
];

// Decide when the select button always will be visible
$isSelectAlways = !empty($canDo['select']) && empty($canDo['clear']);

?>
<?php if ($modalSelect['src'] && $canDo['select'] ?? true) : ?>
<button type="button" class="btn btn-primary" <?php echo $value && !$isSelectAlways ? 'hidden' : ''; ?>
        data-button-action="select" <?php echo !$isSelectAlways ? 'data-show-when-value=""' : ''; ?>
        data-modal-config="<?php echo $this->escape(json_encode($modalSelect, JSON_UNESCAPED_SLASHES)); ?>">
    <span class="<?php echo !empty($buttonIcons['select']) ? $buttonIcons['select'] : 'icon-file'; ?>" aria-hidden="true"></span> <?php echo Text::_('JSELECT'); ?>
</button>
<?php endif; ?>

<?php if ($modalNew['src'] && $canDo['new'] ?? false) : ?>
<button type="button" class="btn btn-secondary" <?php echo $value ? 'hidden' : ''; ?>
        data-button-action="create" data-show-when-value=""
        data-modal-config="<?php echo $this->escape(json_encode($modalNew, JSON_UNESCAPED_SLASHES)); ?>">
    <span class="icon-plus" aria-hidden="true"></span> <?php echo Text::_('JACTION_CREATE'); ?>
</button>
<?php endif; ?>

<?php if ($modalEdit['src'] && $canDo['edit'] ?? false) : ?>
<button type="button" class="btn btn-primary" <?php echo $value ? '' : 'hidden'; ?>
        data-button-action="edit" data-show-when-value="1"
        data-modal-config="<?php echo $this->escape(json_encode($modalEdit, JSON_UNESCAPED_SLASHES)); ?>"
        data-checkin-url="<?php echo empty($urls['checkin']) ? '' : Route::_($urls['checkin']); ?>">
    <span class="icon-pen-square" aria-hidden="true"></span> <?php echo Text::_('JACTION_EDIT'); ?>
</button>
<?php endif; ?>

<?php if ($canDo['clear'] ?? true) : ?>
<button type="button" class="btn btn-secondary" <?php echo $value ? '' : 'hidden'; ?>
        data-button-action="clear" data-show-when-value="1">
    <span class="icon-times" aria-hidden="true"></span> <?php echo Text::_('JCLEAR'); ?>
</button>
<?php endif; ?>
