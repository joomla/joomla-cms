<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

class CjForumHelper
{
	public static function uploadFiles($postId, $postType, $fieldName = 'attachment_file')
	{
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		
		jimport('joomla.filesystem.file');
		$files = $app->input->files->get($fieldName);
		$existing = $app->input->post->get('existing_attachment', array(), 'array');
		$uploaded = array();
		
		if(empty($files))
		{
			return 0;
		}
			
		foreach ($files as $i=>$file)
		{
			if(empty($file['tmp_name']))
			{
				continue;
			}
				
			$filename = $postId.'_'.$postType.'_'.JFile::makeSafe($file['name']);
			$src = $file['tmp_name'];
			$dest = CF_ATTACHMENTS_DIR.$filename;
				
			if(JFile::upload($src, $dest))
			{
				$upload = new stdClass();
				$upload->name = $filename;
				$upload->size = (int) $file['size'];
				$uploaded[] = $upload;
			}
		}
		
		// first delete the existing attachments which are deleted from the request.
		JArrayHelper::toInteger($existing);
		if(!empty($existing))
		{
			$query = $db->getQuery(true)
				->select('id, folder, filename')
				->from('#__cjforum_attachments')
				->where('post_id = '.$postId.' and post_type = '.$postType);
			
			$db->setQuery($query);
			$attachments = array();
			
			try
			{
				$attachments = $db->loadObjectList();
			}
			catch (Exception $e){}
			
			if(!empty($attachments))
			{
				$absolateAttachments = array();
				foreach ($attachments as $attachment)
				{
					if(!in_array($attachment->id, $existing))
					{
						$absolateAttachments[] = $attachment;
					}
				}
				
				if(!empty($absolateAttachments))
				{
					$removedIds = array();
					foreach ($absolateAttachments as $absolate)
					{
						$removedIds[] = $absolate->id;
						if(JFile::exists(CF_ATTACHMENTS_DIR.$absolate->filename))
						{
							JFile::delete(CF_ATTACHMENTS_DIR.$absolate->filename);
						}
					}
					
					$query = $db->getQuery(true)
						->delete('#__cjforum_attachments')
						->where('id in ('.implode(',', $removedIds).')');
					
					$db->setQuery($query);
					try
					{
						$db->execute();
					}
					catch (Exception $e){}
				}
			}
		}
		else 
		{
			// simply delete all existing attachments as all are deleted from request
			$query = $db->getQuery(true)->delete('#__cjforum_attachments')->where('post_id = '.$postId.' and post_type = '.$postType);
			$db->setQuery($query);
			try
			{
				$db->execute();
			}
			catch (Exception $e){}
		}
					
		if(!empty($uploaded))
		{
			$query = $db->getQuery(true)
				->insert('#__cjforum_attachments')
				->columns('post_id, post_type, created_by, hash, filesize, folder, filetype, filename');
		
			foreach ($uploaded as $upload)
			{
				$hash = md5_file(CF_ATTACHMENTS_DIR.$upload->name);
				$query->values($postId.','.$postType.','.$user->id.','.$db->q($hash).','.$upload->size.','.$db->q(CF_ATTACHMENTS_PATH).','.$db->q('').','.$db->q($upload->name));
			}
		
			$db->setQuery($query);
			try
			{
				$db->execute();
			}
			catch (Exception $e){}
		}
	}
	
	public static function isUserBanned($userId = 0)
	{
		$userId = $userId ? $userId : JFactory::getUser()->id;
		
		if($userId > 0)
		{
			$profileApi = CjForumApi::getProfileApi();
			$profile = $profileApi->getUserProfile($userId);
			return (!empty($profile['banned']) && $profile['banned'] != '0000-00-00 00:00:00');
		}
		else
		{
			return false;
		}
	}
}