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

/** @var \Joomla\CMS\Layout\FileLayout $this */

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

// Do nothing when propagate is disabled
if (empty($canDo['propagate'])) {
    return;
}

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
Text::script('JGLOBAL_ASSOCIATIONS_PROPAGATE_FAILED');

// Language propagate callback name
// Strip off language tag at the end
$tagLength            = strlen($language);
$callbackFunctionStem = substr("jSelectArticle_" . $id, 0, -$tagLength);

?>

<button type="button" class="btn btn-primary" <?php echo $value ? '' : 'hidden'; ?>
        title="<?php echo $this->escape(Text::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_TIP')); ?>"
        data-show-when-value="1"
        onclick="Joomla.propagateAssociation('<?php echo $id; ?>', '<?php echo $callbackFunctionStem; ?>')">
    <span class="icon-sync" aria-hidden="true"></span> <?php echo Text::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_BUTTON'); ?>
</button>
