<?php
/**
 * @package     Joomla.API
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Api\View\Overrides;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

/**
 * The overrides view
 *
 * @since  4.0.0
 */
class JsonapiView extends BaseApiView
{
	/**
	 * The fields to render item in the documents
	 *
	 * @var  array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderItem = [
		'id'
	];

	/**
	 * The fields to render items in the documents
	 *
	 * @var  array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderList = [
		'name',
		'element',
		'type',
		'version',
		'version',
		'detailsurl',
	];
}
