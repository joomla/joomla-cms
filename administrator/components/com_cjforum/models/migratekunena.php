<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

JLoader::register('CjForumHelper', JPATH_ADMINISTRATOR . '/components/com_cjforum/helpers/cjforum.php');
JLoader::import('migrate', JPATH_COMPONENT_ADMINISTRATOR.'/models');
require_once CJLIB_PATH.'/lib/jbbcode/Parser.php';
require_once CJLIB_PATH.'/lib/jbbcode/custom/CjCustomCodeDefinitions.php';

define('CJFORUM_MIGRATION_NUM_TOPICS', 100);
define('CJFORUM_MIGRATION_NUM_REPLIES', 100);
define('CJFORUM_MIGRATION_NUM_USERS', 250);

class CjForumModelMigrateKunena extends CjForumModelMigrate
{
	private $_message = null;
	
	public function getMessage()
	{
		return $this->_message;
	}
	
	public function setMessage($message)
	{
		$this->_message = $message;
	}
	
	public function migrate($step = 0)
	{
		$app = JFactory::getApplication();
		$content = file_get_contents(JPATH_ROOT.'/tmp/migrate_kunena.json');
		
		if(!$content)
		{
			throw new Exception('Migration steps file is missing.');
		}
		
		$steps = json_decode($content);
		
		if(!is_array($steps))
		{
			throw new Exception('Analysis file not found, cannot continue.');
		} 
		else if(!isset($steps[$step]))
		{
			// process completed
			return 1;
		}
		
		$parts = explode('.', $steps[$step]);
		$features = $app->input->get('features', array(), 'array');
		
		if (count($parts) != 2 || !is_numeric($parts[1]))
		{
			throw new Exception('Invalid controller steps file.');
		}
		
		switch ($parts[0])
		{
			case 'categories':
				if(in_array('topics', $features))
				{
					if(! $this->migrateCategories() )
					{
						return false;
					}
					$this->setMessage('<i class="fa fa-folder-open"></i> Categories migration completed.');
				}
				break;
				
			case 'topics':
				if (in_array('topics', $features))
				{
					if(! $this->migrateTopics($parts[1], CJFORUM_MIGRATION_NUM_TOPICS) )
					{
						return false;
					}
					
					$count = $parts[1] + CJFORUM_MIGRATION_NUM_TOPICS;
					$this->setMessage('<i class="fa fa-file-text-o"></i> Migrating topics now.. '.$count.' topics migrated.');
				}
				break;
				
			case 'ranks':
				if(in_array('users', $features))
				{
					if(! $this->migrateRanks($parts[1]) )
					{
						return false;
					}
					$this->setMessage('<i class="fa fa-trophy"></i> Ranks migration completed.');
				}
				break;
				
			case 'users':
				if(in_array('users', $features))
				{
					$migrateAvatars = false;
					if(in_array('avatar', $features))
					{
						$migrateAvatars = true;
					}
					
					if(! $this->migrateUsers($parts[1], CJFORUM_MIGRATION_NUM_USERS, $migrateAvatars, false) )
					{
						return false;
					}
					
					$count = $parts[1] + CJFORUM_MIGRATION_NUM_USERS;
					$this->setMessage('<i class="fa fa-users"></i> Migrating users now.. '.$count.' users migrated.');
				}
				break;
		}
		
		return true;
	}
	
