<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\View\ViewTraits;

// Protect from unauthorized access
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') || die();

trait ProfileList
{
	/**
	 * List of backup profiles, for use with JHtmlSelect
	 *
	 * @var   array
	 */
	public $profileList = [];

	/**
	 * Populates the profileList property with an options list for use by JHtmlSelect
	 *
	 * @param   bool  $includeId  Should I include the profile ID in front of the name?
	 *
	 * @return  void
	 */
	protected function getProfileList($includeId = true)
	{
		/** @var \JDatabaseDriver $db */
		$db = $this->container->db;

		$query = $db->getQuery(true)
			->select([
				$db->qn('id'),
				$db->qn('description'),
			])->from($db->qn('#__ak_profiles'))
			->order($db->qn('id') . " ASC");

		$db->setQuery($query);
		$rawList = $db->loadAssocList();

		$this->profileList = [];

		if (!is_array($rawList))
		{
			return;
		}

		foreach ($rawList as $row)
		{
			$description = $row['description'];

			if ($includeId)
			{
				$description = '#' . $row['id'] . '. ' . $description;
			}

			$this->profileList[] = HTMLHelper::_('select.option', $row['id'], $description);
		}
	}
}
