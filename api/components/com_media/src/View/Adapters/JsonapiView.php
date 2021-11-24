<?php
/**
 * @package     Joomla.API
 * @subpackage  com_media
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Api\View\Adapters;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\Component\Media\Api\Helper\AdapterTrait;

/**
 * Media web service view
 *
 * @since  __DEPLOY_VERSION__
 */
class JsonapiView extends BaseApiView
{
	use AdapterTrait;

	/**
	 * The fields to render item in the documents
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $fieldsToRenderItem = [
		'provider_id',
		'name',
		'path',
	];

	/**
	 * The fields to render items in the documents
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $fieldsToRenderList = [
		'provider_id',
		'name',
		'path',
	];
}
