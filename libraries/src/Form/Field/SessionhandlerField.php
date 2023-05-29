<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla Platform.
 * Provides a select list of session handler options.
 *
 * @since  1.7.0
 */
class SessionhandlerField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type = 'Sessionhandler';

    /**
     * Method to get the session handler field options.
     *
     * @return  object[]  The field option objects.
     *
     * @since   1.7.0
     */
    protected function getOptions()
    {
        $options = [];

        // Get the options from the session object.
        foreach (Session::getHandlers() as $store) {
            $options[] = HTMLHelper::_('select.option', strtolower($store), Text::_('JLIB_FORM_VALUE_SESSION_' . $store), 'value', 'text');
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
