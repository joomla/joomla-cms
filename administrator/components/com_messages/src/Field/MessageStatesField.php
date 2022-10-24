<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Messages\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;
use Joomla\Component\Messages\Administrator\Helper\MessagesHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Message States field.
 *
 * @since  3.6.0
 */
class MessageStatesField extends ListField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   3.6.0
     */
    protected $type = 'MessageStates';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   3.6.0
     */
    protected function getOptions()
    {
        // Merge state options with any additional options in the XML definition.
        return array_merge(parent::getOptions(), MessagesHelper::getStateOptions());
    }
}
