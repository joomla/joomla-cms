<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 07.06.13 23:17 $
* @package CBLib\Image
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Image;

use Imagine;
use Imagine\Image\Color;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImagineInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\ManipulatorInterface;
use Exception;

defined('CBLIB') or die();

/**
 * CBLib\Image\Image Class implementation (e.g. new \CBLib\Image\Image())
 */
class Image {

	/** @var array */
	private $source					=	array();
	/** @var string */
	private $name					=	'';
	/** @var ImageInterface */
	private $imagine				=	null;
	/** @var ImageInterface */
	private $image					=	null;
	/** @var string */
	private $destination			=	'';
	/** @var boolean */
	private $alwaysResample			=	true;
	/** @var boolean */
	private $maintainAspectRatio	=	true;

	/**
	 * Processes images using Imagine library
	 *
	 * @param string  $software             The image software to use for processing images
	 * @param bool    $alwaysResample       If images should always resize even if their size is within the maximum limits
	 * @param bool    $maintainAspectRatio  If images should always maintain their aspect ratios when resizing
	 */
	public function __construct( $software = 'gd', $alwaysResample = true, $maintainAspectRatio = true ) {
		switch ( strtolower( $software ) ) {
			case 'gmagick':
				$this->imagine		=	new Imagine\Gmagick\Imagine();
				break;
			case 'imagick':
				$this->imagine		=	new Imagine\Imagick\Imagine();
				break;
			case 'gd':
			default:
				$this->imagine		=	new Imagine\Gd\Imagine();
				break;
		}

		$this->alwaysResample		=	$alwaysResample;
		$this->maintainAspectRatio	=	$maintainAspectRatio;
	}

	/**
	 * Returns the Imagine image software object
	 *
	 * @return ImagineInterface
	 */
	public function getImagine() {
		return $this->imagine;
	}

	/**
	 * Sets the opened image object for reuse in subsequent saves or additional imagine adjustments
	 *
	 * @param ImageInterface|ManipulatorInterface $image
	 */
	public function setImage( $image ) {
		$this->image	=	$image;
	}

	/**
	 * Returns the already opened image object
	 *
	 * @return ImageInterface|ManipulatorInterface
	 */
	public function getImage() {
		return $this->image;
	}

	/**
	 * Opens image object from image source to allow for pre-processing (e.g. add a rotato before resizing and saving)
	 *
	 * @return null|ImageInterface|ManipulatorInterface
	 * @throws Exception
	 */
	public function openImage() {
		$source			=	$this->getSource();

		if ( ( ! $source ) || ( ! isset( $source['tmp_name'] ) ) ) {
			throw new Exception( 'Missing source or source tmp_name.' );
		}

		$image			=	$this->getImage();

		if ( ! $image ) {
			$image		=	$this->getImagine()->open( $source['tmp_name'] );

			$this->setImage( $image );
		}

		return $image;
	}

	/**
	 * Sets the filename to store the image as
	 * If not supplied one will be generated from source
	 *
	 * @param string $name
	 */
	public function setName( $name ) {
		$this->name		=	$name;
	}

	/**
	 * Returns the source or set name to be used as filename
	 *
	 * @return null|string
	 * @throws Exception
	 */
	public function getName() {
		if ( ! $this->name ) {
			// No name override was supplied so lets generate one with a unique suffix:
			$source			=	$this->getSource();

			if ( ( ! $source ) || ( ! isset( $source['name'] ) ) ) {
				throw new Exception( 'Missing source or source name.' );
			}

			$this->name		=	uniqid( pathinfo( $source['name'], PATHINFO_FILENAME ) . '_' );
		}

		return $this->name;
	}

