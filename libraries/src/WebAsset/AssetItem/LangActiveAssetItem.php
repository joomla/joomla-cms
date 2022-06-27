<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset\AssetItem;

use Joomla\CMS\Factory;
use Joomla\CMS\WebAsset\WebAssetItem;

/**
 * Web Asset Item class for load asset file for active language.
 * Used in core templates.
 *
 * @since  4.0.0
 */
class LangActiveAssetItem extends WebAssetItem
{
    /**
     * Class constructor
     *
     * @param   string  $name          The asset name
     * @param   string  $uri           The URI for the asset
     * @param   array   $options       Additional options for the asset
     * @param   array   $attributes    Attributes for the asset
     * @param   array   $dependencies  Asset dependencies
     *
     * @since   4.0.0
     */
    public function __construct(
        string $name,
        string $uri = null,
        array $options = [],
        array $attributes = [],
        array $dependencies = []
    ) {
        parent::__construct($name, $uri, $options, $attributes, $dependencies);

        // Prepare Uri depend from the active language
        $langTag = Factory::getApplication()->getLanguage()->getTag();
        $client  = $this->getOption('client');

        // Create Uri <client>/language/<langTag>/<langTag>.css
        if ($client) {
            $this->uri = $client . '/language/' . $langTag . '/' . $langTag . '.css';
        } else {
            $this->uri = 'language/' . $langTag . '/' . $langTag . '.css';
        }
    }
}
