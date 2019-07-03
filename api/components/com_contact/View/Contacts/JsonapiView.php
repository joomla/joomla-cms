<?php
/**
 * @package     Joomla.API
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Api\View\Contacts;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

/**
 * The contacts view
 *
 * @since  4.0.0
 */
class JsonapiView extends BaseApiView
{
	/**
	 * The fields to render item in the documents
	 *
	 * @var  string
	 * @since  4.0.0
	 */
	protected $fieldsToRenderItem = ['id', 'alias', 'name', 'catid', 'created'];

	/**
	 * The fields to render items in the documents
	 *
	 * @var  string
	 * @since  4.0.0
	 */
	protected $fieldsToRenderList = ['id', 'alias', 'name', 'catid', 'created'];
}
