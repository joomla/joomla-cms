<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Updater
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.base.adapterinstance');

/**
 * UpdateAdapter class.
 *
 * @since  11.1
 */
abstract class JUpdateAdapter extends JAdapterInstance
{
	/**
	 * Resource handle for the XML Parser
	 *
	 * @var    resource
	 * @since  12.1
	 */
	protected $xmlParser;

	/**
	 * Element call stack
	 *
	 * @var    array
	 * @since  12.1
	 */
	protected $stack = array('base');

	/**
	 * ID of update site
	 *
	 * @var    string
	 * @since  12.1
	 */
	protected $updateSiteId = 0;

	/**
	 * Columns in the extensions table to be updated
	 *
	 * @var    array
	 * @since  12.1
	 */
	protected $updatecols = array('NAME', 'ELEMENT', 'TYPE', 'FOLDER', 'CLIENT', 'VERSION', 'DESCRIPTION', 'INFOURL');

	/**
	 * The minimum stability required for updates to be taken into account. The possible values are:
	 * 0	dev			Development snapshots, nightly builds, pre-release versions and so on
	 * 1	alpha		Alpha versions (work in progress, things are likely to be broken)
	 * 2	beta		Beta versions (major functionality in place, show-stopper bugs are likely to be present)
	 * 3	rc			Release Candidate versions (almost stable, minor bugs might be present)
	 * 4	stable		Stable versions (production quality code)
	 *
	 * @var    int
	 * @since  14.1
	 *
	 * @see    JUpdater
	 */
	protected $minimum_stability = JUpdater::STABILITY_STABLE;

	/**
	 * Gets the reference to the current direct parent
	 *
	 * @return  object
	 *
	 * @since   11.1
	 */
	protected function _getStackLocation()
	{
		return implode('->', $this->stack);
	}

	/**
	 * Gets the reference to the last tag
	 *
	 * @return  object
	 *
	 * @since   11.1
	 */
	protected function _getLastTag()
	{
		return $this->stack[count($this->stack) - 1];
	}

	/**
	 * Finds an update
	 *
	 * @param   array  $options  Options to use: update_site_id: the unique ID of the update site to look at
	 *
	 * @return  array  Update_sites and updates discovered
	 *
	 * @since   11.1
	 */
	abstract public function findUpdate($options);
}