	public function analyse()
	{
		$db = JFactory::getDbo();
		$steps = array();
		
		try 
		{
			$i = 0;
			$features = JFactory::getApplication()->input->getArray(array('features'=>'array'));
			if(empty($features['features']))
			{
				return false;
			}
			
			$modules = $tables = array();
			
			foreach ($features['features'] as $feature)
			{
				switch ($feature)
				{
					case 'topics':
						
						$steps[$i++] = 'categories.0';;
						$modules['topics'] = CJFORUM_MIGRATION_NUM_TOPICS;
						$tables['topics'] = '#__kunena_topics';
						
						try 
						{
							$query = 'truncate table #__cjforum_topics';
							$db->setQuery($query);
							$db->execute();
							
							$query = 'truncate table #__cjforum_replies';
							$db->setQuery($query);
							$db->execute();
							
							$query = 'delete from #__assets where name like \'com_cjforum.topic.%\'';
							$db->setQuery($query);
							$db->execute();
						}
						catch (Exception $e)
						{
							throw new Exception($e->getMessage());
						}
						
						break;
						
					case 'users':
						
						$steps[$i++] = 'ranks.0';;
						$modules['users'] = CJFORUM_MIGRATION_NUM_USERS;
						$tables['users'] = '#__kunena_users';
						break;
				}
			}
			
			foreach ($modules as $module=>$limit)
			{
				$query = $db->getQuery(true)->select('count(*)')->from($tables[$module]);
				$db->setQuery($query);
				$count = (int) $db->loadResult();
				
				if ($count > 0)
				{
					$start = 0;
					$steps[$i++] = $module.'.'.$start;
					
					while ($count > $start + $limit)
					{
						$start = $start + $limit;
						$steps[$i++] = $module.'.'.$start;
					}
				}
			}
			
			$json = json_encode($steps);
			if( JFile::write(JPATH_ROOT.'/tmp/migrate_kunena.json', $json) )
			{
				return true;
			}
		}
		catch (Exception $e)
		{
// 			var_dump($e->getMessage());
			return false;
		}
		
		return false;
	}
	
	private function migrateCategories()
	{
		// check if the categories are already upgraded
		$basePath = JPATH_ADMINISTRATOR . '/components/com_categories';
		require_once $basePath . '/models/category.php';
		
		jimport('joomla.application.categories');
		$jcats = JCategories::getInstance('CjForum');
		
		if(count($jcats->get(0)->getChildren()) > 0)
		{
			if(! JFile::exists(JPATH_ROOT.'/tmp/migrate_kunena_categories.json'))
			{
				throw new Exception('Categories are already migrated or created manually, please delete them to continue.');
			}
			else 
			{
				JFactory::getApplication()->enqueueMessage('Categories are already migrated, skipping them.');
				return true;
			}
		}
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('p1.id, p1.name, p1.alias, p1.description, p1.published, p1.parent_id')
			->from('#__kunena_categories p1')
			->join('left', '#__kunena_categories p2 on p1.parent_id = p2.id')
			->join('left', '#__kunena_categories p3 on p2.parent_id = p3.id')
			->join('left', '#__kunena_categories p4 on p3.parent_id = p4.id')
			->join('left', '#__kunena_categories p5 on p4.parent_id = p5.id')
			->order('p5.parent_id, p4.parent_id, p3.parent_id, p2.parent_id, p1.parent_id');
		
		$db->setQuery($query);
		$categories = $db->loadAssocList('id');
		
		if (!$categories)
		{
			throw new Exception('Unable to create categories!');
		}
		
		$migrated = array();
		
		foreach ($categories as $category)
		{
			$path = $category['alias']; 
			
			if($category['parent_id'] > 0 && !empty($categories[$category['parent_id']]))
			{
				$path = $categories[$category['parent_id']]['alias'].'/'.$path;
			}
			
			$parentId = $category['parent_id'] > 0 ? $migrated[$category['parent_id']]->id : 0;

			$newId = $this->addCategory($category, $path, $parentId);
			$migrated[$category['id']] = new stdClass();
			$migrated[$category['id']]->id = $newId;
			$migrated[$category['id']]->asset_id = (int) $jcats->get($newId)->asset_id;
			$migrated[$category['id']]->level = (int) $jcats->get($newId)->level;
		}
		
		$json = json_encode($migrated);
		
		if( JFile::write(JPATH_ROOT.'/tmp/migrate_kunena_categories.json', $json) )
		{
			return true;
		}
		
		return false;
	}
	
