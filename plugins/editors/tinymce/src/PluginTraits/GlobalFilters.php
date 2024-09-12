<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Editors\TinyMCE\PluginTraits;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\User\User;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Handles the Joomla filters for the TinyMCE editor.
 *
 * @since  4.1.0
 */
trait GlobalFilters
{
    /**
     * Get the global text filters to arbitrary text as per settings for current user groups
     * @param   User  $user  The user object
     *
     * @return  InputFilter
     *
     * @since   4.1.0
     */
    protected static function getGlobalFilters($user)
    {
        // Filter settings
        $config     = ComponentHelper::getParams('com_config');
        $userGroups = Access::getGroupsByUser($user->id);
        $filters    = $config->get('filters');

        $forbiddenListTags       = [];
        $forbiddenListAttributes = [];
        $customListTags          = [];
        $customListAttributes    = [];
        $allowedListTags         = [];
        $allowedListAttributes   = [];

        $allowedList   = false;
        $forbiddenList = false;
        $customList    = false;
        $unfiltered    = false;

        /**
         * Cycle through each of the user groups the user is in.
         * Remember they are included in the public group as well.
         */
        foreach ($userGroups as $groupId) {
            // May have added a group but not saved the filters.
            if (!isset($filters->$groupId)) {
                continue;
            }

            // Each group the user is in could have different filtering properties.
            $filterData = $filters->$groupId;
            $filterType = strtoupper($filterData->filter_type);

            if ($filterType === 'NH') {
                // Maximum HTML filtering.
            } elseif ($filterType === 'NONE') {
                // No HTML filtering.
                $unfiltered = true;
            } else {
                /**
                 * Forbidden or allowed lists.
                 * Preprocess the tags and attributes.
                 */
                $tags           = explode(',', $filterData->filter_tags);
                $attributes     = explode(',', $filterData->filter_attributes);
                $tempTags       = [];
                $tempAttributes = [];

                foreach ($tags as $tag) {
                    $tag = trim($tag);

                    if ($tag) {
                        $tempTags[] = $tag;
                    }
                }

                foreach ($attributes as $attribute) {
                    $attribute = trim($attribute);

                    if ($attribute) {
                        $tempAttributes[] = $attribute;
                    }
                }

                /**
                 * Collect the list of forbidden or allowed tags and attributes.
                 * Each list is cumulative.
                 * "BL" is deprecated in Joomla! 4, will be removed in Joomla! 5
                 */
                if (\in_array($filterType, ['BL', 'FL'])) {
                    $forbiddenList           = true;
                    $forbiddenListTags       = array_merge($forbiddenListTags, $tempTags);
                    $forbiddenListAttributes = array_merge($forbiddenListAttributes, $tempAttributes);
                } elseif (\in_array($filterType, ['CBL', 'CFL'])) {
                    // "CBL" is deprecated in Joomla! 4, will be removed in Joomla! 5
                    // Only set to true if Tags or Attributes were added
                    if ($tempTags || $tempAttributes) {
                        $customList           = true;
                        $customListTags       = array_merge($customListTags, $tempTags);
                        $customListAttributes = array_merge($customListAttributes, $tempAttributes);
                    }
                } elseif (\in_array($filterType, ['WL', 'AL'])) {
                    // "WL" is deprecated in Joomla! 4, will be removed in Joomla! 5
                    $allowedList           = true;
                    $allowedListTags       = array_merge($allowedListTags, $tempTags);
                    $allowedListAttributes = array_merge($allowedListAttributes, $tempAttributes);
                }
            }
        }

        // Remove duplicates before processing (because the forbidden list uses both sets of arrays).
        $forbiddenListTags       = array_unique($forbiddenListTags);
        $forbiddenListAttributes = array_unique($forbiddenListAttributes);
        $customListTags          = array_unique($customListTags);
        $customListAttributes    = array_unique($customListAttributes);
        $allowedListTags         = array_unique($allowedListTags);
        $allowedListAttributes   = array_unique($allowedListAttributes);

        // Unfiltered assumes first priority.
        if ($unfiltered) {
            // Don't apply filtering.
            return false;
        }

        // Custom forbidden list precedes Default forbidden list.
        if ($customList) {
            $filter = InputFilter::getInstance([], [], 1, 1);

            // Override filter's default forbidden tags and attributes
            if ($customListTags) {
                $filter->blockedTags = $customListTags;
            }

            if ($customListAttributes) {
                $filter->blockedAttributes = $customListAttributes;
            }
        } elseif ($forbiddenList) {
            // Forbidden list takes second precedence.
            // Remove the allowed tags and attributes from the forbidden list.
            $forbiddenListTags       = array_diff($forbiddenListTags, $allowedListTags);
            $forbiddenListAttributes = array_diff($forbiddenListAttributes, $allowedListAttributes);

            $filter = InputFilter::getInstance($forbiddenListTags, $forbiddenListAttributes, 1, 1);

            // Remove allowed tags from filter's default forbidden list
            if ($allowedListTags) {
                $filter->blockedTags = array_diff($filter->blockedTags, $allowedListTags);
            }

            // Remove allowed attributes from filter's default forbidden list
            if ($allowedListAttributes) {
                $filter->blockedAttributes = array_diff($filter->blockedAttributes, $allowedListAttributes);
            }
        } elseif ($allowedList) {
            // Allowed list take third precedence.
            // Turn off XSS auto clean
            $filter = InputFilter::getInstance($allowedListTags, $allowedListAttributes, 0, 0, 0);
        } else {
            // No HTML takes last place.
            $filter = InputFilter::getInstance();
        }

        return $filter;
    }
}
