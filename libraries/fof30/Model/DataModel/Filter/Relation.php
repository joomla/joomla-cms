<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Model\DataModel\Filter;

use JDatabaseQuery;

defined('_JEXEC') || die;

class Relation extends Number
{
	/** @var JDatabaseQuery The COUNT sub-query to filter by */
	protected $subQuery = null;

	public function __construct($db, $relationName, $subQuery)
	{
		$field = (object) [
			'name' => $relationName,
			'type' => 'relation',
		];

		parent::__construct($db, $field);

		$this->subQuery = $subQuery;
	}

	public function callback($value)
	{
		return call_user_func($value, $this->subQuery);
	}

	public function getFieldName()
	{
		return '(' . (string) $this->subQuery . ')';
	}
}
