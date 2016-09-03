<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die;

extract( $displayData );

/**
 * Layout variables
 * -----------------
 * @var   string  $autocomplete   Autocomplete attribute for the field.
 * @var   boolean $autofocus      Is autofocus enabled?
 * @var   string  $class          Classes for the input.
 * @var   string  $description    Description of the field.
 * @var   boolean $disabled       Is this field disabled?
 * @var   string  $group          Group the field belongs to. <fields> section in form XML.
 * @var   boolean $hidden         Is this field hidden in the form?
 * @var   string  $hint           Placeholder for the field.
 * @var   string  $id             DOM id of the field.
 * @var   string  $label          Label of the field.
 * @var   string  $labelclass     Classes to apply to the label.
 * @var   boolean $multiple       Does this field support multiple values?
 * @var   string  $name           Name of the input field.
 * @var   string  $onchange       Onchange attribute for the field.
 * @var   string  $onclick        Onclick attribute for the field.
 * @var   string  $pattern        Pattern (Reg Ex) of value of the form field.
 * @var   boolean $readonly       Is this field read only?
 * @var   boolean $repeat         Allows extensions to duplicate elements.
 * @var   boolean $required       Is this field required?
 * @var   integer $size           Size attribute of the input.
 * @var   boolean $spellcheck     Spellcheck state for the form field.
 * @var   string  $validate       Validation rules to apply.
 * @var   string  $value          Value attribute of the field.
  *
 * @var   array   $menus           List of the menu items
 * @var   array   $menubarSource   Menu items for builder
 * @var   array   $buttons         List of the buttons
 * @var   array   $buttonsSource   Buttons by group, for the builder
 * @var   array   $toolbarPreset   Toolbar presset (default values)
 * @var   array   $viewLevels      List of Access View Levels
 * @var   JForm[] $viewLevelForms  Form with extra options for each level
 *
 * @var   JLayoutFile  $this       Context
 */

JHtml::_( 'behavior.core' );
JHtml::_( 'stylesheet', 'media/editors/tinymce/skins/lightgray/skin.min.css', array(), false );
JHtml::_( 'jquery.ui', array( 'core', 'sortable' ) );
JHtml::_( 'script', 'editors/tinymce/tinymce-builder.js', false, true );

$doc = JFactory::getDocument();
$doc->addScriptOptions('plg_editors_tinymce_builder', array(
	'menus'         => $menus,
	'buttons'       => $buttons,
	'toolbarPreset' => $toolbarPreset,
	'formControl'   => $name . '[toolbars]',
));

?>
<div id="joomla-tinymce-builder">
	<div class="mce-tinymce mce-container mce-panel">
		<div class="mce-container-body mce-stack-layout">

			<div class="mce-container mce-menubar mce-toolbar mce-stack-layout-item">
				<div class="mce-container-body mce-flow-layout timymce-builder-menu source" data-group="menu"
					data-value="<?php echo $this->escape(json_encode($menubarSource)); ?>">
				</div>
			</div>

			<div class="mce-toolbar-grp mce-container mce-panel mce-stack-layout-item">
				<div class="mce-container-body mce-flow-layout timymce-builder-toolbar source" data-group="toolbar"
					data-value="<?php echo $this->escape(json_encode($buttonsSource)); ?>">
				</div>
			</div>
		</div>
	</div>

	<!-- Render tabs for each view level -->
	<ul class="nav nav-tabs" id="view-level-tabs">
		<?php foreach ( $viewLevels as $i => $level ): ?>
		<li class="<?php echo ! $i ? 'active' : '' ?>">
			<a href="#view-level-<?php echo $level['value']; ?>"><?php echo $level['text']; ?></a>
		</li>
		<?php endforeach; ?>
	</ul>

	<!-- Render tab content for each view level -->
	<div class="tab-content">
		<?php foreach ( $viewLevels as $i => $level ):
			$levelId = $level['value'];

			// Take the preset for default value
			$preset  = $levelId == 6 || $levelId == 3 ? $toolbarPreset['advanced'] : $toolbarPreset['simple'];

			// Take existing values
			$valMenu = empty($value['toolbars'][$levelId]['menu']) ? $preset['menu'] : $value['toolbars'][$levelId]['menu'];
			$valBar1 = empty($value['toolbars'][$levelId]['toolbar1']) ? $preset['toolbar1'] : $value['toolbars'][$levelId]['toolbar1'];
			$valBar2 = empty($value['toolbars'][$levelId]['toolbar2']) ? $preset['toolbar2'] : $value['toolbars'][$levelId]['toolbar2'];
		?>
			<div class="tab-pane <?php echo ! $i ? 'active' : '' ?>" id="view-level-<?php echo $levelId; ?>">
				<div class="btn-toolbar clearfix">
					<div class="btn-group pull-right">
						<button type="button" class="btn btn-mini btn-success button-action"
							data-action="setPreset" data-preset="simple" data-level="<?php echo $levelId; ?>">
							<?php echo JText::_('PLG_TINY_FIELD_VALUE_SIMPLE'); ?></button>
						<button type="button" class="btn btn-mini btn-warning button-action"
						    data-action="setPreset" data-preset="advanced" data-level="<?php echo $levelId; ?>">
							<?php echo JText::_('PLG_TINY_FIELD_VALUE_ADVANCED'); ?></button>
						<button type="button" class="btn btn-mini btn-danger button-action"
						     data-action="clearPane" data-level="<?php echo $levelId; ?>">
							<?php echo JText::_('JCLEAR'); ?></button>
					</div>
				</div>

				<div class="mce-tinymce mce-container mce-panel">
					<div class="mce-container-body mce-stack-layout">
						<div class="mce-container mce-menubar mce-toolbar timymce-builder-menu target"
							data-group="menu" data-level="<?php echo $levelId; ?>"
							data-value="<?php echo $this->escape(json_encode($valMenu)); ?>"></div>

						<div class="mce-toolbar-grp mce-container mce-panel timymce-builder-toolbar target"
						    data-group="toolbar1" data-level="<?php echo $levelId; ?>"
						    data-value="<?php echo $this->escape(json_encode($valBar1)); ?>"></div>
						<div class="mce-toolbar-grp mce-container mce-panel timymce-builder-toolbar target"
						    data-group="toolbar2" data-level="<?php echo $levelId; ?>"
						    data-value="<?php echo $this->escape(json_encode($valBar2)); ?>"></div>
					</div>
				</div>

				<!-- Render the form for extra options -->
				<?php echo $this->sublayout('leveloptions', array('form' => $viewLevelForms[$level['value']])); ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>

<style>
	.mce-menubar,
	.mce-panel {
		min-height: 18px;
		border-bottom: 1px solid rgba(217,217,217,0.52);
	}

	.mce-tinymce {
		margin-bottom: 20px;
	}
</style>