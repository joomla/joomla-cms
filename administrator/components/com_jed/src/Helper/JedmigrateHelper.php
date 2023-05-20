<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


use Exception;
use Joomla\CMS\Factory;

use function defined;

/**
 * JED Helper
 *
 * @package   JED
 * @since     4.0.0
 */
class JedmigrateHelper
{
    /**
     * @param           $params
     * @param   string  $sql
     *
     *
     * @since 4.0.0
     */
    public static function doSql($params, string $sql)
    {
        /*$jed3_db_host     = $params->get('jed3_db_host');
        $jed3_db_database_name = $params->get('jed3_db_database_name');
        $jed3_db_user     = $params->get('jed3_db_user');
        $jed3_db_password = $params->get('jed3_db_password');
        $jed3_db_prefix   = $params->get('jed3_db_prefix');*/
        $replacestr = "" . $params->get('jed3_db_database_name') . '.' . $params->get('jed3_db_prefix') . "_";


        /* Rearrange Query */
        $sql = str_replace("wqyh6_", $replacestr, $sql);

        try {
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $query->setQuery($sql);
            $db->setQuery($query);
            $db->execute();
        } catch (Exception $e) {
            echo "An Error Occurred - Failed SQL - " . $sql;
            exit();
        }
    }
}
