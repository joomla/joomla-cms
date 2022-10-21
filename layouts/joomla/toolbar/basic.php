<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

Factory::getDocument()->getWebAssetManager()
    ->useScript('core')
    ->useScript('webcomponent.toolbar-button');

extract($displayData, EXTR_OVERWRITE);

/**
 * Layout variables
 * -----------------
 * @var   int     $id
 * @var   string  $onclick
 * @var   string  $class
 * @var   string  $text
 * @var   string  $btnClass
 * @var   string  $tagName
 * @var   string  $htmlAttributes
 * @var   string  $task             The task which should be executed
 * @var   bool    $listCheck        Boolean, whether selection from a list is needed
 * @var   string  $form             CSS selector for a target form
 * @var   bool    $formValidation   Whether the form need to be validated before run the task
 * @var   string  $dropdownItems    The dropdown HTML
 * @var   string  $hasButtons
 * @var   string  $caretClass
 * @var   string  $toggleSplit
 */
$tagName = $tagName ?? 'button';

$taskAttr = '';
$title    = '';
$idAttr   = !empty($id)             ? ' id="' . $id . '"' : '';
$listAttr = !empty($listCheck)      ? ' list-selection' : '';
$formAttr = !empty($form)           ? ' form="' . $this->escape($form) . '"' : '';
$validate = !empty($formValidation) ? ' form-validation' : '';
$msgAttr  = !empty($message)        ? ' confirm-message="' . $this->escape($message) . '"' : '';

if ($id === 'toolbar-help') {
    $title = ' title="' . Text::_('JGLOBAL_OPENS_IN_A_NEW_WINDOW') . '"';
}

if (!empty($task)) {
    $taskAttr = ' task="' . $task . '"';
} elseif (!empty($onclick)) {
    $htmlAttributes .= ' onclick="' . $onclick . '"';
}

$direction = Factory::getLanguage()->isRtl() ? 'dropdown-menu-end' : '';
?>
<joomla-toolbar-button <?php echo $idAttr . $taskAttr . $listAttr . $formAttr . $validate . $msgAttr; ?>>
<<?php echo $tagName; ?>
    class="<?php echo $btnClass ?? ''; ?>"
    <?php echo $htmlAttributes ?? ''; ?>
    <?php echo $title; ?>
    >
    <span class="<?php echo trim($class ?? ''); ?>" aria-hidden="true"></span>
    <?php echo $text ?? ''; ?>
</<?php echo $tagName; ?>>
<?php // If there is no toggle split then ensure the drop down items are rendered inside the custom element ?>
<?php if (!($toggleSplit ?? true) && isset($dropdownItems) && trim($dropdownItems) !== '') : ?>
    <div class="dropdown-menu<?php echo ' ' . $direction; ?>">
        <?php echo $dropdownItems; ?>
    </div>
<?php endif; ?>
</joomla-toolbar-button>
