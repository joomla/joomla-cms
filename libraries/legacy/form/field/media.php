<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Provides a modal media selector including upload mechanism
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldMedia extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Media';

	/**
	 * The initialised state of the document object.
	 *
	 * @var    boolean
	 * @since  11.1
	 */
	protected static $initialised = false;

	/**
	 * Method to get the field input markup for a media selector.
	 * Use attributes to identify specific created_by and asset_id fields
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$assetField = $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';
		$authorField = $this->element['created_by_field'] ? (string) $this->element['created_by_field'] : 'created_by';
		$asset = $this->form->getValue($assetField) ? $this->form->getValue($assetField) : (string) $this->element['asset_id'];
		if ($asset == '')
		{
			$asset = JRequest::getCmd('option');
		}

		$link = (string) $this->element['link'];
		if (!self::$initialised)
		{

			// Load the modal behavior script.
			JHtml::_('behavior.modal');

			// Build the script.
			$script = array();
			$script[] = '	function jInsertFieldValue(value, id) {';
			$script[] = '		var old_value = document.id(id).value;';
			$script[] = '		if (old_value != value) {';
			$script[] = '			var elem = document.id(id);';
			$script[] = '			elem.value = value;';
			$script[] = '			elem.fireEvent("change");';
			$script[] = '			if (typeof(elem.onchange) === "function") {';
			$script[] = '				elem.onchange();';
			$script[] = '			}';
			$script[] = '			jMediaRefreshPreview(id);';
			$script[] = '		}';
			$script[] = '	}';

			$script[] = '	function jMediaRefreshPreview(id) {';
			$script[] = '		var value = document.id(id).value;';
			$script[] = '		var img = document.id(id + "_preview");';
			$script[] = '		if (img) {';
			$script[] = '			if (value) {';
			$script[] = '				img.src = "' . JURI::root() . '" + value;';
			$script[] = '				document.id(id + "_preview_empty").setStyle("display", "none");';
			$script[] = '				document.id(id + "_preview_img").setStyle("display", "");';
			$script[] = '			} else { ';
			$script[] = '				img.src = ""';
			$script[] = '				document.id(id + "_preview_empty").setStyle("display", "");';
			$script[] = '				document.id(id + "_preview_img").setStyle("display", "none");';
			$script[] = '			} ';
			$script[] = '		} ';
			$script[] = '	}';

			$script[] = '	function jMediaRefreshPreviewTip(tip)';
			$script[] = '	{';
			$script[] = '		tip.setStyle("display", "block");';
			$script[] = '		var img = tip.getElement("img.media-preview");';
			$script[] = '		var id = img.getProperty("id");';
			$script[] = '		id = id.substring(0, id.length - "_preview".length);';
			$script[] = '		jMediaRefreshPreview(id);';
			$script[] = '	}';

			// Add the script to the document head.
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

			self::$initialised = true;
		}

		// Initialize variables.
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		// The text field.
		$html[] = '<div class="fltlft">';
		$html[] = '	<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . ' readonly="readonly"' . $attr . ' />';
		$html[] = '</div>';

		$directory = (string) $this->element['directory'];
		if ($this->value && file_exists(JPATH_ROOT . '/' . $this->value))
		{
			$folder = explode('/', $this->value);
			array_diff_assoc($folder, explode('/', JComponentHelper::getParams('com_media')->get('image_path', 'images')));
			array_pop($folder);
			$folder = implode('/', $folder);
		}
		elseif (file_exists(JPATH_ROOT . '/' . JComponentHelper::getParams('com_media')->get('image_path', 'images') . '/' . $directory))
		{
			$folder = $directory;
		}
		else
		{
			$folder = '';
		}
		// The button.
		$html[] = '<div class="button2-left">';
		$html[] = '	<div class="blank">';
		$html[] = '		<a class="modal" title="' . JText::_('JLIB_FORM_BUTTON_SELECT') . '"' . ' href="'
			. ($this->element['readonly'] ? ''
			: ($link ? $link
				: 'index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;asset=' . $asset . '&amp;author='
				. $this->form->getValue($authorField)) . '&amp;fieldid=' . $this->id . '&amp;folder=' . $folder) . '"'
			. ' rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
		$html[] = JText::_('JLIB_FORM_BUTTON_SELECT') . '</a>';
		$html[] = '	</div>';
		$html[] = '</div>';

		$html[] = '<div class="button2-left">';
		$html[] = '	<div class="blank">';
		$html[] = '		<a title="' . JText::_('JLIB_FORM_BUTTON_CLEAR') . '"' . ' href="#" onclick="';
		$html[] = 'jInsertFieldValue(\'\', \'' . $this->id . '\');';
		$html[] = 'return false;';
		$html[] = '">';
		$html[] = JText::_('JLIB_FORM_BUTTON_CLEAR') . '</a>';
		$html[] = '	</div>';
		$html[] = '</div>';

		// The Preview.
		$preview = (string) $this->element['preview'];
		$showPreview = true;
		$showAsTooltip = false;
		switch ($preview)
		{
			case 'no':
			case 'none':
				$showPreview = false;
				break;
			case 'yes':
			case 'show':
				break;
			case 'tooltip':
			default:
				$showAsTooltip = true;
				$options = array(
					'onShow' => 'jMediaRefreshPreviewTip',
				);
				JHtml::_('behavior.tooltip', '.hasTipPreview', $options);
				break;
		}

		if ($showPreview)
		{
			if ($this->value && file_exists(JPATH_ROOT . '/' . $this->value))
			{
				$src = JURI::root() . $this->value;
			}
			else
			{
				$src = '';
			}

			$attr = array(
				'id' => $this->id . '_preview',
				'class' => 'media-preview',
				'style' => 'max-width:160px; max-height:100px;'
			);
			$img = JHtml::image($src, JText::_('JLIB_FORM_MEDIA_PREVIEW_ALT'), $attr);
			$previewImg = '<div id="' . $this->id . '_preview_img"' . ($src ? '' : ' style="display:none"') . '>' . $img . '</div>';
			$previewImgEmpty = '<div id="' . $this->id . '_preview_empty"' . ($src ? ' style="display:none"' : '') . '>'
				. JText::_('JLIB_FORM_MEDIA_PREVIEW_EMPTY') . '</div>';

			$html[] = '<div class="media-preview fltlft">';
			if ($showAsTooltip)
			{
				$tooltip = $previewImgEmpty . $previewImg;
				$options = array(
					'title' => JText::_('JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE'),
					'text' => JText::_('JLIB_FORM_MEDIA_PREVIEW_TIP_TITLE'),
					'class' => 'hasTipPreview'
				);
				$html[] = JHtml::tooltip($tooltip, $options);
			}
			else
			{
				$html[] = ' ' . $previewImgEmpty;
				$html[] = ' ' . $previewImg;
			}
			$html[] = '</div>';
		}

		return implode("\n", $html);
	}
}
