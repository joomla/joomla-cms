<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Actionlogs\Administrator\Field;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Actionlogs\Administrator\Helper\ActionlogsHelper;

/**
 * Field to load a list of all users that have logged actions
 *
 * @since  3.9.0
 */
class LogtypeField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.9.0
     */
    protected $type = 'LogType';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   3.9.0
     */
    public function getOptions()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('extension'))
            ->from($db->quoteName('#__action_logs_extensions'));

        $extensions = $db->setQuery($query)->loadColumn();

        $options = [];

        foreach ($extensions as $extension) {
            ActionlogsHelper::loadTranslationFiles($extension);
            $extensionName = Text::_($extension);
            $options[ApplicationHelper::stringURLSafe($extensionName) . '_' . $extension] = HTMLHelper::_('select.option', $extension, $extensionName);
        }

        ksort($options);

        return array_merge(parent::getOptions(), array_values($options));
    }
}
