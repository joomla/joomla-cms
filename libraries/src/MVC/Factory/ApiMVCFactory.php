<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\MVC\Factory;

/**
 * Factory to create MVC objects based on a namespace. Note that in an API Application model and table objects will be
 * created from their administrator counterparts.
 *
 * @since  4.0.0
 */
final class ApiMVCFactory extends MVCFactory
{
    /**
     * Method to load and return a model object.
     *
     * @param   string  $name    The name of the model.
     * @param   string  $prefix  Optional model prefix.
     * @param   array   $config  Optional configuration array for the model.
     *
     * @return  \Joomla\CMS\MVC\Model\ModelInterface  The model object
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function createModel($name, $prefix = '', array $config = array())
    {
        $model = parent::createModel($name, $prefix, $config);

        if (!$model) {
            $model = parent::createModel($name, 'Administrator', $config);
        }

        return $model;
    }

    /**
     * Method to load and return a table object.
     *
     * @param   string  $name    The name of the table.
     * @param   string  $prefix  Optional table prefix.
     * @param   array   $config  Optional configuration array for the table.
     *
     * @return  \Joomla\CMS\Table\Table  The table object
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function createTable($name, $prefix = '', array $config = array())
    {
        $table = parent::createTable($name, $prefix, $config);

        if (!$table) {
            $table = parent::createTable($name, 'Administrator', $config);
        }

        return $table;
    }
}
