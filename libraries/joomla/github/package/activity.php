<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API Activity class for the Joomla Platform.
 *
 * @since  3.3 (CMS)
 *
 * @documentation  https://developer.github.com/v3/activity/
 *
 * @property-read  JGithubPackageActivityEvents  $events  GitHub API object for markdown.
 */
class JGithubPackageActivity extends JGithubPackage
{
	protected $name = 'Activity';

	protected $packages = array(
		'events', 'notifications', 'starring', 'watching'
	);
}
