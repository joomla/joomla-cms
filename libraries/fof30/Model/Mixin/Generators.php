<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Model\Mixin;

defined('_JEXEC') || die;

use Exception;
use Generator;
use ReflectionObject;

/**
 * Trait for PHP 5.5 Generators
 */
trait Generators
{
	/**
	 * Returns a PHP Generator of DataModel instances based on your currently set Model state. You can foreach() the
	 * returned generator to walk through each item of the data set.
	 *
	 * WARNING! This only works on PHP 5.5 and later.
	 *
	 * When the generator is done you might get a PHP warning. This is normal. Joomla! doesn't support multiple db
	 * cursors being open at once. What we do instead is clone the database object. Of course it cannot close the db
	 * connection when we dispose of it (since it's already in use by Joomla), hence the warning. Pay no attention.
	 *
	 * @param   integer  $limitstart      How many items from the start to skip (0 = do not skip)
	 * @param   integer  $limit           How many items to return (0 = all)
	 * @param   bool     $overrideLimits  Set to true to override limitstart, limit and ordering
	 *
	 * @return  Generator  A PHP generator of DataModel objects
	 * @throws  Exception
	 * @since   3.3.2
	 */
	public function &getGenerator($limitstart = 0, $limit = 0, $overrideLimits = false)
	{
		$limitstart = max($limitstart, 0);
		$limit      = max($limit, 0);

		$query = $this->buildQuery($overrideLimits);

		$db = clone $this->getDbo();
		$db->setQuery($query, $limitstart, $limit);
		$cursor = $db->execute();

		$reflectDB     = new ReflectionObject($db);
		$refFetchAssoc = $reflectDB->getMethod('fetchAssoc');
		$refFetchAssoc->setAccessible(true);

		while ($data = $refFetchAssoc->invoke($db, $cursor))
		{
			$item = clone $this;
			$item->clearState()->reset(true);
			$item->bind($data);
			$item->relationManager = clone $this->relationManager;
			$item->relationManager->rebase($item);

			yield $item;
		}
	}
}