	/**
	 * Sets the image source location either from $_FILE array or as a image path
	 *
	 * @param array|string $source
	 */
	public function setSource( $source ) {
		if ( ! is_array( $source ) ) {
			// We're not a $_FILE array, but a source file path; lets parse it to a usable $_FILE array:
			$ext		=	pathinfo( $source, PATHINFO_EXTENSION );
			$mimes		=	array( 'bmp' => 'image/bmp', 'gif' => 'image/gif', 'jpeg' => 'image/jpeg', 'jpg' => 'image/jpeg', 'png' => 'image/png' );
			$source		=	array(	'name' => pathinfo( $source, PATHINFO_BASENAME ),
									'type' => ( isset( $mimes[$ext] ) ? $mimes[$ext] : 'application/octet-stream' ),
									'tmp_name' => $source,
									'error' => 0,
									'size' => filesize( $source )
								);
		}

		$this->source	=	$source;
	}

	/**
	 * Returns the $_FILE formatted source array
	 *
	 * @return array
	 */
	public function getSource() {
		return $this->source;
	}

	/**
	 * Sets the destination to store all image saves to
	 *
	 * @param string $path
	 */
	public function setDestination( $path ) {
		$this->destination	=	$path;
	}

	/**
	 * Returns the destination of all image saves
	 *
	 * @return string
	 */
	public function getDestination() {
		return $this->destination;
	}

	/**
	 * Sets if images should always be resample (resized) before saving
	 *
	 * @param bool $alwaysResample
	 */
	public function setAlwaysResample( $alwaysResample ) {
		$this->alwaysResample	=	$alwaysResample;
	}

	/**
	 * Returns if images are always being resampled
	 *
	 * @return bool
	 */
	public function getAlwaysResample() {
		return $this->alwaysResample;
	}

	/**
	 * Sets if images should always maintain their aspect ratios when resizing
	 *
	 * @param bool $maintainAspectRatio
	 */
	public function setMaintainAspectRatio( $maintainAspectRatio ) {
		$this->maintainAspectRatio	=	$maintainAspectRatio;
	}

	/**
	 * Returns if images are always maintaining their aspect ratios
	 *
	 * @return bool
	 */
	public function getMaintainAspectRatio() {
		return $this->maintainAspectRatio;
	}

	/**
	 * Returns a storage safe filename
	 *
	 * @param  string $string
	 * @return string
	 */
	private function cleanStorageString( $string ) {
		// Replace spaces with understore:
		$string		=	str_replace( ' ', '_', $string );
		// Replace duplicate underscores with a single underscore:
		$string		=	preg_replace( '/_+/', '_', $string );
		// Remove invalid characters:
		$string		=	preg_replace( '/[^-a-zA-Z0-9_]/', '', $string );

		return $string;
	}

	/**
	 * Returns the filename cleaned
	 *
	 * @return string
	 */
	public function getCleanName() {
		return $this->cleanStorageString( $this->getName() );
	}

	/**
	 * Returns the file extension cleaned
	 *
	 * @return null|string
	 * @throws Exception
	 */
	public function getCleanExt() {
		$source		=	$this->getSource();

		if ( ( ! $source ) || ( ! isset( $source['name'] ) ) ) {
			throw new Exception( 'Missing source or source name.' );
		}

		return $this->cleanStorageString( pathinfo( $source['name'], PATHINFO_EXTENSION ) );
	}

	/**
	 * Returns the file name and extension pair cleaned
	 *
	 * @return null|string
	 * @throws Exception
	 */
	public function getCleanFilename() {
		$name	=	$this->getCleanName();
		$ext	=	$this->getCleanExt();

		if ( ( ! $name ) || ( ! $ext ) ) {
			throw new Exception( 'Missing name or extension.' );
		}

		return $name . '.' . $ext;
	}

