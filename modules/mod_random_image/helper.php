<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_random_image
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_random_image
 *
 * @package     Joomla.Site
 * @subpackage  mod_random_image
 * @since       1.5
 */
class ModRandomImageHelper
{
	/**
	 * @var	params	the params array for the module
	 */
	protected $params;
	
	/**
	 * @var	cms	the glue object for talking to the cms
	 */
	protected $cms;
	
	/**
	 *
	 * Constructor.
	 *
	 * @param	params	JRegistry		params
	 * @param	cms		CMS Glue object	glue for cms API
	 *
	 * @return	modRandomImageHelper object
	 *
	 * @since Unspecified Possible Future Version
	 */
	public function __construct( $params, $cms )
	{
		$this->params = $params;
		$this->cms = $cms;
	}
	/**
	 * getRandomImage.
	 *
	 * @param	images	array	Array of image file names
	 *
	 * @return	image filename
	 *
	 * @since Unspecified Possible Future Version
	 */
	public function getRandomImage($images)
	{
		$width	= $this->params->get('width');
		$height	= $this->params->get('height');
		$i			= count($images);
		$random		= mt_rand(0, $i - 1);
		$image		= $images[$random];
		$size		= getimagesize(JPATH_BASE . '/' . $image->folder . '/' . $image->name);


		if ($width == '') {
			$width = 100;
		}

		if ($size[0] < $width) {
			$width = $size[0];
		}

		$coeff = $size[0]/$size[1];
		if ($height == '') {
			$height = (int) ($width/$coeff);
		} else {
			$newheight = min ($height, (int) ($width/$coeff));
			if ($newheight < $height) {
				$height = $newheight;
			} else {
				$width = $height * $coeff;
			}
		}

		$image->width	= $width;
		$image->height	= $height;
		$image->folder	= str_replace('\\', '/', $image->folder);

		return $image;
	}
	/**
	 * getImages
	 *
	 * @param	theFolder	string	path to image directory
	 *
	 * @return	array eligible images
	 *
	 * @since Unspecified Possible Future Version
	 */
	public function getImages($theFolder, $type)
	{
		$folder = $this->getFolder($theFolder);
		$directory = JPATH_BASE . '/' . $folder;
		return $this->getFilenameArray($directory, $type, $folder);
	}
	/**
	 * getFolder
	 *
	 * @param	theFolder	string	path to image directory
	 *
	 * @return	string		path relative to site base of image directory
	 *
	 * @since Unspecified Possible Future Version
	 */
	public function getFolder($theFolder)
	{ 
		$folder = $this->removeLiveSite($theFolder);
		$folder = $this->makeRelativePath($folder,JPATH_SITE);
		$folder = str_replace('\\', DIRECTORY_SEPARATOR, $folder);
		$folder = str_replace('/', DIRECTORY_SEPARATOR, $folder);

		return $folder;
	}
	/**
	 *
	 * createOutput.
	 *
	 * This method outputs through the selected template the results of the
	 * module.
	 *
	 * @return	none
	 *
	 * @since Unspecified Possible Future Version
	 */
	public function createOutput()
	{
 		$link	= $this->params->get('link');
		$moduleclass_sfx = $this->params->get('moduleclass_sfx');

		$images	= $this->getImages($this->params->get('folder'),
			$this->params->get('type', 'jpg')
		);

		if (!count($images)) {
			echo $this->cms->getTranslatedText('MOD_RANDOM_IMAGE_NO_IMAGES');
		} else {
			$image = $this->getRandomImage($images);
			require $this->cms->getLayoutPath($this->params->get('layout'));
		}
	}
	/**
	 *	removeLiveSite
	 *
	 *	Removes the current live site from the folder string, if present.
	 *
	 *	@param	path	String	the path/uri to the folder
	 *	@private
	 *
	 *	@return	string
	 */
	 private function removeLiveSite( $path )
	 {
		$LiveSite	= $this->cms->getBaseURL();

		// if folder includes livesite info, remove
		if ($this->cms->strpos($path, $LiveSite) === 0) {
			$path = str_replace($LiveSite, '', $path);
		}
		
		return $path;
	 }
	/**
	 *	makeRelativePath
	 *
	 *	Makes the given path relative to another path, if possible.
	 *
	 *	@param	path	String	the path/uri to the folder
	 *	@param	base	String	the path to base it off.
	 *	@private
	 *
	 *	@return	string
	 */
	 private function makeRelativePath( $path, $base )
	 {
		// if folder includes absolute path, remove
		if ($this->cms->strpos($path, $base) === 0) {
			$path= str_replace($base . '/', '', $path);
		}
		return $path;
	 }
	/**
	 *	getFilenameArray
	 *
	 *	Gets the names of the files in the given directory.
	 *
	 *	@param	directory	String	the directory to look in
	 *	@param	type		String	the regex string for image type matches
	 *	@param	folder		String	path relative to site base 
	 *	@private
	 *
	 *	@return	array
	 */
	 private function getFilenameArray( $directory, $type, $folder )
	 {
		$images	= array();

		if (!is_dir($directory)) { return $images; }
		
		if ($handle = opendir($directory)) {
			$i = 0;
			while (false !== ($file = readdir($handle))) {
				if ($file != '.' && $file != '..' &&
						!is_dir($directory . '/' . $file) &&
						preg_match('/'.$type.'/', $file)) {
					$images[$i] = new stdClass;
					$images[$i]->name	= $file;
					$images[$i]->folder	= $folder;
					$i++;
				}
			}
		}
		closedir($handle);
		
		return $images;
	 }
}
