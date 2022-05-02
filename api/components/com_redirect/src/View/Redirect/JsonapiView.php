<?php
/**
 * @package     Joomla.API
 * @subpackage  com_redirect
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Redirect\Api\View\Redirect;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

/**
 * The redirect view
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
		'id',
		'old_url',
		'new_url',
		'referer',
		'comment',
		'hits',
		'published',
		'created_date',
		'modified_date',
		'header',
	];

	/**
	 * The fields to render items in the documents
	 *
	 * @var  array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderList = [
		'id',
		'old_url',
		'new_url',
		'referer',
		'comment',
		'hits',
		'published',
		'created_date',
		'modified_date',
		'header',
	];
}
