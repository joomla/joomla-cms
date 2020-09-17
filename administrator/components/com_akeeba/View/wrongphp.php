<?php
/**
 * Obsolete PHP version notification
 *
 * @copyright Copyright (c) 2018-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

(defined('_JEXEC') || defined('WPINC') || defined('APATH_BASE') || defined('AKEEBA_COMMON_WRONGPHP') || defined('KICKSTART')) or die;

if (!function_exists('akeeba_common_wrongphp'))
{
	/**
	 * This function checks if you are using an obsolete PHP version. It returns and boolean status and optionally
	 * prints an error page if your PHP version is, indeed, too old.
	 *
	 * * minPHPVersion: minimum PHP version supported by this software, e.g. "7.2.0"
	 * * recommendedPHPVersion: recommended PHP version to use with this software, e.g. "7.3"
	 * * softwareName: human-readable software name, e.g. "Akeeba Example"
	 * * silentResutls: suppress error messages on old PHP version, just return false (default: TRUE)
	 * * longVersion: current PHP version, long format, e.g. "7.3.1-12ubuntu3.2". Skip to automatically determine.
	 * * shortVersion: current PHP version, short format, e.g. "7.3". Skip to automatically determine.
	 * * currentTimestamp: current UNIX timestamp. Skip to automatically determine.
	 *
	 * You need to provide at the very least the minPHPVersion, recommendedPHPVersion and softwareName.
	 *
	 * @param  array  $config
	 *
	 * @return bool  FALSE if your PHP version is too old. TRUE if your PHP version is still supported.
	 * @throws Exception
	 */
	function akeeba_common_wrongphp($config = array())
	{
		/**
		 * Format: version => [maintenance_date, eol_date]
		 *
		 * For versions older than 5.6 we use a fake maintenance_date because this information no longer exists on PHP's
		 * site and it's irrelevant anyway; these PHP versions are already EOL therefore we only use their EOL date.
		 */
		$phpDates = array(
			'3.0' => array('1990-01-01 00:00:00', '2000-10-20 00:00:00'),
			'4.0' => array('1990-01-01 00:00:00', '2001-06-23 00:00:00'),
			'4.1' => array('1990-01-01 00:00:00', '2002-03-12 00:00:00'),
			'4.2' => array('1990-01-01 00:00:00', '2002-09-06 00:00:00'),
			'4.3' => array('1990-01-01 00:00:00', '2005-03-31 00:00:00'),
			'4.4' => array('1990-01-01 00:00:00', '2008-08-07 00:00:00'),
			'5.0' => array('1990-01-01 00:00:00', '2005-09-05 00:00:00'),
			'5.1' => array('1990-01-01 00:00:00', '2006-08-24 00:00:00'),
			'5.2' => array('1990-01-01 00:00:00', '2011-01-11 00:00:00'),
			'5.3' => array('1990-01-01 00:00:00', '2014-08-14 00:00:00'),
			'5.4' => array('1990-01-01 00:00:00', '2015-09-03 00:00:00'),
			'5.5' => array('1990-01-01 00:00:00', '2016-07-10 00:00:00'),
			'5.6' => array('2017-01-10 00:00:00', '2018-12-31 00:00:00'),
			'7.0' => array('2018-01-01 00:00:00', '2019-01-10 00:00:00'),
			'7.1' => array('2018-12-01 00:00:00', '2019-12-01 00:00:00'),
			'7.2' => array('2019-11-30 00:00:00', '2020-11-30 00:00:00'),
			'7.3' => array('2020-12-06 00:00:00', '2021-12-06 00:00:00'),
			'7.4' => array('2021-11-28 00:00:00', '2022-11-28 00:00:00'),
		);

		// Make sure I have all necessary configuration variables
		$config = array_merge(array(
			'minPHPVersion'         => '7.2.0',
			'recommendedPHPVersion' => '7.3',
			'softwareName'          => 'This software',
			'silentResults'         => false,
			'longVersion'           => PHP_VERSION,
			'shortVersion'          => sprintf('%d.%d', PHP_MAJOR_VERSION, PHP_MINOR_VERSION),
			'currentTimestamp'      => time(),
		), $config);

		// Selectively extract configuration variables. Do not use extract(), it's potentially dangerous.
		$minPHPVersion         = $config['minPHPVersion'];
		$recommendedPHPVersion = $config['recommendedPHPVersion'];
		$softwareName          = $config['softwareName'];
		$silentResults         = $config['silentResults'];
		$longVersion           = $config['longVersion'];
		$shortVersion          = $config['shortVersion'];
		$currentTimestamp      = $config['currentTimestamp'];

		if (!version_compare($longVersion, $minPHPVersion, 'lt'))
		{
			unset($minPHPVersion, $recommendedPHPVersion, $softwareName, $longVersion, $shortVersion, $phpDates,
				$silentResults, $currentTimestamp);

			return true;
		}

// Typically used in the frontend to not divulge any information about the server
		if ($silentResults)
		{
			return false;
		}

		/**
		 * Safe defaults for PHP versions older than 5.3.0.
		 *
		 * Older PHP versions don't even have support for DateTime so we need these defaults to prevent this warning script from
		 * bringing the site down with an error.
		 */
		$isEol      = true;
		$isAncient  = true;
		$isSecurity = false;
		$isCurrent  = false;

		$eolDateFormatted      = $phpDates[$shortVersion][1];
		$securityDateFormatted = $phpDates[$shortVersion][0];


		/**
		 * This can only work on PHP 5.2.0 or later
		 */
		if (version_compare($longVersion, '5.2.0', 'ge'))
		{
			$tzGmt        = new DateTimeZone('GMT');
			$securityDate = new DateTime($phpDates[$shortVersion][0], $tzGmt);
			$eolDate      = new DateTime($phpDates[$shortVersion][1], $tzGmt);

			/**
			 * Ancient:  This PHP version has reached end-of-life more than 2 years ago
			 * EOL:      This PHP version has reached end-of-life
			 * Security: This PHP version has reached the Security Support date but not the EOL date yet
			 * Current:  This PHP version is still in Active Support
			 */
			$isEol      = $eolDate->getTimestamp() <= $currentTimestamp;
			$isAncient  = $isEol && (($currentTimestamp - $eolDate->getTimestamp()) >= 63072000);
			$isSecurity = !$isEol && ($securityDate->getTimestamp() <= $currentTimestamp);
			$isCurrent  = !$isEol && !$isSecurity;

			$eolDateFormatted      = $eolDate->format('l, d F Y');
			$securityDateFormatted = $securityDate->format('l, d F Y');
		}

		$characterization = $isCurrent ? 'unsupported' : 'older';
		$characterization = $isEol ? 'obsolete' : $characterization;
		$characterization = $isAncient ? 'dangerously obsolete' : $characterization;

		?>

		<div style="margin: 1em">
			<p style="font-size: 180%; margin: 2em 1em; padding: 2em 1em; text-align: center; border: thin solid #f0ad4e; background-color: gold; font-weight: bold; border-radius: 0.25em">
				<?php echo $softwareName ?> requires PHP <?php echo $minPHPVersion ?> or later.
			</p>
			<h2><?php echo ucfirst($characterization) ?> PHP version <?php echo $longVersion ?> detected</h2>
			<hr />
			<p>
				We recommend that you upgrade your site to PHP <?php echo $recommendedPHPVersion ?> or later. If you are
				unsure how to do this, please ask your host.
			</p>
			<p>
				<a href="https://www.akeeba.com/how-do-version-numbers-work.html">Version numbers don't make
					sense?</a>
			</p>

			<hr />

			<?php if ($isAncient): ?>
				<h3>Urgent security advice</h3>

				<p>
					Your version of PHP, <?php echo $longVersion ?>, <a href="http://php.net/eol.php">has reached the end
						of its life</a> a <strong>very</strong> long time ago, namely on
					<?php echo $eolDateFormatted ?>. It has known security vulnerabilities which are used to
					compromise (“hack”) web servers. It is no longer safe using it in production. You are <strong>VERY
						STRONGLY</strong> advised to upgrade your server to a <a
							href="https://www.php.net/supported-versions.php">supported PHP version</a> as soon as possible.
				</p>
			<?php elseif ($isEol): ?>
				<h3>Security advice</h3>

				<p>
					Your version of PHP, <?php echo $longVersion ?>, <a href="http://php.net/eol.php">has reached the end
						of its life</a> on <?php echo $eolDateFormatted ?>. End-of-life PHP versions may have
					as-yet-undiscovered security vulnerabilities which can be used to compromise (“hack”) your site. It is
					no
					longer safe using it in production, even if your host or your Linux distribution claim otherwise – the
					PHP
					developers themselves have said time over time that not all security vulnerabilities fixes can be
					backported
					to End-of-Life versions of PHP since they may require architectural changes in PHP itself. You are
					<strong>strongly</strong> advised to upgrade your server to a <a
							href="https://www.php.net/supported-versions.php">supported PHP version</a> as soon as possible.
				</p>

			<?php elseif ($isSecurity): ?>
				<h3>Security reminder</h3>

				<p>
					Your version of PHP, <?php echo $longVersion ?>, has entered the “Security Support” phase of its life on
					<?php echo $securityDateFormatted ?>. As such, only security issues will be addressed but not
					any of its known functional issues (“bugs”). Unfixed functional issues in PHP can lead to your site not
					working
					properly. It is advisable to plan migrating your site to a
					<a href="https://www.php.net/supported-versions.php">supported PHP version</a> no later than
					<?php echo $eolDateFormatted ?> – that's when PHP
					<?php echo $shortVersion ?> will become End-of-Life, therefore
					completely
					unsuitable for use on a live server.
				</p>
			<?php endif; ?>

			<?php if ($isSecurity || $isCurrent): ?>
				<h3>Why is my PHP version not supported?</h3>

				<p>
					Even though PHP <?php echo $shortVersion ?> will be supported by the PHP
					project until <?php echo $eolDateFormatted ?> we are unfortunately unable to provide
					support for it in our software. This has to do either with missing features or third party libraries.
					Older
					PHP versions are missing features we require for our software to work efficiently and be written in a
					way
					that makes it possible for us to provide a plethora of relevant features while maintaining good quality
					control. Moreover, third party libraries we use to provide some of the software's features do not
					support
					older PHP versions for the same reason – so even if we don't absolutely need to use at least PHP
					<?php echo $minPHPVersion ?> the third party libraries do, making it impossible for our software to run on
					your
					older version <?php echo $shortVersion ?>. We apologize for the inconvenience.
				</p>
				<p>
					We'd like to remind you, however, that newer PHP versions are always faster and more well-tested than
					their
					predecessors. Upgrading your site to a newer PHP version will not only let our software run but will
					also
					make your site faster, more stable and help it perform better in search engine results.
				</p>
			<?php endif; ?>
		</div>

		<?php return false;
	}
}

/**
 * Immediately executes the akeeba_common_wrongphp() function on all of our software except Kickstart.
 */
if (!defined('KICKSTART'))
{
	try
	{
		return akeeba_common_wrongphp(array(
			// Configuration -- Override before calling this script
			'minPHPVersion'         => isset($minPHPVersion) ? $minPHPVersion : '7.2.0',
			'recommendedPHPVersion' => isset($recommendedPHPVersion) ? $recommendedPHPVersion : '7.3',
			'softwareName'          => isset($softwareName) ? $softwareName : 'This software',
			'silentResults'         => isset($silentResults) ? $silentResults : false,
			// Override these to test the script
			'longVersion'           => isset($longVersion) ? $longVersion : PHP_VERSION,
			'shortVersion'          => isset($shortVersion) ? $shortVersion : sprintf('%d.%d', PHP_MAJOR_VERSION, PHP_MINOR_VERSION),
			'currentTimestamp'      => isset($currentTimestamp) ? $currentTimestamp : time(),
		));
	}
	catch (Exception $e)
	{
		// This should never happen
		return false;
	}
}