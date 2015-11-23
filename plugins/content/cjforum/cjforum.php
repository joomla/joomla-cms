<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_BASE') or die;

require_once JPATH_ROOT.'/components/com_cjlib/framework/api.php';
require_once JPATH_ROOT.'/components/com_cjforum/helpers/route.php';
require_once JPATH_ROOT.'/components/com_content/helpers/route.php';

class plgContentCjforum extends JPlugin
{
	protected $autoloadLanguage = true;
	
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}
	
	public function onContentPrepare($context, &$topic, &$params, $page = 0)
	{
		$app = JFactory::getApplication();
		$menu = $app->getMenu();
		$language = JFactory::getLanguage();
		$language->load('com_cjforum');
	
		if (($menu->getActive() == $menu->getDefault()) || ($context != 'com_cjforum.topic' && $context != 'com_cjforum.reply' && $context != 'com_content.article') || $page > 0 || empty($topic->id)) 
		{
			return true;
		}
		
		if($context == 'com_cjforum.topic' || $context == 'com_cjforum.reply')
		{
			$topic->text = preg_replace_callback('/\{CJATTACHMENT(.*?)\}/', 'replaceAttachmentTags', $topic->text);
		}

		if($context == 'com_content.article')
		{
			$appParams = JComponentHelper::getParams('com_cjforum');
			$pointsApp = $appParams->get('points_component', 'cjforum');
			
			if($pointsApp == 'cjforum')
			{
				$user = JFactory::getUser();
				$api = new CjLibApi();
		
				$topicUrl = ContentHelperRoute::getArticleRoute($topic->id.':'.$topic->alias, $topic->catid.':'.$topic->category_alias);
				$topicLink = JHtml::link($topicUrl, CjLibUtils::escape($topic->title));
					
				$title = $description = JText::sprintf('COM_CJFORUM_POINTS_READING_ARTICLE', $topicLink, array('jsSafe'=>true));
				$options = array('function'=>'com_content.read', 'reference'=>$topic->id, 'info'=>$description, 'component'=>'com_content', 'title'=>$title);
				$api->awardPoints($pointsApp, $user->id, $options);
			}
		}
	}
	
	public function onContentAfterSave($context, $article, $isNew)
	{
		if ( ($context != 'com_content.form' && $context != 'com_content.article') || !$article->id || !$isNew || $article->state != 1)
		{
			return true;
		}
	
		$api = new CjLibApi();
		$user = JFactory::getUser($article->created_by);
		$language = JFactory::getLanguage();
	
		$params = JComponentHelper::getParams('com_cjforum');
		$streamApp = $params->get('stream_component', 'cjforum');
		$pointsApp = $params->get('points_component', 'cjforum');
		$profileApp = $params->get('profile_component', 'cjforum');
		$language->load('com_cjforum');
	
		$articleUrl = ContentHelperRoute::getArticleRoute($article->id.($article->alias ? ':'.$article->alias : ''), $article->catid.($article->category_alias ? ':'.$article->category_alias : ''));
		$articleLink = JHtml::link($articleUrl, CjLibUtils::escape($article->title));
	
		if($pointsApp == 'cjforum') // other components, we need not care
		{
			$title = $description = JText::sprintf('COM_CJFORUM_POINTS_NEW_ARTICLE', $articleLink, array('jsSafe'=>true));
			$options = array('function'=>'com_content.create', 'reference'=>$article->id, 'info'=>$description, 'component'=>'com_content', 'title'=>$title);
			$api->awardPoints($pointsApp, $article->created_by, $options);
		}
	
		if($streamApp == 'cjforum')
		{
			$userName = $api->getUserProfileUrl($profileApp, $article->created_by, false, $user->name);
				
			$activity = new stdClass();
			$activity->type = 'com_content.create';
			$activity->href = $articleUrl;
			$activity->title = JText::sprintf('COM_CJFORUM_ACTIVITY_NEW_ARTICLE', $userName, $articleLink);;
			$activity->description = $article->introtext;
			$activity->userId = $article->created_by;
			$activity->featured = 0;
			$activity->language = $language->getTag();
			$activity->itemId = $article->id;
			$activity->parentId = $article->catid;
			$activity->length = $params->get('max_description_length', 0);
				
			$api->pushActivity($streamApp, $activity);
		}
	}
	
	public function onContentBeforeDelete($context, $article)
	{
		if ($context != 'com_content.article')
		{
			return true;
		}
	
		$api = new CjLibApi();
		$language = JFactory::getLanguage();
	
		$params = JComponentHelper::getParams('com_cjforum');
		$pointsApp = $params->get('points_component', 'cjforum');
		$language->load('com_cjforum');
	
		if($pointsApp == 'cjforum') // other components, we need not care
		{
			$title = $description = JText::sprintf('COM_CJFORUM_POINTS_DELETED_ARTICLE', $article->title, array('jsSafe'=>true));
			$options = array('function'=>'com_content.delete', 'reference'=>$article->id, 'info'=>$description, 'component'=>'com_content', 'title'=>$title);
			$api->awardPoints($pointsApp, $article->created_by, $options);
		}
	}
	
	public function onContentChangeState($context, $pks, $value)
	{
		if ($context != 'com_content.article')
		{
			return true;
		}
	
		$api = new CjLibApi();
		$language = JFactory::getLanguage();
		$db = JFactory::getDbo();
			
		$params = JComponentHelper::getParams('com_cjforum');
		$streamApp = $params->get('stream_component', 'cjforum');
		$pointsApp = $params->get('points_component', 'cjforum');
		$profileApp = $params->get('profile_component', 'cjforum');
		$language->load('com_cjforum');
		$articles = array();
	
		try
		{
			JArrayHelper::toInteger($pks);
				
			$query = $db->getQuery(true)
			->select('a.id, a.title, a.introtext, a.catid, a.alias, a.created_by')
			->select('c.alias as category_alias')
			->select('u.name as author')
			->from('#__content AS a')
			->join('INNER', '#__categories AS c on c.id = a.catid')
			->join('INNER', '#__users AS u on a.created_by = u.id')
			->where('a.id in ('.implode(',', $pks).')');
				
			$db->setQuery($query);
			$articles = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			return true;
		}
	
		foreach ($articles as $article)
		{
			if($pointsApp == 'cjforum') // other components, we need not care
			{
				if($value == 0 || $value == -2)
				{
					$title = $description = JText::sprintf('COM_CJFORUM_POINTS_DELETED_ARTICLE', $article->title, array('jsSafe'=>true));
					$options = array('function'=>'com_content.delete', 'reference'=>$article->id, 'info'=>$description, 'component'=>'com_content', 'title'=>$title);
					$api->awardPoints($pointsApp, $article->created_by, $options);
				}
				else
				{
					$articleUrl = ContentHelperRoute::getArticleRoute($article->id.':'.$article->alias, $article->catid.':'.$article->category_alias);
					$articleLink = JHtml::link($articleUrl, CjLibUtils::escape($article->title));
						
					$title = $description = JText::sprintf('COM_CJFORUM_POINTS_NEW_ARTICLE', $articleLink, array('jsSafe'=>true));
					$options = array('function'=>'com_content.create', 'reference'=>$article->id, 'info'=>$description, 'component'=>'com_content', 'title'=>$title);
					$api->awardPoints($pointsApp, $article->created_by, $options);
				}
			}
	
			if($streamApp == 'cjforum' && $value == 1)
			{
				try
				{
					$query = $db->getQuery(true)
					->select('count(*)')
					->from('#__cjforum_activity AS a')
					->join('inner', '#__cjforum_activity_types AS b on a.activity_type = b.id')
					->where('b.activity_name = '.$db->q('com_content.create').' and a.item_id = '.$article->id.' and a.parent_id = '.$article->catid);
						
					$db->setQuery($query);
					$count = (int) $db->loadResult();
						
					if(!$count)
					{
						$articleUrl = ContentHelperRoute::getArticleRoute($article->id.':'.$article->alias, $article->catid.':'.$article->category_alias);
						$articleLink = JHtml::link($articleUrl, CjLibUtils::escape($article->title));
						$userName = $api->getUserProfileUrl($profileApp, $article->created_by, false, $article->author);
	
						$activity = new stdClass();
						$activity->type = 'com_content.create';
						$activity->href = $articleUrl;
						$activity->title = JText::sprintf('COM_CJFORUM_ACTIVITY_NEW_ARTICLE', $userName, $articleLink);;
						$activity->description = $article->introtext;
						$activity->userId = $article->created_by;
						$activity->featured = 0;
						$activity->language = $language->getTag();
						$activity->itemId = $article->id;
						$activity->parentId = $article->catid;
						$activity->length = $params->get('readmore_limit', 0);
	
						$api->pushActivity($streamApp, $activity);
					}
				}
				catch (Exception $e)
				{
					// nothin
				}
			}
		}
	}
	
	public function onContentPrepareForm($form, $data)
	{
		$app = JFactory::getApplication();
		$option = $app->input->get('option');
		$extension = $app->input->get('extension');
		
		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}
		
		if($option == 'com_categories' && $extension == 'com_cjforum')
		{
			JForm::addFormPath(__DIR__ . '/forms');
			$form->loadFile('category', false);
			return true;
		}
		
		return true;
	}
}

function replaceAttachmentTags($matches)
{
	$params = json_decode(str_replace(']', '}', str_replace('[', '{', trim($matches[1]))));

	if(empty($params->id))
	{
		return '';
	}

	$return = '<div class="well well-small">';
	$return .= '<a href="#" onclick="document.adminForm.d_id.value='.$params->id.';Joomla.submitbutton(\'topic.download\'); return false;" target="_blank">';
	$return .= '<i class="fa fa-download"></i> '.JText::_('COM_FORUM_DOWNLOAD_ATTACHMENT').'</a></div>';

	return $return;
}
