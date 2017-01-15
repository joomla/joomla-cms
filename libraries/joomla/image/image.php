<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Image
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Image\Image;

/**
 * Class to manipulate an image.
 *
 * @since  11.3
 */
class JImage extends Image
{
	/**
	 * True for best quality. False for speed
	 *
	 * @var    boolean
	 * @since  3.7.0
	 */
	protected $generateBestQuality = true;

	/**
	 * Class constructor.
	 *
	 * @param   mixed  $source  Either a file path for a source image or a GD resource handler for an image.
	 *
	 * @since   11.3
	 * @throws  RuntimeException
	 */
	public function __construct($source = null)
	{
		// Inject the PSR-3 compatible logger in for forward compatibility
		$this->setLogger(JLog::createDelegatedLogger());

		parent::__construct($source);
	}

	/**
	 * Method to crop the current image.
	 *
	 * @param   mixed    $width      The width of the image section to crop in pixels or a percentage.
	 * @param   mixed    $height     The height of the image section to crop in pixels or a percentage.
	 * @param   integer  $left       The number of pixels from the left to start cropping.
	 * @param   integer  $top        The number of pixels from the top to start cropping.
	 * @param   boolean  $createNew  If true the current image will be cloned, cropped and returned; else
	 *                               the current image will be cropped and returned.
	 *
	 * @return  JImage
	 *
	 * @since   11.3
	 * @throws  LogicException
	 */
	public function crop($width, $height, $left = null, $top = null, $createNew = true)
	{
		// Make sure the resource handle is valid.
		if (!$this->isLoaded())
		{
			throw new LogicException('No valid image was loaded.');
		}

		// Sanitize width.
		$width = $this->sanitizeWidth($width, $height);

		// Sanitize height.
		$height = $this->sanitizeHeight($height, $width);

		// Autocrop offsets
		if (is_null($left))
		{
			$left = round(($this->getWidth() - $width) / 2);
		}

		if (is_null($top))
		{
			$top = round(($this->getHeight() - $height) / 2);
		}

		// Sanitize left.
		$left = $this->sanitizeOffset($left);

		// Sanitize top.
		$top = $this->sanitizeOffset($top);

		// Create the new truecolor image handle.
		$handle = imagecreatetruecolor($width, $height);

		// Allow transparency for the new image handle.
		imagealphablending($handle, false);
		imagesavealpha($handle, true);

		if ($this->isTransparent())
		{
			// Get the transparent color values for the current image.
			$rgba  = imageColorsForIndex($this->handle, imagecolortransparent($this->handle));
			$color = imageColorAllocateAlpha($handle, $rgba['red'], $rgba['green'], $rgba['blue'], $rgba['alpha']);

			// Set the transparent color values for the new image.
			imagecolortransparent($handle, $color);
			imagefill($handle, 0, 0, $color);
		}

		if (!$this->generateBestQuality)
		{
			imagecopyresized($handle, $this->handle, 0, 0, $left, $top, $width, $height, $width, $height);
		}
		else
		{
			imagecopyresampled($handle, $this->handle, 0, 0, $left, $top, $width, $height, $width, $height);
		}

		// If we are cropping to a new image, create a new JImage object.
		if ($createNew)
		{
			// @codeCoverageIgnoreStart
			$new = new JImage($handle);

			return $new;

			// @codeCoverageIgnoreEnd
		}
		// Swap out the current handle for the new image handle.
		else
		{
			// Free the memory from the current handle
			$this->destroy();

			$this->handle = $handle;

			return $this;
		}
	}

