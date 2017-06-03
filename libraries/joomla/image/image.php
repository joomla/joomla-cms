<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Image
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Class to manipulate an image.
 *
 * @since  11.3
 */
class JImage
{
	/**
	 * @const  integer
	 * @since  11.3
	 */
	const SCALE_FILL = 1;

	/**
	 * @const  integer
	 * @since  11.3
	 */
	const SCALE_INSIDE = 2;

	/**
	 * @const  integer
	 * @since  11.3
	 */
	const SCALE_OUTSIDE = 3;

	/**
	 * @const  integer
	 * @since  12.2
	 */
	const CROP = 4;

	/**
	 * @const  integer
	 * @since  12.3
	 */
	const CROP_RESIZE = 5;

	/**
	 * @const  integer
	 * @since  3.2
	 */
	const SCALE_FIT = 6;

	/**
	 * @const  string
	 * @since  3.4.2
	 */
	const ORIENTATION_LANDSCAPE = 'landscape';

	/**
	 * @const  string
	 * @since  3.4.2
	 */
	const ORIENTATION_PORTRAIT = 'portrait';

	/**
	 * @const  string
	 * @since  3.4.2
	 */
	const ORIENTATION_SQUARE = 'square';

	/**
	 * @var    resource  The image resource handle.
	 * @since  11.3
	 */
	protected $handle;

	/**
	 * @var    string  The source image path.
	 * @since  11.3
	 */
	protected $path = null;

	/**
	 * @var    array  Whether or not different image formats are supported.
	 * @since  11.3
	 */
	protected static $formats = array();

	/**
	 * @var    boolean  True for best quality. False for speed
	 *
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
		// Verify that GD support for PHP is available.
		if (!extension_loaded('gd'))
		{
			// @codeCoverageIgnoreStart
			JLog::add('The GD extension for PHP is not available.', JLog::ERROR);
			throw new RuntimeException('The GD extension for PHP is not available.');

			// @codeCoverageIgnoreEnd
		}

		// Determine which image types are supported by GD, but only once.
		if (!isset(self::$formats[IMAGETYPE_JPEG]))
		{
			$info = gd_info();
			self::$formats[IMAGETYPE_JPEG] = ($info['JPEG Support']) ? true : false;
			self::$formats[IMAGETYPE_PNG]  = ($info['PNG Support']) ? true : false;
			self::$formats[IMAGETYPE_GIF]  = ($info['GIF Read Support']) ? true : false;
		}

		// If the source input is a resource, set it as the image handle.
		if (is_resource($source) && (get_resource_type($source) == 'gd'))
		{
			$this->handle = &$source;
		}
		elseif (!empty($source) && is_string($source))
		{
			// If the source input is not empty, assume it is a path and populate the image handle.
			$this->loadFile($source);
		}
	}

	/**
	 * Method to return a properties object for an image given a filesystem path.
	 * The result object has values for image width, height, type, attributes, bits, channels, mime type, file size and orientation.
	 *
	 * @param   string  $path  The filesystem path to the image for which to get properties.
	 *
	 * @return  stdClass
	 *
	 * @since   11.3
	 *
	 * @throws  InvalidArgumentException
	 * @throws  RuntimeException
	 */
	public static function getImageFileProperties($path)
	{
		// Make sure the file exists.
		if (!file_exists($path))
		{
			throw new InvalidArgumentException('The image file does not exist.');
		}

		// Get the image file information.
		$info = getimagesize($path);

		if (!$info)
		{
			// @codeCoverageIgnoreStart
			throw new RuntimeException('Unable to get properties for the image.');

			// @codeCoverageIgnoreEnd
		}

		// Build the response object.
		$properties = (object) array(
			'width'       => $info[0],
			'height'      => $info[1],
			'type'        => $info[2],
			'attributes'  => $info[3],
			'bits'        => isset($info['bits']) ? $info['bits'] : null,
			'channels'    => isset($info['channels']) ? $info['channels'] : null,
			'mime'        => $info['mime'],
			'filesize'    => filesize($path),
			'orientation' => self::getOrientationString((int) $info[0], (int) $info[1]),
		);

		return $properties;
	}

