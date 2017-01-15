<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Quickicon.phpversioncheck
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Plugin to check the PHP version and display a warning about its support status
 *
 * @since  3.7.0
 */
class PlgQuickiconPhpVersionCheck extends JPlugin
{
	/**
	 * Constant representing the active PHP version being fully supported
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	const PHP_SUPPORTED = 0;

	/**
	 * Constant representing the active PHP version receiving security support only
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	const PHP_SECURITY_ONLY = 1;

	/**
	 * Constant representing the active PHP version being unsupported
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	const PHP_UNSUPPORTED = 2;

	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  3.7.0
	 */
	protected $app;

	/**
	 * Load plugin language files automatically
	 *
	 * @var    boolean
	 * @since  3.7.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Check the PHP version after the admin component has been dispatched.
	 *
	 * @param   string  $context  The calling context
	 *
	 * @return  array
	 *
	 * @since   3.7.0
	 */
	public function onGetIcons($context)
	{
		if (!$this->shouldDisplayMessage())
		{
			return [];
		}

		$supportStatus = $this->getPhpSupport();

		if ($supportStatus['status'] !== self::PHP_SUPPORTED)
		{
			// Enqueue the notification message; set a warning if receiving security support or "error" if unsupported
			switch ($supportStatus['status'])
			{
				case self::PHP_SECURITY_ONLY:
					$this->app->enqueueMessage($supportStatus['message'], 'warning');

					break;

				case self::PHP_UNSUPPORTED:
					$this->app->enqueueMessage($supportStatus['message'], 'error');

					break;
			}
		}

		return [];
	}

	/**
	 * Gets PHP support status.
	 *
	 * @return  array  Array of PHP support data
	 *
	 * @since   3.7.0
	 * @note    The dates used in this method should correspond to the dates given on PHP.net
	 * @see     https://secure.php.net/supported-versions.php
	 * @see     https://secure.php.net/eol.php
	 */
	private function getPhpSupport()
	{
		$phpSupportData = array(
			'5.5' => array(
				'security' => '2015-07-10',
				'eos'      => '2016-07-21'
			),
			'5.6' => array(
				'security' => '2016-12-31',
				'eos'      => '2018-12-31'
			),
			'7.0' => array(
				'security' => '2017-12-03',
				'eos'      => '2018-12-03'
			),
			'7.1' => array(
				'security' => '2018-12-01',
				'eos'      => '2019-12-01'
			),
		);

		// Fill our return array with default values
		$supportStatus = array(
			'status'  => self::PHP_SUPPORTED,
			'message' => null,
		);

		// Check the PHP version's support status using the minor version
		$activePhpVersion = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;

		// Do we have the PHP version's data?
		if (isset($phpSupportData[$activePhpVersion]))
		{
			// First check if the version has reached end of support
			$today           = new JDate;
			$phpEndOfSupport = new JDate($phpSupportData[$activePhpVersion]['eos']);

			if ($phpNotSupported = $today > $phpEndOfSupport)
			{
				/*
				 * Find the oldest PHP version still supported that is newer than the current version,
				 * this is our recommendation for users on unsupported platforms
				 */
				foreach ($phpSupportData as $version => $versionData)
				{
					$versionEndOfSupport = new JDate($versionData['eos']);

					if (version_compare($version, $activePhpVersion, 'ge') && ($today < $versionEndOfSupport))
					{
						$recommendedVersion             = $version;
						$recommendedVersionEndOfSupport = $versionEndOfSupport;

						break;
					}
				}

				$supportStatus['status']  = self::PHP_UNSUPPORTED;
				$supportStatus['message'] = JText::sprintf(
					'PLG_QUICKICON_PHPVERSIONCHECK_UNSUPPORTED',
					PHP_VERSION,
					$recommendedVersion,
					$recommendedVersionEndOfSupport->format(JText::_('DATE_FORMAT_LC4'))
				);
			}

			// If the version is still supported, check if it has reached security support only
			$phpSecurityOnlyDate = new JDate($phpSupportData[$activePhpVersion]['security']);

			if (!$phpNotSupported && $today > $phpSecurityOnlyDate)
			{
				$supportStatus['status']  = self::PHP_SECURITY_ONLY;
				$supportStatus['message'] = JText::sprintf(
					'PLG_QUICKICON_PHPVERSIONCHECK_SECURITY_ONLY', PHP_VERSION, $phpEndOfSupport->format(JText::_('DATE_FORMAT_LC4'))
				);
			}
		}

		return $supportStatus;
	}

	/**
	 * Determines if the message should be displayed
	 *
	 * @return  boolean
	 *
	 * @since   3.7.0
	 */
	private function shouldDisplayMessage()
	{
		// Only on admin app
		if (!$this->app->isClient('administrator'))
		{
			return false;
		}

		// Only if authenticated
		if (JFactory::getUser()->guest)
		{
			return false;
		}

		// Only on HTML documents
		if ($this->app->getDocument()->getType() !== 'html')
		{
			return false;
		}

		// Only on full page requests
		if ($this->app->input->getCmd('tmpl', 'index') === 'component')
		{
			return false;
		}

		// Only to com_cpanel
		if ($this->app->input->get('option') !== 'com_cpanel')
		{
			return false;
		}

		return true;
	}
}
