<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Media
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.media.mediaexception');
jimport('joomla.filesystem.file');
jimport('joomla.log.log');

JLoader::register('MediaException', JPATH_PLATFORM.'/joomla/media/mediaexception.php');

/**
 * Class to manipulate an image.
 *
 * @package     Joomla.Platform
 * @subpackage  Media
 * @since       11.1
 */
class JImage
{
	/**
	 * @var    integer
	 * @since  11.1
	 */
	const SCALE_FILL = 1;

	/**
	 * @var    integer
	 * @since  11.1
	 */
	const SCALE_INSIDE = 2;

	/**
	 * @var    integer
	 * @since  11.1
	 */
	const SCALE_OUTSIDE = 3;

	/**
	 * @var    resource  The image handle.
	 * @since  11.1
	 */
	protected $handle;

	/**
	 * @var    string  The source image path.
	 * @since  11.1
	 */
	protected $path = null;

	/**
	 * @var    array  Whether or not different image formats are supported.
	 * @since  11.1
	 */
	protected static $formats = array();

	/**
	 * @var    bool  True if the default filter classes and format support has been registered.
	 * @since  11.1
	 */
	protected static $registered = false;

	/**
	 * Class constructor.
	 *
	 * @param   mixed  $source  Either a file path for a source image or a GD resource handler for an image.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @throws  MediaException
	 */
	public function __construct($source = null)
	{
		// Verify that GD support for PHP is available.
		if (!function_exists('gd_info') || !function_exists('imagecreatetruecolor')) {
			JLog::add('The GD extension for PHP is not available.', JLog::ERROR);
			throw new MediaException();
		}

		// Let's make sure we register the filter classes and figure out the format support.
		if (!self::$registered) {
			self::register();
			self::$registered = true;
		}

		// If the source input is a resource, set it as the image handle.
		if ((is_resource($source) && get_resource_type($source) == 'gd')) {
			$this->handle = &$source;
		} elseif (!empty($source) && is_string($source)) {
			// If the source input is not empty, assume it is a path and populate the image handle.
			$this->loadFromFile($source);
		}
	}

	/**
	 * Method to return a properties object for an image given a filesystem path.  The
	 * result object has values for image width, height, type, attributes, mime type, bits,
	 * and channels.
	 *
	 * @param   string  $path  The filesystem path to the image for which to get properties.
	 *
	 * @return  object
	 *
	 * @since   11.1
	 * @throws  MediaException
	 */
	public static function getImageFileProperties($path)
	{
		// Make sure the file exists.
		if (!JFile::exists($path)) {
			JLog::add('The image file does not exist.', JLog::ERROR);
			throw new MediaException();
		}

		// Get the image file information.
		$info = @getimagesize($path);
		if (!$info) {
			JLog::add('Unable to get properties for the image.', JLog::ERROR);
			throw new MediaException();
		}

		// Build the response object.
		$properties	= (object) array(
			'width'      => $info[0],
			'height'     => $info[1],
			'type'       => $info[2],
			'attributes' => $info[3],
			'bits'       => @$info['bits'],
			'channels'   => @$info['channels'],
			'mime'       => $info['mime']
		);

		return $properties;
	}

