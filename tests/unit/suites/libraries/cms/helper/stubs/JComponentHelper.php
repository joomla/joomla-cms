<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Registry\Registry;

/**
 * Stub class for JComponentHelper
 *
 * @package     Joomla.UnitTest
 * @subpackage  Media
 * @since       __DEPLOY_VERSIO__
 */
class JComponentHelper {

	protected static $params = null;

	public static function getParams()
	{
		if (is_null(self::$params)) {
			self::$params = new Registry;
		}

		self::$params = new Registry;

		self::$params->set('check_mime', 1);
		self::$params->set('upload_mime', array());
		self::$params->set('upload_extensions', 'bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,BMP,CSV,DOC,GIF,ICO,JPG,JPEG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,SWF,TXT,XCF,XLS' );
		self::$params->set('upload_maxsize', 10);
		self::$params->set('file_path', 'images');
		self::$params->set('image_path', 'images');
		self::$params->set('restrict_uploads', '1');
		self::$params->set('image_extensions', 'bmp,gif,jpg,png');
		self::$params->set('ignore_extensions', '');
		self::$params->set('upload_mime', 'image/jpeg,image/gif,image/png,image/bmp,application/x-shockwave-flash,application/msword,application/excel,application/pdf,application/powerpoint,text/plain,application/x-zip');
		self::$params->set('upload_mime_illegal', 'text/html');

		return self::$params;
	}
}