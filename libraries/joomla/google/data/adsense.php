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
	 * Method to get an Adsense account's settings from Google
	 *
	 * @param   string  $accountID    ID of account to get.
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
			$jdata = $this->auth->query($url, null, null, 'get');
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
	 * Method to retrieve a list of AdSense clients from Google
	 *
	 * @param   array  $options   Search settings
	 * @param   int    $maxpages  Minimum number of events to retrieve (more may be retrieved depending on page size)
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function listCalendars($options = array(), $maxpages = 1)
	{
		if ($this->authenticated())
		{
			$url = 'https://www.googleapis.com/adsense/v1.1/accounts?' . explode($options, '&');
			$jdata = $this->auth->query($url, null, null, 'get');
			if ($jdata->body = json_decode($jdata->body, true) && array_key_exists('items', $data))
			{
				if ($maxpages != 1 && array_key_exists('nextPageToken', $data))
				{
					$data['items'] = array_merge($data['items'], $this->listEvents($options, $maxpages - 1));
				}
				return $data['items'];
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
}
