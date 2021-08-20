<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Image\Image;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

/**
 * Responsive Images helper class
 *
 * @since  __DEPLOY_VERSION__
 */
class ResponsiveImagesHelper
{
	/**
	 * Responsive image size options
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $defaultSizes = array('800x600', '600x400', '400x200');

	/**
	 * Responsive image creation method
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $creationMethod = 2;

	/**
	 * Method to generate different-sized versions of form images
	 *
	 * @param   array  $images  images to have responsive versions
	 *
	 * @return  array  images for which responsive sizes are generated
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function generateFormImages($images)
	{
		$imagesGenerated = [];

		foreach ($images as $image)
		{
			// Clean image sources from additional info
			$image->name = HTMLHelper::cleanImageURL($image->name)->url;

			// Generate new responsive images if file exists
			if (is_file(JPATH_ROOT . '/' . $image->name))
			{
				$imgObj = new Image(JPATH_ROOT . '/' . $image->name);
				$imgObj->createMultipleSizes($image->sizes, $image->method);

				$imagesGenerated[] = $image;
			}
		}

		return $imagesGenerated;
	}

	/**
	 * Method to generate different-sized versions of content images
	 *
	 * @param   string   $content  editor content
	 *
	 * @return  array    generated images
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function generateContentImages($content)
	{
		$images = HTMLHelper::getContentImageSources($content);

		$imagesGenerated = [];

		foreach ($images as $image)
		{
			// Generate new responsive images if file exists
			if (is_file(JPATH_ROOT . '/' . $image))
			{
				$method = static::getContentMethod($content, $image);
				$sizes  = static::getContentSizes($content, $image);

				$imgObj = new Image(JPATH_ROOT . '/' . $image);
				$imgObj->createMultipleSizes($sizes, $method);

				$imagesGenerated[] = $image;
			}
		}

		return $imagesGenerated;
	}

	/**
	 * Method that compares initial and final versions of image sizes and finds unused ones
	 *
	 * @param   Image[]  $initImages   initial version
	 * @param   Image[]  $finalImages  final version
	 *
	 * @return  array    paths to the unused images
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getUnusedImages($initImages, $finalImages)
	{
		$unusedImages = [];

		// Final versions empty means that image is deleted
		if (empty($finalImages))
		{
			foreach ($initImages as $initImage)
			{
				$unusedImages[] = explode('/', $initImage->getPath(), 2)[1];
			}

			return $unusedImages;
		}

		// Get final image paths
		foreach ($finalImages as $finalImage)
		{
			$finalImagePaths[] = $finalImage->getPath();
		}

		// Check if initial image path exists in final image paths
		foreach ($initImages as $initImage)
		{
			$initImagePath = $initImage->getPath();

			if (!in_array($initImagePath, $finalImagePaths))
			{
				// Insert image source to array
				$unusedImages[] = explode('/', $initImagePath, 2)[1];
			}
		}

		return $unusedImages;
	}

	/**
	 * Method that takes initial and final versions of form images and finds unused ones
	 *
	 * @param   array  $initImages   initial versions
	 * @param   array  $finalImages  final versions
	 *
	 * @return  array  unused images
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getUnusedFormImages($initImages, $finalImages)
	{
		$unusedImages = [];

		foreach ($finalImages as $key => $finalImage)
		{
			$initImage = $initImages[$key];

			// Clean image names from additional info
			$initImage->name  = HTMLHelper::cleanImageURL($initImage->name)->url;
			$finalImage->name = HTMLHelper::cleanImageURL($finalImage->name)->url;

			if (is_file(JPATH_ROOT . '/' . $initImage->name))
			{
				$initImgObj  = new Image(JPATH_ROOT . '/' . $initImage->name);

				$initRespImages  = $initImgObj->generateMultipleSizes($initImage->sizes, $initImage->method);
				$finalRespImages = [];

				// If image exists in final, get final responsive versions
				if (is_file(JPATH_ROOT . '/' . $finalImage->name))
				{
					$finalImgObj = new Image(JPATH_ROOT . '/' . $finalImage->name);
					$finalRespImages = $finalImgObj->generateMultipleSizes($finalImage->sizes, $finalImage->method);
				}

				$unusedImages = array_merge($unusedImages, static::getUnusedImages($initRespImages, $finalRespImages));
			}
		}

		return $unusedImages;
	}

	/**
	 * Method that takes initial and final versions of content images and finds unused ones
	 *
	 * @param   string  $initContent   initial version
	 * @param   string  $finalContent  final version
	 *
	 * @return  array   unused images
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getUnusedContentImages($initContent, $finalContent)
	{
		// Get initial images
		$initImages = HTMLHelper::getContentImageSources($initContent);

		$unusedImages = [];

		foreach ($initImages as $initImage)
		{
			if (is_file(JPATH_ROOT . '/' . $initImage))
			{
				$imgObj = new Image(JPATH_ROOT . '/' . $initImage);

				// Get initial sizes
				$initRespImages = $imgObj->generateMultipleSizes(
					static::getContentSizes($initContent, $initImage),
					static::getContentMethod($initContent, $initImage)
				);
				$finalRespImages = [];

				// If image exists in final, get final responsive versions
				if (HTMLHelper::getContentImage($finalContent, $initImage))
				{
					$finalRespImages = $imgObj->generateMultipleSizes(
						static::getContentSizes($finalContent, $initImage),
						static::getContentMethod($finalContent, $initImage)
					);
				}

				// Compare initial and final sizes of images
				$unusedImages = array_merge($unusedImages, static::getUnusedImages($initRespImages, $finalRespImages));
			}
		}

		return $unusedImages;
	}

	/**
	 * Method to generate a srcset attribute for an image
	 *
	 * @param   string   $imgSource  image source. Example: images/joomla_black.png
	 * @param   array    $sizes      array of strings. Example: $sizes = array('1200x800','800x600');
	 * @param   integer  $method     1-3 resize $scaleMethod | 4 create by cropping | 5 resize then crop
	 *
	 * @return  mixed    generated srcset attribute or false if not generated
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function generateSrcset($imgSource, $sizes, $method)
	{
		$imgObj = new Image(JPATH_ROOT . '/' . $imgSource);

		$srcset = "";

		if ($images = $imgObj->generateMultipleSizes($sizes, $method))
		{
			// Iterate through responsive images and generate srcset
			foreach ($images as $key => $image)
			{
				// Get source from path: PATH/images/joomla_800x600.jpg - images/joomla_800x600.jpg
				$imageSource = explode('/', $image->getPath(), 2)[1];

				// Insert srcset value for current responsive image: (img_name img_size, ...)
				$srcset .= sprintf(
					'%s %dw%s ', $imageSource, $image->getWidth(), $key !== count($images) - 1 ? ',' : ''
				);
			}
		}

		return !empty($srcset) ? $srcset : false;
	}

	/**
	 * Method to generate a sizes attribute for an image
	 *
	 * @param   string  $imgSource  image source. Example: images/joomla_black.png
	 *
	 * @return  string  generated sizes attribute or false if not generated
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function generateSizes($imgSource)
	{
		$imgObj = new Image(JPATH_ROOT . '/' . $imgSource);

		return sprintf('(max-width: %1$dpx) 100vw, %1$dpx', $imgObj->getWidth());
	}

	/**
	 * Method to add srcset and sizes attributes to img tags of content
	 *
	 * @param   string   $content  content to which srcset attributes must be inserted
	 *
	 * @return  string  content with srcset attributes inserted
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function addContentSrcsetAndSizes($content)
	{
		$images = HTMLHelper::getContentImageSources($content);

		// Remove previously generated attributes
		$content = HTMLHelper::removeContentAttribs($content, ['srcset', 'sizes']);

		// Generate srcset and sizes for all images
		foreach ($images as $image)
		{
			$method = static::getContentMethod($content, $image);
			$sizes  = static::getContentSizes($content, $image);

			if ($srcset = static::generateSrcset($image, $sizes, $method))
			{
				// Insert new attributes: <img src="" /> - <img src="" srcset="" sizes="">
				$content = preg_replace(
					'/(<img [^>]+' . preg_quote($image, '/') . '.*?) \/>/',
					'$1 srcset="' . $srcset . '" sizes="' . static::generateSizes($image) . '" />',
					$content
				);
			}
		}

		return $content;
	}

	/**
	 * Returns responsive image size options depending on parameters
	 *
	 * @param   int       $isCustom     1 if custom options are set
	 * @param   stdClass  $sizeOptions  Responsive size options
	 *
	 * @return  array     Responsive image sizes
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getDefaultSizes($isCustom = 0, $sizeOptions = null)
	{
		// In case no custom size option is set
		if (!$isCustom || is_null($sizeOptions) || ($isCustom && empty($sizeOptions)))
		{
			// Get plugin options
			$plugin = PluginHelper::getPlugin('content', 'responsiveimages');
			$params = new Registry($plugin->params);

			// In case plugin custom sizes are not set
			if (!$params->get('custom_sizes'))
			{
				return static::$defaultSizes;
			}

			$sizeOptions = $params->get('custom_size_options');
		}

		// Create an array with custom sizes
		$customSizes = [];

		foreach ((array) $sizeOptions as $option)
		{
			if (isset($option->width) && isset($option->height))
			{
				$customSizes[] = $option->width . 'x' . $option->height;
			}
		}

		return $customSizes;
	}

	/**
	 * Returns responsive image creation method
	 *
	 * @param   int  $isCustom  1 if custom options are set
	 * @param   int  $method    1-3 resize $scaleMethod | 4 create by cropping | 5 resize then crop
	 *
	 * @return  int  Image creation method
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getDefaultMethod($isCustom = 0, $method = 0)
	{
		if (!$isCustom || ($isCustom && $method === 0))
		{
			// Get plugin options
			$plugin = PluginHelper::getPlugin('content', 'responsiveimages');
			$params = new Registry($plugin->params);

			// In case plugin custom sizes are not set
			if (!$params->get('custom_sizes'))
			{
				return static::$creationMethod;
			}

			$method = $params->get('creation_method');
		}

		return $method;
	}

	/**
	 * Returns custom responsive size options of a content image
	 *
	 * @param   string  $content  editor content
	 * @param   string  $image    image source
	 *
	 * @return  mixed   Custom sizes or null if not exists
	 *
	 * @since   __DEPLOY__
	 */
	public static function getContentSizes($content, $image)
	{
		$imgTag = HTMLHelper::getContentImage($content, $image);

		if (!is_null($imgTag))
		{
			// Get custom image sizes from data-jimage-responsive attribute
			$customSizes = preg_match('/data-jimage-responsive *= *"(.*?)"/', $imgTag, $matched) ? $matched[1] : null;

			if (!is_null($customSizes))
			{
				// Replace single quotes with double (to have valid JSON) then decode
				$customSizes = str_replace('\'', "\"", $customSizes);
				$customSizes = $customSizes ? json_decode($customSizes) : [];

				// Create an array that contains only sizes (not titles)
				$sizes = [];

				foreach ($customSizes as $item)
				{
					$sizes[] = $item->size;
				}

				$contentSizes = count($sizes) > 0 ? array_unique($sizes) : null;
			}
		}

		return $contentSizes ?? static::getDefaultSizes();
	}