	/**
	 * Resizes and saves an image from source to its destination
	 *
	 * @param  int   $maxWidth             The maximum width of the image
	 * @param  int   $maxHeight            The maximum height of the image
	 * @param  bool  $maintainAspectRatio  If aspect ratio should be maintained when resizing (if null defaults to global)
	 * @param  bool  $alwaysResample       If image should always be resized resulting in a resampling of the images data (if null defaults to global)
	 * @throws Exception
	 */
	public function processImage( $maxWidth, $maxHeight, $maintainAspectRatio = null, $alwaysResample = null ) {
		$source								=	$this->getSource();
		$destination						=	$this->getDestination();
		$filename							=	$this->getCleanFilename();

		if ( ( ! $source ) || ( ! $destination ) || ( ! $filename ) ) {
			throw new Exception( 'Missing source, destination, or filename.' );
		}

		/** @var $image ImageInterface|ManipulatorInterface */
		$image								=	$this->openImage();

		if ( ! $image ) {
			throw new Exception( 'Image failed to load.' );
		}

		$imageSize							=	$image->getSize();
		$imageWidth							=	$imageSize->getWidth();
		$imageHeight						=	$imageSize->getHeight();

		if ( ! $maxWidth ) {
			$maxWidth						=	$imageWidth;
		}

		if ( ! $maxHeight ) {
			$maxHeight						=	$imageHeight;
		}

		if ( $maintainAspectRatio === null ) {
			$maintainAspectRatio			=	$this->getMaintainAspectRatio();
		}

		if ( $alwaysResample === null ) {
			$alwaysResample					=	$this->getAlwaysResample();
		}

		if ( ( $imageWidth > $maxWidth ) || ( $imageHeight > $maxHeight ) || $alwaysResample ) {
			$newImageSize					=	new Box( $maxWidth, $maxHeight );

			if ( ! $maintainAspectRatio ) {
				$image						=	$image->resize( $newImageSize )->save( $destination . $filename );
			} else {
				if ( $maintainAspectRatio == 2 ) {
					if ( ( $imageWidth < $maxWidth ) || ( $imageHeight < $maxHeight ) ) {
						if ( $imageWidth < $maxWidth ) {
							$widthDiff		=	max( 0, ( $maxWidth - $imageWidth ) );
						} else {
							$widthDiff		=	0;
						}

						if ( $imageHeight < $maxHeight ) {
							$heightDiff		=	max( 0, ( $maxHeight - $imageHeight ) );
						} else {
							$heightDiff		=	0;
						}

						$newImageWidth		=	( $imageWidth + $widthDiff + $heightDiff );
						$newImageHeight		=	( $imageHeight + $widthDiff + $heightDiff );

						$image				=	$image->resize( new Box( $newImageWidth, $newImageHeight ) );
					}

					$imageMode				=	ImageInterface::THUMBNAIL_OUTBOUND;
				} else {
					$imageMode				=	ImageInterface::THUMBNAIL_INSET;
				}

				$image						=	$image->thumbnail( $newImageSize, $imageMode )->save( $destination . $filename );
			}
		} else {
			$image							=	$image->save( $destination . $filename );
		}

		$this->setImage( $image );
	}

	/**
	 * Resizes an image to the specified height and width (optionally respecting aspect ratio)
	 *
	 * @param  int   $width                The width to resize the image to
	 * @param  int   $height               The height to resize the image to
	 * @param  bool  $maintainAspectRatio  If aspect ratio should be maintained when resizing (if null defaults to global)
	 * @param  bool  $save                 If the image should be saved or just set into memory (useful for chaining modifications)
	 * @throws Exception
	 */
	public function resizeImage( $width, $height, $maintainAspectRatio = null, $save = false ) {
		$source						=	$this->getSource();
		$destination				=	$this->getDestination();
		$filename					=	$this->getCleanFilename();

		if ( ( ! $source ) || ( $save && ( ( ! $destination ) || ( ! $filename ) ) ) ) {
			throw new Exception( 'Missing source, destination, or filename.' );
		}

		/** @var $image ImageInterface|ManipulatorInterface */
		$image						=	$this->openImage();

		if ( ! $image ) {
			throw new Exception( 'Image failed to load.' );
		}

		$imageSize						=	$image->getSize();
		$imageWidth						=	$imageSize->getWidth();
		$imageHeight					=	$imageSize->getHeight();

		if ( $maintainAspectRatio === null ) {
			$maintainAspectRatio		=	$this->getMaintainAspectRatio();
		}

		$newImageSize					=	new Box( $width, $height );

		if ( ! $maintainAspectRatio ) {
			$image						=	$image->resize( $newImageSize );
		} else {
			if ( $maintainAspectRatio == 2 ) {
				if ( ( $imageWidth < $width ) || ( $imageHeight < $height ) ) {
					if ( $imageWidth < $width ) {
						$widthDiff		=	max( 0, ( $width - $imageWidth ) );
					} else {
						$widthDiff		=	0;
					}

					if ( $imageHeight < $height ) {
						$heightDiff		=	max( 0, ( $height - $imageHeight ) );
					} else {
						$heightDiff		=	0;
					}

					$newImageWidth		=	( $imageWidth + $widthDiff + $heightDiff );
					$newImageHeight		=	( $imageHeight + $widthDiff + $heightDiff );

					$image				=	$image->resize( new Box( $newImageWidth, $newImageHeight ) );
				}

				$imageMode				=	ImageInterface::THUMBNAIL_OUTBOUND;
			} else {
				$imageMode				=	ImageInterface::THUMBNAIL_INSET;
			}

			$image						=	$image->thumbnail( $newImageSize, $imageMode );
		}

		if ( $save ) {
			$image						=	$image->save( $destination . $filename );
		}

		$this->setImage( $image );
	}

