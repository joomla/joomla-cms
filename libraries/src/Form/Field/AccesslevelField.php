<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\HTML\HTMLHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla Platform.
 * Provides a list of access levels. Access levels control what users in specific
 * groups can see.
 *
 * @see    JAccess
 * @since  1.7.0
 */
class AccesslevelField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type = 'Accesslevel';

    /**
     * Method to get the field options.
     *
     * @return  object[]  The field option objects.
     *
     * @since   4.0.0
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), HTMLHelper::_('access.assetgroups'));
    }
}
