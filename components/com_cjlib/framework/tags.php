<?php
/**
 * @version		$Id: tags.php 01 2012-07-06 11:37:09Z maverick $
 * @package		CoreJoomla.CJLib
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2012 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */
defined('_JEXEC') or die('Restricted access');

class CjTags{
	
	private $_db;
	private $_tbl_tags;
	private $_tbl_tags_map;
	private $_tbl_tags_stats;
	
	function CjTags($db, $tbl_tags, $tbl_tags_map, $tbl_tags_stats){
		
		$this->_db = $db;
		$this->_tbl_tags = $tbl_tags;
		$this->_tbl_tags_map = $tbl_tags_map;
		$this->_tbl_tags_stats = $tbl_tags_stats;
	}
	
	function get_tags_cloud($limit=20){
		
// 		$query = 'SELECT tag_text, ts.num_items'
// 			. ' FROM '.$this->_tbl_tags_map.' tm'
// 			. ' INNER JOIN '.$this->_tbl_tags.' t ON tm.tag_id = t.id'
// 			. ' INNER JOIN '.$this->_tbl_tags_stats.' ts ON t.id = ts.tag_id'
// 			. ' GROUP BY tag_text';
		$query = '
			select 
				a.id, a.tag_text, a.alias, s.num_items 
			from 
				'.$this->_tbl_tags.' a 
			left join 
				(select(floor(max(id) * rand())) as maxid from '.$this->_tbl_tags.') as t on a.id >= t.maxid 
			left join 
				'.$this->_tbl_tags_stats.' s on a.id=s.tag_id 
			order 
				by s.num_items';
		
		$this->_db->setQuery($query, 0, $limit);
		
		return $this->_db->loadObjectList();
	}
	
	function get_related_items($itemid, $limit=20){
		
		$query = '
			select 
				p2.item_id 
			from 
				( select tag_id from '.$this->_tbl_tags_map.' where item_id = '.$itemid.' limit '.$limit.') as p1 
			inner join
				'.$this->_tbl_tags_map.' p2 on p1.tag_id = p2.tag_id 
			group by
				p2.item_id';
		
		$this->_db->setQuery($query, 0, $limit);
		
		return (APP_VERSION == '1.5' ? $this->_db->loadResultArray() : $this->_db->loadColumn());
	}
	
	function get_related_tags($search, $limit){
		
		$query = '
			select
				tm2.tag_id, t2.tag_text
			from
				(
					select 
						item_id 
					from 
						'.$this->_tbl_tags.' t1
					inner join
						'.$this->_tbl_tags_map.' tp1 on t1.id = tp1.tag_id 
					where 
						t1.tag_text like "%' . $this->_db->getEscaped($search) . '%" 
					limit 
						'.$limit.'
				) AS tm1
			inner join
				'.$this->_tbl_tags_map.' tm2 on tm1.item_id = tm2.item_id
			inner join
				'.$this->_tbl_tags.' t2 on tm2.tag_id = t2.id
			group by
				tm2.tag_id';
		
		$this->_db->setQuery($query, 0, $limit);
		
		return $this->_db->loadObjectList();
	}
	
	function get_tagged_items_by_tagids($tagids=array(), $limit=20){
		
		if(count($tagids) > 0){
			
			$tag_query = implode(',', $tagids);
			
			$query = '
				select 
					tm.item_id
				from
					'.$this->_tbl_tags.' t1
				inner join
					'.$this->_tbl_tags_map.' tm ON t1.id = tm.tag_id
				where
					t1.id in ('.$tag_query.')
				group by
					tm.item_id';
			
			$this->_db->setQuery($query, 0, $limit);
			
			return $this->_db->loadColumn();
		}else{
			
			return false;
		}
	}

