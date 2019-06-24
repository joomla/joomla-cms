<?php
/**
 * @package     Joomla.API
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Api\View\Clients;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

/**
 * The banner view
 *
 * @since  4.0.0
 */
class JsonapiView extends BaseApiView
{
	/**
	 * The fields to render in the documents
	 *
	 * @var  string
	 * @since  4.0.0
	 */
	protected $fieldsToRender = [
		'id',
		'name',
		'contact',
		'checked_out',
		'checked_out_time',
		'state',
		'metakey',
		'purchase_type',
		'nbanners',
		'editor',
		'count_published',
		'count_unpublished',
		'count_trashed',
		'count_archived'
	];
}
