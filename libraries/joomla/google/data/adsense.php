<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Google
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Google Adsense data class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Google
 * @since       12.3
 */
class JGoogleDataAdsense extends JGoogleData
{
	/**
	 * Constructor.
	 *
	 * @param   JRegistry    $options  Google options object
	 * @param   JGoogleAuth  $auth     Google data http client object
	 *
	 * @since   12.3
	 */
	public function __construct(JRegistry $options = null, JGoogleAuth $auth = null)
	{
		parent::__construct($options, $auth);

		if (isset($this->auth) && !$this->auth->getOption('scope'))
		{
			$this->auth->setOption('scope', 'https://www.googleapis.com/auth/adsense');
		}
	}

	/**
	 * Method to get an Adsense account's settings from Google
	 *
	 * @param   string   $accountID    ID of account to get
	 * @param   boolean  $subaccounts  Include list of subaccounts
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   12.3
	 */
	public function getAccount($accountID, $subaccounts = true)
	{
		if ($this->isAuthenticated())
		{
			$url = 'https://www.googleapis.com/adsense/v1.1/accounts/' . urlencode($accountID) . ($subaccounts ? '?tree=true' : '');
			$jdata = $this->query($url);

			if ($data = json_decode($jdata->body, true))
			{
				return $data;
			}
			else
			{
				throw new UnexpectedValueException("Unexpected data received from Google: `{$jdata->body}`.");
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to retrieve a list of AdSense accounts from Google
	 *
	 * @param   array  $options   Search settings
	 * @param   int    $maxpages  Maximum number of pages of accounts to return
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
	 */
	public function listAccounts($options = array(), $maxpages = 1)
	{
		if ($this->isAuthenticated())
		{
			$next = array_key_exists('nextPageToken', $options) ? $options['nextPage'] : null;
			unset($options['nextPageToken']);
			$url = 'https://www.googleapis.com/adsense/v1.1/accounts?' . http_build_query($options);

			return $this->listGetData($url, $maxpages, $next);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to retrieve a list of AdSense clients from Google
	 *
	 * @param   string  $accountID  ID of account to list the clients from
	 * @param   array   $options    Search settings
	 * @param   int     $maxpages   Maximum number of pages of accounts to return
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
	 */
	public function listClients($accountID, $options = array(), $maxpages = 1)
	{
		if ($this->isAuthenticated())
		{
			$next = array_key_exists('nextPageToken', $options) ? $options['nextPage'] : null;
			unset($options['nextPageToken']);
			$url = 'https://www.googleapis.com/adsense/v1.1/accounts/' . urlencode($accountID) . '/adclients?' . http_build_query($options);

			return $this->listGetData($url, $maxpages, $next);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to get an AdSense AdUnit
	 *
	 * @param   string  $accountID   ID of account to get
	 * @param   string  $adclientID  ID of client to get
	 * @param   string  $adunitID    ID of adunit to get
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   12.3
	 */
	public function getUnit($accountID, $adclientID, $adunitID)
	{
		if ($this->isAuthenticated())
		{
			$url = 'https://www.googleapis.com/adsense/v1.1/accounts/' . urlencode($accountID);
			$url .= '/adclients/' . urlencode($adclientID) . '/adunits/' . urlencode($adunitID);
			$jdata = $this->query($url);

			if ($data = json_decode($jdata->body, true))
			{
				return $data;
			}
			else
			{
				throw new UnexpectedValueException("Unexpected data received from Google: `{$jdata->body}`.");
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to retrieve a list of AdSense Custom Channels for a specific Adunit
	 *
	 * @param   string  $accountID   ID of account
	 * @param   string  $adclientID  ID of client
	 * @param   string  $adunitID    ID of adunit to list channels from
	 * @param   array   $options     Search settings
	 * @param   int     $maxpages    Maximum number of pages of accounts to return
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
	 */
	public function listUnitChannels($accountID, $adclientID, $adunitID, $options = array(), $maxpages = 1)
	{
		if ($this->isAuthenticated())
		{
			$next = array_key_exists('nextPageToken', $options) ? $options['nextPage'] : null;
			unset($options['nextPageToken']);
			$url = 'https://www.googleapis.com/adsense/v1.1/accounts/' . urlencode($accountID);
			$url .= '/adclients/' . urlencode($adclientID) . '/adunits/' . urlencode($adunitID) . '/customchannels?' . http_build_query($options);

			return $this->listGetData($url, $maxpages, $next);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to get an Adsense Channel
	 *
	 * @param   string  $accountID   ID of account to get
	 * @param   string  $adclientID  ID of client to get
	 * @param   string  $channelID   ID of channel to get
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   12.3
	 */
	public function getChannel($accountID, $adclientID, $channelID)
	{
		if ($this->isAuthenticated())
		{
			$url = 'https://www.googleapis.com/adsense/v1.1/accounts/' . urlencode($accountID) . '/adclients/';
			$url .= urlencode($adclientID) . '/customchannels/' . urlencode($channelID);
			$jdata = $this->query($url);

			if ($data = json_decode($jdata->body, true))
			{
				return $data;
			}
			else
			{
				throw new UnexpectedValueException("Unexpected data received from Google: `{$jdata->body}`.");
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to retrieve a list of AdSense Custom Channels
	 *
	 * @param   string  $accountID   ID of account
	 * @param   string  $adclientID  ID of client to list channels from
	 * @param   array   $options     Search settings
	 * @param   int     $maxpages    Maximum number of pages of accounts to return
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
	 */
	public function listChannels($accountID, $adclientID, $options = array(), $maxpages = 1)
	{
		if ($this->isAuthenticated())
		{
			$next = array_key_exists('nextPageToken', $options) ? $options['nextPage'] : null;
			unset($options['nextPageToken']);
			$url = 'https://www.googleapis.com/adsense/v1.1/accounts/' . urlencode($accountID) . '/adclients/' . urlencode($adclientID);
			$url .= '/customchannels?' . http_build_query($options);

			return $this->listGetData($url, $maxpages, $next);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to retrieve a list of AdSense Adunits for a specific Custom Channel
	 *
	 * @param   string  $accountID   ID of account
	 * @param   string  $adclientID  ID of client
	 * @param   string  $channelID   ID of channel to list units from
	 * @param   array   $options     Search settings
	 * @param   int     $maxpages    Maximum number of pages of accounts to return
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
	 */
	public function listChannelUnits($accountID, $adclientID, $channelID, $options = array(), $maxpages = 1)
	{
		if ($this->isAuthenticated())
		{
			$next = array_key_exists('nextPageToken', $options) ? $options['nextPage'] : null;
			unset($options['nextPageToken']);
			$url = 'https://www.googleapis.com/adsense/v1.1/accounts/' . urlencode($accountID) . '/adclients/' . urlencode($adclientID);
			$url .= '/customchannels/' . urlencode($channelID) . '/adunits?' . http_build_query($options);

			return $this->listGetData($url, $maxpages, $next);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to generate a report from Google AdSense
	 *
	 * @param   string  $accountID   ID of account
	 * @param   string  $adclientID  ID of client
	 * @param   array   $options     Search settings
	 * @param   int     $maxpages    Maximum number of pages of accounts to return
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
	 */
	public function listUrlChannels($accountID, $adclientID, $options = array(), $maxpages = 1)
	{
		if ($this->isAuthenticated())
		{
			$next = array_key_exists('nextPageToken', $options) ? $options['nextPage'] : null;
			unset($options['nextPageToken']);
			$url = 'https://www.googleapis.com/adsense/v1.1/accounts/' . urlencode($accountID);
			$url .= '/adclients/' . urlencode($adclientID) . '/urlchannels?' . http_build_query($options);

			return $this->listGetData($url, $maxpages, $next);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to retrieve a list of AdSense Channel URLs
	 *
	 * @param   string  $accountID  ID of account
	 * @param   mixed   $start      Start day
	 * @param   mixed   $end        End day
	 * @param   array   $options    Search settings
	 * @param   int     $maxpages   Maximum number of pages of accounts to return
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
	 */
	public function generateReport($accountID, $start, $end = false, $options = array(), $maxpages = 1)
	{
		if ($this->isAuthenticated())
		{
			if (is_int($start))
			{
				$startobj = new DateTime;
				$startobj->setTimestamp($start);
			}
			elseif (is_string($start))
			{
				$startobj = new DateTime($start);
			}
			elseif (is_a($start, 'DateTime'))
			{
				$startobj = $start;
			}
			else
			{
				throw new InvalidArgumentException('Invalid start time.');
			}

			if (!$end)
			{
				$endobj = new DateTime;
			}
			elseif (is_int($end))
			{
				$endobj = new DateTime;
				$endobj->setTimestamp($end);
			}
			elseif (is_string($end))
			{
				$endobj = new DateTime($end);
			}
			elseif (is_a($end, 'DateTime'))
			{
				$endobj = $end;
			}
			else
			{
				throw new InvalidArgumentException('Invalid end time.');
			}

			$options['startDate'] = $startobj->format('Y-m-d');
			$options['endDate'] = $endobj->format('Y-m-d');

			unset($options['startIndex']);

			$url = 'https://www.googleapis.com/adsense/v1.1/accounts/' . urlencode($accountID) . '/reports?' . http_build_query($options);

			if (strpos($url, '&'))
			{
				$url .= '&';
			}

			$i = 0;
			$data['rows'] = array();

			do
			{
				$jdata = $this->query($url . 'startIndex=' . count($data['rows']));
				$newdata = json_decode($jdata->body, true);

				if ($newdata && array_key_exists('rows', $newdata))
				{
					$newdata['rows'] = array_merge($data['rows'], $newdata['rows']);
					$data = $newdata;
				}
				else
				{
					throw new UnexpectedValueException("Unexpected data received from Google: `{$jdata->body}`.");
				}

				$i++;
			}
			while (count($data['rows']) < $data['totalMatchedRows'] && $i < $maxpages);

			return $data;
		}
		else
		{
			return false;
		}
	}
}
