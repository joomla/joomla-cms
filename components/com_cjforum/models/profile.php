<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumModelProfile extends JModelItem
{
	protected $_context = 'com_cjforum.topic';

	protected function populateState ()
	{
		$app = JFactory::getApplication('site');
		
		// Load state from the request.
		$pk = $app->input->getInt('uId', $app->input->getInt('id'));
		$this->setState('profile.id', $pk);
		
		$offset = $app->input->getUInt('limitstart');
		$this->setState('list.offset', $offset);
		
		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
		
		// TODO: Tune these values based on other permissions.
		$user = JFactory::getUser();
		
		if ((! $user->authorise('core.edit.state', 'com_cjforum')) && (! $user->authorise('core.edit', 'com_cjforum')))
		{
			$this->setState('filter.published', 1);
		}
	}

	public function getItem ($pk = null)
	{
		$user = JFactory::getUser();
		if(!$pk)
		{
			$pk = (int) $this->getState('profile.id');
			$pk = $pk > 0 ? $pk : $user->id;
			$this->setState('profile.id', $pk);
		}
		
		if ($this->_item === null)
		{
			$this->_item = array();
		}
		
		if (! isset($this->_item[$pk]))
		{
			try
			{
				$db = $this->getDbo();
				
				$query = $db->getQuery(true)
					->select('u.id, u.name, u.username, u.email, u.block, u.registerDate, u.lastvisitDate, u.sendEmail, u.activation, u.params')
					->from('#__users AS u');
				
				$query->select(
						$this->getState('item.select', 
								'a.about, a.handle, a.topics, a.replies, a.avatar, a.rank, a.fans, a.thankyou, a.birthday, a.hits, a.points, a.last_post_time, a.banned,'.
								'a.gender, a.location, a.twitter, a.facebook, a.gplus, a.linkedin, a.flickr, a.bebo, a.skype, a.attribs, a.metadesc, a.metadata'));
				
				$query->join('left', '#__cjforum_users AS a on a.id = u.id');
				
				$query
					->select('r.title as rank_title')
					->join('left', '#__cjforum_ranks AS r on a.rank = r.id');
				
				$query->where("u.id = ".$pk);
				$db->setQuery($query);
// 				echo $query->dump();jexit();
				
				$data = $db->loadObject();
				
				if (empty($data))
				{
					return JError::raiseError(404, JText::_('COM_CJFORUM_ERROR_USER_NOT_FOUND'));
				}

				// Convert parameter fields to objects.
				$registry = new JRegistry();
				$registry->loadString($data->attribs);
				$params = clone $this->getState('params');
				
				if($params)
				{
					$data->params = clone $params;
				}
				else
				{
					$data->params =  new JRegistry();
				}
				
				$data->params->merge($registry);
				
				$registry = new JRegistry();
				$registry->loadString($data->metadata);
				$data->metadata = $registry;
				
				// Extract custom profile fields
				$fields = $data->params->toArray();
				$data->fields = new JRegistry();
				foreach($fields as $key => $value)
				{
					if(strpos($key, 'profile_field_') === 0)
					{
						$data->fields->def($key, $value);
					}
				}
				
				// Technically guest could edit an topic, but lets not check
				// that to improve performance a little.
				if (! $user->get('guest'))
				{
					$userId = $user->get('id');
					$asset = 'com_cjforum';
					
					// Check general edit permission first.
					if ($user->authorise('core.edit', $asset))
					{
						$data->params->set('access-edit', true);
					}
					// Now check if edit.own is available.
					elseif (! empty($userId) && $user->authorise('core.edit.own', $asset))
					{
						// Check for a valid user and that they are the owner.
						if ($userId == $data->id)
						{
							$data->params->set('access-edit', true);
						}
					}
					
					// Check general edit state permission first.
					if ($user->authorise('core.edit.state', $asset))
					{
						$data->params->set('access-edit-state', true);
					}
					// Now check if edit.state.own is available.
					elseif (! empty($userId) && $user->authorise('core.edit.state.own', $asset))
					{
						// Check for a valid user and that they are the owner.
						if ($userId == $data->id)
						{
							$data->params->set('access-edit-state', true);
						}
					}
				}
				
				// Compute view access permissions.
				if ($access = $this->getState('filter.access'))
				{
					// If the access filter has been set, we already know this user can view.
					$data->params->set('access-view', true);
				}
				
				$this->_item[$pk] = $data;
			}
			catch (Exception $e)
			{
				if ($e->getCode() == 404)
				{
					// Need to go thru the error handler to allow Redirect to work.
					JError::raiseError(404, $e->getMessage());
				}
				else
				{
					$this->setError($e);
					$this->_item[$pk] = false;
				}
			}
		}

		return $this->_item[$pk];
	}

	public function hit ($pk = 0)
	{
		$input = JFactory::getApplication()->input;
		$hitcount = $input->getInt('hitcount', 1);
		
		if ($hitcount)
		{
			$pk = (! empty($pk)) ? $pk : (int) $this->getState('profile.id');
			
			$table = JTable::getInstance('Profile', 'CjForumTable');
			$table->load($pk);
			$table->hit($pk);
		}
		
		return true;
	}
	
	public function getSummary()
	{
		$db = JFactory::getDbo();
		$summary = new stdClass();
		$pk = (int) $this->getState('profile.id', JFactory::getUser()->id);
		
		$topics = $this->getTopics(5);
		$summary->topics = $topics->items;

		$favorites = $this->getFavorites(5);
		$summary->favorites = $favorites->items;

		$reputation = $this->getReputation(5);
		$summary->reputation = $reputation->items;

		$activities = $this->getActivity(5);
		$summary->activities = $activities->items;
		
		$query = $db->getQuery(true)
			->select('r.topic_id, r.id')
			->select('a.title, a.alias, a.language, a.catid, concat(a.id, \':\', a.alias) slug')
			->select('c.title as category_title, c.alias as category_alias, concat(c.id, \':\', c.alias) catslug')
			->from('#__cjforum_replies AS r')
			->join('left', '#__cjforum_topics AS a ON a.id = r.topic_id')
			->join('left', '#__categories AS c on a.catid = c.id')
			->where('r.created_by = '.$pk.' and r.state = 1 and a.state = 1')
			->group('r.topic_id')
			->order('r.created desc');
		
		$db->setQuery($query, 0, 5);
		$summary->replies = $db->loadObjectList();
		
		return $summary;
	}
	
	public function getTopics($limit = 20)
	{
		$return = new stdClass();
		$router = JFactory::getApplication()->getRouter();
		$pk = $this->getState('profile.id');
		
		JLoader::import('topics', JPATH_COMPONENT.'/models');
		$model = JModelLegacy::getInstance( 'topics', 'CjForumModel' );
		
		$state = $model->getState(); // access the state first so that it can be modified
		$model->setState('filter.author_id', $pk);
		$model->setState('list.ordering', 'a.created');
		$model->setState('list.direction', 'desc');
		$model->setState('list.limit', $limit);
		$return->items = $model->getItems();
		
// 		$router->setVars(array('id'=>$this->_item[$pk]->handle));
		$return->pagination = $model->getPagination();
		$return->pagination->setAdditionalUrlParam('id', $pk);
		
		return $return;
	}
	
	public function getFavorites($limit = 20)
	{
		$return = new stdClass();
		$router = JFactory::getApplication()->getRouter();
		$pk = $this->getState('profile.id');
		
		JLoader::import('topics', JPATH_COMPONENT.'/models');
		$model = JModelLegacy::getInstance( 'topics', 'CjForumModel' );
		
		$state = $model->getState(); // access the state first so that it can be modified
		$model->setState('filter.author_id', $pk);
		$model->setState('filter.favored', 1);
		$model->setState('list.ordering', 'a.created');
		$model->setState('list.direction', 'desc');
		$model->setState('list.limit', $limit);
		$return->items = $model->getItems();
		
// 		$router->setVars(array('id'=>$this->_item[$pk]->handle));
		$return->pagination = $model->getPagination();
		$return->pagination->setAdditionalUrlParam('id', $pk);
		
		return $return;
	}
	
	public function getReputation($limit = 20)
	{
		$return = new stdClass();
		$router = JFactory::getApplication()->getRouter();
		$pk = $this->getState('profile.id');
		
		JLoader::import('reputation', JPATH_COMPONENT.'/models');
		$model = JModelLegacy::getInstance( 'reputation', 'CjForumModel' );
		
		$state = $model->getState(); // access the state first so that it can be modified
		$model->setState('filter.author_id', $pk);
		$model->setState('list.ordering', 'a.created');
		$model->setState('list.direction', 'desc');
		$model->setState('list.limit', $limit);
		$return->items = $model->getItems();
		
// 		$router->setVars(array('id'=>$this->_item[$pk]->handle));
		$return->pagination = $model->getPagination();
		$return->pagination->setAdditionalUrlParam('id', $pk);
		
		return $return;
	}
	
	public function getActivity($limit = 20)
	{
		$return = new stdClass();
		$router = JFactory::getApplication()->getRouter();
		$pk = $this->getState('profile.id');
	
		JLoader::import('activities', JPATH_COMPONENT.'/models');
		$model = JModelLegacy::getInstance( 'activities', 'CjForumModel' );
	
		$state = $model->getState(); // access the state first so that it can be modified
		$model->setState('filter.author_id', $pk);
		$model->setState('list.ordering', 'a.created');
		$model->setState('list.direction', 'desc');
		$model->setState('list.limit', $limit);
		$return->items = $model->getItems();
	
		// 		$router->setVars(array('id'=>$this->_item[$pk]->handle));
		$return->pagination = $model->getPagination();
		$return->pagination->setAdditionalUrlParam('id', $pk);
	
		return $return;
	}
	
	public function getArticles($limit = 10)
	{
		$return = new stdClass();
		$router = JFactory::getApplication()->getRouter();
		$pk = $this->getState('profile.id');
		
		JLoader::import('articles', JPATH_ROOT.'/components/com_content/models');
		$model = JModelLegacy::getInstance( 'articles', 'ContentModel' );
		
		$state = $model->getState(); // access the state first so that it can be modified
		$model->setState('filter.author_id', $pk);
		$model->setState('list.ordering', 'a.created');
		$model->setState('list.direction', 'desc');
		$model->setState('list.limit', $limit);
		$return->items = $model->getItems();
		
// 		$router->setVars(array('id'=>$this->_item[$pk]->handle));
		$return->pagination = $model->getPagination();
		$return->pagination->setAdditionalUrlParam('id', $pk);
		
		return $return;
	}
	
	public function getQuestions($limit = 20)
	{
		$return = new stdClass();
		$router = JFactory::getApplication()->getRouter();
		$pk = $this->getState('profile.id');
		
		JLoader::import('questions', JPATH_ROOT.'/components/com_communityanswers/models');
		$model = JModelLegacy::getInstance( 'questions', 'CommunityAnswersModel' );
		
		$state = $model->getState(); // access the state first so that it can be modified
		$model->setState('filter.author_id', $pk);
		$model->setState('list.ordering', 'a.created');
		$model->setState('list.direction', 'desc');
		$model->setState('list.limit', $limit);
		$return->items = $model->getItems();
		
// 		$router->setVars(array('id'=>$this->_item[$pk]->handle));
		$return->pagination = $model->getPagination();
		$return->pagination->setAdditionalUrlParam('id', $pk);
		
		return $return;
	}

	public function getQuizzes($limit = 20)
	{
	    $return = new stdClass();
	    $router = JFactory::getApplication()->getRouter();
	    $pk = $this->getState('profile.id');
	
	    JLoader::import('quizzes', JPATH_ROOT.'/components/com_communityquiz/models');
	    $model = JModelLegacy::getInstance( 'quizzes', 'CommunityQuizModel' );
	
	    $state = $model->getState(); // access the state first so that it can be modified
	    $model->setState('filter.author_id', $pk);
	    $model->setState('list.ordering', 'a.created');
	    $model->setState('list.direction', 'desc');
	    $model->setState('list.limit', $limit);
	    $return->items = $model->getItems();
	
	    // 		$router->setVars(array('id'=>$this->_item[$pk]->handle));
	    $return->pagination = $model->getPagination();
	    $return->pagination->setAdditionalUrlParam('id', $pk);
	
	    return $return;
	}

	public function getSurveys($limit = 20)
	{
		$return = new stdClass();
		$router = JFactory::getApplication()->getRouter();
		$pk = $this->getState('profile.id');
	
		JLoader::import('surveys', JPATH_ROOT.'/components/com_communitysurveys/models');
		$model = JModelLegacy::getInstance( 'surveys', 'CommunitySurveysModel' );
	
		$state = $model->getState(); // access the state first so that it can be modified
		$model->setState('filter.author_id', $pk);
		$model->setState('list.ordering', 'a.created');
		$model->setState('list.direction', 'desc');
		$model->setState('list.limit', $limit);
		$return->items = $model->getItems();
	
		// 		$router->setVars(array('id'=>$this->_item[$pk]->handle));
		$return->pagination = $model->getPagination();
		$return->pagination->setAdditionalUrlParam('id', $pk);
	
		return $return;
	}
	
	public function getPolls($limit = 20)
	{
		$return = new stdClass();
		$router = JFactory::getApplication()->getRouter();
		$pk = $this->getState('profile.id');
		
		JLoader::import('polls', JPATH_ROOT.'/components/com_communitypolls/models');
		$model = JModelLegacy::getInstance( 'polls', 'CommunityPollsModel' );
		
		$state = $model->getState(); // access the state first so that it can be modified
		$model->setState('filter.author_id', $pk);
		$model->setState('list.ordering', 'a.created');
		$model->setState('list.direction', 'desc');
		$model->setState('list.limit', $limit);
		$return->items = $model->getItems();
		
// 		$router->setVars(array('id'=>$this->_item[$pk]->handle));
		$return->pagination = $model->getPagination();
		$return->pagination->setAdditionalUrlParam('id', $pk);
		
		return $return;
	}
}