<?php
/**
 * @package     Joomla.API
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Api\View\Banners;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

/**
 * The banners view
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
		'alias',
		'checked_out',
		'checked_out_time',
		'catid',
		'clicks',
		'metakey',
		'sticky',
		'impmade',
		'imptotal',
		'state',
		'ordering',
		'purchase_type',
		'language',
		'publish_up',
		'publish_down',
		'language_image',
		'editor',
		'category_title',
		'client_name',
		'client_purchase_type'
	];
}