	/**
	 * Method to crop the current image.
	 *
	 * @param   mixed    $width      The width of the image section to crop in pixels or a percentage.
	 * @param   mixed    $height     The height of the image section to crop in pixels or a percentage.
	 * @param   integer  $left       The number of pixels from the left to start cropping.
	 * @param   integer  $top        The number of pixels from the top to start cropping.
	 * @param   bool     $createNew  If true the current image will be cloned, cropped and returned; else
	 *                               the current image will be cropped and returned.
	 *
	 * @return  JImage
	 *
	 * @since   11.1
	 * @throws  MediaException
	 */
	function crop($width, $height, $left, $top, $createNew = true)
	{
		// Make sure the file handle is valid.
		if ((!is_resource($this->handle) || get_resource_type($this->handle) != 'gd')) {
			JLog::add('The image is invalid.', JLog::ERROR);
			throw new MediaException();
		}

		// Sanitize width.
		$width = ($width === null) ? $height : $width;
		if (preg_match('/^[0-9]+(\.[0-9]+)?\%$/', $width)) {
			$width = intval(round($this->getWidth() * floatval(str_replace('%', '', $width)) / 100));
		} else {
			$width = intval(round(floatval($width)));
		}

		// Sanitize height.
		$height = ($height === null) ? $width : $height;
		if (preg_match('/^[0-9]+(\.[0-9]+)?\%$/', $height)) {
			$height = intval(round($this->getHeight() * floatval(str_replace('%', '', $height)) / 100));
		} else {
			$height = intval(round(floatval($height)));
		}

		// Sanitize left.
		$left = intval(round(floatval($left)));

		// Sanitize top.
		$top = intval(round(floatval($top)));

		// Create the new truecolor image handle.
		$handle = imagecreatetruecolor($width, $height);

		// Allow transparency for the new image handle.
		imagealphablending($handle, false);
		imagesavealpha($handle, true);

		if ($this->isTransparent()) {
			// Get the transparent color values for the current image.
			$rgba = imageColorsForIndex($this->handle, imagecolortransparent($this->handle));
			$color = imageColorAllocate($this->handle, $rgba['red'], $rgba['green'], $rgba['blue']);

			// Set the transparent color values for the new image.
			imagecolortransparent($handle, $color);
			imagefill($handle, 0, 0, $color);

			imagecopyresized(
				$handle,
				$this->handle,
				0, 0,
				$left,
				$top,
				$width,
				$height,
				$width,
				$height
			);
		} else {
			imagecopyresampled(
				$handle,
				$this->handle,
				0, 0,
				$left,
				$top,
				$width,
				$height,
				$width,
				$height
			);
		}

		// If we are cropping to a new image, create a new JImage object.
		if ($createNew) {
			$new = new JImage($handle);
			return $new;
		}
		// Swap out the current handle for the new image handle.
		else {
			$this->handle = $handle;
			return $this;
		}
	}

	/**
	 * Method to apply a filter to the image by type.  Two examples are: grayscale and sketchy.
	 *
	 * @param   string  $type  The name of the image filter to apply.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @see     JImageFilter
	 * @throws  MediaException
	 */
	public function filter($type)
	{
		// Make sure the file handle is valid.
		if ((!is_resource($this->handle) || get_resource_type($this->handle) != 'gd')) {
			JLog::add('The image is invalid.', JLog::ERROR);
			throw new MediaException();
		}

		// Sanitize the filter type.
		$type = preg_replace('#[^A-Z0-9_]#i', '', $type);

		// Verify that the filter type exists.
		$className = 'JImageFilter'.ucfirst($type);
		if (!class_exists($className)) {
			JLog::add('The '.ucfirst($type).' image filter is not available.', JLog::ERROR);
			throw new MediaException();
		}

		// Make sure that the filter class is valid.
		$instance = new $className;
		if (is_callable(array($instance, 'execute'))) {

			// Setup the arguments to call the filter execute method.
			$args = func_get_args();
			array_shift($args);
			array_unshift($args, $this->handle);

			// Call the filter execute method.
			call_user_func_array(array($instance, 'execute'), $args);
		}
		// The filter class is invalid.
		else {
			JLog::add('The '.ucfirst($type).' image filter is not valid.', JLog::ERROR);
			throw new MediaException();
		}
	}

	/**
	 * Method to get the height of the image in pixels.
	 *
	 * @return  integer
	 *
	 * @since   11.1
	 * @throws  MediaException
	 */
	public function getHeight()
	{
		// Make sure the file handle is valid.
		if ((!is_resource($this->handle) || get_resource_type($this->handle) != 'gd')) {
			JLog::add('The image is invalid.', JLog::ERROR);
			throw new MediaException();
		}

		return imagesy($this->handle);
	}

	/**
	 * Method to get the width of the image in pixels.
	 *
	 * @return  integer
	 *
	 * @since   11.1
	 * @throws  MediaException
	 */
	public function getWidth()
	{
		// Make sure the file handle is valid.
		if ((!is_resource($this->handle) || get_resource_type($this->handle) != 'gd')) {
			JLog::add('The image is invalid.', JLog::ERROR);
			throw new MediaException();
		}

		return imagesx($this->handle);
	}

	/**
	 * Method to determine whether or not the image has transparency.
	 *
	 * @return  bool
	 *
	 * @since   11.1
	 * @throws  MediaException
	 */
	public function isTransparent()
	{
		// Make sure the file handle is valid.
		if ((!is_resource($this->handle) || get_resource_type($this->handle) != 'gd')) {
			JLog::add('The image is invalid.', JLog::ERROR);
			throw new MediaException();
		}

		return (imagecolortransparent($this->handle) >= 0);
	}

