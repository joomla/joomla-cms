<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Messages\Administrator\Helper;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Messages helper class.
 *
 * @since  1.6
 */
class MessagesHelper
{
    /**
     * Get a list of filter options for the state of a module.
     *
     * @return  array  An array of \JHtmlOption elements.
     *
     * @since   1.6
     */
    public static function getStateOptions()
    {
        // Build the filter options.
        $options   = array();
        $options[] = HTMLHelper::_('select.option', '1', Text::_('COM_MESSAGES_OPTION_READ'));
        $options[] = HTMLHelper::_('select.option', '0', Text::_('COM_MESSAGES_OPTION_UNREAD'));
        $options[] = HTMLHelper::_('select.option', '-2', Text::_('JTRASHED'));

        return $options;
    }
}
