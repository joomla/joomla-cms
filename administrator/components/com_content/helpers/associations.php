<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
<<<<<<< HEAD
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
=======
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
>>>>>>> joomla/master
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Content associations helper.
 *
<<<<<<< HEAD
 * @since  __DEPLOY_VERSION__
=======
 * @since  3.7.0
>>>>>>> joomla/master
 */
class ContentAssociationsHelper extends JAssociationExtensionHelper
{
	/**
	 * The extension name
	 *
	 * @var     array   $extension
	 *
<<<<<<< HEAD
	 * @since   __DEPLOY_VERSION__
=======
	 * @since   3.7.0
>>>>>>> joomla/master
	 */
	protected $extension = 'com_content';

	/**
	 * Array of item types
	 *
	 * @var     array   $itemTypes
	 *
<<<<<<< HEAD
	 * @since   __DEPLOY_VERSION__
=======
	 * @since   3.7.0
>>>>>>> joomla/master
	 */
	protected $itemTypes = array('article', 'category');

	/**
	 * Has the extension association support
	 *
	 * @var     boolean   $associationsSupport
	 *
<<<<<<< HEAD
	 * @since   __DEPLOY_VERSION__
=======
	 * @since   3.7.0
>>>>>>> joomla/master
	 */
	protected $associationsSupport = true;

	/**
	 * Get the associated items for an item
	 *
	 * @param   string  $typeName  The item type
	 * @param   int     $id        The id of item for which we need the associated items
	 *
	 * @return  array
	 *
<<<<<<< HEAD
	 * @since   __DEPLOY_VERSION__
=======
	 * @since   3.7.0
>>>>>>> joomla/master
	 */

	public function getAssociations($typeName, $id)
	{
		$type = $this->getType($typeName);

		$context    = $this->extension . '.item';
		$catidField = 'catid';

		if ($typeName === 'category')
		{
			$context    = 'com_categories.item';
			$catidField = '';
		}

		// Get the associations.
		$associations = JLanguageAssociations::getAssociations(
			$this->extension,
			$type['tables']['a'],
			$context,
			$id,
			'id',
			'alias',
			$catidField
		);

		return $associations;
	}

	/**
	 * Get item information
	 *
	 * @param   string  $typeName  The item type
	 * @param   int     $id        The id of item for which we need the associated items
	 *
	 * @return  JTable|null
	 *
<<<<<<< HEAD
	 * @since   __DEPLOY_VERSION__
=======
	 * @since   3.7.0
>>>>>>> joomla/master
	 */
	public function getItem($typeName, $id)
	{
		if (empty($id))
		{
			return null;
		}

		$table = null;

		switch ($typeName)
		{
			case 'article':
				$table = JTable::getInstance('Content');
				break;

			case 'category':
				$table = JTable::getInstance('Category');
				break;
		}

		if (is_null($table))
		{
			return null;
		}

		$table->load($id);

		return $table;
	}

	/**
	 * Get information about the type
	 *
	 * @param   string  $typeName  The item type
	 *
	 * @return  array  Array of item types
	 *
<<<<<<< HEAD
	 * @since   __DEPLOY_VERSION__
=======
	 * @since   3.7.0
>>>>>>> joomla/master
	 */
	public function getType($typeName = '')
	{
		$fields  = $this->getFieldsTemplate();
		$tables  = array();
		$joins   = array();
		$support = $this->getSupportTemplate();
		$title   = '';

		if (in_array($typeName, $this->itemTypes))
		{

			switch ($typeName)
			{
				case 'article':

					$support['state'] = true;
					$support['acl'] = true;
					$support['checkout'] = true;

					$tables = array(
						'a' => '#__content'
					);

					$title = 'article';
					break;

				case 'category':
					$fields['created_user_id'] = 'a.created_user_id';
					$fields['ordering'] = 'a.lft';
					$fields['level'] = 'a.level';
					$fields['catid'] = '';
					$fields['state'] = 'a.published';

					$support['state'] = true;
					$support['acl'] = true;
					$support['checkout'] = true;

					$tables = array(
						'a' => '#__categories'
					);

					$title = 'category';
					break;
			}
		}

		return array(
			'fields'  => $fields,
			'support' => $support,
			'tables'  => $tables,
			'joins'   => $joins,
			'title'   => $title
		);
	}
}
