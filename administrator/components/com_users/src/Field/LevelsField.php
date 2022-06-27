<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;
use Joomla\Component\Users\Administrator\Helper\DebugHelper;

/**
 * Access Levels field.
 *
 * @since  3.6.0
 */
class LevelsField extends ListField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   3.6.0
     */
    protected $type = 'Levels';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects
     *
     * @since   3.6.0
     */
    protected function getOptions()
    {
        // Merge any additional options in the XML definition.
        return array_merge(parent::getOptions(), DebugHelper::getLevelsOptions());
    }
}
