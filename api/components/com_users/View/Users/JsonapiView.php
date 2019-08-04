<?php
/**
 * @package     Joomla.API
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Api\View\Users;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

/**
 * The users view
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
		'groups',
		'name',
		'username',
		'email',
		'password',
		'block',
		'sendEmail',
		'registerDate',
		'lastvisitDate',
		'activation',
		'params',
		'lastResetTime',
		'resetCount',
		'otpKey',
		'otep',
		'requireReset',
	];

	/**
	 * The fields to render items in the documents
	 *
	 * @var  array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderList = [
		'id',
		'name',
		'username',
		'email',
		'password',
		'block',
		'sendEmail',
		'registerDate',
		'lastvisitDate',
		'activation',
		'params',
		'lastResetTime',
		'resetCount',
		'otpKey',
		'otep',
		'requireReset',
		'group_count',
		'group_names',
		'note_count',
	];
}
