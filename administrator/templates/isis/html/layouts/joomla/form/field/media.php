<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

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
 * @var   string   $preview         The preview image relative path
 * @var   integer  $previewHeight   The image preview height
 * @var   integer  $previewWidth    The image preview width
 * @var   string   $asset           The asset text
 * @var   string   $authorField     The label text
 * @var   string   $folder          The folder text
 * @var   string   $link            The link text
 */
extract($displayData);

// The button.
if ($disabled != true)
{
	JHtml::_('bootstrap.tooltip');
}

$attr = '';

// Initialize some field attributes.
$attr .= !empty($class) ? ' class="input-small hasTooltip field-media-input ' . $class . '"' : ' class="input-small hasTooltip field-media-input"';
$attr .= !empty($size) ? ' size="' . $size . '"' : '';

// Initialize JavaScript field attributes.
$attr .= !empty($onchange) ? ' onchange="' . $onchange . '"' : '';

switch ($preview)
{
	case 'no': // Deprecated parameter value
	case 'false':
	case 'none':
		$showPreview = false;
		$showAsTooltip = false;
		break;
	case 'yes': // Deprecated parameter value
	case 'true':
	case 'show':
	case 'tooltip':
	default:
		$showPreview = true;
		$showAsTooltip = true;
		break;
}

// Pre fill the contents of the popover
if ($showPreview)
{
	if ($value && file_exists(JPATH_ROOT . '/' . $value))
	{
		$src = JUri::root() . $value;
	}
	else
	{
		$src = JText::_('JLIB_FORM_MEDIA_PREVIEW_EMPTY');
	}
}

// The url for the modal
$url    = ($readonly ? ''
	: ($link ? $link
		: 'index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;asset='
		. $asset . '&amp;author=' . $authorId)
	. '&amp;fieldid={field-media-id}&amp;folder=' . $folder);
?>
<div class="field-media-wrapper"
	data-basepath="<?php echo JUri::root(); ?>"
	data-url="<?php echo $url; ?>"
	data-modal=".modal"
	data-modal-width="100%"
	data-modal-height="400px"
	data-input=".field-media-input"
	data-button-select=".button-select"
	data-button-clear=".button-clear"
	data-button-save-selected=".button-save-selected"
	data-preview="<?php echo $showPreview ? 'true' : 'false'; ?>"
	data-preview-container=".field-media-preview"
	data-preview-width="<?php echo $previewWidth; ?>"
	data-preview-height="<?php echo $previewHeight; ?>"
	>
	<?php
	// Render the modal
	echo JHtml::_('bootstrap.renderModal',
		'imageModal_'. $id,
		array(
			'title' => JText::_('JLIB_FORM_CHANGE_IMAGE'),
			'closeButton' => true,
			'footer' => '<button type="button" class="btn" data-dismiss="modal">' . JText::_('JCANCEL') . '</button>'
		)
	);

	JHtml::_('script', 'media/mediafield.min.js', false, true, false, false, true);
	?>
	<?php if ($showPreview) : ?>
	<div class="input-prepend input-append">
	<span rel="popover" class="add-on pop-helper field-media-preview"
		title="<?php echo	JText::_('JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE'); ?>" data-content="<?php echo JText::_('JLIB_FORM_MEDIA_PREVIEW_EMPTY'); ?>"
		data-original-title="<?php echo JText::_('JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE'); ?>" data-trigger="hover">
		<i class="icon-eye"></i>
	</span>
	<?php else: ?>
		<div class="input-append">
	<?php endif; ?>
			<input type="text" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>" readonly="readonly"<?php echo $attr; ?>/>
			<?php if ($disabled != true) : ?>
				<a class="btn add-on button-select"><?php echo JText::_("JLIB_FORM_BUTTON_SELECT"); ?></a>
				<a class="btn icon-remove hasTooltip add-on button-clear" title="<?php echo JText::_("JLIB_FORM_BUTTON_CLEAR"); ?>"></a>
			<?php endif; ?>
	</div>
</div>
