<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Googlecloudstorage
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Defines the PUT operation on buckets
 *
 * @package     Joomla.Platform
 * @subpackage  Googlecloudstorage
 * @since       ??.?
 */
class JGooglecloudstorageBucketsPut extends JGooglecloudstorageBuckets
{
	/**
	 * Creates the request for creating a bucket and returns the response
	 *
	 * @param   string  $bucket          The bucket name
	 * @param   string  $bucketLocation  The bucket region (default: US Standard)
	 * @param   string  $predefinedAcl   The predefined ACL
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function putBucket($bucket, $bucketLocation = null, $predefinedAcl = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/";
		$content = "";
		$headers = array(
			"Host" => $bucket . "." . $this->options->get("api.url"),
			"Date" => date("D, d M Y H:i:s O"),
			"x-goog-api-version" => 2,
			"x-goog-project-id" => $this->options->get("project.id"),
		);

		if ($predefinedAcl != null)
		{
			$headers["x-goog-acl"] = $predefinedAcl;
		}

		if ($bucketLocation != null)
		{
			$content = "<CreateBucketConfiguration>\n"
				. "<LocationConstraint>" . $bucketLocation . "</LocationConstraint>\n"
				. "</CreateBucketConfiguration>";

			$headers["Content-Type"] = "application/x-www-form-urlencoded; charset=utf-8";
		}

		$headers["Content-Length"] = strlen($content);
		$authorization = $this->getAuthorization(
			$this->options->get("api.oauth.scope.read-write")
		);
		$headers["Authorization"] = $authorization;

		// Send the http request
		$response = $this->client->put($url, $content, $headers);

		// Process the response
		return $this->processResponse($response);
	}

	/**
	 * Creates the XML which will be sent in a put request with the acl query parameter
	 *
	 * @param   string  $acl  An array containing the ACL permissions
	 *
	 * @return string The XML
	 */
	public function createAclXml($acl)
	{
		$content = "<AccessControlList>\n";

		foreach ($acl as $aclKey => $aclValue)
		{
			if (strcmp($aclKey, "Owner") === 0)
			{
				$content .= "<Owner>\n<ID>" . $aclValue . "</ID>\n</Owner>\n";
			}
			else
			{
				$content .= "<Entries>\n";

				foreach ($aclValue as $entry)
				{
					$content .= "<Entry>\n";

					foreach ($entry as $entryKey => $entryValue)
					{
						if (is_array($entryValue))
						{
							$content .= "<Scope type=\"" . $entryValue["type"] . "\">\n";

							foreach ($entryValue as $scopeKey => $scopeValue)
							{
								if (strcmp($scopeKey, "type") !== 0)
								{
									$content .= "<" . $scopeKey . ">" . $scopeValue . "</" . $scopeKey . ">\n";
								}
							}

							$content .= "</Scope>\n";
						}
						else
						{
							// Permission
							$content .= "<" . $entryKey . ">" . $entryValue . "</" . $entryKey . ">\n";
						}
					}

					$content .= "</Entry>\n";
				}

				$content .= "</Entries>\n";
			}
		}

		$content .= "</AccessControlList>";

		return $content;
	}

	/**
	 * Creates the request for setting the permissions on an existing bucket
	 * using access control lists (ACL)
	 *
	 * @param   string  $bucket  The bucket name
	 * @param   string  $acl     An array containing the ACL permissions
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function putBucketAcl($bucket, $acl = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?acl";
		$content = "";
		$headers = array(
			"Host" => $bucket . "." . $this->options->get("api.url"),
			"Date" => date("D, d M Y H:i:s O"),
			"x-goog-api-version" => 2,
			"x-goog-project-id" => $this->options->get("project.id"),
		);

		// Check for ACL permissions
		if (is_array($acl))
		{
			$headers["Content-Type"] = "application/x-www-form-urlencoded; charset=utf-8";
			$content = $this->createAclXml($acl);
		}

		$headers["Content-Length"] = strlen($content);
		$authorization = $this->getAuthorization(
			$this->options->get("api.oauth.scope.full-control")
		);
		$headers["Authorization"] = $authorization;

		// Send the http request
		$response = $this->client->put($url, $content, $headers);

		// Process the response
		return $this->processResponse($response);
	}

	/**
	 * Creates the XML which will be sent in a put request with the cors query parameter
	 *
	 * @param   string  $corsConfig  An array containing the CORS configuration
	 *
	 * @return string The XML
	 */
	public function createCorsXml($corsConfig)
	{
		$content = "<CorsConfig>\n";

		// Foreach Cors
		foreach ($corsConfig as $cors)
		{
			$content .= "<Cors>\n";

			foreach ($cors as $corsKey => $corsValue)
			{
				if (strcmp($corsKey, "MaxAgeSec") === 0)
				{
					$content .= "<MaxAgeSec>" . $corsValue . "</MaxAgeSec>\n";
				}
				else
				{
					$content .= "<" . $corsKey . ">\n";

					foreach ($corsValue as $item)
					{
						$itemName = substr($corsKey, 0, -1);
						$content .= "<" . $itemName . ">" . $item . "</" . $itemName . ">\n";
					}

					$content .= "</" . $corsKey . ">\n";
				}
			}

			$content .= "</Cors>\n";
		}

		$content .= "</CorsConfig>";

		return $content;
	}