	/**
	 * Returns custom creation method option of a content image
	 *
	 * @param   string  $content  editor content
	 * @param   string  $image    image source
	 *
	 * @return  integer  creation method
	 *
	 * @since   __DEPLOY__
	 */
	public static function getContentMethod($content, $image)
	{
		$imgTag = preg_match('/(<img [^>]+' . preg_quote($image, '/') . '.*?>)/', $content, $matched) ? $matched[1] : null;

		if (!is_null($imgTag))
		{
			// Get custom image sizes from data-jimage-method attribute
			$method = preg_match('/data-jimage-method *= *"(.*?)"/', $imgTag, $matched) ? (int) $matched[1] : null;
		}

		return $method ?? static::getDefaultMethod();
	}

	/**
	 * Returns srcset attribute value depending on the provided parameters
	 *
	 * @param   string    $imgSource  image source. Example: images/joomla_black.png
	 * @param   int       $isCustom   1 if custom options are set
	 * @param   stdClass  $sizes      Responsive size options
	 * @param   int       $method     Responsive size options
	 *
	 * @return  string    Srcset attribute value
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function createFormSrcset($imgSource, $isCustom, $sizes, $method)
	{
		return static::generateSrcset(
			$imgSource, static::getDefaultSizes($isCustom, $sizes), static::getDefaultMethod($isCustom, $method)
		) ?? '';
	}
}
