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
use Joomla\CMS\Helper\ContentHelper;

/**
 * @since __DEPLOY_VERSION__
 */
class StepHelper extends ContentHelper
{
    public static function getTourLanguage($id)
    {
        if (empty($id)) {
            // Throw an error or ...
            return "*";
        }

        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('language');
        $query->from('#__guidedtours');
        $query->where('id = ' . $id);
        $db->setQuery($query);

        return $db->loadResult();
    }
}
