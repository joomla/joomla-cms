<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_fields
 * 
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

if (! key_exists('field', $displayData))
{
	return;
}

$field = $displayData['field'];
$value = $field->value;
if (! $value)
{
	return;
}

// Loading the language
JFactory::getLanguage()->load('com_fields', JPATH_ADMINISTRATOR . '/components/com_fields');

JHtml::_('jquery.framework');

$doc = JFactory::getDocument();

// Adding the javascript gallery library
$doc->addScript('media/com_fields/js/fotorama.min.js');
$doc->addStyleSheet('media/com_fields/css/fotorama.min.css');

$value = (array) $value;

$thumbWidth = $field->fieldparams->get('thumbnail_width', '64');
$maxImageWidth = $field->fieldparams->get('max_width');

// Main container
$buffer = '<div class="fotorama" data-nav="thumbs" data-width="100%">';
foreach ($value as $path)
{
	// Only process valid paths
	if (! $path)
	{
		continue;
	}

	// The root folder
	$root = $field->fieldparams->get('directory', 'images');
	foreach (JFolder::files(JPATH_ROOT . '/' . $root . '/' . $path, '.', $field->fieldparams->get('recursive', '1'), true) as $file)
	{
		// Skip none image files
		if (! in_array(strtolower(JFile::getExt($file)), array(
				'jpg',
				'png',
				'bmp',
				'gif'
		)))
		{
			continue;
		}

		// Relative path
		$localPath = str_replace(JPATH_ROOT . '/' . $root . '/', '', $file);
		$webImagePath = $root . '/' . $localPath;

		if ($maxImageWidth)
		{
			$resize = JPATH_CACHE . '/com_fields/gallery/' . $field->id . '/' . $maxImageWidth . '/' . $localPath;
			if (! JFile::exists($resize))
			{
				// Creating the folder structure for the max sized image
				if (! JFolder::exists(dirname($resize)))
				{
					JFolder::create(dirname($resize));
				}
				// Getting the properties of the image
				$properties = JImage::getImageFileProperties($file);
				if ($properties->width > $maxImageWidth)
				{
					// Creating the max sized image for the image
					$imgObject = new JImage($file);
					$imgObject->resize($maxImageWidth, 0, false, JImage::SCALE_INSIDE);
					$imgObject->toFile($resize);
				}
			}
			if (JFile::exists($resize))
			{
				$webImagePath = JUri::base(true) . str_replace(JPATH_ROOT, '', $resize);
			}
		}

		// Thumbnail path for the image
		$thumb = JPATH_CACHE . '/com_fields/gallery/' . $field->id . '/' . $thumbWidth . '/' . $localPath;

		if (! JFile::exists($thumb))
		{
			try
			{
				// Creating the folder structure for the thumbnail
				if (! JFolder::exists(dirname($thumb)))
				{
					JFolder::create(dirname($thumb));
				}

				// Getting the properties of the image
				$properties = JImage::getImageFileProperties($file);
				if ($properties->width > $thumbWidth)
				{
					// Creating the thumbnail for the image
					$imgObject = new JImage($file);
					$imgObject->resize($thumbWidth, 0, false, JImage::SCALE_INSIDE);
					$imgObject->toFile($thumb);
				}
			}
			catch (Exception $e)
			{
				JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_FIELDS_TYPE_GALLERY_IMAGE_ERROR', $file, $e->getMessage()));
			}
		}

		if (JFile::exists($thumb))
		{
			// Linking to the real image and loading only the thumbnail
			$buffer .= '<a href="' . $webImagePath . '"><img src="' . JUri::base(true) . str_replace(JPATH_ROOT, '', $thumb) . '"/></a>';
		}
		else
		{
			// Thumbnail doesn't exist, loading the full image
			$buffer .= '<img src="' . $webImagePath . '"/>';
		}
	}
}
$buffer .= '</div>';

echo $buffer;
