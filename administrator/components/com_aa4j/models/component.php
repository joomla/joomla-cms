<?php
/**
 * @version		$Id: component.php 09/01/2012 21.17
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_aa4j
 */
class Aa4jModelComponent extends JModelList
{
		public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
					'nation', 'b.countryname',
			);
		}

		parent::__construct($config);
	}
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function populateState()
	{
	
		$nation = $this->getUserStateFromRequest($this->context.'.filter.nation', 'filter_nation');
		$this->setState('filter.nation', $nation);
	}

	/**
	 * Get the component information.
	 *
	 * @return	object
	 * @since	1.6
	 */
	function getComponent()
	{
		// Initialise variables.
		$option = $this->getState('component.option');
		// var_dump('usrd:'.$option);
      $db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$query->select( 'a.username, e.longitude, e.countryname, e.citta, e.stato, e.latitude');
		//$query->order('a.registerDate DESC');
		$query->from('#__users AS a, #__userextras AS e');
		$query->where('a.id=e.id ');
	//	$query->where('a.id=e.id');
	  // Add filter for registration nation select list
		$nation = $this->getState('filter.nation');
   //jexit( var_dump($nation));
  // var_dump($nation);
		// Apply the nation filter.
		if (($this->getState('filter.nation') !=='*') && ($this->getState('filter.nation') !==null))
			{
				$query->where(
					'countryname = '.$db->quote($nation)
				);
			}	
		$query->order('a.registerDate ASC');
		$db->setQuery($query);
		$result = $db->loadObjectList();
		$myxml = "<markers>\n";
	    $cont= 0;
		$coord=array();
		foreach ($result as $row){
		  $cont++;
		  $myxml .= "<marker 
		           html='".$this->parseToXML($row->countryname)."' 
						   city='".$this->parseToXML($row->citta)."'
						   username='".$this->parseToXML($row->username)."'
						   lat='".$row->latitude."'
						   lng='".$row->longitude."'
						   type='user'>
		           </marker>";
		  if ($cont==1)			{
		     $coord[]=$row->latitude;
			 $coord[]=$row->longitude;
		  }
		}
		$myxml .= "</markers>\n";
		$path="usermap.xml";
	    $filenum=fopen($path,"w");
	    $url=JURI::base()."usermap.xml";        
	    fwrite($filenum,$myxml);
	    fclose($filenum);  	
		return (array) $coord;		
	}

	/**
	 * Method to save the configuration data.
	 *
	 * @param	array	An array containing all global config data.
	 *
	 * @return	bool	True on success, false on failure.
	 * @since	1.6
	 */
	public function save($data)
	{
		$table	= JTable::getInstance('extension');

		// Save the rules.
		if (isset($data['params']) && isset($data['params']['rules'])) {
			jimport('joomla.access.rules');
			$rules	= new JRules($data['params']['rules']);
			$asset	= JTable::getInstance('asset');

			if (!$asset->loadByName($data['option'])) {
				$root	= JTable::getInstance('asset');
				$root->loadByName('root.1');
				$asset->name = $data['option'];
				$asset->title = $data['option'];
				$asset->setLocation($root->id,'last-child');
			}
			$asset->rules = (string) $rules;

			if (!$asset->check() || !$asset->store()) {
				$this->setError($asset->getError());
				return false;
			}

			// We don't need this anymore
			unset($data['option']);
			unset($data['params']['rules']);
		}

		// Load the previous Data
		if (!$table->load($data['id'])) {
			$this->setError($table->getError());
			return false;
		}

		unset($data['id']);

		// Bind the data.
		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		// Check the data.
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Store the data.
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		// Clean the cache.
		$cache = JFactory::getCache('_system');
		$cache->clean();

		return true;
	}
	function parseToXML($htmlStr) 
{ 
$xmlStr=str_replace('<','&lt;',$htmlStr); 
$xmlStr=str_replace('>','&gt;',$xmlStr); 
$xmlStr=str_replace('"','&quot;',$xmlStr); 
$xmlStr=str_replace("'",'&#39;',$xmlStr); 
$xmlStr=str_replace("&",'&amp;',$xmlStr); 
return $xmlStr; 
} 
}