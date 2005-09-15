<?php
/**
* @version $Id: admin.cachemanager.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Content
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

class comCacheManagerData {

	/**
	 * An Array of Class CacheManagerItem
	 * Index by Cache Group ID
	 *
	 * @var Array
	 */
	var $items = null;

	/**
	 * Parse $path for cache file groups.
	 * Any files identifided as cache are logged
	 * in a group and stored in $this->items.
	 *
	 * @param String $path
	 */
	function addPath( $path ){
		if ($handle = opendir( $path )) {
			while (false !== ($file = readdir($handle))) {
				if( is_dir( $path."/".$file ) && $file != "." && $file != ".." ){
					$this->addPath( $path."/".$file );
				}elseif( is_file( $path."/".$file ) ){
					$filename = basename( $path."/".$file );
					$group = null;

					// Get cache group name from file
					if(substr( $filename, 0, 6 ) == "cache_")
						$group = $this->_getCacheGroupName( $filename );

					// If the file ends with .cache it is a patTemplate cache file
					if(substr( $filename, strlen($filename)-6 ) == ".cache")
						$group = "patTemplate";

					if($group){
						if(!isset( $this->items[$group]) ){
							$this->items[$group] = new CacheManagerItem( $group );
						}
						$this->items[$group]->updateSize( filesize( $path."/".$file )/ 1024 );
					}
				}
			}
			closedir($handle);
		}
	}

	/**
	 * Retrive a Cache Group ID from a cache filename
	 *
	 * @param String $filename
	 * @return String
	 */
	function _getCacheGroupName( $filename ){
		$parts = explode( "_", $filename );
		for($i=1;$i<count($parts)-1;$i++){
			$group[] = $parts[$i];
		}
		return implode($group, "_");
	}

	/**
	 * Get the number of current Cache Groups
	 *
	 * @return int
	 */
	function getGroupCount(){
		return count($this->items);
	}

	/**
	 * Retrun an Array containing a sub set of the total
	 * number of Cache Groups as defined by the params.
	 *
	 * @param Int $start
	 * @param Int $limit
	 * @return Array
	 */
	function &getRows( $start, $limit ){
		$i=0;
		if(count($this->items) == 0) return null;

		foreach($this->items as $item) {
			if($i >= $start && $i < $start+$limit)
				$rows[] = $item;
			$i++;
		}
		return $rows;
	}

	/**
	 * Clean out a cache group as named by param.
	 * If no param is passed clean all cache groups.
	 *
	 * @param String $group
	 */
	function cleanCache( $group='' ){
		global $mosConfig_cachepath;

		if($group == "patTemplate"){
			/**
			* Hack to remove patTemplate cache
			* as it does not use Cache_Lite groups
			**/
			$path = $mosConfig_cachepath.'/patTemplate/';
			$files = mosReadDirectory( $path, '.cache' );
			foreach ( $files as $file ) {
				$file = $path . $file;
				unlink( $file );
			}
		}else{
			//$cache =& mosFactory::getCache( $group );
			mosCache::cleanCache( $group );
		}
	}

	function cleanCacheList( $array ){
		foreach ($array as $group) {
			$this->cleanCache( $group );
		}
	}
}

/**
 * This Class is used by CacheManagerData to store group cache data.
 *
 */
class CacheManagerItem {

	var $group = "";
	var $size = 0;
	var $count = 0;

	function CacheManagerItem ( $group ){
		$this->group = $group;
	}

	function updateSize( $size ){
		$this->size = number_format($this->size + $size, 2);
		$this->count++;
	}
}

/**
 * This class controls the component and it's output
 * Current it is used as a Static class
 */
class comCacheManager {

	function show(){
		global $mosConfig_cachepath, $mainframe;

		$task = 		$mainframe->getUserStateFromRequest( "task", 'task' );
		$option = 		$mainframe->getUserStateFromRequest( "option", 'option' );
		$cachegroup =	$mainframe->getUserStateFromRequest( "cachegroup", 'cachegroup' );
		$cachelist =	$mainframe->getUserStateFromRequest( "cid", 'cid' );

		$cmData = new comCacheManagerData();

		switch ( $task ) {
			case 'cleanallcache':
				$cmData->cleanCache();
				$cmData->addPath( $mosConfig_cachepath );
				comCacheManager::_listCache( $option, $cmData );
				break;
			case 'cleancache':
				$cmData->cleanCacheList( $cachelist );
				$cmData->addPath( $mosConfig_cachepath );
				comCacheManager::_listCache( $option, $cmData );
				break;
			default:
				$cmData->addPath( $mosConfig_cachepath );
				comCacheManager::_listCache( $option, $cmData );
				break;
		}
	}

	function _listCache( $option, &$cmData){
		global $mosConfig_list_limit, $mainframe;

		$limit 			= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
		$limitstart 	= $mainframe->getUserStateFromRequest( "viewban{$option}limitstart", 'limitstart', 0 );

		// load files
		mosFS::load( '@admin_html' );
		mosFS::load( '@pageNavigationAdmin' );

		$pageNav = new mosPageNav( $cmData->getGroupCount(), $limitstart, $limit );

		cacheManagerScreens::viewCache( $option, $cmData->getRows( $limitstart, $limit ), $pageNav );
	}
}

comCacheManager::show();
?>