	/**
	 * Method to detect whether an image's orientation is landscape, portrait or square.
	 * The orientation will be returned as a string.
	 *
	 * @return  mixed   Orientation string or null.
	 *
	 * @since   3.4.2
	 */
	public function getOrientation()
	{
		if ($this->isLoaded())
		{
			return self::getOrientationString($this->getWidth(), $this->getHeight());
		}

		return;
	}

	/**
	 * Compare width and height integers to determine image orientation.
	 *
	 * @param   integer  $width   The width value to use for calculation
	 * @param   integer  $height  The height value to use for calculation
	 *
	 * @return  string   Orientation string
	 *
	 * @since   3.4.2
	 */
	private static function getOrientationString($width, $height)
	{
		if ($width > $height)
		{
			return self::ORIENTATION_LANDSCAPE;
		}

		if ($width < $height)
		{
			return self::ORIENTATION_PORTRAIT;
		}

		return self::ORIENTATION_SQUARE;
	}

	/**
	 * Method to generate thumbnails from the current image. It allows
	 * creation by resizing or cropping the original image.
	 *
	 * @param   mixed    $thumbSizes      String or array of strings. Example: $thumbSizes = array('150x75','250x150');
	 * @param   integer  $creationMethod  1-3 resize $scaleMethod | 4 create cropping | 5 resize then crop
	 *
	 * @return  array    returns the generated thumb in the results array
	 *
	 * @since   12.2
	 * @throws  LogicException
	 * @throws  InvalidArgumentException
	 */
	public function generateThumbs($thumbSizes, $creationMethod = self::SCALE_INSIDE)
	{
		// Make sure the resource handle is valid.
		if (!$this->isLoaded())
		{
			throw new LogicException('No valid image was loaded.');
		}

		// Accept a single thumbsize string as parameter
		if (!is_array($thumbSizes))
		{
			$thumbSizes = array($thumbSizes);
		}

		// Process thumbs
		$generated = array();

		if (!empty($thumbSizes))
		{
			foreach ($thumbSizes as $thumbSize)
			{
				// Desired thumbnail size
				$size = explode('x', strtolower($thumbSize));

				if (count($size) != 2)
				{
					throw new InvalidArgumentException('Invalid thumb size received: ' . $thumbSize);
				}

				$thumbWidth  = $size[0];
				$thumbHeight = $size[1];

				switch ($creationMethod)
				{
					// Case for self::CROP
					case 4:
						$thumb = $this->crop($thumbWidth, $thumbHeight, null, null, true);
						break;

					// Case for self::CROP_RESIZE
					case 5:
						$thumb = $this->cropResize($thumbWidth, $thumbHeight, true);
						break;

					default:
						$thumb = $this->resize($thumbWidth, $thumbHeight, true, $creationMethod);
						break;
				}

				// Store the thumb in the results array
				$generated[] = $thumb;
			}
		}

		return $generated;
	}

