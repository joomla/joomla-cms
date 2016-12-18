<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Layout variables
 * ---------------------
 *
 * @var  string   $asset The asset text
 * @var  string   $authorField The label text
 * @var  integer  $authorId The author id
 * @var  string   $class The class text
 * @var  boolean  $disabled True if field is disabled
 * @var  string   $folder The folder text
 * @var  string   $id The label text
 * @var  string   $link The link text
 * @var  string   $name The name text
 * @var  string   $preview The preview image relative path
 * @var  integer  $previewHeight The image preview height
 * @var  integer  $previewWidth The image preview width
 * @var  string   $onchange  The onchange text
 * @var  boolean  $readonly True if field is readonly
 * @var  integer  $size The size text
 * @var  string   $value The value text
 * @var  string   $src The path and filename of the image
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
		$showPreview = true;
		$showAsTooltip = false;
		break;
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
	: ($link ?: 'index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;asset='
		. $asset . '&amp;author=' . $authorId)
	. '&amp;fieldid={field-media-id}&amp;ismoo=0&amp;folder=' . $folder);
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
	data-preview-as-tooltip="<?php echo $showAsTooltip ? 'true' : 'false'; ?>"
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
			'footer' => '<button class="btn" data-dismiss="modal">' . JText::_('JCANCEL') . '</button>'
		)
	);

	JHtml::_('script', 'media/mediafield.min.js', array('version' => 'auto', 'relative' => true));
	?>
	<?php if ($showPreview && $showAsTooltip) : ?>
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
	<?php if ($showPreview && !$showAsTooltip) : ?>
		<div class="field-media-preview" style="width: <?php echo $previewWidth; ?>px; max-height: <?php echo $previewHeight; ?>px;margin-top:10px;"></div>
	<?php endif; ?>
</div>
