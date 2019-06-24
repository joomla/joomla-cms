<?php
/**
 * @package     Joomla.API
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Api\View\Banner;

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
		'cid',
		'type',
		'name',
		'alias',
		'imptotal',
		'impmade',
		'clicks',
		'clickurl',
		'state',
		'catid',
		'description',
		'custombannercode',
		'sticky',
		'ordering',
		'metakey',
		'params',
		'own_prefix',
		'metakey_prefix',
		'purchase_type',
		'track_clicks',
		'track_impressions',
		'checked_out',
		'checked_out_time',
		'publish_up',
		'publish_down',
		'reset',
		'created',
		'language',
		'created_by',
		'created_by_alias',
		'modified',
		'modified_by',
		'version',
		'tagsHelper',
		'contenthistoryHelper',
	];
}
