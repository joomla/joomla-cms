<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Guided Tours component helper.
 *
 * @since 4.3.0
 */
class GuidedtoursHelper
{
    /**
     * Sets a step language
     *
     * @param   int     $id  Id of a step
     * @param   string  $language  The language to apply to the step
     *
     * @return  boolean
     *
     * @since  4.3.0
     */
    public static function setStepLanguage(int $id, string $language = '*'): string
    {
        if ($id < 0) {
            return false;
        }

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $fields = [
            $db->quoteName('language') . ' = ' . $db->quote($language),
        ];

        $conditions = [
            $db->quoteName('tour_id') . ' = ' . $db->quote($id),
        ];

        $query->update($db->quoteName('#__guidedtour_steps'))->set($fields)->where($conditions);

        $db->setQuery($query);

        return $db->execute();
    }
}
