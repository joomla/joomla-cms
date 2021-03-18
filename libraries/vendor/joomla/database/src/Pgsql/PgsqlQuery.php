<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Pgsql;

use Joomla\Database\Pdo\PdoQuery;
use Joomla\Database\Query\PostgresqlQueryBuilder;
use Joomla\Database\Query\QueryElement;

/**
 * PDO PostgreSQL Query Building Class.
 *
 * @since  1.0
 *
 * @property-read  QueryElement  $forUpdate  The FOR UPDATE element used in "FOR UPDATE" lock
 * @property-read  QueryElement  $forShare   The FOR SHARE element used in "FOR SHARE" lock
 * @property-read  QueryElement  $noWait     The NOWAIT element used in "FOR SHARE" and "FOR UPDATE" lock
 * @property-read  QueryElement  $returning  The RETURNING element of INSERT INTO
 */
class PgsqlQuery extends PdoQuery
{
	use PostgresqlQueryBuilder;

	/**
	 * The list of zero or null representation of a datetime.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $nullDatetimeList = ['1970-01-01 00:00:00'];
}