	/**
	 * Crops an image at the specified X/Y coordinates of a specific height and width
	 *
	 * @param  int   $cropX       The X coordinates to crop from
	 * @param  int   $cropY       The Y coordinates to crop from
	 * @param  int   $cropWidth   The width of the crop
	 * @param  int   $cropHeight  The height of the crop
	 * @param  bool  $save        If the image should be saved or just set into memory (useful for chaining modifications)
	 * @throws Exception
	 */
	public function cropImage( $cropX, $cropY, $cropWidth, $cropHeight, $save = false ) {
		$source				=	$this->getSource();
		$destination		=	$this->getDestination();
		$filename			=	$this->getCleanFilename();

		if ( ( ! $source ) || ( $save && ( ( ! $destination ) || ( ! $filename ) ) ) ) {
			throw new Exception( 'Missing source, destination, or filename.' );
		}

		$image				=	$this->openImage();

		if ( ! $image ) {
			throw new Exception( 'Image failed to load.' );
		}

		$cropLocation		=	new Point( $cropX, $cropY );
		$copSize			=	new Box( $cropWidth, $cropHeight );

		$image				=	$image->crop( $cropLocation, $copSize );

		if ( $save ) {
			$image			=	$image->save( $destination . $filename );
		}

		$this->setImage( $image );
	}

	/**
	 * Rotates an image at the specified angle
	 *
	 * @param  int          $angle       The angle to rotate the image at
	 * @param  null|string  $background  Optional color to be used for the empty background space created by the rotation
	 * @param  bool         $save        If the image should be saved or just set into memory (useful for chaining modifications)
	 * @throws Exception
	 */
	public function rotateImage( $angle, $background = null, $save = false ) {
		$source				=	$this->getSource();
		$destination		=	$this->getDestination();
		$filename			=	$this->getCleanFilename();

		if ( ( ! $source ) || ( $save && ( ( ! $destination ) || ( ! $filename ) ) ) ) {
			throw new Exception( 'Missing source, destination, or filename.' );
		}

		$image				=	$this->openImage();

		if ( ! $image ) {
			throw new Exception( 'Image failed to load.' );
		}

		if ( $background ) {
			$background		=	new Color( $background );
		}

		$image				=	$image->rotate( $angle, $background );

		if ( $save ) {
			$image			=	$image->save( $destination . $filename );
		}

		$this->setImage( $image );
	}

	/**
	 * Saves the image from source to its destination
	 *
	 * @throws Exception
	 */
	public function saveImage() {
		$source			=	$this->getSource();
		$destination	=	$this->getDestination();
		$filename		=	$this->getCleanFilename();

		if ( ( ! $source ) || ( ! $destination ) || ( ! $filename ) ) {
			throw new Exception( 'Missing source, destination, or filename.' );
		}

		$image			=	$this->openImage();

		if ( ! $image ) {
			throw new Exception( 'Image failed to load.' );
		}

		$image			=	$image->save( $destination . $filename );

		$this->setImage( $image );
	}
}
