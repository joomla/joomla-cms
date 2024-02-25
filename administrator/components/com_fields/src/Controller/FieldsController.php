<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Administrator\Controller;

use Joomla\CMS\MVC\Controller\AdminController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Fields list controller class.
 *
 * @since  3.7.0
 */
class FieldsController extends AdminController
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     *
     * @since   3.7.0
     */
    protected $text_prefix = 'COM_FIELDS_FIELD';

    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The name of the model.
     * @param   string  $prefix  The prefix for the PHP class name.
     * @param   array   $config  Array of configuration parameters.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
     *
     * @since   3.7.0
     */
    public function getModel($name = 'Field', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Append context variable to list redirect so that parent menu item stays open after an
     * admin action like publish, un-publish... fields
     *
     * @return  string  The arguments to append to the redirect URL.
     *
     * @since   4.2.9
     */
    protected function getRedirectToListAppend()
    {
        $append = parent::getRedirectToListAppend();

        $context = $this->input->getString('context');

        if ($context) {
            $append .= '&context=' . $context;
        }

        return $append;
    }
}
