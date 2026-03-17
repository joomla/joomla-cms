<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contenthistory\Administrator\Controller;

use Joomla\CMS\MVC\Controller\BaseController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Contenthistory list controller class.
 *
 * @since  3.2
 */
class PreviewController extends BaseController
{
    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The name of the model
     * @param   string  $prefix  The prefix for the model
     * @param   array   $config  An additional array of parameters
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model
     *
     * @since   3.2
     */
    public function getModel($name = 'Preview', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