	private function migrateTopics($start, $limit = 10)
	{
		// first check if categories migrated
		if(! JFile::exists(JPATH_ROOT.'/tmp/migrate_kunena_categories.json'))
		{
			if (! $this->migrateCategories() || ! JFile::exists(JPATH_ROOT.'/tmp/migrate_kunena_categories.json'))
			{
				throw new Exception('Unable to migrate categories.');
			}
		}
		
		$categories = json_decode(file_get_contents(JPATH_ROOT.'/tmp/migrate_kunena_categories.json'), true);
		
		if(empty($categories))
		{
			throw new Exception('No categories found.');
		}
		
		try
		{
			$app = JFactory::getApplication();
			$db = JFactory::getDbo();
				
			$query = $db->getQuery(true)
				->select('a.id, a.subject title, a.category_id catid, a.hits, a.last_post_userid replied_by, a.last_post_time replied,'.
					'case a.hold when 0 then 1 else 0 end state, case a.ordering when 1 then 1 else 0 end featured, a.locked')
					->select('m.ip ip_address, m.time created, m.userid created_by')
					->select('mt.message introtext')
					->from('#__kunena_topics as a')
					->join('left', '#__kunena_messages as m on a.id = m.thread')
					->join('left', '#__kunena_messages_text as mt on m.id = mt.mesid')
					->where('m.parent = 0')
					->order('a.id');
				
			$db->setQuery($query, $start, $limit);
			$topics = $db->loadAssocList();

			if(empty($topics))
			{
				$app->enqueueMessage('No topics in this iteration, continue with next..');
				return false;
			}
			
			JLoader::import('joomla.application.component.model');
			JLoader::import('migratetopic', JPATH_COMPONENT_ADMINISTRATOR.'/models');
			$topicModel = JModelLegacy::getInstance( 'MigrateTopic', 'CjForumModel' );
			$replyModel = JModelLegacy::getInstance( 'Reply', 'CjForumModel' );
			
			$parser = new JBBCode\Parser();
			$parser->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());
			$parser->addCodeDefinitionSet(new JBBCode\CjCodeDefinitionSet());
			$topicIds = array(); 
				
			foreach ($topics as $topic)
			{
				$topic['alias'] = CjLibUtils::getUrlSafeString($topic['title']);
				$topic['parent_asset_id'] = !empty($categories[$topic['catid']]['asset_id']) ? $categories[$topic['catid']]['asset_id'] : 1;
				$topic['level'] = !empty($categories[$topic['catid']]['level']) ? $categories[$topic['catid']]['level'] + 1 : 2;
				$topic['catid'] = !empty($categories[$topic['catid']]['id']) ? $categories[$topic['catid']]['id'] : 0;
				$topic['created'] = JFactory::getDate($topic['created'])->toSql();
				$topic['replied'] = JFactory::getDate($topic['replied'])->toSql();
				$topic['language'] = !empty($topic['language']) ? $topic['language'] : '*';
				$topic['introtext'] = $parser->parse(nl2br(htmlspecialchars($topic['introtext'], ENT_COMPAT, 'UTF-8')))->getAsHtml();
				$topic['access'] = 1;
				
				if(! $topicModel->save($topic) )
				{
					$app->enqueueMessage($topicModel->getError());
					continue;
				}
				
				$topicIds[] = $topic['id'];
			}
			
			/************** REPLIES ****************/
			$query = $db->getQuery(true)
				->select('m.id, m.thread topic_id, m.time created, m.userid created_by, case m.hold when 0 then 1 else 0 end state, m.ip ip_address, mt.message description')
				->from('#__kunena_messages as m')
				->join('left', '#__kunena_messages_text as mt on m.id = mt.mesid')
				->where('m.parent != 0 and m.thread in ('.implode(',', $topicIds).')');
				
			$db->setQuery($query);
			$replies = $db->loadObjectList();
			
			if(count($replies))
			{
				for ($i = 0; $i < ceil(count($replies) / CJFORUM_MIGRATION_NUM_REPLIES); $i++)
				{
					$replyIds = array();
					$query = $db->getQuery(true)
						->insert('#__cjforum_replies')
						->columns('id, topic_id, description, state, created_by, created, access, ip_address');
					
					for ($num = 0; $num < CJFORUM_MIGRATION_NUM_REPLIES; $num++)
					{
						if(!isset($replies[$i * CJFORUM_MIGRATION_NUM_REPLIES + $num]))
						{
							break;
						}
						
						$reply = $replies[ $i * CJFORUM_MIGRATION_NUM_REPLIES + $num ];
						$reply->created = JFactory::getDate($reply->created)->toSql();
						$reply->description = $parser->parse(nl2br(htmlspecialchars($reply->description, ENT_COMPAT, 'UTF-8')))->getAsHtml();
						$reply->access = 1;
						
						$query->values(
								$reply->id.','.
								$db->q($reply->topic_id).','.
								$db->q($reply->description).','.
								$reply->state.','.
								$reply->created_by.','.
								$db->q($reply->created).','.
								$reply->access.','.
								$db->q($reply->ip_address)
						);
						
						$replyIds[] = $reply->id;
					}

					$query = $query->__toString();
					$query = $query .' on duplicate key update description = values (description)';
					
					$db->setQuery($query);
					$db->execute();
				}
			}
			
			return true;
		}
		catch (Exception $e)
		{
			throw new Exception($e->getMessage());
		}
	}
	
	private function migrateTopicsDeleted($start, $limit = 100)
	{
		// first check if categories migrated
		if(! JFile::exists(JPATH_ROOT.'/tmp/migrate_kunena_categories.json'))
		{
			if (! $this->migrateCategories() || ! JFile::exists(JPATH_ROOT.'/tmp/migrate_kunena_categories.json'))
			{
				throw new Exception('Unable to migrate categories.');
			}
		}
		
		$categories = json_decode(file_get_contents(JPATH_ROOT.'/tmp/migrate_kunena_categories.json'), true);
		
		if(empty($categories))
		{
			return false;
		}
		
		try 
		{
			$app = JFactory::getApplication();
			$db = JFactory::getDbo();
			
			$query = $db->getQuery(true)
				->select('a.id, a.subject title, a.category_id catid, a.hits, a.last_post_userid replied_by, a.last_post_time replied,'.
						'case a.hold when 0 then 1 else 0 end state, case a.ordering when 1 then 1 else 0 end featured, a.locked')
				->select('m.ip ip_address, m.time created, m.userid created_by')
				->select('mt.message introtext')
				->from('#__kunena_topics as a')
				->join('left', '#__kunena_messages as m on a.id = m.thread')
				->join('left', '#__kunena_messages_text as mt on m.id = mt.mesid')
				->where('m.parent = 0')
				->order('a.id');
			
			$db->setQuery($query, $start, $limit);
			$topics = $db->loadObjectList('id');
			
			if( empty($topics) )
			{
				// this might be because topics are over. So we can continue here.
				return true;
// 				throw new Exception('Topics could not be loaded.');
			}

			// first get all the replies as well
			$topicIds = array_keys($topics);
			
			$topicQuery = $db->getQuery(true)
				->insert('#__cjforum_topics')
				->columns('id, title, alias, introtext, state, locked, catid, created, created_by, access, hits, featured, language, replied, replied_by, ip_address');
			
			$assetQuery = $db->getQuery(true)
				->insert('#__assets')
				->columns('parent_id, level, name, title, rules');
			
			$parser = new JBBCode\Parser();
			$parser->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());
			$parser->addCodeDefinitionSet(new JBBCode\CjCodeDefinitionSet());
			
			foreach ($topics as $topic)
			{
				$topic->alias = CjLibUtils::getUrlSafeString($topic->title);
				$topic->parent_asset_id = !empty($categories[$topic->catid]['asset_id']) ? $categories[$topic->catid]['asset_id'] : 1;
				$topic->level = !empty($categories[$topic->catid]['level']) ? $categories[$topic->catid]['level'] + 1 : 2;
				$topic->catid = !empty($categories[$topic->catid]['id']) ? $categories[$topic->catid]['id'] : 0;
				$topic->created = JFactory::getDate($topic->created);
				$topic->replied = JFactory::getDate($topic->replied);
				$topic->language = !empty($topic->language) ? $topic->language : '*';
				$topic->introtext = $parser->parse(nl2br(htmlspecialchars($topic->introtext, ENT_COMPAT, 'UTF-8')))->getAsHtml();
				$topic->access = 1;
				
				$topicQuery->values(
						$topic->id.','.
						$db->q($topic->title).','.
						$db->q($topic->alias).','.
						$db->q($topic->introtext).','.
						$topic->state.','.
						$topic->locked.','.
						$topic->catid.','.
						$db->q($topic->created).','.
						$topic->created_by.','.
						$topic->access.','.
						$topic->hits.','.
						$topic->featured.','.
						$db->q($topic->language).','.
						$db->q($topic->replied).','.
						$topic->replied_by.','.
						$db->q($topic->ip_address)
				);
				
				$assetQuery->values(
						$topic->parent_asset_id . ','.
						$topic->level.','.
						$db->q('com_cjforum.topic.'.$topic->id).','.
						$db->q($topic->title).','.
						$db->q('{"core.delete":[],"core.edit":[],"core.edit.state":[],"core.reply":[],"core.auto.reply":[],"core.vote":[]}')
				);
			}
			
			$topicQuery = $topicQuery->__toString();;
			$topicQuery = $topicQuery . ' on duplicate key update introtext = values (introtext)';
			
			$db->setQuery($topicQuery);
			
			if(!$db->execute())
			{
				throw new Exception('Unable to insert topics: Error: 1');
			}
			
			$assetQuery = $assetQuery->__toString();
			$assetQuery = $assetQuery . ' on duplicate key update name = values (name)';
			
			$db->setQuery($assetQuery);
			if(!$db->execute())
			{
				throw new Exception('Unable to insert assets: Error: 2');
			}
			
// 			/************ ATTACHMENTS ***************/
			
// 			$query = $db->getQuery(true)
// 				->insert('#__cjforum_attachments')
// 				->columns('id, post_id, post_type, created_by, hash, folder, filesize, filetype, filename')
// 				->values(
// 						$db->getQuery(true)
// 							->select('a.id, m.thread post_id, 1 post_type, a.userid created_by, a.hash, a.size, a.folder, a.filetype, a.filename')
// 							->from('#__kunena_attachments AS a')
// 							->join('inner', '#__kunena_messages AS m on a.mesid = m.id')
// 							->where('m.thread in ('.implode(',', $topicIds).')')
// 				);
			
// 			$db->setQuery($query);
// 			$db->execute();
			
			/************** REPLIES ****************/
			$query = $db->getQuery(true)
				->select('m.id, m.thread topic_id, m.time created, m.userid created_by, case m.hold when 0 then 1 else 0 end state, m.ip ip_address, mt.message description')
				->from('#__kunena_messages as m')
				->join('left', '#__kunena_messages_text as mt on m.id = mt.mesid')
				->where('m.parent != 0 and m.thread in ('.implode(',', $topicIds).')');
			
			$db->setQuery($query);
			$replies = $db->loadObjectList();
				
			if(count($replies))
			{
				for ($i = 0; $i < ceil(count($replies) / CJFORUM_MIGRATION_NUM_REPLIES); $i++)
				{
					$replyIds = array();
					$query = $db->getQuery(true)
						->insert('#__cjforum_replies')
						->columns('id, topic_id, description, state, created_by, created, access, ip_address');
					
					for ($num = 0; $num < CJFORUM_MIGRATION_NUM_REPLIES; $num++)
					{
						if(!isset($replies[$i * CJFORUM_MIGRATION_NUM_REPLIES + $num]))
						{
							break;
						}
						
						$reply = $replies[$i*CJFORUM_MIGRATION_NUM_REPLIES + $num];
						$reply->created = JFactory::getDate($reply->created)->toSql();
						$reply->description = $parser->parse(nl2br(htmlspecialchars($reply->description, ENT_COMPAT, 'UTF-8')))->getAsHtml();
						$reply->access = 1;
						
						$query->values(
								$reply->id.','.
								$db->q($reply->topic_id).','.
								$db->q($reply->description).','.
								$reply->state.','.
								$reply->created_by.','.
								$db->q($reply->created).','.
								$reply->access.','.
								$db->q($reply->ip_address)
						);
						
						$replyIds[] = $reply->id;
					}

					$query = $query->__toString();
					$query = $query .' on duplicate key update description = values (description)';
					
					$db->setQuery($query);
					$db->execute();
					
// 					$attachmentQuery2 = $db->getQuery(true)
// 						->insert('#__cjforum_attachments')
// 						->columns('id, post_id, post_type, created_by, hash, folder, filesize, filetype, filename')
// 						->values(
// 								$db->getQuery(true)
// 								->select('a.id, a.mesid post_id, 2 post_type, a.userid created_by, a.hash, a.size, a.folder, a.filetype, a.filename')
// 								->from('#__kunena_attachments AS a')
// 								->where('a.mesid in ('.implode(',', $replyIds).')')
// 						);
					
// 					$db->setQuery($query);
// 					$db->execute();
				}
			}

// 			JLoader::import('joomla.application.component.model');
// 			JLoader::import('topic', JPATH_COMPONENT_ADMINISTRATOR.'/models');
// 			$topicModel = JModelLegacy::getInstance( 'topic', 'CjForumModel' );
// 			$replyModel = JModelLegacy::getInstance( 'reply', 'CjForumModel' );

// 			foreach ($topics as $topicId=>$topic)
// 			{
// 				$topic['created'] = JFactory::getDate($topic['created'])->toSql();
// 				$topic['introtext'] = CJFunctions::parse_html($topic['introtext'], false, true, true);
// 				$topic['alias'] = JApplicationHelper::stringURLSafe($topic['title']);
// 				$topic['catid'] = isset($categories->$topic['catid']) ? $categories->$topic['catid'] : 0;
// 				$topic['language'] = '*';
				
// 				if(! $topicModel->save($topic) )
// 				{
// 					$app->enqueueMessage($topicModel->getError());
// 				}
				
// 				$id = (int) $topicModel->getState($topicModel->getName() . '.id');
				
// 				foreach ($replies as $reply)
// 				{
// 					if($reply['topic_id'] == $topicId)
// 					{
// 						$reply['created'] = JFactory::getDate($reply['created'])->toSql();
// 						$reply['description'] = CJFunctions::parse_html($reply['description'], false, true, true);
// 						$reply['topic_id'] = $id;
						
// 						if(! $replyModel->save($reply) )
// 						{
// 							$app->enqueueMessage($replyModel->getError());
// 						}
						
// 						$replyModel->setState($replyModel->getName() . '.id', null);
// 					}
// 				}
				
// 				$topicModel->setState($topicModel->getName() . '.id', null);
// 			}
			
			return true;
		}
		catch (Exception $e)
		{
			throw new Exception($e->getMessage());
		}
	}
	
	private function migrateRanks($force = false)
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		
		try 
		{
			if(! JFile::exists(JPATH_ROOT.'/tmp/migrate_kunena_ranks.json'))
			{
				// check if ranks are already migrated, if yes, do not migrate
				$query = $db->getQuery(true)->select('count(*)')->from('#__cjforum_ranks');
				$db->setQuery($query);
				$count = (int)$db->loadResult();
				
				if($count > 0)
				{
					throw new Exception('Ranks are already migrated or created manually. First delete them to start migration.');
				}
			}
			else 
			{
				// file exists means the ranks are already migrated. skip this step now.
				return true;
			}
			
			$query = $db->getQuery(true)
				->select('a.rank_id id, a.rank_title title, a.rank_min min_posts, a.rank_special rank_type, a.rank_image')
				->from('#__kunena_ranks AS a');
			
			$db->setQuery($query);
			$ranks = $db->loadAssocList();
			
			$migrated = array();
			if(empty($ranks)) return true;
			
			JLoader::import('joomla.application.component.model');
			JLoader::import('topic', JPATH_COMPONENT_ADMINISTRATOR.'/models');
			$rankModel = JModelLegacy::getInstance( 'rank', 'CjForumModel' );
			JFolder::create(JPATH_ROOT.'/images/ranks/default/');
			
			foreach ($ranks as $rank)
			{
				$id = $rank['id'];
				$rank['id'] = 0;
				$rank['language'] = '*';
				JFile::move(JPATH_ROOT.'/media/kunena/ranks/'.$rank['rank_image'], JPATH_ROOT.'/images/ranks/default/'.$rank['rank_image']);
				$rank['rank_image'] = '/images/ranks/default/'.$rank['rank_image'];
				
				if(! $rankModel->save($rank) )
				{
					$app->enqueueMessage($rankModel->getError());
				}
				
				$migrated[$id] = (int) $rankModel->getState($rankModel->getName() . '.id');
				$rankModel->setState($rankModel->getName() . '.id', null);
			}
					
			$json = json_encode($migrated);
			
			if( JFile::write(JPATH_ROOT.'/tmp/migrate_kunena_ranks.json', $json) )
			{
				// truncate users table as it is going to get migrated.
				$query = 'truncate table #__cjforum_users';
				$db->setQuery($query);
				$db->execute();
				
				return true;
			}
		}
		catch (Exception $e)
		{
			throw new Exception($e->getMessage());
		}
		
		return false;
	}
	
	private function migrateUsers($start, $limit = 250, $migrateAvatars = false, $migratePoints = false)
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		
		require_once CJLIB_PATH.'/framework/class.upload.php';
		$sizes = array(16, 32, 48, 64, 96, 128, 160, 192, 256);
		
		// first check if categories migrated
		if(! JFile::exists(JPATH_ROOT.'/tmp/migrate_kunena_ranks.json'))
		{
			if (! $this->migrateRanks() || ! JFile::exists(JPATH_ROOT.'/tmp/migrate_kunena_ranks.json'))
			{
				throw new Exception('Unable to migrate ranks, users migration cannot continue.');
			}
		}
		
		$ranks = json_decode(file_get_contents(JPATH_ROOT.'/tmp/migrate_kunena_ranks.json'), true);

		if(empty($ranks))
		{
			$app->enqueueMessage('Unable to get ranks upgrade data. Migration cannot continue.');
			return false;
		}
		
		try
		{
			$parser = new JBBCode\Parser();
			$parser->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());
			$parser->addCodeDefinitionSet(new JBBCode\CjCodeDefinitionSet());
			
			$query = $db->getQuery(true)
				->select('u.id, u.username AS handle')
				->select('ku.avatar, ku.rank, ku.thankyou, ku.birthdate birthday, ku.uhits hits, ku.signature, ku.gender, ku.location, ku.banned')
				->select('ku.twitter, ku.facebook, ku.linkedin, ku.flickr, ku.bebo, ku.skype')
				->from('#__users AS u')
				->join('left', '#__kunena_users AS ku on u.id = ku.userid');
			
			$db->setQuery($query, $start, $limit);
			$users = $db->loadObjectList('id');
			
			if (empty($users))
			{
				$app->enqueueMessage($db->getErrorMsg());
				return true;
			}
			
			$query = $db->getQuery(true)
				->insert('#__cjforum_users')
				->columns('id, handle, avatar, rank, thankyou, birthday, hits, signature, gender, location, banned, twitter, facebook, linkedin, flickr, bebo, skype');
			
			foreach ($users as $user)
			{
				if(!empty($user->avatar) && JFile::exists(JPATH_ROOT.'/media/kunena/avatars/'.$user->avatar) && $migrateAvatars)
				{
					$file_path = JPATH_ROOT.'/media/kunena/avatars/'.$user->avatar;
					$success = true;
					$avatarName = CjLibUtils::getRandomKey();
					
					foreach ($sizes as $size)
					{
						$handle = new thumnail_upload($file_path);
						$handle->file_overwrite = true;
						$handle->file_auto_rename = false;
						$handle->image_convert = 'jpg';
						$handle->jpeg_quality = 80;
						$handle->image_resize = true;
						$handle->image_x = $size;
						$handle->image_y = $size;
						$handle->file_new_name_body = $avatarName;
						$handle->process(CF_AVATAR_BASE_DIR.'size-'.$size.'/');
							
						if (!$handle->processed) 
						{
							$app->enqueueMessage('Avatar conversion failed. User ID: '.$user->id);
							$success = false;
						}
					}
					
					if($success)
					{
						$user->avatar = $avatarName.'.jpg';
					}
					else 
					{
						$user->avatar = '';	
					}
				}
				else 
				{
					$user->avatar = '';
				}
				
				$user->signature = $parser->parse(nl2br(htmlspecialchars($user->signature, ENT_COMPAT, 'UTF-8')))->getAsHtml();
				
				$query->values(
					$user->id.','.
					$db->q(str_replace('-', '_', $user->handle)).','.
					$db->q($user->avatar).','.
					(isset($ranks[$user->rank]) ? $ranks[$user->rank] : 0 ).','.
					$user->thankyou.','.
					$db->q($user->birthday).','.
					$user->hits.','.
					$db->q($user->signature).','.
					$user->gender.','.
					$db->q($user->location).','.
					$db->q($user->banned).','.
					$db->q($user->twitter).','.
					$db->q($user->facebook).','.
					$db->q($user->linkedin).','.
					$db->q($user->flickr).','.
					$db->q($user->bebo).','.
					$db->q($user->skype)
				);
			}
			
			$query = $query->__toString();;
			$query = $query . ' on duplicate key update avatar = values (avatar), signature = values(signature)';
			
			$db->setQuery($query);
			
			if($db->execute())
			{
				return true;
			}
		}
		catch (Exception $e)
		{
			throw new Exception($e->getMessage());
		}
		
		return true;
	}

	public function getTable ($type = 'Topic', $prefix = 'CjForumTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
	private function addCategory($node, $path = '', $parent_id = 0)
	{
		$config = array( 'table_path' => JPATH_ADMINISTRATOR.'/components/com_categories/tables');
		$model = new CategoriesModelCategory( $config );
		
		// make sure the alias does not starts with a number, otherwise it will fail in routing.
		$parts = explode('-', $node['alias']);
		
		if(isset($parts[0]) && is_numeric($parts[0]))
		{
			$node['alias'] = 'c'.$node['alias'];
		}

		$data = array(
				'id' => 0,
				'parent_id' => $parent_id,
				'path' => $path.$node['alias'],
				'extension' => 'com_cjforum',
				'title' => $node['name'],
				'alias' => $node['alias'],
				'description' => $node['description'],
				'published' => $node['published'],
				'language' => '*');
			
		$status = $model->save( $data );

		if(!$status)
		{
			throw new Exception('Unable to create category!');
		}
		
		$newid = $model->getState($model->getName().'.id');
		return $newid;
	}
	
	public function syncTopics()
	{
		$db = JFactory::getDbo();
		
		try 
		{
			$query = 'update #__cjforum_topics AS a set a.asset_id = (select id from #__assets b where b.name = concat(\'com_cjforum.topic.\', a.id) )';
			$db->setQuery($query);
			$db->execute();
			
			$query = 'update #__cjforum_topics AS a set a.replies = (select count(*) from #__cjforum_replies AS b where b.topic_id = a.id group by b.topic_id)';
			$db->setQuery($query);
			$db->execute();
			
			$query = $db->getQuery(true)
				->insert('#__cjforum_attachments')
				->columns('id, created_by, hash, folder, filesize, filetype, filename, post_id, post_type')
				->values(
					$db->getQuery(true)
						->select('a.id, a.userid created_by, a.hash, a.folder, a.size, a.filetype, a.filename')
						->select('case m.parent when 0 then m.id else m.parent end post_id')
						->select('case m.parent when 0 then 1 else 2 end post_type')
						->from('#__kunena_attachments AS a')
						->join('inner', '#__kunena_messages AS m on a.mesid = m.id')
				);

			$query = $query->__toString();;
			$query = $query . ' on duplicate key update filename = values (filename)';

			$db->setQuery($query);
			$db->execute();
			
			$query = $db->getQuery(true)
				->insert('#__cjforum_thankyou')
				->columns('item_id, item_type, created_by, created, assigned_to')
				->values(
					$db->getQuery(true)
						->select('a.postid, case m.parent when 0 then 1 else 2 end, a.userid, a.time, a.targetuserid')
						->from('#__kunena_thankyou AS a')
						->join('left', '#__kunena_messages AS m on m.id = a.postid')
				);
			
			$query = $query->__toString();;
			$query = $query . ' on duplicate key update assigned_to = values (assigned_to)';

			$db->setQuery($query);
			$db->execute();
			
			return true;
		}
		catch (Exception $e)
		{
			throw new Exception('Unable to do final steps! Error: '.$db->getErrorMsg());
		}
		
		return false;
	}
	
	public function syncUsers()
	{
		$db = JFactory::getDbo();
		
		try
		{
			// now update posts and replies counts
			$query = $db->getQuery(true)
				->update('#__cjforum_users A')
				->join('inner', '(select created_by, count(*) as topics from #__cjforum_topics group by created_by) B on B.created_by = A.id')
				->set('A.topics = B.topics');
				
			$db->setQuery($query);
			$db->execute();
				
			$query = $db->getQuery(true)
				->update('#__cjforum_users A')
				->join('inner', '(select created_by, count(*) as replies from #__cjforum_replies group by created_by) B on B.created_by = A.id')
				->set('A.replies = B.replies');
				
			$db->setQuery($query);
			$db->execute();
			
			return true;
		}
		catch (Exception $e)
		{
			throw new Exception('Unable to do final steps! Error: '.$db->getErrorMsg());
		}
		
		return false;
	}
}