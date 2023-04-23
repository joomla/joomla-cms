<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

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
 * @var   string   $language
 */

// Scripts for backward compatibility
/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('field.modal-fields');
$wa->addInlineScript(
'window.jSelectArticle_' . $id . ' = function (id, title, catid, object, url, language) {
  window.processModalSelect("Article", "' . $id . '", id, title, catid, object, url, language);
}',
    ['name' => 'inline.select_article_' . $id],
    ['type' => 'module']
);

// Language propagate callback name
if ($canDo['propagate'] ?? false) {
    Text::script('JGLOBAL_ASSOCIATIONS_PROPAGATE_FAILED');

    // Strip off language tag at the end
    $tagLength            = strlen($language);
    $callbackFunctionStem = substr("jSelectArticle_" . $id, 0, -$tagLength);
} else {
    $callbackFunctionStem = '';
}

// Prepare options for Modals
$modalSelect = [
    'popupType'  => 'iframe',
    'src'        => $urls['select'] ?? '',
    'textHeader' => $modalTitles['select'] ?? Text::_('JSELECT'),
];
$modalNew = [
    'popupType'  => 'iframe',
    'src'        => $urls['new'] ?? '',
    'textHeader' => $modalTitles['new'] ?? Text::_('JACTION_CREATE'),
];
$modalEdit = [
    'popupType'  => 'iframe',
    'src'        => $urls['edit'] ?? '',
    'textHeader' => $modalTitles['edit'] ?? Text::_('JACTION_EDIT'),
];

?>

<?php if ($modalSelect['src'] && $canDo['select'] ?? true) : ?>
<button type="button" class="btn btn-primary" <?php echo $value ? 'hidden' : ''; ?>
        data-button-action="select" data-show-when-value=""
        data-modal-config="<?php echo $this->escape(json_encode($modalSelect)); ?>">
    <span class="icon-file" aria-hidden="true"></span> <?php echo Text::_('JSELECT'); ?>
</button>
<?php endif; ?>

<?php if ($modalNew['src'] && $canDo['new'] ?? false) : ?>
<button type="button" class="btn btn-secondary" <?php echo $value ? 'hidden' : ''; ?>
        data-button-action="create" data-show-when-value=""
        data-modal-config="<?php echo $this->escape(json_encode($modalNew)); ?>">
    <span class="icon-plus" aria-hidden="true"></span> <?php echo Text::_('JACTION_CREATE'); ?>
</button>
<?php endif; ?>

<?php if ($modalEdit['src'] && $canDo['edit'] ?? false) : ?>
<button type="button" class="btn btn-primary" <?php echo $value ? '' : 'hidden'; ?>
        data-button-action="edit" data-show-when-value="1"
        data-modal-config="<?php echo $this->escape(json_encode($modalEdit)); ?>"
        data-checkin-url="<?php echo $this->escape($urls['checkin'] ?? ''); ?>">
    <span class="icon-pen-square" aria-hidden="true"></span> <?php echo Text::_('JACTION_EDIT'); ?>
</button>
<?php endif; ?>

<?php if ($canDo['clear'] ?? true) : ?>
<button type="button" class="btn btn-secondary" <?php echo $value ? '' : 'hidden'; ?>
        data-button-action="clear" data-show-when-value="1">
    <span class="icon-times" aria-hidden="true"></span> <?php echo Text::_('JCLEAR'); ?>
</button>
<?php endif; ?>

<?php if ($canDo['propagate'] ?? false) : ?>
<button type="button" class="btn btn-primary" <?php echo $value ? '' : 'hidden'; ?>
        title="<?php echo $this->escape(Text::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_TIP')); ?>"
        data-show-when-value="1"
        onclick="Joomla.propagateAssociation('<?php echo $id; ?>', '<?php echo $callbackFunctionStem; ?>')">
    <span class="icon-sync" aria-hidden="true"></span> <?php echo Text::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_BUTTON'); ?>
</button>
<?php endif; ?>
