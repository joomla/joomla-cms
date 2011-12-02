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
class LanguagesModelOverrides extends JModelList
{
  /**
   * Constructor
   *
   * @param   array An optional associative array of configuration settings
   * @return  void
   * @since   2.0
   */
  public function __construct($config = array())
  {
    parent::__construct($config);

    $this->filter_fields = array('key', 'text');
  }

  /**
   * Retrieves the images data
   *
   * @access  public
   * @return  array   Array of objects containing the images data from the database
   * @since   1.5.5
   */
  function getOverrides($all = false)
  {
		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if(!empty($this->cache[$store]))
    {
			return $this->cache[$store];
		}

    $filename = constant('JPATH_'.strtoupper($this->getState('filter.client'))).DS.'language'.DS.'overrides'.DS.$this->getState('filter.language').'.override.ini';
    $strings = LanguagesHelper::parseFile($filename);

    if(!$all && $this->getTotal() > $this->getState('list.limit'))
    {
      $strings = array_slice($strings, $this->getStart(), $this->getState('list.limit'), true);
    }

		// Add the items to the internal cache.
		$this->cache[$store] = $strings;

		return $this->cache[$store];
  }

  /**
   * Method to get the pagination object for the list.
   * This method uses 'getTotel', 'getStart' and the current
   * list limit of this view.
   *
   * @return  object  A pagination object
   * @since   2.0
   */
  /*function getPagination()
  {
    jimport('joomla.html.pagination');
    return new JPagination($this->getTotal(), $this->getStart(), $this->getState('list.limit'));
  }*/

  /**
   * Method to get the total number of images
   *
   * @access  public
   * @return  int     The total number of images
   * @since   1.5.5
   */
  function getTotal()
  {
		// Get a storage key.
		$store = $this->getStoreId('getTotal');

		// Try to load the data from internal storage.
		if(!empty($this->cache[$store]))
    {
			return $this->cache[$store];
		}

		// Add the total to the internal cache.
		$this->cache[$store] = count($this->getOverrides(true));

		return $this->cache[$store];
  }

  /**
   * Method to get the starting number of items for the data set.
   *
   * @return  int The starting number of items available in the data set.
   * @since   2.0
   */
  /*public function getStart()
  {
    $start = $this->getState('list.start');
    $limit = $this->getState('list.limit');
    $total = $this->getTotal();
    if($start > $total - $limit)
    {
      $start = max(0, (int)(ceil($total / $limit) - 1) * $limit);
    }

    return $start;
  }*/

  /**
   * Method to auto-populate the model state.
   *
   * Note. Calling getState in this method will result in recursion.
   *
   * @param   string  An optional ordering field.
   * @param   string  An optional direction (asc|desc).
   * @return  void
   * @since   2.0
   */
  protected function populateState($ordering = null, $direction = null)
  {
    $client = $this->getUserStateFromRequest('com_languages.overrides.filter.client', 'filter_client', 0, 'int') ? 'administrator' : 'site';
    $this->setState('filter.client', $client);

    $language = $this->getUserStateFromRequest('com_languages.overrides.filter.language', 'filter_language', 'en-GB', 'cmd');
    $this->setState('filter.language', $language);

		// List state information.
		parent::populateState('key', 'asc');
  }

  /**
   * Method to delete one or more images
   *
   * @access  public
   * @return  int     Number of successfully deleted images, boolean false if an error occured
   * @since   1.5.5
   */
  function delete($cids)
  {
    if(!JFactory::getUser()->authorise('core.delete', 'com_languages'))
    {
      $this->setError(JText::_('COM_OVERRIDES_ERROR_DELETE_NOT_PERMITTED'));

      return false;
    }

    $app = JFactory::getApplication();

    jimport('joomla.filesystem.file');

    $app = JFactory::getApplication();

    $filename = constant('JPATH_'.strtoupper($this->getState('filter.client'))).DS.'language'.DS.'overrides'.DS.$this->getState('filter.language').'.override.ini';
    $strings = LanguagesHelper::parseFile($filename);

    foreach($cids as $key)
    {
      if(isset($strings[$key]))
      {
        unset($strings[$key]);
      }
    }

    $registry = new JRegistry();
    $registry->loadObject($strings);

    $filename = constant('JPATH_'.strtoupper($app->getUserState('com_overrider.filter.client'))).DS.'language'.DS.'overrides'.DS.$app->getUserState('com_overrider.filter.language').'.override.ini';

    JFile::write($filename, $registry->toString('INI'));

    $this->cleanCache();

    return count($cid);
  }
}