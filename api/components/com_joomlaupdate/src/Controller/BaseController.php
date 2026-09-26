<?php

/**
 * @package     Joomla.API
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Joomlaupdate\Api\Controller;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\CMS\MVC\Controller\Exception\ResourceNotFound;
use Tobscure\JsonApi\Exception\InvalidParameterException;

abstract class BaseController extends ApiController
{
    /**
     * Validate if the update token is correct and auto update is enabled
     *
     * @return void
     *
     * @since 5.4.0
     *
     * @throws \Exception
     */
    protected function validateUpdateToken(): void
    {
        $config = ComponentHelper::getParams('com_joomlaupdate');

        if ($config->get('updatesource') !== 'default' || (int) $config->get('minimum_stability') !== 4 || !$config->get('autoupdate')) {
            throw new ResourceNotFound('Auto update is disabled', 404);
        }

        $token = $this->input->server->get('HTTP_X_JUPDATE_TOKEN', '', 'STRING');

        if (empty($token)) {
            throw new InvalidParameterException('Token is required', 403, null, 'token');
        }

        if ($config->get('update_token') !== $token) {
            throw new InvalidParameterException('Invalid token', 403, null, 'token');
        }
    }
}