	/**
	 * Method to load a file into the JImage object as the resource.
	 *
	 * @param   string  $path  The filesystem path to load as an image.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @throws  MediaException
	 */
	function loadFromFile($path)
	{
		// Make sure the file exists.
		if (!JFile::exists($path)) {
			JLog::add('The image file does not exist.', JLog::ERROR);
			throw new MediaException();
		}

		// Get the image properties.
		$properties = self::getImageFileProperties($path);

		// Attempt to load the image based on the MIME-Type
		switch ($properties->mime)
		{
			case 'image/gif':
				// Make sure the image type is supported.
				if (self::$formats[IMAGETYPE_GIF]) {
					JLog::add('Attempting to load an image of unsupported type GIF.', JLog::ERROR);
					throw new MediaException();
				}

				// Attempt to create the image handle.
				$handle = @imagecreatefromgif($path);
				if (!is_resource($handle)) {
					JLog::add('Unable to process image.', JLog::ERROR);
					throw new MediaException();
				}
				$this->handle = $handle;
				break;

			case 'image/jpeg':
				// Make sure the image type is supported.
				if (!self::$formats[IMAGETYPE_JPEG]) {
					JLog::add('Attempting to load an image of unsupported type JPG.', JLog::ERROR);
					throw new MediaException();
				}

				// Attempt to create the image handle.
				$handle = @imagecreatefromjpeg($path);
				if (!is_resource($handle)) {
					JLog::add('Unable to process image.', JLog::ERROR);
					throw new MediaException();
				}
				$this->handle = $handle;
				break;

			case 'image/png':
				// Make sure the image type is supported.
				if (self::$formats[IMAGETYPE_PNG]) {
					JLog::add('Attempting to load an image of unsupported type PNG.', JLog::ERROR);
					throw new MediaException();
				}

				// Attempt to create the image handle.
				$handle = @imagecreatefrompng($path);
				if (!is_resource($handle)) {
					JLog::add('Unable to process image.', JLog::ERROR);
					throw new MediaException();
				}
				$this->handle = $handle;
				break;

			default:
				JLog::add('Attempting to load an image of unsupported type: '.$properties->mime, JLog::ERROR);
				throw new MediaException();
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
	 * @param   bool     $createNew    If true the current image will be cloned, resized and returned; else
	 *                                 the current image will be resized and returned.
	 * @param   integer  $scaleMethod
	 *
	 * @return  JImage
	 *
	 * @since   11.1
	 * @throws  MediaException
	 */
	function resize($width, $height, $createNew = true, $scaleMethod = JImage::SCALE_INSIDE)
	{
		// Make sure the file handle is valid.
		if ((!is_resource($this->handle) || get_resource_type($this->handle) != 'gd')) {
			JLog::add('The image is invalid.', JLog::ERROR);
			throw new MediaException();
		}

		// Sanitize width.
		$width = ($width === null) ? $height : $width;
		if (preg_match('/^[0-9]+(\.[0-9]+)?\%$/', $width)) {
			$width = intval(round($this->getWidth() * floatval(str_replace('%', '', $width)) / 100));
		} else {
			$width = intval(round(floatval($width)));
		}

		// Sanitize height.
		$height = ($height === null) ? $width : $height;
		if (preg_match('/^[0-9]+(\.[0-9]+)?\%$/', $height)) {
			$height = intval(round($this->getHeight() * floatval(str_replace('%', '', $height)) / 100));
		} else {
			$height = intval(round(floatval($height)));
		}

		// Prepare the dimensions for the resize operation.
		$dimensions = $this->prepareDimensions($width, $height, $scaleMethod);

		// Create the new truecolor image handle.
		$handle = imagecreatetruecolor($dimensions->width, $dimensions->height);

		// Allow transparency for the new image handle.
		imagealphablending($handle, false);
		imagesavealpha($handle, true);

		if ($this->isTransparent()) {
			// Get the transparent color values for the current image.
			$rgba = imageColorsForIndex($this->handle, imagecolortransparent($this->handle));
			$color = imageColorAllocate($this->handle, $rgba['red'], $rgba['green'], $rgba['blue']);

			// Set the transparent color values for the new image.
			imagecolortransparent($handle, $color);
			imagefill($handle, 0, 0, $color);

			imagecopyresized(
				$handle,
				$this->handle,
				0, 0, 0, 0,
				$dimensions->width,
				$dimensions->height,
				$this->getWidth(),
				$this->getHeight()
			);
		} else {
			imagecopyresampled(
				$handle,
				$this->handle,
				0, 0, 0, 0,
				$dimensions->width,
				$dimensions->height,
				$this->getWidth(),
				$this->getHeight()
			);
		}

		// If we are resizing to a new image, create a new JImage object.
		if ($createNew) {
			$new = new JImage($handle);
			return $new;
		}
		// Swap out the current handle for the new image handle.
		else {
			$this->handle = $handle;
			return $this;
		}
	}

	/**
	 * Method to write the current image out to a file.
	 *
	 * @param   string   $path     The filesystem path to save the image.
	 * @param   integer  $type     The image type to save the file as.
	 * @param   array    $options  The image type options to use in saving the file.
	 *
	 * @return  void
	 *
	 * @see     http://www.php.net/manual/image.constants.php
	 * @since   11.1
	 * @throws  MediaException
	 */
	function toFile($path, $type = IMAGETYPE_JPEG, $options=array())
	{
		// Make sure the file handle is valid.
		if ((!is_resource($this->handle) || get_resource_type($this->handle) != 'gd')) {
			JLog::add('The image is invalid.', JLog::ERROR);
			throw new MediaException();
		}

		switch ($type)
		{
			case IMAGETYPE_GIF:
				imagegif($this->handle, $path);
				break;

			case IMAGETYPE_PNG:
				imagepng($this->handle, $path, (array_key_exists('quality', $options)) ? $options['quality'] : 0);
				break;

			case IMAGETYPE_JPEG:
			default:
				imagejpeg($this->handle, $path, (array_key_exists('quality', $options)) ? $options['quality'] : 100);
				break;
		}
	}

	/**
	 * Method to get the new dimensions for a resized image.
	 *
	 * @param   mixed    $width        The width of the resized image in pixels or a percentage.
	 * @param   mixed    $height       The height of the resized image in pixels or a percentage.
	 * @param   integer  $scaleMethod
	 *
	 * @return  object
	 *
	 * @since   11.1
	 * @throws  MediaException
	 */
	protected function prepareDimensions($width, $height, $scaleMethod)
	{
		// Instantiate variables.
		$dimensions = new stdClass();

		switch ($scaleMethod)
		{
			case JImage::SCALE_FILL:
				$dimensions->width = $width;
				$dimensions->height = $height;
				break;

			case JImage::SCALE_INSIDE:
			case JImage::SCALE_OUTSIDE:
				$rx = $this->getWidth() / $width;
				$ry = $this->getHeight() / $height;

				if ($scaleMethod == JImage::SCALE_INSIDE) {
					$ratio = ($rx > $ry) ? $rx : $ry;
				}
				else {
					$ratio = ($rx < $ry) ? $rx : $ry;
				}

				$dimensions->width  = round($this->getWidth() / $ratio);
				$dimensions->height = round($this->getHeight() / $ratio);
				break;

			default:
				JLog::add('Invalid scale method.', JLog::ERROR);
				throw new MediaException();
				break;
		}

		return $dimensions;
	}

	/**
	 * Method to register all of the filter classes with the system autoloader and determine
	 * the image formats support.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected static function register()
	{
		// Determine which image types are supported by GD.
		$info = gd_info();
		self::$formats[IMAGETYPE_JPEG] = ($info['JPEG Support']) 	 ? true : false;
		self::$formats[IMAGETYPE_PNG]  = ($info['PNG Support']) 	 ? true : false;
		self::$formats[IMAGETYPE_GIF]  = ($info['GIF Read Support']) ? true : false;

		// Define the expected folder in which to find input classes.
		$folder = dirname(__FILE__).'/filters';
		
		jimport('joomla.filesystem.folder');
		foreach(JFolder::files($folder,"\.php$") as $entry){
					// Get the name and full path for each file.
					$name = preg_replace('#\.[^.]*$#', '', $entry);
					$path = $folder.'/'.$entry;
					// Register the class with the autoloader.
					JLoader::register('JImageFilter'.ucfirst($name), $path);
		}
	}
	
	/**
	 * Method to return the path
	 * 
	 * $return	string
	 * 
	 * @since	11.1
	 */
	public function getPath(){
		return $this->path;
	}
}
