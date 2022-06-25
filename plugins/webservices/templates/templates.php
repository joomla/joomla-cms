<?php

/**
 * @package     Joomla.Templates
 * @subpackage  Webservices.Templates
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;

/**
 * Web Services adapter for com_templates.
 *
 * @since  4.0.0
 */
class PlgWebservicesTemplates extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Registers com_templates's API's routes in the application
     *
     * @param   ApiRouter  &$router  The API Routing object
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onBeforeApiRoute(&$router)
    {
        $router->createCRUDRoutes(
            'v1/templates/styles/site',
            'styles',
            ['component' => 'com_templates', 'client_id' => 0]
        );

        $router->createCRUDRoutes(
            'v1/templates/styles/administrator',
            'styles',
            ['component' => 'com_templates', 'client_id' => 1]
        );
    }
}
