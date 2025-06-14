<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Actionlogs\Administrator\Field;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Actionlogs\Administrator\Helper\ActionlogsHelper;
use Joomla\Database\ParameterType;

/**
 * Field to load a list of all users that have logged actions
 *
 * @since  5.1.0
 */
class UserlogtypeField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  5.1.0
     */
    protected $type = 'UserLogType';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   5.1.0
     */
    public function getOptions()
    {
        $db    = $this->getDatabase();
        $user  = Factory::getApplication()->getIdentity();
        $query = $db->getQuery(true)
            ->select($db->quoteName('extensions'))
            ->from($db->quoteName('#__action_logs_users'))
            ->where($db->quoteName('user_id') . ' = :userid')
            ->bind(':userid', $user->id, ParameterType::INTEGER);

        $extensions = $db->setQuery($query)->loadColumn();
        $userExt    = [];
        $params     = ComponentHelper::getParams('com_actionlogs');
        $globalExt  = $params->get('loggable_extensions', []);

        if (!empty($extensions)) {
            $userExt = substr($extensions[0], 2);
            $userExt = substr($userExt, 0, -2);
            $userExt = explode('","', $userExt);
        }

        $common  = array_merge($globalExt, array_intersect($globalExt, $userExt));
        $options = [];

        foreach ($common as $extension) {
            ActionlogsHelper::loadTranslationFiles($extension);
            $extensionName                                                                = Text::_($extension);
            $options[ApplicationHelper::stringURLSafe($extensionName) . '_' . $extension] = HTMLHelper::_('select.option', $extension, $extensionName);
        }

        ksort($options);

        return array_merge(parent::getOptions(), array_values($options));
    }
}
