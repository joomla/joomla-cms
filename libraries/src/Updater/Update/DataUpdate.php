<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Updater\Update;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Updater\DownloadSource;
use Joomla\CMS\Updater\Updater;
use Joomla\CMS\Version;
use Joomla\Registry\Registry;

/**
 * Update class. It is used by Updater::update() to install an update. Use Updater::findUpdates() to find updates for
 * an extension.
 *
 * @since  1.7.0
 */
class DataUpdate extends AbstractUpdate
{
    /**
     * Object for holding for data
     *
     * @var    resource
     * @since  3.0.0
     */
    protected $data;

    /**
     * Loads an XML file from a URL.
     *
     * @param mixed $updateObject The object of the update containing all information.
     * @param int $minimumStability The minimum stability required for updating the extension {@see Updater}
     *
     * @return  boolean  True on success
     *
     * @since   1.7.0
     */
    public function loadFromData($updateObject, $minimumStability = Updater::STABILITY_STABLE)
    {
        foreach ($updateObject as $key => $data) {
            $this->$key = $updateObject->$key;
        }

        $dataJson = json_decode($updateObject->data);
        $this->targetplatform = $dataJson->targetplatform;

        foreach ($dataJson->downloads as $download) {
            $source = new DownloadSource;
            foreach ($download as $key => $data) {
                $key = strtolower($key);
                $source->$key = $data;
            }
            $this->downloadSources[] = $source;
        }

        foreach ($dataJson->hashes as $hashAlgorithm => $hashSum) {
            $this->$hashAlgorithm = (object) ["_data" => $hashSum];
        }

        $this->currentUpdate = new \stdClass();
        $this->downloadurl = new \stdClass();
        $this->downloadurl->_data = $this->downloadSources[0]->url;
        $this->downloadurl->format = $this->downloadSources[0]->format;
        $this->downloadurl->type = $this->downloadSources[0]->type;

        return true;
    }

    /**
     * Converts a tag to numeric stability representation. If the tag doesn't represent a known stability level (one of
     * dev, alpha, beta, rc, stable) it is ignored.
     *
     * @param string $tag The tag string, e.g. dev, alpha, beta, rc, stable
     *
     * @return  integer
     *
     * @since   3.4
     */
    protected function stabilityTagToInteger($tag)
    {
        $constant = '\\Joomla\\CMS\\Updater\\Updater::STABILITY_' . strtoupper($tag);

        if (\defined($constant)) {
            return \constant($constant);
        }

        return Updater::STABILITY_STABLE;
    }
}
