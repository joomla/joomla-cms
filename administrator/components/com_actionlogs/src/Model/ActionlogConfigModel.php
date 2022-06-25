<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Actionlogs\Administrator\Model;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use stdClass;

/**
 * Model to interact with the action log configuration.
 *
 * @since  4.2.0
 */
class ActionlogConfigModel extends BaseDatabaseModel
{
    /**
     * Returns the action logs config for the given context.
     *
     * @param   string    $context  The context of the content
     *
     * @return  stdClass|null  An object contains content type parameters, or null if not found
     *
     * @since   4.2.0
     */
    public function getLogContentTypeParams(string $context): ?stdClass
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('a.*')
            ->from($db->quoteName('#__action_log_config', 'a'))
            ->where($db->quoteName('a.type_alias') . ' = :context')
            ->bind(':context', $context);

        $db->setQuery($query);

        return $db->loadObject();
    }
}
