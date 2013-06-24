<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Provides a modal media selector including upload mechanism
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 * @since       1.6
 */
class JFormFieldMedia extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $type = 'Media';

	/**
	 * The initialised state of the document object.
	 *
	 * @var    boolean
	 * @since  1.6
	 */
	protected static $initialised = false;

	/**
	 * Method to get the field input markup for a media selector.
	 * Use attributes to identify specific created_by and asset_id fields
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$assetField = $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';
		$authorField = $this->element['created_by_field'] ? (string) $this->element['created_by_field'] : 'created_by';
		$asset = $this->form->getValue($assetField) ? $this->form->getValue($assetField) : (string) $this->element['asset_id'];
		if ($asset == '')
		{
			$asset = JFactory::getApplication()->input->get('option');
		}

		$link = (string) $this->element['link'];
		if (!self::$initialised)
		{
			// Load the modal behavior script.
			JHtml::_('behavior.modal');

			// Build the script.
			$script = '
				function jInsertFieldValue(value, id)
				{
					(function($)
					{
						var old_value = document.id(id).value;
						if (old_value != value) {
							var elem = document.id(id);
							elem.value = value;
							elem.fireEvent("change");
							if (typeof(elem.onchange) === "function") {
								elem.onchange();
							}
							jMediaRefreshPreview(id);
						}
					})(jQuery);
				}

				function jMediaRefreshPreview(id)
				{
					(function($)
					{
						var value = $("#" + id).val();
						var img = $("#" + id + "_preview_img");
						if (img) {
							if (value) {
								img.attr("src", "' . JURI::root() . '" + value);
								$("#" + id + "_preview span.hasTipPreview")
								.attr("title", $("#" + id + "_preview_hasimg").html())
								.tooltip("fixTitle");
								$(".hasTipPreview").on("shown", function () {
									$(".tooltip.image-preview").addClass("hasimage");
								});
							} else {
								img.attr("src", "");
								$("#" + id + "_preview span.hasTipPreview").attr("title", $("#" + id + "_preview_empty").html()).tooltip("fixTitle");
								$(".hasTipPreview").on("shown", function () {
									$(".tooltip.image-preview").removeClass("hasimage");
								});
							}
						}
					})(jQuery);
				}


			';

			// Add the script to the document head.
			JFactory::getDocument()->addScriptDeclaration($script);

			self::$initialised = true;
		}

		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		// The text field.
		$html[] = '<div class="input-prepend input-append">';

		// The Preview.
		$preview = (string) $this->element['preview'];
		switch ($preview)
		{
			case 'no': // Deprecated parameter value
			case 'false':
			case 'none':
				$showPreview = false;
				break;
			default:
				JHtml::_('bootstrap.tooltip', '.hasTipPreview', array('template' => '<div class="tooltip image-preview"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'));
				$showPreview = true;
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
			$width = isset($this->element['preview_width']) ? (int) $this->element['preview_width'] : 300;
			$height = isset($this->element['preview_height']) ? (int) $this->element['preview_height'] : 200;
			$style = '';
			$style .= ($width > 0) ? 'max-width:' . $width . 'px;' : '';
			$style .= ($height > 0) ? 'max-height:' . $height . 'px;' : '';

			$imgattr = array(
				'id' => $this->id . '_preview_img',
				'class' => 'media-preview',
				'style' => $style,
			);
			$img = JHtml::image($src, JText::_('JLIB_FORM_MEDIA_PREVIEW_ALT'), $imgattr);
			$previewHasImg = '<div id="' . $this->id . '_preview_hasimg" style="display:none">'
				. $img . '</div>';
			$previewImgEmpty = '<div id="' . $this->id . '_preview_empty" style="display:none">'
				. JHtml::tooltipText('JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE', 'JLIB_FORM_MEDIA_PREVIEW_EMPTY') . '</div>';

			$html[] = '<div class="media-preview add-on" id="' . $this->id . '_preview">';
			$html[] = $previewImgEmpty . $previewHasImg;
			$options = array(
				'title' => 'JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE',
				'text' => '<i class="icon-eye"></i>',
				'class' => 'hasTipPreview'
			);
			$html[] = JHtml::tooltip('JLIB_FORM_MEDIA_PREVIEW_EMPTY', $options);
			$html[] = '</div>';
		}

		$html[] = '	<input type="text" class="input-small" name="' . $this->name . '" id="' . $this->id . '" value="'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" readonly="readonly"' . $attr . ' />';

		$directory = (string) $this->element['directory'];
		if ($this->value && file_exists(JPATH_ROOT . '/' . $this->value))
		{
			$folder = explode('/', $this->value);
			$folder = array_diff_assoc($folder, explode('/', JComponentHelper::getParams('com_media')->get('image_path', 'images')));
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
		if ($this->element['disabled'] != true)
		{
			JHtml::_('bootstrap.tooltip');

			$html[] = '<a class="modal btn" title="' . JText::_('JLIB_FORM_BUTTON_SELECT') . '" href="'
				. ($this->element['readonly'] ? ''
				: ($link ? $link
					: 'index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;asset=' . $asset . '&amp;author='
					. $this->form->getValue($authorField)) . '&amp;fieldid=' . $this->id . '&amp;folder=' . $folder) . '"'
				. ' rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
			$html[] = JText::_('JLIB_FORM_BUTTON_SELECT') . '</a><a class="btn hasTooltip" title="' . JHtml::tooltipText('JLIB_FORM_BUTTON_CLEAR') . '" href="#" onclick="';
			$html[] = 'jInsertFieldValue(\'\', \'' . $this->id . '\');';
			$html[] = 'return false;';
			$html[] = '">';
			$html[] = '<i class="icon-remove"></i></a>';
		}

		$html[] = '</div>';

		return implode("\n", $html);
	}
}