	/**
	 * Creates the request for setting the CORS configuration on an existing bucket
	 *
	 * @param   string  $bucket  The bucket name
	 * @param   string  $cors    An array containing the CORS configuration
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function putBucketCors($bucket, $cors = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?cors";
		$content = "";
		$headers = array(
			"Host" => $bucket . "." . $this->options->get("api.url"),
			"Date" => date("D, d M Y H:i:s O"),
			"x-goog-api-version" => 2,
			"x-goog-project-id" => $this->options->get("project.id"),
		);

		// Check for CORS rules
		if (is_array($cors))
		{
			$headers["Content-Type"] = "application/x-www-form-urlencoded; charset=utf-8";
			$content = $this->createCorsXml($cors);
		}

		$headers["Content-Length"] = strlen($content);
		$authorization = $this->getAuthorization(
			$this->options->get("api.oauth.scope.full-control")
		);
		$headers["Authorization"] = $authorization;

		// Send the http request
		$response = $this->client->put($url, $content, $headers);

		// Process the response
		return $this->processResponse($response);
	}

	/**
	 * Creates the XML which will be sent in a put request with the lifecycle query parameter
	 *
	 * @param   string  $lifecycle  An array containing the lifecycle configuration
	 *
	 * @return string The XML
	 */
	public function createLifecycleXml($lifecycle)
	{
		$content = "<LifecycleConfiguration>\n";

		foreach ($lifecycle as $rule)
		{
			$content .= "<Rule>\n";

			foreach ($rule as $ruleKey => $ruleValue)
			{
				if (strcmp($ruleKey, "Action") === 0)
				{
					$content .= "<Action><" . $ruleValue . "/></Action>\n";
				}
				else
				{
					$content .= "<" . $ruleKey . ">\n";

					foreach ($ruleValue as $condition => $conditionValue)
					{
						$content .= "<" . $condition . ">" . $conditionValue . "</" . $condition . ">\n";
					}

					$content .= "</" . $ruleKey . ">\n";
				}
			}

			$content .= "</Rule>\n";
		}

		$content .= "</LifecycleConfiguration>";

		return $content;
	}

	/**
	 * Creates the request for setting the lifecycle configuration on an existing bucket
	 *
	 * @param   string  $bucket     The bucket name
	 * @param   string  $lifecycle  An array containing the CORS configuration
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function putBucketLifecycle($bucket, $lifecycle = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?lifecycle";
		$content = "";
		$headers = array(
			"Host" => $bucket . "." . $this->options->get("api.url"),
			"Date" => date("D, d M Y H:i:s O"),
			"x-goog-api-version" => 2,
			"x-goog-project-id" => $this->options->get("project.id"),
		);

		// Check for lifecycle configuration
		if (is_array($lifecycle))
		{
			$headers["Content-Type"] = "application/x-www-form-urlencoded; charset=utf-8";
			$content = $this->createLifecycleXml($lifecycle);
		}

		$headers["Content-Length"] = strlen($content);
		$authorization = $this->getAuthorization(
			$this->options->get("api.oauth.scope.full-control")
		);
		$headers["Authorization"] = $authorization;

		// Send the http request
		$response = $this->client->put($url, $content, $headers);

		// Process the response
		return $this->processResponse($response);
	}

	/**
	 * Creates the XML which will be sent in a put request with the logging query parameter
	 *
	 * @param   string  $logging  An array containing the logging configuration
	 *
	 * @return string The XML
	 */
	public function createLoggingXml($logging)
	{
		$content = "<Logging>\n";

		foreach ($logging as $key => $value)
		{
			$content .= "<" . $key . ">" . $value . "</" . $key . ">\n";
		}

		$content .= "</Logging>";

		return $content;
	}

	/**
	 * Creates the request for enabling logging on an existing bucket
	 *
	 * @param   string  $bucket   The bucket name
	 * @param   string  $logging  An array containing the logging configuration
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function putBucketLogging($bucket, $logging = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?logging";
		$content = "";
		$headers = array(
			"Host" => $bucket . "." . $this->options->get("api.url"),
			"Date" => date("D, d M Y H:i:s O"),
			"x-goog-api-version" => 2,
			"x-goog-project-id" => $this->options->get("project.id"),
		);

		// Check for lifecycle configuration
		if (is_array($logging))
		{
			$headers["Content-Type"] = "application/x-www-form-urlencoded; charset=utf-8";
			$content = $this->createLoggingXml($logging);
		}

		$headers["Content-Length"] = strlen($content);
		$authorization = $this->getAuthorization(
			$this->options->get("api.oauth.scope.full-control")
		);
		$headers["Authorization"] = $authorization;

		// Send the http request
		$response = $this->client->put($url, $content, $headers);

		// Process the response
		return $this->processResponse($response);
	}
}
