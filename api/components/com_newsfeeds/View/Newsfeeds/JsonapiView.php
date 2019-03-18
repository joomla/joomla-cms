<?php
/**
 * @package     Joomla.API
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Newsfeeds\Api\View\Newsfeeds;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
/**
 * The newsfeeds json api view
 *
 * @since  __DEPLOY_VERSION__
 */
class JsonapiView extends BaseApiView
{
	/**
	 * The fields to render in the documents
	 *
	 * @var  string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $fieldsToRender = [
		'id',
		'name',
		'published',
		'catid',
		'language'
	];
}