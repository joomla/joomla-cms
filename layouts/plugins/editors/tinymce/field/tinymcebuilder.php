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
 * @var   array   $checkedOptions Options that will be set as checked.
 * @var   boolean $hasValue       Has this field a value assigned?
 * @var   array   $options        Options available for this field.
 *
 * @var   array   $menus           List of the menu items
 * @var   array   $buttons         List of the buttons
 * @var   array   $buttonsSet      Buttons by group, for the builder
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
	'menus'      => $menus,
	'buttons'    => $buttons,
	'buttonsSet' => $buttonsSet,
));

?>
<div id="joomla-tinymce-builder">
	<div class="mce-tinymce mce-container mce-panel">
		<div class="mce-container-body mce-stack-layout">
			<div class="mce-container mce-menubar mce-toolbar mce-stack-layout-item mce-first">
				<div class="mce-container-body mce-flow-layout timymce-builder-menu source">
				<?php foreach($menus as $name => $info): ?>
					<div class="mce-btn mce-menubtn mce-flow-layout-item mce-toolbar-item"
						data-name="<?php echo $this->escape($name); ?>"
					>
						<button type="button" tabindex="-1">
							<span class="mce-txt"><?php echo $info['label']; ?></span> <i class="mce-caret"></i>
						</button>
					</div>
				<?php endforeach; ?>
				</div>
			</div>

			<div class="mce-toolbar-grp mce-container mce-panel mce-stack-layout-item">
				<div class="mce-container-body mce-flow-layout timymce-builder-toolbar source">
					<?php foreach ( $buttonsSet as $name ):
						if ( empty( $buttons[$name] ) )
						{
							continue;
						}
						$button = $buttons[$name];
						?>
						<div class="mce-btn" data-name="<?php echo $this->escape($name); ?>"
						     aria-label="<?php echo $this->escape( $button['label'] ); ?>"
						>
							<button type="button" tabindex="-1">
								<?php if ( ! empty( $button['text'] ) ): ?>
									<?php echo $button['text']; ?>
								<?php else: ?>
									<i class="mce-ico mce-i-<?php echo $name; ?>"></i>
								<?php endif; ?>
							</button>
						</div>
					<?php endforeach; ?>
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
		<?php foreach ( $viewLevels as $i => $level ): ?>
			<div class="tab-pane <?php echo ! $i ? 'active' : '' ?>" id="view-level-<?php echo $level['value']; ?>">

				<div class="mce-tinymce mce-container mce-panel">
					<div class="mce-container-body mce-stack-layout">
						<div class="mce-container mce-menubar mce-toolbar timymce-builder-menu target"></div>
						<div class="mce-toolbar-grp mce-container mce-panel timymce-builder-toolbar target"></div>
						<div class="mce-toolbar-grp mce-container mce-panel timymce-builder-toolbar target"></div>
					</div>
				</div>

				<!-- Render the form for extra options -->
				<?php //echo $this->sublayout('leveloptions', array('form' => $viewLevelForms[$level['value']])); ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
<script>
!(function($){
	jQuery( document ).ready( function() {
		var $copyHelper = null, removeIntent = false;

		$('.timymce-builder-menu.source').sortable({
			connectWith: '.timymce-builder-menu.target',
			items: '.mce-btn',
			cancel: '',
			//tolerance: 'pointer',
			// http://stackoverflow.com/questions/6940390/how-do-i-duplicate-item-when-using-jquery-sortable
			helper: function(event, el) {
				$copyHelper = el.clone().insertAfter(el);
				return el;
			},
			stop: function() {
				$copyHelper && $copyHelper.remove();
			}
		});

		$('.timymce-builder-toolbar.source').sortable({
			connectWith: '.timymce-builder-toolbar.target',
			items: '.mce-btn',
			cancel: '',
			//tolerance: 'pointer',
			helper: function(event, el) {
				$copyHelper = el.clone().insertAfter(el);
				return el;
			},
			stop: function() {
				$copyHelper && $copyHelper.remove();
			}
		});

		$('.timymce-builder-menu.target, .timymce-builder-toolbar.target').sortable({
			items: '.mce-btn',
			cancel: '',
			//tolerance: 'pointer',
			receive: function(event, el) {
				$copyHelper = null;
			},
			over: function (event, ui) {
				removeIntent = false;
			},
			out: function (event, ui) {
				removeIntent = true;
			},
			beforeStop: function (event, ui) {
				if(removeIntent){
					ui.item.remove();
				}
			}
		});
	});
})(jQuery);

</script>
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