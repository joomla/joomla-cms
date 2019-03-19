<?php
/**
 * @package     Joomla.API
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Search\Api\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\ApiController;
/**
 * The contacts controller
 *
 * @since  __DEPLOY_VERSION__
 */
class SearchController extends ApiController
{
    /**
     * The content type of the item.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $contentType = 'search';
}
