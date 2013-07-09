<?php
/**
 * @package     Joomla.Platform
 * @subpackage  AmazonS3
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Defines the get operation on the service
 *
 * @package     Joomla.Platform
 * @subpackage  AmazonS3
 * @since       ??.?
 */
class JGetService
{
	/**
	 * @var    String	The request body.
	 * @since  ??.?
	 */
	protected $request;
	
	/**
	 * Constructor.
	 *
	 * @since   ??.?
	 */
	public function __construct($accessKeyId, $signature)
	{
		$this->request = "GET / HTTP/1.1\n";
		$this->request .= JCommonRequestHeaders::getHost("s3.amazonaws.com") .= "\n";
		$this->request .= JCommonRequestHeaders::getDate(getdate()) .= "\n";
		$this->request .= JCommonRequestHeaders::getAuthorization(
			$this->createAuthorization($accessKeyId, $signature)
		);
	
	}
	
	/**
	 * Creates the authorization access key and signature
	 *
	 * @since   ??.?
	 */
	private function createAuthorization($accessKeyId, $signature) {
		return "AWS " . $accessKeyId . ":" . $signature;
	}
}