	/**
	 * Method to resize the current image.
	 *
	 * @param   mixed    $width        The width of the resized image in pixels or a percentage.
	 * @param   mixed    $height       The height of the resized image in pixels or a percentage.
	 * @param   boolean  $createNew    If true the current image will be cloned, resized and returned; else
	 *                                 the current image will be resized and returned.
	 * @param   integer  $scaleMethod  Which method to use for scaling
	 *
	 * @return  JImage
	 *
	 * @since   11.3
	 * @throws  LogicException
	 */
	public function resize($width, $height, $createNew = true, $scaleMethod = self::SCALE_INSIDE)
	{
		// Make sure the resource handle is valid.
		if (!$this->isLoaded())
		{
			throw new LogicException('No valid image was loaded.');
		}

		// Sanitize width.
		$width = $this->sanitizeWidth($width, $height);

		// Sanitize height.
		$height = $this->sanitizeHeight($height, $width);

		// Prepare the dimensions for the resize operation.
		$dimensions = $this->prepareDimensions($width, $height, $scaleMethod);

		// Instantiate offset.
		$offset    = new stdClass;
		$offset->x = $offset->y = 0;

		// Center image if needed and create the new truecolor image handle.
		if ($scaleMethod == self::SCALE_FIT)
		{
			// Get the offsets
			$offset->x = round(($width - $dimensions->width) / 2);
			$offset->y = round(($height - $dimensions->height) / 2);

			$handle = imagecreatetruecolor($width, $height);

			// Make image transparent, otherwise cavas outside initial image would default to black
			if (!$this->isTransparent())
			{
				$transparency = imagecolorAllocateAlpha($this->handle, 0, 0, 0, 127);
				imagecolorTransparent($this->handle, $transparency);
			}
		}
		else
		{
			$handle = imagecreatetruecolor($dimensions->width, $dimensions->height);
		}

		// Allow transparency for the new image handle.
		imagealphablending($handle, false);
		imagesavealpha($handle, true);

		if ($this->isTransparent())
		{
			// Get the transparent color values for the current image.
			$rgba  = imageColorsForIndex($this->handle, imagecolortransparent($this->handle));
			$color = imageColorAllocateAlpha($handle, $rgba['red'], $rgba['green'], $rgba['blue'], $rgba['alpha']);

			// Set the transparent color values for the new image.
			imagecolortransparent($handle, $color);
			imagefill($handle, 0, 0, $color);
		}

		if (!$this->generateBestQuality)
		{
			imagecopyresized(
				$handle,
				$this->handle,
				$offset->x,
				$offset->y,
				0,
				0,
				$dimensions->width,
				$dimensions->height,
				$this->getWidth(),
				$this->getHeight()
			);
		}
		else
		{
			imagecopyresampled(
				$handle,
				$this->handle,
				$offset->x,
				$offset->y,
				0,
				0,
				$dimensions->width,
				$dimensions->height,
				$this->getWidth(),
				$this->getHeight()
			);
		}

		// If we are resizing to a new image, create a new JImage object.
		if ($createNew)
		{
			// @codeCoverageIgnoreStart
			$new = new JImage($handle);

			return $new;

			// @codeCoverageIgnoreEnd
		}
		// Swap out the current handle for the new image handle.
		else
		{
			// Free the memory from the current handle
			$this->destroy();

			$this->handle = $handle;

			return $this;
		}
	}

	/**
	 * Method to get an image filter instance of a specified type.
	 *
	 * @param   string  $type  The image filter type to get.
	 *
	 * @return  JImageFilter
	 *
	 * @since   11.3
	 * @throws  RuntimeException
	 */
	protected function getFilterInstance($type)
	{
		// Sanitize the filter type.
		$type = strtolower(preg_replace('#[^A-Z0-9_]#i', '', $type));

		// Verify that the filter type exists.
		$className = 'JImageFilter' . ucfirst($type);

		if (!class_exists($className))
		{
			JLog::add('The ' . ucfirst($type) . ' image filter is not available.', JLog::ERROR);
			throw new RuntimeException('The ' . ucfirst($type) . ' image filter is not available.');
		}

		// Instantiate the filter object.
		$instance = new $className($this->handle);

		// Verify that the filter type is valid.
		if (!($instance instanceof JImageFilter))
		{
			// @codeCoverageIgnoreStart
			JLog::add('The ' . ucfirst($type) . ' image filter is not valid.', JLog::ERROR);
			throw new RuntimeException('The ' . ucfirst($type) . ' image filter is not valid.');

			// @codeCoverageIgnoreEnd
		}

		return $instance;
	}

	/**
	 * Method for set option of generate thumbnail method
	 *
	 * @param   boolean  $quality  True for best quality. False for best speed.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public function setThumbnailGenerate($quality = true)
	{
		$this->generateBestQuality = (boolean) $quality;
	}
}
