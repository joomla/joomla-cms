<?php
/**
 * @package     Joomla.API
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Api\View\Client;

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
		'typeAlias',
		'id',
		'checked_out_time',
		'name',
		'contact',
		'email',
		'checked_out',
		'checked_out_time',
		'extrainfo',
		'state',
		'metakey',
		'own_prefix',
		'metakey_prefix',
		'purchase_type',
		'track_clicks',
		'track_impressions',
	];
}
