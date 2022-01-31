<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string       $autocomplete   Autocomplete attribute for the field.
 * @var   boolean      $autofocus      Is autofocus enabled?
 * @var   string       $class          Classes for the input.
 * @var   string       $description    Description of the field.
 * @var   boolean      $disabled       Is this field disabled?
 * @var   string       $group          Group the field belongs to. <fields> section in form XML.
 * @var   boolean      $hidden         Is this field hidden in the form?
 * @var   string       $hint           Placeholder for the field.
 * @var   string       $id             DOM id of the field.
 * @var   string       $label          Label of the field.
 * @var   string       $labelclass     Classes to apply to the label.
 * @var   boolean      $multiple       Does this field support multiple values?
 * @var   string       $name           Name of the input field.
 * @var   string       $onchange       Onchange attribute for the field.
 * @var   string       $onclick        Onclick attribute for the field.
 * @var   string       $pattern        Pattern (Reg Ex) of value of the form field.
 * @var   boolean      $readonly       Is this field read only?
 * @var   boolean      $repeat         Allows extensions to duplicate elements.
 * @var   boolean      $required       Is this field required?
 * @var   integer      $size           Size attribute of the input.
 * @var   boolean      $spellcheck     Spellcheck state for the form field.
 * @var   string       $validate       Validation rules to apply.
 * @var   array        $value          Value of the field.
 * @var   array        $menus          List of the menu items
 * @var   array        $menubarSource  Menu items for builder
 * @var   array        $buttons        List of the buttons
 * @var   array        $buttonsSource  Buttons by group, for the builder
 * @var   array        $toolbarPreset  Toolbar preset (default values)
 * @var   int          $setsAmount     Amount of sets
 * @var   array        $setsNames      List of Sets names
 * @var   Form[]       $setsForms      Form with extra options for an each set
 * @var   string       $languageFile   TinyMCE language file to translate the buttons
 * @var   FileLayout   $this           Context
 */

/** @var HtmlDocument $doc */
$doc = Factory::getApplication()->getDocument();
$wa  = $doc->getWebAssetManager();

// Add assets
$wa->registerAndUseStyle('tinymce.skin', 'media/vendor/tinymce/skins/ui/oxide/skin.min.css')
	->registerAndUseStyle('plg_editors_tinymce.builder', 'plg_editors_tinymce/tinymce-builder.css', [], [], ['tinymce.skin', 'dragula'])
	->registerAndUseScript('plg_editors_tinymce.builder', 'plg_editors_tinymce/tinymce-builder.js', [], ['type' => 'module'], ['core', 'dragula'])
	->useStyle('webcomponent.joomla-tab')
	->useScript('webcomponent.joomla-tab');

// Add TinyMCE language file to translate the buttons
if ($languageFile)
{
	$wa->registerAndUseScript('tinymce.language', $languageFile, [], ['defer' => true]);
}

// Add the builder options
$doc->addScriptOptions('plg_editors_tinymce_builder',
	[
		'menus'         => $menus,
		'buttons'       => $buttons,
		'toolbarPreset' => $toolbarPreset,
		'formControl'   => $name . '[toolbars]',
	]
);

?>
<div id="joomla-tinymce-builder">
	<h3><?php echo Text::_('PLG_TINY_SET_TARGET_PANEL_TITLE'); ?></h3>
	<p><?php echo Text::_('PLG_TINY_SET_TARGET_PANEL_DESCRIPTION'); ?></p>
	<p><?php echo Text::_('PLG_TINY_SET_SOURCE_PANEL_DESCRIPTION'); ?></p>
	<div class="tox tox-tinymce">
		<div class="tox-editor-container">
			<div class="tox-menubar tinymce-builder-menu source" data-group="menu"
				data-value="<?php echo $this->escape(json_encode($menubarSource)); ?>">
			</div>
			<div class="tox-toolbar tinymce-builder-toolbar source" data-group="toolbar"
				data-value="<?php echo $this->escape(json_encode($buttonsSource)); ?>">
			</div>
		</div>
	</div>
	<hr>
	<joomla-tab orientation="vertical" id="joomla-tinymce-builder-sets" recall breakpoint="974">
		<?php foreach ($setsNames as $num => $title) : ?>
		<?php $isActive = $num === $setsAmount - 1; ?>
			<joomla-tab-element class="tab-pane" id="set-<?php echo $num; ?>" <?php echo $isActive; ?> name="<?php echo $title; ?>">
				<?php // Render tab content for each set ?>
					<?php
						$presetButtonClasses = [
							'simple'   => 'btn-success',
							'medium'   => 'btn-info',
							'advanced' => 'btn-warning',
						];
						// Check whether the values exists, and if empty then use from preset
						if (empty($value['toolbars'][$num]['menu'])
							&& empty($value['toolbars'][$num]['toolbar1'])
							&& empty($value['toolbars'][$num]['toolbar2']))
						{
							// Take the preset for default value
							switch ($num) {
								case 0:
									$preset = $toolbarPreset['advanced'];
									break;
								case 1:
									$preset = $toolbarPreset['medium'];
									break;
								default:
									$preset = $toolbarPreset['simple'];
							}

							$value['toolbars'][$num] = $preset;
						}

						// Take existing values
						$valMenu = empty($value['toolbars'][$num]['menu'])     ? array() : $value['toolbars'][$num]['menu'];
						$valBar1 = empty($value['toolbars'][$num]['toolbar1']) ? array() : $value['toolbars'][$num]['toolbar1'];
						$valBar2 = empty($value['toolbars'][$num]['toolbar2']) ? array() : $value['toolbars'][$num]['toolbar2'];

					?>
					<?php echo $this->sublayout('setaccess', array('form' => $setsForms[$num])); ?>
					<div class="btn-toolbar float-end mt-3">
						<div class="btn-group btn-group-sm">

						<?php foreach(array_keys($toolbarPreset) as $presetName) :
							$btnClass = empty($presetButtonClasses[$presetName]) ? 'btn-primary' : $presetButtonClasses[$presetName];
							?>
							<button type="button" class="btn <?php echo $btnClass; ?> button-action"
								data-action="setPreset" data-preset="<?php echo $presetName; ?>" data-set="<?php echo $num; ?>">
								<?php echo Text::_('PLG_TINY_SET_PRESET_BUTTON_' . $presetName); ?>
							</button>
						<?php endforeach; ?>

							<button type="button" class="btn btn-danger button-action"
								data-action="clearPane" data-set="<?php echo $num; ?>">
								<?php echo Text::_('JCLEAR'); ?>
							</button>
						</div>
					</div>

					<div class="clearfix mb-1"></div>

					<div class="tox tox-tinymce mb-3">
						<div class="tox-editor-container">
							<div class="tox-menubar tinymce-builder-menu target"
								data-group="menu" data-set="<?php echo $num; ?>"
								data-value="<?php echo $this->escape(json_encode($valMenu)); ?>">
							</div>
							<div class="tox-toolbar tinymce-builder-toolbar target"
								data-group="toolbar1" data-set="<?php echo $num; ?>"
								data-value="<?php echo $this->escape(json_encode($valBar1)); ?>">
							</div>
							<div class="tox-toolbar tinymce-builder-toolbar target"
								data-group="toolbar2" data-set="<?php echo $num; ?>"
								data-value="<?php echo $this->escape(json_encode($valBar2)); ?>">
							</div>
						</div>
					</div>

					<?php // Render the form for extra options ?>
					<?php echo $this->sublayout('setoptions', array('form' => $setsForms[$num])); ?>
			</joomla-tab-element>
		<?php endforeach; ?>
	</joomla-tab>
</div>
