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
 * Guided tour step helper
 *
 * @since __DEPLOY_VERSION__
 */
class StepHelper
{
    /**
     * Get a tour language
     *
     * @param   int  $id  Id of a tour
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function getTourLanguage(int $id): string
    {
        if (empty($id)) {
            return "*";
        }

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('language')
            ->from($db->quoteName('#__guidedtours'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);

        $db->setQuery($query);

        return $db->loadResult();
    }
}
