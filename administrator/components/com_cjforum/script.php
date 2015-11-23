<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die();

class com_cjforumInstallerScript
{

	function install ($parent)
	{
		// $parent is the class calling this method
		$parent->getParent()->setRedirectURL('index.php?option=com_cjforum');
	}

	function uninstall ($parent)
	{
		// $parent is the class calling this method
		echo '<p>' . JText::_('COM_CJFORUM_UNINSTALL_TEXT') . '</p>';
	}

	function update ($parent)
	{
		$db = JFactory::getDBO();
		if (method_exists($parent, 'extension_root'))
		{
			$sqlfile = $parent->getPath('extension_root') . '/sql/install.mysql.utf8.sql';
		}
		else
		{
			$sqlfile = $parent->getParent()->getPath('extension_root') . '/sql/install.mysql.utf8.sql';
		}
		// Don't modify below this line
		$buffer = file_get_contents($sqlfile);
		if ($buffer !== false)
		{
			jimport('joomla.installer.helper');
			$queries = JInstallerHelper::splitSql($buffer);
			if (count($queries) != 0)
			{
				foreach ($queries as $query)
				{
					$query = trim($query);
					if ($query != '' && $query{0} != '#')
					{
						$db->setQuery($query);
						if (! $db->query())
						{
// 							JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
// 							return false;
						}
					}
				}
			}
		}
		// $parent is the class calling this method
		echo '<p>' . JText::_('COM_CJFORUM_UPDATE_TEXT') . '</p>';
		$parent->getParent()->setRedirectURL('index.php?option=com_cjforum&view=topics');
	}

