<?php
/**
 * SQL File loader
 * 
 * Loads an SQL file into a database 
 * Heavily borrowed from Alexey Ozerov (BigDump v0.29b)
 * 
 * PHP4/5
 *  
 * Created on Oct 30, 2008
 * 
 * @package Joomla.Framework
 * @subpackage Database.Loader
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * @version SVN: $Id:$
 */
 
// No direct access
defined('JPATH_BASE') or die();

class JLoaderSQL extends JDataLoad implements Serializable {
	/** JStream internal stream object */
	private $_stream;
	/** DBO */
	private $_dbo;
	/** @var int How many chars are read per time */
	protected $data_chunk_length = 16384;
	/** @var int How many lines may be considered to be one query (except text lines) */ 
	protected $max_query_lines = 300;
	
	protected $lines_per_session = 3000;
	protected $delay_per_session = 0;
	
	protected $total_queries = 0;
	protected $filename = '';
	protected $offset = 0;
	protected $start = 0;
	protected $queries = 0;
	protected $comment = Array('#', '-- ');
	
	public function __construct($options) {
		if(!isset($options['filename'])) {
			$this->setError(42, 'Filename not set');
			return false;
		}
		
		$this->_stream =& JFactory::getStream(); 
		$this->setProperties($options);
		if(!$this->_stream->open($this->filename)) {
			$this->setError(43, 'Failed to open file: '. $this->_stream->getError());
			return false;
		}
		
		$this->_dbo =& JFactory::getDBO();
		
		// Hope these work
		@ini_set('auto_detect_line_endings', true);
		@set_time_limit(0);
	}
	
	public function load($offset=-1) {
		if($offset > -1) $this->offset = $offset; 
		if(!$this->_stream->seek($this->offset)) {
			$this->setError('JLoaderSQL::load: Failed to seek SQL file to '. $this->offset);
			return false;
		}
		$query = '';
		$querylines = 0;
		$inparents = false;
		$linenumber = $this->start;
		// Stay processing as long as the $linespersession is not reached or the query is still incomplete		
		while($linenumber < ($this->start + $this->lines_per_session) || $query != "") {
			// Read the whole next line			
			$dumpline = "";
			while(!$this->_stream->eof() && substr($dumpline, -1) != "\n") {
				$dumpline = $this->_stream->gets($this->data_chunk_length);
			}
			if($dumpline === "") break;
			
			// Handle DOS and Mac encoded linebreaks (I don't know if it will work on Win32 or Mac Servers)

			$dumpline = str_replace("\r\n", "\n", $dumpline);
			$dumpline = str_replace("\r", "\n", $dumpline);
			
			// Skip comments and blank lines only if NOT in parents
			if (!$inparents) {
				$skipline = false;
				reset($this->comment);
				foreach ($this->comment as $comment_value) {
					if (!$inparents && (trim($dumpline) == "" || strpos($dumpline, $comment_value) === 0)) {
						$skipline = true;
						break;
					}
				}
				if ($skipline) {
					$linenumber++;
					continue;
				}
			}
			
			// Remove double back-slashes from the dumpline prior to count the quotes ('\\' can only be within strings)
			$dumpline_deslashed = str_replace ("\\\\","",$dumpline);
			
			// Count ' and \' in the dumpline to avoid query break within a text field ending by ;
			// Please don't use double quotes ('"')to surround strings, it wont work

      		$parents=substr_count ($dumpline_deslashed, "'")-substr_count ($dumpline_deslashed, "\\'");
      		if ($parents % 2 != 0) $inparents=!$inparents;
      		
      		// Add the line to query
			$query .= $dumpline;
			
			// Don't count the line if in parents (text fields may include unlimited linebreaks)
      		if (!$inparents) $querylines++;
      		
      		// Stop if query contains more lines as defined by $this->max_query_lines
			if ($querylines>$this->max_query_lines) {
				$this->setError('JLoaderSQL::load: Oversized query on line '. $linenumber);
				return false;
			}
			
			// Execute query if end of query detected (; as last character) AND NOT in parents
			if (ereg(";$",trim($dumpline)) && !$inparents) {
				$this->_dbo->setQuery(trim($query));
				try {
					$this->_dbo->Query();
				} catch (JException $e) {
        			echo ("<p class=\"error\">Error at the line $linenumber: ". trim($dumpline)."</p>\n");
          			echo ("<p>Query: ".trim(nl2br(htmlentities($query)))."</p>\n");
          			echo ("<p>MySQL: ".mysql_error()."</p>\n");
          			return false;
        		}
		        $this->total_queries++;
		        $this->queries++;
		        $query="";
		        $querylines=0;
      		}
      		$linenumber++;
		}
		
		if ($linenumber < ($this->start+$this->lines_per_session)) {
			return true; // we're all finished!
		} else {
			// TODO: If there is a task system available, redirect through that
			JError::raiseError(500, 'JLoaderSQL::load: Ran out of time during load');
			return false;
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	public function serialize() {
		return '';
	}
	
	public function unserialize($data) {
		return true;
	}
}
