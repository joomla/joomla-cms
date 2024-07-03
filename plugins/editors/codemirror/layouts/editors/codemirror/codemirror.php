<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.codemirror
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\Registry\Registry;

extract($displayData);

/**
 * Layout variables
 *
 * @var   FileLayout $this
 * @var   object     $options     JS options for editor
 * @var   Registry   $params      Plugin parameters
 * @var   string     $id          The id of the input
 * @var   string     $name        The name of the input
 * @var   integer    $cols        Textarea cols attribute
 * @var   integer    $rows        Textarea rows attribute
 * @var   string     $content     The value
 * @var   string     $buttons     Editor XTD buttons
 */

$option  = ' options="' . $this->escape(json_encode($options)) . '"';
$style   = '';

if ($options->width) {
    $style .= 'width:' . $options->width . ';';
}
if ($options->height) {
    $style .= 'height:' . $options->height . ';';
}

// Fullscreen combo
$fsCombo = '';
if (empty($options->readOnly)) {
    $fskeys          = $params->get('fullScreenMod', []);
    $fskeys[]        = $params->get('fullScreen', 'F10');
    $fullScreenCombo = implode('-', $fskeys);
    $fsCombo         = ' fs-combo=' . $this->escape($fullScreenCombo);
}

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('plg_editors_codemirror');
$wa->useStyle('plg_editors_codemirror')
    ->useScript('webcomponent.editor-codemirror');
?>
<joomla-editor-codemirror <?php echo $fsCombo . $option; ?>>
<?php echo '<textarea name="' . $name . '" id="' . $id . '" cols="' . $cols . '" rows="' . $rows . '" style="' . $style . '">' . $content . '</textarea>'; ?>
<?php if ($fsCombo) : ?>
    <p class="small float-end">
        <?php echo Text::sprintf('PLG_CODEMIRROR_TOGGLE_FULL_SCREEN', $fullScreenCombo); ?>
    </p>
<?php endif; ?>
<?php echo $buttons ?? ''; ?>
</joomla-editor-codemirror>
