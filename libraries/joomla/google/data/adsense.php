<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Google
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Google Adsense data class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Google
 * @since       1234
 */
class JGoogleDataAdsense extends JGoogleData
{
	/**
	 * Constructor.
	 *
	 * @param   JRegistry    $options  Google options object
	 * @param   JGoogleAuth  $auth     Google data http client object
	 *
	 * @since   1234
	 */
	public function __construct(JRegistry $options = null, JGoogleAuth $auth = null)
	{
		$options = isset($options) ? $options : new JRegistry;
		if (!$options->get('scope'))
		{
			$options->set('scope', 'https://www.googleapis.com/auth/adsense');
		}
		if (isset($auth) && !$auth->getOption('scope'))
		{
			$auth->setOption('scope', 'https://www.googleapis.com/auth/adsense');
		}

		parent::__construct($options, $auth);
	}

	/**
	 * Method to retrieve a list of AdSense data
	 *
	 * @param   array   $url       URL to GET
	 * @param   int     $maxpages  Maximum number of pages of accounts to return
	 * @param   string  $token     Next page token
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	protected function listGetData($url, $maxpages = 1, $token = null)
	{
		if (strpos($url, '&'))
		{
			$qurl .= '&pageToken=' . $token;
		}
		else
		{
			$qurl .= 'pageToken=' . $token;
		}
		$jdata = $this->auth->query($qurl);
		if ($data = json_decode($jdata->body, true) && array_key_exists('items', $data))
		{
			if ($maxpages != 1 && array_key_exists('nextPageToken', $data))
			{
				$data['items'] = array_merge($data['items'], $this->listGetData($url, $maxpages - 1, $data['nextPageToken']));
			}
			return $data['items'];
		}
		else
		{
			throw new UnexpectedValueException("Unexpected data received from Google: `{$jdata->body}`.");
		}
	}

	/**
	 * Method to get an Adsense account's settings from Google
	 *
	 * @param   string  $accountID    ID of account to get
	 * @param   bool    $subaccounts  Include list of subaccounts
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   1234
	 */
	public function getAccount($accountID, $subaccounts = true)
	{
		if ($this->authenticated())
		{
			$url = 'https://www.googleapis.com/adsense/v1.1/accounts/' . $accountID . $subaccounts ? '?tree=true' : '';
			$jdata = $this->auth->query($url);
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
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function listAccounts($options = array(), $maxpages = 1)
	{
		if ($this->authenticated())
		{
			$next = array_key_exists('nextPageToken', $options) ? $options['nextPage'] : null;
			unset($options['nextPageToken']);
			$url = 'https://www.googleapis.com/adsense/v1.1/accounts?' . explode($options, '&');
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
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function listClients($accountID, $options = array(), $maxpages = 1)
	{
		if ($this->authenticated())
		{
			$next = array_key_exists('nextPageToken', $options) ? $options['nextPage'] : null;
			unset($options['nextPageToken']);
			$url = 'https://www.googleapis.com/adsense/v1.1/accounts/' . $accountID . '/adclients?' . explode($options, '&');
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
	 * @since   1234
	 */
	public function getUnit($accountID, $adclientID, $adunitID)
	{
		if ($this->authenticated())
		{
			$url = 'https://www.googleapis.com/adsense/v1.1/accounts/' . $accountID . '/adclients/' . $adclientID . '/adunits/' . $adunitID;
			$jdata = $this->auth->query($url);
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
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function listUnitChannels($accountID, $adclientID, $adunitID, $options = array(), $maxpages = 1)
	{
		if ($this->authenticated())
		{
			$next = array_key_exists('nextPageToken', $options) ? $options['nextPage'] : null;
			unset($options['nextPageToken']);
			$url = 'https://www.googleapis.com/adsense/v1.1/accounts/' . $accountID;
			$url .= '/adclients/' . $adclientID . '/adunits/' . $adunitID . '/customchannels?' . explode($options, '&');
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
	 * @since   1234
	 */
	public function getChannel($accountID, $adclientID, $channelID)
	{
		if ($this->authenticated())
		{
			$url = 'https://www.googleapis.com/adsense/v1.1/accounts/' . $accountID . '/adclients/' . $adclientID . '/customchannels/' . $channelID;
			$jdata = $this->auth->query($url);
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
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function listChannels($accountID, $adclientID, $options = array(), $maxpages = 1)
	{
		if ($this->authenticated())
		{
			$next = array_key_exists('nextPageToken', $options) ? $options['nextPage'] : null;
			unset($options['nextPageToken']);
			$url = 'https://www.googleapis.com/adsense/v1.1/accounts/' . $accountID . '/adclients/' . $adclientID;
			$url .= '/customchannels?' . explode($options, '&');
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
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function listChannelUnits($accountID, $adclientID, $channelID, $options = array(), $maxpages = 1)
	{
		if ($this->authenticated())
		{
			$next = array_key_exists('nextPageToken', $options) ? $options['nextPage'] : null;
			unset($options['nextPageToken']);
			$url = 'https://www.googleapis.com/adsense/v1.1/accounts/' . $accountID . '/adclients/' . $adclientID;
			$url .= '/customchannels/' . $channelID . '/adunits?' . explode($options, '&');
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
	 * @param   string  $accountID  ID of account
	 * @param   array   $options    Search settings
	 * @param   int     $maxpages   Maximum number of pages of accounts to return
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function listUrlChannels($accountID, $options = array(), $maxpages = 1)
	{
		if ($this->authenticated())
		{
			$next = array_key_exists('nextPageToken', $options) ? $options['nextPage'] : null;
			unset($options['nextPageToken']);
			$url = 'https://www.googleapis.com/adsense/v1.1/accounts/' . $accountID . '/adclients/' . $adclientID . '/urlchannels?' . explode($options, '&');
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
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function generateReport($accountID, $start, $end, $options = array(), $maxpages = 1)
	{
		if ($this->authenticated())
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

			if (is_int($end))
			{
				$endobj = new DateTime;
				$endobj->setTimestamp($end);
			}
			elseif (is_string($start))
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

			$begin = array_key_exists('startIndex', $options) ? $options['startIndex'] : 0;
			unset($options['startIndex']);

			$url = 'https://www.googleapis.com/adsense/v1.1/accounts/' . $accountID . '/reports?' . explode($options, '&');
			if (strpos($url, '&'))
			{
				$url .= '&';
			}

			$i = 0;
			$data['rows'] = array();
			do
			{
				$jdata = $this->auth->query($url . 'startIndex=' . count($data['rows']));
				if ($newdata = json_decode($jdata->body, true) && array_key_exists('items', $newdata))
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
