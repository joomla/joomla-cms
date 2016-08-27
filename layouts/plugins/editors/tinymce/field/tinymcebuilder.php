<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

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
 * @var   array    $checkedOptions  Options that will be set as checked.
 * @var   boolean  $hasValue        Has this field a value assigned?
 * @var   array    $options         Options available for this field.
 *
 * @var   array    $buttons         List of the buttons
 */

$styleCss = 'media/editors/tinymce/skins/lightgray/skin.min.css';
JHtml::_('stylesheet', $styleCss, array(), false);

?>
<div class="mce-tinymce mce-container mce-panel">
	<div class="mce-container-body mce-stack-layout">
		<div class="mce-container mce-menubar mce-toolbar mce-stack-layout-item mce-first">
			<div class="mce-container-body mce-flow-layout">
				<div id="mceu_30"
				     class="mce-widget mce-btn mce-menubtn mce-flow-layout-item mce-first mce-btn-has-text mce-toolbar-item"
				     tabindex="-1" aria-labelledby="mceu_30" role="menuitem" aria-haspopup="true" aria-expanded="false">
					<button id="mceu_30-open" role="presentation" type="button" tabindex="-1"><span
							class="mce-txt">Edit</span> <i class="mce-caret"></i></button>
				</div>
				<div id="mceu_31"
				     class="mce-widget mce-btn mce-menubtn mce-flow-layout-item mce-btn-has-text mce-toolbar-item"
				     tabindex="-1" aria-labelledby="mceu_31" role="menuitem" aria-haspopup="true" aria-expanded="false">
					<button id="mceu_31-open" role="presentation" type="button" tabindex="-1"><span class="mce-txt">Insert</span>
						<i class="mce-caret"></i></button>
				</div>
				<div id="mceu_32"
				     class="mce-widget mce-btn mce-menubtn mce-flow-layout-item mce-btn-has-text mce-toolbar-item"
				     tabindex="-1" aria-labelledby="mceu_32" role="menuitem" aria-haspopup="true" aria-expanded="false">
					<button id="mceu_32-open" role="presentation" type="button" tabindex="-1"><span
							class="mce-txt">View</span> <i class="mce-caret"></i></button>
				</div>
				<div id="mceu_33"
				     class="mce-widget mce-btn mce-menubtn mce-flow-layout-item mce-btn-has-text mce-toolbar-item"
				     tabindex="-1" aria-labelledby="mceu_33" role="menuitem" aria-haspopup="true" aria-expanded="false">
					<button id="mceu_33-open" role="presentation" type="button" tabindex="-1"><span class="mce-txt">Format</span>
						<i class="mce-caret"></i></button>
				</div>
				<div id="mceu_34"
				     class="mce-widget mce-btn mce-menubtn mce-flow-layout-item mce-btn-has-text mce-toolbar-item"
				     tabindex="-1" aria-labelledby="mceu_34" role="menuitem" aria-haspopup="true" aria-expanded="false">
					<button id="mceu_34-open" role="presentation" type="button" tabindex="-1"><span class="mce-txt">Table</span>
						<i class="mce-caret"></i></button>
				</div>
				<div id="mceu_35"
				     class="mce-widget mce-btn mce-menubtn mce-flow-layout-item mce-last mce-btn-has-text mce-toolbar-item"
				     tabindex="-1" aria-labelledby="mceu_35" role="menuitem" aria-haspopup="true" aria-expanded="false">
					<button id="mceu_35-open" role="presentation" type="button" tabindex="-1"><span class="mce-txt">Tools</span>
						<i class="mce-caret"></i></button>
				</div>
			</div>
		</div>

		<div class="mce-toolbar-grp mce-container mce-panel mce-stack-layout-item">
			<div id="mceu_36-body" class="mce-container-body mce-stack-layout">
				<div id="mceu_37" class="mce-container mce-toolbar mce-stack-layout-item mce-first mce-last"
				     role="toolbar">
					<div id="mceu_37-body" class="mce-container-body mce-flow-layout">
						<div id="mceu_38" class="mce-container mce-flow-layout-item mce-first mce-btn-group"
						     role="group">
							<div id="mceu_38-body">
								<div id="mceu_0" class="mce-widget mce-btn mce-btn-small mce-first" tabindex="-1"
								     aria-labelledby="mceu_0" role="button" aria-label="Bold">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-bold"></i></button>
								</div>
								<div id="mceu_1" class="mce-widget mce-btn mce-btn-small" tabindex="-1"
								     aria-labelledby="mceu_1" role="button" aria-label="Italic">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-italic"></i></button>
								</div>
								<div id="mceu_2" class="mce-widget mce-btn mce-btn-small" tabindex="-1"
								     aria-labelledby="mceu_2" role="button" aria-label="Underline">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-underline"></i></button>
								</div>
								<div id="mceu_3" class="mce-widget mce-btn mce-btn-small mce-last" tabindex="-1"
								     aria-labelledby="mceu_3" role="button" aria-label="Strikethrough">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-strikethrough"></i></button>
								</div>
							</div>
						</div>
						<div id="mceu_39" class="mce-container mce-flow-layout-item mce-btn-group" role="group">
							<div id="mceu_39-body">
								<div id="mceu_4" class="mce-widget mce-btn mce-btn-small mce-first" tabindex="-1"
								     aria-labelledby="mceu_4" role="button" aria-label="Align left">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-alignleft"></i></button>
								</div>
								<div id="mceu_5" class="mce-widget mce-btn mce-btn-small" tabindex="-1"
								     aria-labelledby="mceu_5" role="button" aria-label="Align center">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-aligncenter"></i></button>
								</div>
								<div id="mceu_6" class="mce-widget mce-btn mce-btn-small" tabindex="-1"
								     aria-labelledby="mceu_6" role="button" aria-label="Align right">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-alignright"></i></button>
								</div>
								<div id="mceu_7" class="mce-widget mce-btn mce-btn-small mce-last" tabindex="-1"
								     aria-labelledby="mceu_7" role="button" aria-label="Justify">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-alignjustify"></i></button>
								</div>
							</div>
						</div>
						<div id="mceu_40" class="mce-container mce-flow-layout-item mce-btn-group" role="group">
							<div id="mceu_40-body">
								<div id="mceu_8"
								     class="mce-widget mce-btn mce-btn-small mce-menubtn mce-fixed-width mce-listbox mce-first mce-last mce-btn-has-text"
								     tabindex="-1" aria-labelledby="mceu_8" role="button" aria-haspopup="true">
									<button id="mceu_8-open" role="presentation" type="button" tabindex="-1"><span
											class="mce-txt">Paragraph</span> <i class="mce-caret"></i></button>
								</div>
							</div>
						</div>
						<div id="mceu_41" class="mce-container mce-flow-layout-item mce-btn-group" role="group">
							<div id="mceu_41-body">
								<div id="mceu_9" class="mce-widget mce-btn mce-btn-small mce-first" tabindex="-1"
								     aria-labelledby="mceu_9" role="button" aria-label="Bullet list">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-bullist"></i></button>
								</div>
								<div id="mceu_10" class="mce-widget mce-btn mce-btn-small mce-last" tabindex="-1"
								     aria-labelledby="mceu_10" role="button" aria-label="Numbered list">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-numlist"></i></button>
								</div>
							</div>
						</div>
						<div id="mceu_42" class="mce-container mce-flow-layout-item mce-btn-group" role="group">
							<div id="mceu_42-body">
								<div id="mceu_11" class="mce-widget mce-btn mce-btn-small mce-first" tabindex="-1"
								     aria-labelledby="mceu_11" role="button" aria-label="Decrease indent">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-outdent"></i></button>
								</div>
								<div id="mceu_12" class="mce-widget mce-btn mce-btn-small mce-last" tabindex="-1"
								     aria-labelledby="mceu_12" role="button" aria-label="Increase indent">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-indent"></i></button>
								</div>
							</div>
						</div>
						<div id="mceu_43" class="mce-container mce-flow-layout-item mce-btn-group" role="group">
							<div id="mceu_43-body">
								<div id="mceu_13" class="mce-widget mce-btn mce-btn-small mce-first mce-disabled"
								     tabindex="-1" aria-labelledby="mceu_13" role="button" aria-label="Undo"
								     aria-disabled="true">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-undo"></i></button>
								</div>
								<div id="mceu_14" class="mce-widget mce-btn mce-btn-small mce-last mce-disabled"
								     tabindex="-1" aria-labelledby="mceu_14" role="button" aria-label="Redo"
								     aria-disabled="true">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-redo"></i></button>
								</div>
							</div>
						</div>
						<div id="mceu_44" class="mce-container mce-flow-layout-item mce-btn-group" role="group">
							<div id="mceu_44-body">
								<div id="mceu_15" class="mce-widget mce-btn mce-btn-small mce-first" tabindex="-1"
								     aria-labelledby="mceu_15" role="button" aria-label="Insert/edit link">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-link"></i></button>
								</div>
								<div id="mceu_16" class="mce-widget mce-btn mce-btn-small" tabindex="-1"
								     aria-labelledby="mceu_16" role="button" aria-label="Remove link">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-unlink"></i></button>
								</div>
								<div id="mceu_17" class="mce-widget mce-btn mce-btn-small mce-last" tabindex="-1"
								     aria-labelledby="mceu_17" role="button" aria-label="Source code">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-code"></i></button>
								</div>
							</div>
						</div>
						<div id="mceu_45" class="mce-container mce-flow-layout-item mce-btn-group" role="group">
							<div id="mceu_45-body">
								<div id="mceu_18" class="mce-widget mce-btn mce-btn-small mce-first" tabindex="-1"
								     aria-labelledby="mceu_18" role="button" aria-label="Horizontal line">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-hr"></i></button>
								</div>
								<div id="mceu_19" class="mce-widget mce-btn mce-btn-small mce-menubtn mce-last"
								     tabindex="-1" aria-labelledby="mceu_19" role="button" aria-label="Table"
								     aria-haspopup="true">
									<button id="mceu_19-open" role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-table"></i> <i class="mce-caret"></i></button>
								</div>
							</div>
						</div>
						<div id="mceu_46" class="mce-container mce-flow-layout-item mce-btn-group" role="group">
							<div id="mceu_46-body">
								<div id="mceu_20" class="mce-widget mce-btn mce-btn-small mce-first" tabindex="-1"
								     aria-labelledby="mceu_20" role="button" aria-label="Subscript">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-subscript"></i></button>
								</div>
								<div id="mceu_21" class="mce-widget mce-btn mce-btn-small mce-last" tabindex="-1"
								     aria-labelledby="mceu_21" role="button" aria-label="Superscript">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-superscript"></i></button>
								</div>
							</div>
						</div>
						<div id="mceu_47" class="mce-container mce-flow-layout-item mce-btn-group" role="group">
							<div id="mceu_47-body">
								<div id="mceu_22" class="mce-widget mce-btn mce-btn-small mce-first mce-last"
								     tabindex="-1" aria-labelledby="mceu_22" role="button"
								     aria-label="Special character">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-charmap"></i></button>
								</div>
							</div>
						</div>
						<div id="mceu_48" class="mce-container mce-flow-layout-item mce-btn-group" role="group">
							<div id="mceu_48-body">
								<div id="mceu_23"
								     class="mce-widget mce-btn mce-btn-small mce-first mce-last mce-btn-has-text"
								     tabindex="-1" aria-labelledby="mceu_23" role="button" aria-label="Module">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-none icon-file-add"></i><span
											class="mce-txt">Module</span></button>
								</div>
							</div>
						</div>
						<div id="mceu_49" class="mce-container mce-flow-layout-item mce-btn-group" role="group">
							<div id="mceu_49-body">
								<div id="mceu_24"
								     class="mce-widget mce-btn mce-btn-small mce-first mce-last mce-btn-has-text"
								     tabindex="-1" aria-labelledby="mceu_24" role="button" aria-label="Article">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-none icon-file-add"></i><span
											class="mce-txt">Article</span></button>
								</div>
							</div>
						</div>
						<div id="mceu_50" class="mce-container mce-flow-layout-item mce-btn-group" role="group">
							<div id="mceu_50-body">
								<div id="mceu_25"
								     class="mce-widget mce-btn mce-btn-small mce-first mce-last mce-btn-has-text"
								     tabindex="-1" aria-labelledby="mceu_25" role="button" aria-label="Image">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-none icon-pictures"></i><span
											class="mce-txt">Image</span></button>
								</div>
							</div>
						</div>
						<div id="mceu_51" class="mce-container mce-flow-layout-item mce-btn-group" role="group">
							<div id="mceu_51-body">
								<div id="mceu_26"
								     class="mce-widget mce-btn mce-btn-small mce-first mce-last mce-btn-has-text"
								     tabindex="-1" aria-labelledby="mceu_26" role="button" aria-label="Page Break">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-none icon-copy"></i><span
											class="mce-txt">Page Break</span></button>
								</div>
							</div>
						</div>
						<div id="mceu_52" class="mce-container mce-flow-layout-item mce-last mce-btn-group"
						     role="group">
							<div id="mceu_52-body">
								<div id="mceu_27"
								     class="mce-widget mce-btn mce-btn-small mce-first mce-last mce-btn-has-text"
								     tabindex="-1" aria-labelledby="mceu_27" role="button" aria-label="Read More">
									<button role="presentation" type="button" tabindex="-1"><i
											class="mce-ico mce-i-none icon-arrow-down"></i><span class="mce-txt">Read More</span>
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>


	</div>
</div>
