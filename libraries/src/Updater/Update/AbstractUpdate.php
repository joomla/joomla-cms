<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Updater\Update;

use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Updater\DownloadSource;
use Joomla\CMS\Updater\Updater;

\defined('JPATH_PLATFORM') or die;

abstract class AbstractUpdate extends CMSObject implements UpdateInterface
{
    /**
     * Update manifest `<name>` element
     *
     * @var    string
     * @since  1.7.0
     */
    public $name;

    /**
     * Update manifest `<description>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $description;

    /**
     * Update manifest `<element>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $element;

    /**
     * Update manifest `<type>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type;

    /**
     * Update manifest `<version>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $version;

    /**
     * Update manifest `<infourl>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $infourl;

    /**
     * Update manifest `<client>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $client;

    /**
     * Update manifest `<group>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $group;

    /**
     * Update manifest `<downloads>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $downloads;

    /**
     * Update manifest `<downloadsource>` elements
     *
     * @var    DownloadSource[]
     * @since  3.8.3
     */
    protected $downloadSources = array();

    /**
     * Update manifest `<tags>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $tags;

    /**
     * Update manifest `<maintainer>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $maintainer;

    /**
     * Update manifest `<maintainerurl>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $maintainerurl;

    /**
     * Update manifest `<category>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $category;

    /**
     * Update manifest `<relationships>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $relationships;

    /**
     * Update manifest `<targetplatform>` element
     *
     * @var    string
     * @since  1.7.0
     */
    protected $targetplatform;

    /**
     * Extra query for download URLs
     *
     * @var    string
     * @since  3.2.0
     */
    protected $extra_query;

    /**
     * Object containing the current update data
     *
     * @var    \stdClass
     * @since  3.0.0
     */
    protected $currentUpdate;

    /**
     * Object containing the latest update data
     *
     * @var    \stdClass
     * @since  3.0.0
     */
    protected $latest;

    /**
     * The minimum stability required for updates to be taken into account. The possible values are:
     * 0    dev            Development snapshots, nightly builds, pre-release versions and so on
     * 1    alpha        Alpha versions (work in progress, things are likely to be broken)
     * 2    beta        Beta versions (major functionality in place, show-stopper bugs are likely to be present)
     * 3    rc            Release Candidate versions (almost stable, minor bugs might be present)
     * 4    stable        Stable versions (production quality code)
     *
     * @var    integer
     * @since  14.1
     *
     * @see    Updater
     */
    protected $minimum_stability = Updater::STABILITY_STABLE;

    /**
     * Array with compatible versions used by the pre-update check
     *
     * @var    array
     * @since  3.10.2
     */
    protected $compatibleVersions = array();
}