	public function get_tags_by_itemids($ids){
		
		if(!is_array($ids)) $ids = array($ids);
	
		$query = '
			select
				map.item_id,
				tag.id as tag_id, tag.tag_text, tag.alias, tag.description
			from
				'.$this->_tbl_tags_map.'
			left join
				'.$this->_tbl_tags.' tag on tag.id = map.tag_id
			where
				map.item_id in ('.implode(',', $ids).')';
			
		$this->_db->setQuery($query);
		$tags = $this->_db->loadObjectList();
	
		return !empty($tags) ? $tags : array();
	}
	
	function get_tag_details($tag_id){
		
		$query = '
			select 
				id, tag_text, alias
			from
				'.$this->_tbl_tags.' 
			where 
				id='.$tag_id;
		
		$this->_db->setQuery($query);
		
		return $this->_db->loadObject();
	}
	
	public function insert_tags($itemid, $strtags){

		$tags = explode(',', $strtags);
		
		// first filter out the tags
		foreach($tags as $i=>$tag){
				
			$tag = preg_replace('/[^-\pL.\x20]/u', '', $tag);
			if(empty($tag)) unset($tags[$i]);
		}
		
		// now if there are any new tags, insert them.
		if(!empty($tags)){
				
			$inserts = array();
			$sqltags = array();
			
			foreach($tags as $tag){
		
				$alias = JFilterOutput::stringURLUnicodeSlug($tag);
				$inserts[] = '('.$this->_db->quote($tag).','.$this->_db->quote($alias).')';
				$sqltags[] = $this->_db->quote($tag);
			}
			
			$query = 'insert ignore into '.$this->_tbl_tags.' (tag_text, alias) values '.implode(',', $inserts);
			$this->_db->setQuery($query);
			
			if(!$this->_db->query()){
				
				return false;
			}
					
			// we need to get all tag ids matching the input tags
			$query = 'select id from '.$this->_tbl_tags.' where tag_text in ('.implode(',', $sqltags).')';
			$this->_db->setQuery($query);
			$insertids = $this->_db->loadColumn();

			if(!empty($insertids)){
		
				$mapinserts = array();
				$statinserts = array();
		
				foreach($insertids as $insertid){
						
					$mapinserts[] = '('.$insertid.','.$itemid.')';
					$statinserts[] = '('.$insertid.','.'1)';
				}
		
				$query = 'insert ignore into '.$this->_tbl_tags_map.'(tag_id, item_id) values '.implode(',', $mapinserts);
				$this->_db->setQuery($query);

				if(!$this->_db->query()){
					
					return false;
				}
				
				$query = 'insert ignore into '.$this->_tbl_tags_stats.'(tag_id, num_items) values '.implode(',', $statinserts);
				$this->_db->setQuery($query);
				
				if(!$this->_db->query()){
					
					return false;
				}
			}
			
			// now remove all non-matching tags ids from the map
			$where = '';
			
			if(!empty($insertids)){
				
				$where = ' and tag_id not in ('.implode(',', $insertids).')';
			}

			$query = 'select tag_id from '.$this->_tbl_tags_map.' where item_id = '.$itemid.$where;
			$this->_db->setQuery($query);
			$removals = $this->_db->loadColumn();

			$where = '';
			
			if(!empty($removals)){
				
				$query = 'delete from '.$this->_tbl_tags_map.' where tag_id in ('.implode(',', $removals).')';
				$this->_db->setQuery($query);
				$this->_db->query();
				
				$where = ' or s.tag_id in ('.implode(',', $removals).')';
			}

			// now update the stats
			$query = '
				update
					'.$this->_tbl_tags_stats.' s
				set
					s.num_items = (select count(*) from '.$this->_tbl_tags_map.' m where m.tag_id = s.tag_id)
				where
					s.tag_id in (select tag_id from '.$this->_tbl_tags_map.' m1 where m1.item_id = '.$itemid.')'.$where;
			
			$this->_db->setQuery($query);
			$this->_db->query();
		} else {
			
			$query = 'delete from '.$this->_tbl_tags_map.' where item_id = '.$itemid;
			$this->_db->setQuery($query);
			$this->_db->query();
		}
	}
}
?>