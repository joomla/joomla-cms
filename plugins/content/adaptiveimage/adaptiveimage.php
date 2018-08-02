<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.adaptiveimage
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\AdaptiveImage\JSONFocusStore;
use Joomla\CMS\Uri\Uri;

/**
 * Adaptive Image Plugin
 *
 * @since  4.0
 */
class PlgContentAdaptiveImage extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;
	
	/**
	 * Base path for cache images.
	 *
	 * @var     string
	 *
	 * @since   4.0.0
	 */
	protected $cacheDir =  "/media/focus";
	/**
	 * Plugin that inserts focus points into the image.
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   mixed    &$row     An object with a "text" property.
	 * @param   mixed    &$params  Additional parameters.
	 * @param   integer  $page     Optional page number. Unused. Defaults to zero.
	 *
	 * @return  boolean	True on success.
	 */
	public function onContentPrepare($context, &$row, &$params, $page = 0)
	{
		// Add ResponsifyJS into the client page
		HTMLHelper::_('script', 'media/plg_media-action_smartcrop/js/responsive-images.min.js', ['version' => 'auto', 'relative' => false]);

		// Don't run this plugin when the content is being indexed
		if ($context === 'com_finder.indexer')
		{
			return true;
		}

		if (is_object($row))
		{
			return $this->insertFocus($row->text, $params);
		}
		return $this->insertFocus($row, $params);
	}
	/**
	 * Inserts focus points into the image.
	 *
	 * @param   string  &$text    HTML string.
	 * @param   mixed   &$params  Additional parameters.
	 *
	 * @return  boolean  True on success.
	 */
	protected function insertFocus(&$text, &$params)
	{
		// Check if the directory is present or not
		if (! is_dir(JPATH_SITE . $this->cacheDir))
		{
			return false;
		}

		// Get all the content of the directory.
		$cacheFolderImages = scandir(JPATH_SITE . $this->cacheDir);
		
		unset($cacheFolderImages[0]);
		unset($cacheFolderImages[1]);

		// Regular Expression from <img> tags in article
		$searchImage = '(<img[^>]+>)';

		// Match pattern and return array into $images
		preg_match_all($searchImage, $text, $images);

		// Process image one by one
		foreach ($images[0] as $key => $image)
		{
			// Clean path of the image and store in $src[1].
			preg_match('(src="([^"]+)")', $image, $src);

			// Image Path
			$imgPath = "/" . $src[1];

			// Check if the original image is present or not
			if (file_exists($imgPath))
			{
				$storage = new JSONFocusStore;
				$storage->deleteFocus($imgPath);
				$storage->deleteResizedImages($imgPath);
				continue;
			}

			// Filtering only the image name
			$imageName = explode("/", $imgPath);
			$imageName = $imageName[max(array_keys($imageName))];

			$cacheImages = array();
			foreach ($cacheFolderImages as $key => $name)
			{
				// Decrypting the image name 
				$imgWidth = explode("_", $name);
				$imgName = explode(".", $imgWidth[1]);
				$imgWidth = $imgWidth[0];
				$extension = $imgName[1];
				$imgName = base64_decode($imgName[0]) . "." . $extension;
				
				if (strpos($imgName, $imageName))
				{
					$imgData["width"] = $imgWidth;
					$imgData["name"]  = Uri::base() . $this->cacheDir . "/" . $name;
					array_push($cacheImages, $imgData);
				}
			}
			// Arrangeing widths in the order
			arsort($cacheImages);

			// Skiping if no resized images are present
			if (empty($cacheImages))
			{
				continue;
			}
			
			// @TODO Use Jlayout for creation of the picture element.

			// Generating the tag
			$element = "<picture>\n";

			foreach ($cacheImages as $key => $attributes)
			{
				$source = "<source media=\"(min-width: " . $attributes["width"] . "px)\" srcset=\"" . $attributes["name"] . "\">\n";
				$element .= $source;
			}
			$element .= $image . "\n</picture>";

			// Replaceing the previous tag with new one in the article.
			$text = str_replace($image, $element, $text);
		}
		return true;
	}
}
