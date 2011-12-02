<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-2.0/JG/trunk/administrator/components/com_joomgallery/models/images.php $
// $Id: images.php 3401 2011-10-14 06:39:05Z chraneco $
/****************************************************************************************\
**   JoomGallery 2                                                                      **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2011  JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

jimport('joomla.application.component.modellist');

/**
 * Images model
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class LanguagesModelStrings extends JModel
{
	public function refresh()
	{
    require_once JPATH_COMPONENT.'/helpers/languages.php';

		$app = JFactory::getApplication();

    $app->setUserState('com_languages.overrides.cachedtime', null);

    try
    {
      $this->_db->setQuery('TRUNCATE TABLE #__overrider');
      $this->_db->query();
    }
    catch(JDatabaseException $e)
    {
      return $e;
    }

    $query = $this->_db->getQuery(true)
      ->insert('#__overrider')
      ->columns('constant, string, file');

    $client = $app->getUserState('com_languages.overrides.filter.client', 'site') ? 'administrator' : 'site';
    $language = $app->getUserState('com_languages.overrides.filter.language', 'en-GB');

    $base = constant('JPATH_'.strtoupper($client)).DS;
		$path = $base.'language'.DS.$language;

    $files = array();
		if(JFolder::exists($path))
		{
			$files = JFolder::files($path, $language.'.*ini$', false, true);
		}

    $path = $base.'components';
    $files = array_merge($files, JFolder::files($path, $language.'.*ini$', 3, true));

    $path = $base.'modules';
    $files = array_merge($files, JFolder::files($path, $language.'.*ini$', 3, true));

    $path = $base.'templates';
    $files = array_merge($files, JFolder::files($path, $language.'.*ini$', 3, true));

    $path = JPATH_ROOT.DS.'plugins';
    $files = array_merge($files, JFolder::files($path, $language.'.*ini$', 3, true));

		foreach($files as $file)
		{
			$strings = LanguagesHelper::parseFile($file);
			if($strings && count($strings))
			{
				$query->clear('values');
				foreach($strings as $key => $string)
				{
					$query->values($this->_db->quote($key).','.$this->_db->quote($string).','.$this->_db->quote($file));
				}

        try
        {
          $this->_db->setQuery($query);
          $this->_db->query();
        }
        catch(JDatabaseException $e)
        {
          return $e;
        }
			}
		}

    $app->setUserState('com_languages.overrides.cachedtime.'.$client.'.'.$language, time());

    return true;
  }

	public function search()
	{
    $results = array();

    $limitstart = JRequest::getInt('more');

    try
    {
      $query = $this->_db->getQuery(true)
            ->select('constant, string, file')
            ->from('#__overrider')
            ->where('string LIKE '.$this->_db->quote('%'.JRequest::getString('searchstring').'%'));

      $this->_db->setQuery($query, $limitstart, 10);
      $results['results'] = $this->_db->loadObjectList();

      $query->clear('select')
            ->select('COUNT(id)');
      $this->_db->setQuery($query);

      if($this->_db->loadResult() > $limitstart + 10)
      {
        $results['more'] = $limitstart + 10;
      }
    }
    catch(JDatabaseException $e)
    {
      return $e;
    }

    return $results;
	}
}