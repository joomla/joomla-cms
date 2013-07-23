<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Amazons3
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Defines the PUT operations on buckets
 *
 * @package     Joomla.Platform
 * @subpackage  Amazons3
 * @since       ??.?
 */
class JAmazons3OperationsBucketsPut extends JAmazons3OperationsBuckets
{
	/**
	 * Creates the request for creating a bucket and returns the response from Amazon
	 *
	 * @param   string  $bucket        The bucket name
	 * @param   string  $bucketRegion  The bucket region (default: US Standard)
	 * @param   string  $acl           An array containing the ACL permissions
	 *                                 (either canned or explicitly specified)
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function putBucket($bucket, $bucketRegion = "", $acl = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/";
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);
		$content = "";

		// Check for ACL permissions
		if (is_array($acl))
		{
			// Check for canned ACL permission
			if (array_key_exists("acl", $acl))
			{
				$headers["x-amz-acl"] = $acl["acl"];
			}
			else
			{
				// Access permissions were specified explicitly
				foreach ($acl as $aclPermission => $aclGrantee)
				{
					$headers["x-amz-grant-" . $aclPermission] = $aclGrantee;
				}
			}
		}

		if ($bucketRegion != "")
		{
			$content = "<CreateBucketConfiguration xmlns=\"http://s3.amazonaws.com/doc/2006-03-01/\">"
				. "<LocationConstraint>" . $bucketRegion . "</LocationConstraint>"
				. "</CreateBucketConfiguration>";

			$headers["Content-type"] = "application/x-www-form-urlencoded; charset=utf-8";
			$headers["Content-Length"] = strlen($content);
		}

		$authorization = $this->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;
		unset($headers["Content-type"]);

		// Send the http request
		$response = $this->client->put($url, $content, $headers);

		// Process the response
		$response_body = $this->processResponse($response);

		return $response_body;
	}

	/**
	 * Creates the request for setting the permissions on an existing bucket
	 * using access control lists (ACL)
	 *
	 * @param   string  $bucket  The bucket name
	 * @param   string  $acl     An array containing the ACL permissions
	 *                           (either canned or explicitly specified)
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function putBucketAcl($bucket, $acl = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?acl";
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);

		// Check for ACL permissions
		if (is_array($acl))
		{
			// Check for canned ACL permission
			if (array_key_exists("acl", $acl))
			{
				$headers["x-amz-acl"] = $acl["acl"];
			}
			else
			{
				// Access permissions were specified explicitly
				foreach ($acl as $aclPermission => $aclGrantee)
				{
					$headers["x-amz-grant-" . $aclPermission] = $aclGrantee;
				}
			}
		}

		$authorization = $this->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;

		// Send the http request
		$response = $this->client->put($url, "", $headers);

		// Process the response
		$response_body = $this->processResponse($response);

		return $response_body;
	}

	/**
	 * Creates the request for setting the CORS configuration for your bucket
	 *
	 * @param   string  $bucket  The bucket name
	 * @param   string  $rules   An array containing the CORS rules
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function putBucketCors($bucket, $rules = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?cors";
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);
		$content = "";

		// Check for CORS rules
		if (is_array($rules))
		{
			$content = "<CORSConfiguration>\n";

			// $rules is an array of rules (which in turn are arrays of rule properties)
			foreach ($rules as $rule)
			{
				$content .= "<CORSRule>\n";

				// Parse the rule properties
				foreach ($rule as $rulePropertyKey => $rulePropertyValue)
				{
					if (is_array($rulePropertyValue))
					{
						// Create a new XML node for each property value
						foreach ($rulePropertyValue as $currentValue)
						{
							$content .= "<" . $rulePropertyKey . ">"
								. $currentValue
								. "</" . $rulePropertyKey . ">" . "\n";
						}
					}
					else
					{
						$content .= "<" . $rulePropertyKey . ">"
							. $rulePropertyValue
							. "</" . $rulePropertyKey . ">" . "\n";
					}
				}

				$content .= "</CORSRule>\n";
			}

			$content .= "</CORSConfiguration>\n";

			// Set the content related headers
			$headers["Content-type"] = "application/x-www-form-urlencoded; charset=utf-8";
			$headers["Content-Length"] = strlen($content);
			$headers["Content-MD5"] = base64_encode(md5($content, true));
		}

		$authorization = $this->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;
		unset($headers["Content-type"]);

		// Send the http request
		$response = $this->client->put($url, $content, $headers);

		// Process the response
		$response_body = $this->processResponse($response);

		return $response_body;
	}

	/**
	 * Creates a new lifecycle configuration for the bucket or replaces
	 * an existing lifecycle configuration
	 *
	 * @param   string  $bucket  The bucket name
	 * @param   string  $rules   An array containing the lifecycle configuration rules
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function putBucketLifecycle($bucket, $rules = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?lifecycle";
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);
		$content = "";

		// Check for lifecycle configuration rules
		if (is_array($rules))
		{
			$content = "<LifecycleConfiguration>\n";

			// $rules is an array of rules (which in turn are arrays of rule properties)
			foreach ($rules as $rule)
			{
				$content .= "<Rule>\n";

				// Parse the rule properties
				foreach ($rule as $rulePropertyKey => $rulePropertyValue)
				{
					if (is_array($rulePropertyValue))
					{
						$content .= "<" . $rulePropertyKey . ">\n";

						foreach ($rulePropertyValue as $currentKey => $currentValue)
						{
							$content .= "<" . $currentKey . ">"
								. $currentValue
								. "</" . $currentKey . ">" . "\n";
						}

						$content .= "</" . $rulePropertyKey . ">\n";
					}
					else
					{
						$content .= "<" . $rulePropertyKey . ">"
							. $rulePropertyValue
							. "</" . $rulePropertyKey . ">" . "\n";
					}
				}

				$content .= "</Rule>\n";
			}

			$content .= "</LifecycleConfiguration>\n";

			// Set the content related headers
			$headers["Content-type"] = "application/x-www-form-urlencoded; charset=utf-8";
			$headers["Content-Length"] = strlen($content);
			$headers["Content-MD5"] = base64_encode(md5($content, true));
		}

		$authorization = $this->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;
		unset($headers["Content-type"]);

		// Send the http request
		$response = $this->client->put($url, $content, $headers);

		// Process the response
		$response_body = $this->processResponse($response);

		return $response_body;
	}

	/**
	 *  Adds to or replaces a policy on a bucket.
	 *
	 * @param   string  $bucket  The bucket name
	 * @param   string  $policy  An array containing the bucket policy
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function putBucketPolicy($bucket, $policy = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?policy";
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);
		$content = "";

		// Check for lifecycle configuration rules
		if (is_array($policy))
		{
			$content .= json_encode($policy);

			// Set the content related headers
			$headers["Content-type"] = "application/x-www-form-urlencoded; charset=utf-8";
			$headers["Content-Length"] = strlen($content);
			$headers["Content-MD5"] = base64_encode(md5($content, true));
		}

		$authorization = $this->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;
		unset($headers["Content-type"]);

		// Send the http request
		$response = $this->client->put($url, $content, $headers);

		// Process the response
		$response_body = $this->processResponse($response);

		return $response_body;
	}

	/**
	 * Sets the logging parameters for a bucket and specifies permissions for
	 * who can view and modify the logging parameters
	 *
	 * @param   string  $bucket   The bucket name
	 * @param   string  $logging  An array containing the logging details
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function putBucketLogging($bucket, $logging = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?logging";
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);
		$content = "";

		// Check for lifecycle configuration rules
		if (is_array($logging))
		{
			$content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
				. "<BucketLoggingStatus xmlns=\"http://doc.s3.amazonaws.com/2006-03-01\">\n"
				. "<LoggingEnabled>\n";

			// $logging is an array of rules (which in turn are arrays of rule properties)
			foreach ($logging as $ruleKey => $ruleValue)
			{
				// Parse the rule properties
				if (strcmp($ruleKey, "TargetGrants") == 0)
				{
					$content .= "<" . $ruleKey . ">\n";

					foreach ($ruleValue as $currentKey => $currentValue)
					{
						$content .= "<" . $currentKey . ">\n";

						foreach ($currentValue as $key => $val)
						{
							if (strcmp($key, "Grantee") == 0)
							{
								$content .= "<Grantee xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:type=\"";

								if (array_key_exists("EmailAddress", $val))
								{
									$content .= "AmazonCustomerByEmail\">\n";
									$content .= "<EmailAddress>" . $val["EmailAddress"] . "</EmailAddress>\n";
								}
								else
								{
									if (array_key_exists("URI", $val))
									{
										$content .= "Group\">\n";
										$content .= "<URI>" . $val["EmailAddress"] . "</URI>\n";
									}
									else
									{
										// Specify the grantee by the person's ID
										$content .= "CanonicalUser\">\n";
										$content .= "<ID>" . $val["ID"] . "</ID>\n";
										$content .= "<DisplayName>" . $val["DisplayName"] . "</DisplayName>\n";
									}
								}
							}
							else
							{
								$content .= "<" . $key . ">" . $val;
							}

							$content .= "</" . $key . ">\n";
						}

						$content .= "</" . $currentKey . ">\n";
					}

					$content .= "</" . $ruleKey . ">\n";
				}
				else
				{
					$content .= "<" . $ruleKey . ">"
						. $ruleValue
						. "</" . $ruleKey . ">" . "\n";
				}
			}

			$content .= "</LoggingEnabled>\n"
				. "</BucketLoggingStatus>\n";
		}
		else
		{
			// If the method is called with only one argument, the logging is disabled
			$content = '<?xml version="1.0" encoding="UTF-8"?>\n'
				. '<BucketLoggingStatus xmlns="http://doc.s3.amazonaws.com/2006-03-01" />';
		}

		// Set the content related headers
		$headers["Content-type"] = "application/x-www-form-urlencoded; charset=utf-8";
		$headers["Content-Length"] = strlen($content);
		$headers["Content-MD5"] = base64_encode(md5($content, true));
		$authorization = $this->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;
		unset($headers["Content-type"]);

		// Send the http request
		$response = $this->client->put($url, $content, $headers);

		// Process the response
		$response_body = $this->processResponse($response);

		return $response_body;
	}

	/**
	 * Enables notifications of specified events for a bucket
	 *
	 * @param   string  $bucket        The bucket name
	 * @param   string  $notification  An array containing the $notification details
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function putBucketNotification($bucket, $notification = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?notification";
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);
		$content = "";

		// Check for lifecycle configuration rules
		if (is_array($notification))
		{
			$content = "<NotificationConfiguration>\n"
				. "<TopicConfiguration>\n";

			// $notification is an array of rules (which in turn are arrays of rule properties)
			foreach ($notification as $ruleKey => $ruleValue)
			{
				$content .= "<" . $ruleKey . ">";
				$content .= $ruleValue;
				$content .= "</" . $ruleKey . ">\n";
			}

			$content .= "</TopicConfiguration>\n";
			$content .= "</NotificationConfiguration>";
		}
		else
		{
			// If the method is called with only one argument, the logging is disabled
			$content .= "<NotificationConfiguration />";
		}

		// Set the content related headers
		$headers["Content-type"] = "application/x-www-form-urlencoded; charset=utf-8";
		$headers["Content-Length"] = strlen($content);
		$headers["Content-MD5"] = base64_encode(md5($content, true));
		$authorization = $this->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;
		unset($headers["Content-type"]);

		// Send the http request
		$response = $this->client->put($url, $content, $headers);

		// Process the response
		$response_body = $this->processResponse($response);

		return $response_body;
	}

	/**
	 * Adds a set of tags to an existing bucket
	 *
	 * @param   string  $bucket  The bucket name
	 * @param   string  $tags    An array containing the tags
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function putBucketTagging($bucket, $tags = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?tagging";
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);
		$content = "";

		// Check for lifecycle configuration rules
		if (is_array($tags))
		{
			$content = "<Tagging>\n"
				. "<TagSet>\n";

			// $tags is an array of rules (which in turn are arrays of rule properties)
			foreach ($tags as $tag)
			{
				$content .= "<Tag>\n";

				foreach ($tag as $ruleKey => $ruleValue)
				{
					$content .= "<" . $ruleKey . ">";
					$content .= $ruleValue;
					$content .= "</" . $ruleKey . ">\n";
				}

				$content .= "</Tag>\n";
			}

			$content .= "</TagSet>\n";
			$content .= "</Tagging>";
		}

		// Set the content related headers
		$headers["Content-type"] = "application/x-www-form-urlencoded; charset=utf-8";
		$headers["Content-Length"] = strlen($content);
		$headers["Content-MD5"] = base64_encode(md5($content, true));
		$authorization = $this->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;
		unset($headers["Content-type"]);

		// Send the http request
		$response = $this->client->put($url, $content, $headers);

		// Process the response
		$response_body = $this->processResponse($response);

		return $response_body;
	}

	/**
	 * Sets the request payment configuration of a bucket
	 *
	 * @param   string  $bucket  The bucket name
	 * @param   string  $payer   Specifies who pays for the download and request fees.
	 *                           Valid Values: Requester | BucketOwner
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function putBucketRequestPayment($bucket, $payer)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?requestPayment";
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);
		$content = "<RequestPaymentConfiguration xmlns=\"http://s3.amazonaws.com/doc/2006-03-01/\">\n"
			. "<Payer>" . $payer . "</Payer>\n"
			. "</RequestPaymentConfiguration>";

		// Set the content related headers
		$headers["Content-type"] = "application/x-www-form-urlencoded; charset=utf-8";
		$headers["Content-Length"] = strlen($content);
		$headers["Content-MD5"] = base64_encode(md5($content, true));
		$authorization = $this->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;
		unset($headers["Content-type"]);

		// Send the http request
		$response = $this->client->put($url, $content, $headers);

		// Process the response
		$response_body = $this->processResponse($response);

		return $response_body;
	}

	/**
	 * Sets the versioning state of an existing bucket
	 *
	 * @param   string  $bucket      The bucket name
	 * @param   string  $versioning  Array with Status and MfaDelete
	 * @param   string  $serialNr    The serial number is generated using either a hardware or virtual MFA device
	 *                               Required for MfaDelete
	 * @param   string  $tokenCode   Also required for MfaDelete
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function putBucketVersioning($bucket, $versioning, $serialNr = null, $tokenCode = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?versioning";
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);

		if (is_array($versioning))
		{
			// Check for MfaDelete
			if (array_key_exists("MfaDelete", $versioning))
			{
				$headers["x-amz-mfa"] = $serialNr . " " . $tokenCode;
			}

			$content = "<VersioningConfiguration xmlns=\"http://s3.amazonaws.com/doc/2006-03-01/\">\n";

			foreach ($versioning as $key => $value)
			{
				$content .= "<" . $key . ">" . $value . "</" . $key . ">\n";
			}

			$content .= "</VersioningConfiguration>";
		}

		// Set the content related headers
		$headers["Content-type"] = "application/x-www-form-urlencoded; charset=utf-8";
		$headers["Content-Length"] = strlen($content);
		$headers["Content-MD5"] = base64_encode(md5($content, true));
		$authorization = $this->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;
		unset($headers["Content-type"]);

		// Send the http request
		$response = $this->client->put($url, $content, $headers);

		// Process the response
		$response_body = $this->processResponse($response);

		return $response_body;
	}

	/**
	 * Sets the configuration of the website that is specified in the website subresource
	 *
	 * @param   string  $bucket   The bucket name
	 * @param   string  $website  An array containing website parameters
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function putBucketWebsite($bucket, $website)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?website";
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);

		if (is_array($website))
		{
			$content = "<WebsiteConfiguration xmlns=\"http://s3.amazonaws.com/doc/2006-03-01/\">\n";

			foreach ($website as $key => $value)
			{
				$content .= "<" . $key . ">\n";

				foreach ($value as $subKey => $subValue)
				{
					$content .= "<" . $subKey . ">";

					if (is_array($subValue))
					{
						$content .= "\n";

						foreach ($subValue as $subKey2 => $subValue2)
						{
							if (is_array($subValue2))
							{
								$content .= "<" . $subKey2 . ">\n";

								foreach ($subValue2 as $subKey3 => $subValue3)
								{
									$content .= "<" . $subKey3 . ">";
									$content .= $subValue3;
									$content .= "</" . $subKey3 . ">\n";
								}

								$content .= "</" . $subKey2 . ">\n";
							}
							else
							{
								$content .= $subValue2;
							}
						}
					}
					else
					{
						$content .= $subValue;
					}

					$content .= "</" . $subKey . ">\n";
				}

				$content .= "</" . $key . ">\n";
			}

			$content .= "</WebsiteConfiguration>";
		}

		// Set the content related headers
		$headers["Content-type"] = "application/x-www-form-urlencoded; charset=utf-8";
		$headers["Content-Length"] = strlen($content);
		$headers["Content-MD5"] = base64_encode(md5($content, true));
		$authorization = $this->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;
		unset($headers["Content-type"]);

		// Send the http request
		$response = $this->client->put($url, $content, $headers);

		// Process the response
		$response_body = $this->processResponse($response);

		return $response_body;
	}
}