	/**
	 * Method to create thumbnails from the current image and save them to disk. It allows creation by resizing
	 * or cropping the original image.
	 *
	 * @param   mixed    $thumbSizes      string or array of strings. Example: $thumbSizes = array('150x75','250x150');
	 * @param   integer  $creationMethod  1-3 resize $scaleMethod | 4 create cropping
	 * @param   string   $thumbsFolder    destination thumbs folder. null generates a thumbs folder in the image folder
	 *
	 * @return  array    An array of JImage objects with thumb paths.
	 *
	 * @since   12.2
	 * @throws  LogicException
	 * @throws  InvalidArgumentException
	 */
	public function createThumbs($thumbSizes, $creationMethod = self::SCALE_INSIDE, $thumbsFolder = null)
	{
		// Make sure the resource handle is valid.
		if (!$this->isLoaded())
		{
			throw new LogicException('No valid image was loaded.');
		}

		// No thumbFolder set -> we will create a thumbs folder in the current image folder
		if (is_null($thumbsFolder))
		{
			$thumbsFolder = dirname($this->getPath()) . '/thumbs';
		}

		// Check destination
		if (!is_dir($thumbsFolder) && (!is_dir(dirname($thumbsFolder)) || !@mkdir($thumbsFolder)))
		{
			throw new InvalidArgumentException('Folder does not exist and cannot be created: ' . $thumbsFolder);
		}

		// Process thumbs
		$thumbsCreated = array();

		if ($thumbs = $this->generateThumbs($thumbSizes, $creationMethod))
		{
			// Parent image properties
			$imgProperties = self::getImageFileProperties($this->getPath());

			foreach ($thumbs as $thumb)
			{
				// Get thumb properties
				$thumbWidth  = $thumb->getWidth();
				$thumbHeight = $thumb->getHeight();

				// Generate thumb name
				$filename      = pathinfo($this->getPath(), PATHINFO_FILENAME);
				$fileExtension = pathinfo($this->getPath(), PATHINFO_EXTENSION);
				$thumbFileName = $filename . '_' . $thumbWidth . 'x' . $thumbHeight . '.' . $fileExtension;

				// Save thumb file to disk
				$thumbFileName = $thumbsFolder . '/' . $thumbFileName;

				if ($thumb->toFile($thumbFileName, $imgProperties->type))
				{
					// Return JImage object with thumb path to ease further manipulation
					$thumb->path     = $thumbFileName;
					$thumbsCreated[] = $thumb;
				}
			}
		}

		return $thumbsCreated;
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
	 * Method to apply a filter to the image by type.  Two examples are: grayscale and sketchy.
	 *
	 * @param   string  $type     The name of the image filter to apply.
	 * @param   array   $options  An array of options for the filter.
	 *
	 * @return  JImage
	 *
	 * @since   11.3
	 * @see     JImageFilter
	 * @throws  LogicException
	 */
	public function filter($type, array $options = array())
	{
		// Make sure the resource handle is valid.
		if (!$this->isLoaded())
		{
			throw new LogicException('No valid image was loaded.');
		}

		// Get the image filter instance.
		$filter = $this->getFilterInstance($type);

		// Execute the image filter.
		$filter->execute($options);

		return $this;
	}

	/**
	 * Method to get the height of the image in pixels.
	 *
	 * @return  integer
	 *
	 * @since   11.3
	 * @throws  LogicException
	 */
	public function getHeight()
	{
		// Make sure the resource handle is valid.
		if (!$this->isLoaded())
		{
			throw new LogicException('No valid image was loaded.');
		}

		return imagesy($this->handle);
	}

	/**
	 * Method to get the width of the image in pixels.
	 *
	 * @return  integer
	 *
	 * @since   11.3
	 * @throws  LogicException
	 */
	public function getWidth()
	{
		// Make sure the resource handle is valid.
		if (!$this->isLoaded())
		{
			throw new LogicException('No valid image was loaded.');
		}

		return imagesx($this->handle);
	}

	/**
	 * Method to return the path
	 *
	 * @return	string
	 *
	 * @since	11.3
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Method to determine whether or not an image has been loaded into the object.
	 *
	 * @return  boolean
	 *
	 * @since   11.3
	 */
	public function isLoaded()
	{
		// Make sure the resource handle is valid.
		if (!is_resource($this->handle) || (get_resource_type($this->handle) != 'gd'))
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to determine whether or not the image has transparency.
	 *
	 * @return  boolean
	 *
	 * @since   11.3
	 * @throws  LogicException
	 */
	public function isTransparent()
	{
		// Make sure the resource handle is valid.
		if (!$this->isLoaded())
		{
			throw new LogicException('No valid image was loaded.');
		}

		return imagecolortransparent($this->handle) >= 0;
	}

	/**
	 * Method to load a file into the JImage object as the resource.
	 *
	 * @param   string  $path  The filesystem path to load as an image.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @throws  InvalidArgumentException
	 * @throws  RuntimeException
	 */
	public function loadFile($path)
	{
		// Destroy the current image handle if it exists
		$this->destroy();

		// Make sure the file exists.
		if (!file_exists($path))
		{
			throw new InvalidArgumentException('The image file does not exist.');
		}

		// Get the image properties.
		$properties = self::getImageFileProperties($path);

		// Attempt to load the image based on the MIME-Type
		switch ($properties->mime)
		{
			case 'image/gif':
				// Make sure the image type is supported.
				if (empty(self::$formats[IMAGETYPE_GIF]))
				{
					// @codeCoverageIgnoreStart
					JLog::add('Attempting to load an image of unsupported type GIF.', JLog::ERROR);
					throw new RuntimeException('Attempting to load an image of unsupported type GIF.');

					// @codeCoverageIgnoreEnd
				}

				// Attempt to create the image handle.
				$handle = imagecreatefromgif($path);

				if (!is_resource($handle))
				{
					// @codeCoverageIgnoreStart
					throw new RuntimeException('Unable to process GIF image.');

					// @codeCoverageIgnoreEnd
				}

				$this->handle = $handle;
				break;

			case 'image/jpeg':
				// Make sure the image type is supported.
				if (empty(self::$formats[IMAGETYPE_JPEG]))
				{
					// @codeCoverageIgnoreStart
					JLog::add('Attempting to load an image of unsupported type JPG.', JLog::ERROR);
					throw new RuntimeException('Attempting to load an image of unsupported type JPG.');

					// @codeCoverageIgnoreEnd
				}

				// Attempt to create the image handle.
				$handle = imagecreatefromjpeg($path);

				if (!is_resource($handle))
				{
					// @codeCoverageIgnoreStart
					throw new RuntimeException('Unable to process JPG image.');

					// @codeCoverageIgnoreEnd
				}

				$this->handle = $handle;
				break;

			case 'image/png':
				// Make sure the image type is supported.
				if (empty(self::$formats[IMAGETYPE_PNG]))
				{
					// @codeCoverageIgnoreStart
					JLog::add('Attempting to load an image of unsupported type PNG.', JLog::ERROR);
					throw new RuntimeException('Attempting to load an image of unsupported type PNG.');

					// @codeCoverageIgnoreEnd
				}

				// Attempt to create the image handle.
				$handle = imagecreatefrompng($path);

				if (!is_resource($handle))
				{
					// @codeCoverageIgnoreStart
					throw new RuntimeException('Unable to process PNG image.');

					// @codeCoverageIgnoreEnd
				}

				$this->handle = $handle;

				break;

			default:
				JLog::add('Attempting to load an image of unsupported type: ' . $properties->mime, JLog::ERROR);
				throw new InvalidArgumentException('Attempting to load an image of unsupported type: ' . $properties->mime);
				break;
		}

		// Set the filesystem path to the source image.
		$this->path = $path;
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
	 * Method to crop an image after resizing it to maintain
	 * proportions without having to do all the set up work.
	 *
	 * @param   integer  $width      The desired width of the image in pixels or a percentage.
	 * @param   integer  $height     The desired height of the image in pixels or a percentage.
	 * @param   boolean  $createNew  If true the current image will be cloned, resized, cropped and returned.
	 *
	 * @return  object  JImage Object for chaining.
	 *
	 * @since   12.3
	 */
	public function cropResize($width, $height, $createNew = true)
	{
		$width  = $this->sanitizeWidth($width, $height);
		$height = $this->sanitizeHeight($height, $width);

		$resizewidth  = $width;
		$resizeheight = $height;

		if (($this->getWidth() / $width) < ($this->getHeight() / $height))
		{
			$resizeheight = 0;
		}
		else
		{
			$resizewidth = 0;
		}

		return $this->resize($resizewidth, $resizeheight, $createNew)->crop($width, $height, null, null, false);
	}

	/**
	 * Method to rotate the current image.
	 *
	 * @param   mixed    $angle       The angle of rotation for the image
	 * @param   integer  $background  The background color to use when areas are added due to rotation
	 * @param   boolean  $createNew   If true the current image will be cloned, rotated and returned; else
	 *                                the current image will be rotated and returned.
	 *
	 * @return  JImage
	 *
	 * @since   11.3
	 * @throws  LogicException
	 */
	public function rotate($angle, $background = -1, $createNew = true)
	{
		// Make sure the resource handle is valid.
		if (!$this->isLoaded())
		{
			throw new LogicException('No valid image was loaded.');
		}

		// Sanitize input
		$angle = (float) $angle;

		// Create the new truecolor image handle.
		$handle = imagecreatetruecolor($this->getWidth(), $this->getHeight());

		// Make background transparent if no external background color is provided.
		if ($background == -1)
		{
			// Allow transparency for the new image handle.
			imagealphablending($handle, false);
			imagesavealpha($handle, true);

			$background = imagecolorallocatealpha($handle, 0, 0, 0, 127);
		}

		// Copy the image
		imagecopy($handle, $this->handle, 0, 0, 0, 0, $this->getWidth(), $this->getHeight());

		// Rotate the image
		$handle = imagerotate($handle, $angle, $background);

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
	 * Method to flip the current image.
	 *
	 * @param   integer  $mode       The flip mode for flipping the image {@link https://secure.php.net/imageflip#refsect1-function.imageflip-parameters}
	 * @param   boolean  $createNew  If true the current image will be cloned, flipped and returned; else the current image will be flipped and returned.
	 *
	 * @return  JImage
	 *
	 * @since   11.3
	 * @throws  LogicException
	 */
	public function flip($mode, $createNew = true)
	{
		// Make sure the resource handle is valid.
		if (!$this->isLoaded())
		{
			throw new LogicException('No valid image was loaded.');
		}

		// Create the new truecolor image handle.
		$handle = imagecreatetruecolor($this->getWidth(), $this->getHeight());

		// Copy the image
		imagecopy($handle, $this->handle, 0, 0, 0, 0, $this->getWidth(), $this->getHeight());

		// Flip the image
		if (!imageflip($handle, $mode))
		{
			throw new LogicException('Unable to flip the image.');
		}

		// If we are resizing to a new image, create a new JImage object.
		if ($createNew)
		{
			// @codeCoverageIgnoreStart
			$new = new JImage($handle);

			return $new;

			// @codeCoverageIgnoreEnd
		}

		// Free the memory from the current handle
		$this->destroy();

		// Swap out the current handle for the new image handle.
		$this->handle = $handle;

		return $this;
	}

	/**
	 * Method to write the current image out to a file.
	 *
	 * @param   string   $path     The filesystem path to save the image.
	 * @param   integer  $type     The image type to save the file as.
	 * @param   array    $options  The image type options to use in saving the file.
	 *
	 * @return  boolean
	 *
	 * @link    https://secure.php.net/manual/image.constants.php
	 * @since   11.3
	 * @throws  LogicException
	 */
	public function toFile($path, $type = IMAGETYPE_JPEG, array $options = array())
	{
		// Make sure the resource handle is valid.
		if (!$this->isLoaded())
		{
			throw new LogicException('No valid image was loaded.');
		}

		switch ($type)
		{
			case IMAGETYPE_GIF:
				return imagegif($this->handle, $path);
				break;

			case IMAGETYPE_PNG:
				return imagepng($this->handle, $path, (array_key_exists('quality', $options)) ? $options['quality'] : 0);
				break;

			case IMAGETYPE_JPEG:
			default:
				return imagejpeg($this->handle, $path, (array_key_exists('quality', $options)) ? $options['quality'] : 100);
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
	 * Method to get the new dimensions for a resized image.
	 *
	 * @param   integer  $width        The width of the resized image in pixels.
	 * @param   integer  $height       The height of the resized image in pixels.
	 * @param   integer  $scaleMethod  The method to use for scaling
	 *
	 * @return  stdClass
	 *
	 * @since   11.3
	 * @throws  InvalidArgumentException  If width, height or both given as zero
	 */
	protected function prepareDimensions($width, $height, $scaleMethod)
	{
		// Instantiate variables.
		$dimensions = new stdClass;

		switch ($scaleMethod)
		{
			case self::SCALE_FILL:
				$dimensions->width = (int) round($width);
				$dimensions->height = (int) round($height);
				break;

			case self::SCALE_INSIDE:
			case self::SCALE_OUTSIDE:
			case self::SCALE_FIT:
				$rx = ($width > 0) ? ($this->getWidth() / $width) : 0;
				$ry = ($height > 0) ? ($this->getHeight() / $height) : 0;

				if ($scaleMethod != self::SCALE_OUTSIDE)
				{
					$ratio = max($rx, $ry);
				}
				else
				{
					$ratio = min($rx, $ry);
				}

				$dimensions->width  = (int) round($this->getWidth() / $ratio);
				$dimensions->height = (int) round($this->getHeight() / $ratio);
				break;

			default:
				throw new InvalidArgumentException('Invalid scale method.');
				break;
		}

		return $dimensions;
	}

	/**
	 * Method to sanitize a height value.
	 *
	 * @param   mixed  $height  The input height value to sanitize.
	 * @param   mixed  $width   The input width value for reference.
	 *
	 * @return  integer
	 *
	 * @since   11.3
	 */
	protected function sanitizeHeight($height, $width)
	{
		// If no height was given we will assume it is a square and use the width.
		$height = ($height === null) ? $width : $height;

		// If we were given a percentage, calculate the integer value.
		if (preg_match('/^[0-9]+(\.[0-9]+)?\%$/', $height))
		{
			$height = (int) round($this->getHeight() * (float) str_replace('%', '', $height) / 100);
		}
		// Else do some rounding so we come out with a sane integer value.
		else
		{
			$height = (int) round((float) $height);
		}

		return $height;
	}

	/**
	 * Method to sanitize an offset value like left or top.
	 *
	 * @param   mixed  $offset  An offset value.
	 *
	 * @return  integer
	 *
	 * @since   11.3
	 */
	protected function sanitizeOffset($offset)
	{
		return (int) round((float) $offset);
	}

	/**
	 * Method to sanitize a width value.
	 *
	 * @param   mixed  $width   The input width value to sanitize.
	 * @param   mixed  $height  The input height value for reference.
	 *
	 * @return  integer
	 *
	 * @since   11.3
	 */
	protected function sanitizeWidth($width, $height)
	{
		// If no width was given we will assume it is a square and use the height.
		$width = ($width === null) ? $height : $width;

		// If we were given a percentage, calculate the integer value.
		if (preg_match('/^[0-9]+(\.[0-9]+)?\%$/', $width))
		{
			$width = (int) round($this->getWidth() * (float) str_replace('%', '', $width) / 100);
		}
		// Else do some rounding so we come out with a sane integer value.
		else
		{
			$width = (int) round((float) $width);
		}

		return $width;
	}

	/**
	 * Method to destroy an image handle and
	 * free the memory associated with the handle
	 *
	 * @return  boolean  True on success, false on failure or if no image is loaded
	 *
	 * @since   12.3
	 */
	public function destroy()
	{
		if ($this->isLoaded())
		{
			return imagedestroy($this->handle);
		}

		return false;
	}

	/**
	 * Method to call the destroy() method one last time
	 * to free any memory when the object is unset
	 *
	 * @see     JImage::destroy()
	 * @since   12.3
	 */
	public function __destruct()
	{
		$this->destroy();
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
