<?php

/**
 * @package     Joomla.Installation
 * @subpackage  Model
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Model;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base Model for the installation model classes
 *
 * @since  4.0.0
 */
class BaseInstallationModel extends BaseDatabaseModel
{
    /**
     * Constructor
     *
     * @param   array                     $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
     * @param   MVCFactoryInterface|null  $factory  The factory.
     *
     * @since   3.0
     * @throws  \Exception
     */
    public function __construct($config = [], MVCFactoryInterface $factory = null)
    {
        // @TODO remove me when the base model is db free
        $config['dbo'] = null;

        parent::__construct($config, $factory);
    }
}