	function preflight ($type, $parent)
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		echo '<p>' . JText::_('COM_CJFORUM_PREFLIGHT_' . $type . '_TEXT') . '</p>';
	}

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight ($type, $parent)
	{
		$db = JFactory::getDbo();
		$update_queries = array();
		
		// Perform all queries - we don't care if it fails
		foreach ($update_queries as $query)
		{
			
			$db->setQuery($query);
			
			try
			{
				
				$db->query();
			}
			catch (Exception $e)
			{
			}
		}
		
		$this->add_core_features_support();
		
		echo "<b><font color=\"red\">Database tables successfully migrated to the latest version. Please check the configuration options once again.</font></b>";
	}

	private function add_core_features_support ()
	{
		
		// add topic content type to content_types table
		$topic_table_def = new stdClass();
		$topic_table_def->special = new stdClass();
		$topic_table_def->special->dbtable = '#__cjforum_topics';
		$topic_table_def->special->key = 'id';
		$topic_table_def->special->type = 'Topic';
		$topic_table_def->special->prefix = 'CjForumTable';
		$topic_table_def->special->config = 'array()';
		
		$topic_table_def->common = new stdClass();
		$topic_table_def->common->dbtable = '#__ucm_content';
		$topic_table_def->common->key = 'ucm_id';
		$topic_table_def->common->type = 'Corecontent';
		$topic_table_def->common->prefix = 'JTable';
		$topic_table_def->common->config = 'array()';
		
		$topic_field_mappings = new stdClass();
		$topic_field_mappings->common = new stdClass();
		$topic_field_mappings->common->core_content_item_id = 'id';
		$topic_field_mappings->common->core_title = 'title';
		$topic_field_mappings->common->core_state = 'state';
		$topic_field_mappings->common->core_alias = 'alias';
		$topic_field_mappings->common->core_created_time = 'created';
		$topic_field_mappings->common->core_modified_time = 'modified';
		$topic_field_mappings->common->core_body = 'introtext';
		$topic_field_mappings->common->core_hits = 'hits';
		$topic_field_mappings->common->core_publish_up = 'publish_up';
		$topic_field_mappings->common->core_publish_down = 'publish_down';
		$topic_field_mappings->common->core_access = 'access';
		$topic_field_mappings->common->core_params = 'null';
		$topic_field_mappings->common->core_featured = 'featured';
		$topic_field_mappings->common->core_metadata = 'metadata';
		$topic_field_mappings->common->core_language = 'language';
		$topic_field_mappings->common->core_images = 'images';
		$topic_field_mappings->common->core_urls = 'urls';
		$topic_field_mappings->common->core_version = 'version';
		$topic_field_mappings->common->core_ordering = 'ordering';
		$topic_field_mappings->common->core_metakey = 'metakey';
		$topic_field_mappings->common->core_metadesc = 'metadesc';
		$topic_field_mappings->common->core_catid = 'catid';
		$topic_field_mappings->common->core_xreference = 'xreference';
		$topic_field_mappings->common->asset_id = 'asset_id';
		
		$topic_field_mappings->special = new stdClass();
		$topic_field_mappings->special->replies = 'replies';
		$topic_field_mappings->special->locked = 'locked';
		$topic_field_mappings->special->locked_by = 'locked_by';
		$topic_field_mappings->special->replied = 'replied';
		$topic_field_mappings->special->replied_by = 'replied_by';
		$topic_field_mappings->special->ip_address = 'ip_address';
		
		$display_lookup_catid = new stdClass();
		$display_lookup_catid->sourceColumn = 'catid';
		$display_lookup_catid->targetTable = '#__categories';
		$display_lookup_catid->targetColumn = 'id';
		$display_lookup_catid->displayColumn = 'title';
		
		$display_lookup_title = new stdClass();
		$display_lookup_title->sourceColumn = 'created_by';
		$display_lookup_title->targetTable = '#__users';
		$display_lookup_title->targetColumn = 'id';
		$display_lookup_title->displayColumn = 'name';
		
		$display_lookup_access = new stdClass();
		$display_lookup_access->sourceColumn = 'access';
		$display_lookup_access->targetTable = '#__viewlevels';
		$display_lookup_access->targetColumn = 'id';
		$display_lookup_access->displayColumn = 'title';
		
		$display_lookup_modified_by = new stdClass();
		$display_lookup_modified_by->sourceColumn = 'modified_by';
		$display_lookup_modified_by->targetTable = '#__users';
		$display_lookup_modified_by->targetColumn = 'id';
		$display_lookup_modified_by->displayColumn = 'name';
		
		$topic_history_options = new stdClass();
		$topic_history_options->formFile = 'administrator/components/com_cjforum/models/forms/topic.xml';
		$topic_history_options->hideFields = array('asset_id', 'checked_out', 'checked_out_time', 'version');
		$topic_history_options->ignoreChanges = array('modified_by', 'modified', 'checked_out', 'checked_out_time', 'version', 'votes');
		$topic_history_options->convertToInt = array('publish_up', 'publish_down', 'featured', 'ordering');
		$topic_history_options->displayLookup = array($display_lookup_catid, $display_lookup_title, $display_lookup_access, $display_lookup_modified_by);
		
		$topic_table = JTable::getInstance('Contenttype', 'JTable');
		$topic_type_id = (int) $topic_table->getTypeId('com_cjforum.topic');
		
		$topic_content_type = array();
		$topic_content_type['type_id'] = $topic_type_id;
		$topic_content_type['type_title'] = 'Topic';
		$topic_content_type['type_alias'] = 'com_cjforum.topic';
		$topic_content_type['table'] = json_encode($topic_table_def);
		$topic_content_type['rules'] = '';
		$topic_content_type['router'] = 'CjForumHelperRoute::getTopicRoute';
		$topic_content_type['field_mappings'] = json_encode($topic_field_mappings);
		$topic_content_type['content_history_options'] = json_encode($topic_history_options);
		
		$topic_table->save($topic_content_type);
		
		// add topic category type to content_types table
		$category_table_def = new stdClass();
		$category_table_def->special = new stdClass();
		$category_table_def->special->dbtable = '#__categories';
		$category_table_def->special->key = 'id';
		$category_table_def->special->type = 'Category';
		$category_table_def->special->prefix = 'JTable';
		$category_table_def->special->config = 'array()';
		
		$category_table_def->common = new stdClass();
		$category_table_def->common->dbtable = '#__ucm_content';
		$category_table_def->common->key = 'ucm_id';
		$category_table_def->common->type = 'Corecontent';
		$category_table_def->common->prefix = 'JTable';
		$category_table_def->common->config = 'array()';
		
		$category_field_mappings = new stdClass();
		$category_field_mappings->common = new stdClass();
		$category_field_mappings->common->core_content_item_id = 'id';
		$category_field_mappings->common->core_title = 'title';
		$category_field_mappings->common->core_state = 'published';
		$category_field_mappings->common->core_alias = 'alias';
		$category_field_mappings->common->core_created_time = 'created_time';
		$category_field_mappings->common->core_modified_time = 'modified_time';
		$category_field_mappings->common->core_body = 'description';
		$category_field_mappings->common->core_hits = 'hits';
		$category_field_mappings->common->core_publish_up = 'publish_up';
		$category_field_mappings->common->core_publish_down = 'publish_down';
		$category_field_mappings->common->core_access = 'access';
		$category_field_mappings->common->core_params = 'params';
		$category_field_mappings->common->core_featured = 'featured';
		$category_field_mappings->common->core_metadata = 'metadata';
		$category_field_mappings->common->core_language = 'language';
		$category_field_mappings->common->core_images = 'images';
		$category_field_mappings->common->core_urls = 'urls';
		$category_field_mappings->common->core_version = 'version';
		$category_field_mappings->common->core_ordering = 'ordering';
		$category_field_mappings->common->core_metakey = 'metakey';
		$category_field_mappings->common->core_metadesc = 'metadesc';
		$category_field_mappings->common->core_catid = 'parent_id';
		$category_field_mappings->common->core_xreference = 'xreference';
		$category_field_mappings->common->asset_id = 'asset_id';
		
		$category_field_mappings->special = new stdClass();
		$category_field_mappings->special->parent_id = 'parent_id';
		$category_field_mappings->special->lft = 'lft';
		$category_field_mappings->special->rgt = 'rgt';
		$category_field_mappings->special->level = 'level';
		$category_field_mappings->special->path = 'path';
		$category_field_mappings->special->extension = 'extension';
		$category_field_mappings->special->note = 'note';
		
		$category_display_created_by = new stdClass();
		$category_display_created_by->sourceColumn = 'created_user_id';
		$category_display_created_by->targetTable = '#__users';
		$category_display_created_by->targetColumn = 'id';
		$category_display_created_by->displayColumn = 'name';
		
		$category_display_access = new stdClass();
		$category_display_access->sourceColumn = 'access';
		$category_display_access->targetTable = '#__viewlevels';
		$category_display_access->targetColumn = 'id';
		$category_display_access->displayColumn = 'title';
		
		$category_display_modified_by = new stdClass();
		$category_display_modified_by->sourceColumn = 'modified_user_id';
		$category_display_modified_by->targetTable = '#__users';
		$category_display_modified_by->targetColumn = 'id';
		$category_display_modified_by->displayColumn = 'name';
		
		$category_display_parent_id = new stdClass();
		$category_display_parent_id->sourceColumn = 'parent_id';
		$category_display_parent_id->targetTable = '#__categories';
		$category_display_parent_id->targetColumn = 'id';
		$category_display_parent_id->displayColumn = 'title';
		
		$category_history_options = new stdClass();
		$category_history_options->formFile = 'administrator/components/com_categories/models/forms/category.xml';
		$category_history_options->hideFields = array('asset_id', 'checked_out', 'checked_out_time' . 'version', 'lft', 'rgt', 'level', 'path', 'extension');
		$category_history_options->ignoreChanges = array('modified_user_id', 'modified_time', 'checked_out', 'checked_out_time', 'version', 'hits', 'path');
		$category_history_options->convertToInt = array('publish_up', 'publish_down');
		$category_history_options->displayLookup = array($category_display_created_by, $category_display_access, $category_display_modified_by, $category_display_parent_id);
		
		$category_table = JTable::getInstance('Contenttype', 'JTable');
		$category_type_id = (int) $category_table->getTypeId('com_cjforum.category');
		
		$category_content_type = array();
		$category_content_type['type_id'] = $category_type_id;
		$category_content_type['type_title'] = 'Topic Category';
		$category_content_type['type_alias'] = 'com_cjforum.category';
		$category_content_type['table'] = json_encode($category_table_def);
		$category_content_type['rules'] = '';
		$category_content_type['router'] = 'CjForumHelperRoute::getCategoryRoute';
		$category_content_type['field_mappings'] = json_encode($category_field_mappings);
		$category_content_type['content_history_options'] = json_encode($category_history_options);
		
		$category_table->save($category_content_type);
		
		// Added default archived point rule
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$created = JFactory::getDate()->toSql();
		
		$query = $db->getQuery(true)
			->insert('#__cjforum_points_rules')
			->columns('id, title, description, app_name, rule_name, points, published, auto_approve, access, created_by, created')
			->values('1, '.$db->q('Archive').','.$db->q('Archived points').','.$db->q('com_cjforum').','.$db->q('com_cjforum.archive').', 0, 1, 1, 1, '.$user->id.','.$db->q($created));
		
		try 
		{
			$db->setQuery($query);
			$db->execute();
		}
		catch (Exception $e){}
	}
}