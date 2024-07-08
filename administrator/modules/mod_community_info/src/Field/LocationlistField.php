<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_community_info
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\CommunityInfo\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * List field for country based location
 *
 * @since  4.5.0
 */
class LocationlistField extends ListField
{
    /**
     * A dropdown field with all activated imagetypes
     *
     * @var    string
     * @since  1.0
     */
    public $type = 'locationlist';

    /**
     * Method to get a list of countries with option values based on
     * geolocation = latitude,longitude
     *
     * @return  array  The field option objects.
     *
     * @since   1.0
     */
    protected function getOptions()
    {
        // Prepare the empty array
        $options = [];

        // Get the list of countries
        require_once JPATH_ADMINISTRATOR . '/modules/mod_community_info/includes/country_list.php';

        // Default option
        $options[] = HTMLHelper::_('select.option', 'en-GB', Text::_('JDEFAULT') . ' (United Kingdom)');

        foreach ($countryListArray as $country) {
            $options[] = HTMLHelper::_('select.option', $country['value'], $country['label']);
        }

        return $options;
    }
}
