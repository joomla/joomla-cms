<?php
/**
 * @version		$Id: articleassociations.php revision date lasteditedby $
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die;

class plgContentarticleassociations extends JPlugin
{
	/**
	 * A Database instance
	 *
	 * @var    JDatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;
	public $liste1, $parent;

	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	public function onContentAfterSave($context, $article, $isNew)
	{

		$id = $article->id;

		// Check if a parent for associations exist
		// Do your query

		// If not parent exists, add it
		if ($this->getParentCount($id) !== 1)
		{
			$query   = $this->db->getQuery(true);
			$columns = array('id', 'parentid', 'approved');
			$values  = array($id, 0, 1);
			$query
				->insert($this->db->quoteName('#__item_associations'))
				->columns($this->db->quoteName($columns))
				->values(implode(',', $values));

			$this->db->setQuery($query)->execute();
		}

		return true;
	}

	public function onContentAfterSaveAssociations($context, $article, $isNew)
	{
		$id = $article->id;
		// Get a db connection.
		// Create a new query object.
		$query = $this->db->getQuery(true);

		// Select all records from the user profile table where key begins with "custom.".
		// Order it by the ordering field.
		$query->select($this->db->quoteName(array('key')));
		$query->from($this->db->quoteName('#__associations'));
		$query->where($this->db->quoteName('id') . " = " . $this->db->quote($id));
		$this->db->setQuery($query);
		$result = $this->db->loadResult();


		// if slave article is edit
		if ((int) $this->getParentId($id) !== 0)
		{
			$query  = $this->db->getQuery(true);
			$fields = array(
				$this->db->quoteName('approved') . ' =0'
			);

			$conditions = array($this->db->quoteName('id') . ' = ' . $this->db->quote($id));
			$query->update($this->db->quoteName('#__item_associations'))->set($fields)->where($conditions);
			$this->db->setQuery($query)->execute();
		}

		// if master article is edit

		$query->clear()
			->select(array('#__item_associations.id'))
			->from($this->db->quoteName('#__item_associations'))
			->join('INNER', $this->db->quoteName('#__associations') . ' ON (' . $this->db->quoteName('#__associations.id') . ' = ' . $this->db->quoteName('#__item_associations.id') . ')')
			->where($this->db->quoteName('#__associations.key') . ' ='  . $this->db->quote($result),'AND')
			->where($this->db->quoteName('#__item_associations.parentid') . ' = 0');
		$this->db->setQuery($query);
		$liste1 = $this->db->loadRowList();

		if ($result && strcmp($this->getParentId($id), "0") == 0)
		{
			$db    = JFactory::getDbo();
			$query = $this->db->getQuery(true);
			$query->select($this->db->quoteName(array('id')));
			$query->from($this->db->quoteName('#__associations'));
			$query->where($this->db->quoteName('key') . " = " . $this->db->quote($result));
			$this->db->setQuery($query);
			$list = $this->db->loadRowList();
			foreach ($list as $res)
			{
				foreach ($res as $association_id)
				{

					if ($id != $association_id)
					{


						$query  = $this->db->getQuery(true);
						$fields = array(
							$this->db->quoteName('approved') . ' =0',
							$this->db->quoteName('parentid') . ' =' . $this->db->quote($id)
						);

						$conditions = array($this->db->quoteName('id') . ' =' . $this->db->quote($association_id));
						$query->update($this->db->quoteName('#__item_associations'))->set($fields)->where($conditions);
						$this->db->setQuery($query);
						$res = $this->db->execute();


					}


				}

			}
		}

		return true;
	}


	public function getParentCount($id)
	{
		$query = $this->db->getQuery(true)
			->select(COUNT($this->db->quoteName(array('parentid'))))
			->from($this->db->quoteName('#__item_associations'))
			->where($this->db->quoteName('id') . ' = ' . (int) $id);
		$this->db->setQuery($query);
		$parentid = $this->db->loadResult();

		return (int) $parentid;
	}


	public function getParentId($id)
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('parentid')))
			->from($this->db->quoteName('#__item_associations'))
			->where($this->db->quoteName('id') . ' = ' . (int) $id);
		$this->db->setQuery($query);
		$parentid = $this->db->loadResult();

		return (int) $parentid;


	}

}
