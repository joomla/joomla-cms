<?php 
 /************************************************************************************* 
 * mysql.php 
 * --------- 
 * Author: Carl Fürstenberg (azatoth@gmail.com) 
 * Copyright: (c) 2005 Carl Fürstenberg, Nigel McNie (http://qbnz.com/highlighter) 
 * Release Version: 1.0.7.8
 * CVS Revision Version: $Revision: 1.6 $ 
 * Date Started: 2004/06/04 
 * Last Modified: $Date: 2006/03/11 21:44:08 $ 
 * 
 * MySQL language file for GeSHi. 
 * 
 * 
 ************************************************************************************* 
 * 
 * This file is part of GeSHi. 
 * 
 * GeSHi is free software; you can redistribute it and/or modify 
 * it under the terms of the GNU General Public License as published by 
 * the Free Software Foundation; either version 2 of the License, or 
 * (at your option) any later version. 
 * 
 * GeSHi is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
 * GNU General Public License for more details. 
 * 
 * You should have received a copy of the GNU General Public License 
 * along with GeSHi; if not, write to the Free Software 
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA 
 * 
 ************************************************************************************/ 
  
$language_data = array ( 
  'LANG_NAME' => 'MySQL', 
  'COMMENT_SINGLE' => array(1 =>'--', 2 => '#'), 
  'COMMENT_MULTI' => array('/*' => '*/'), 
  'CASE_KEYWORDS' => 1, 
  'QUOTEMARKS' => array("'", '"', ''), 
  'ESCAPE_CHAR' => '\\', 
  'KEYWORDS' => array( 
  1 => array( 
  /* Mix */ 
  'ALTER DATABASE', 'ALTER TABLE', 'CREATE DATABASE', 'CREATE INDEX', 'CREATE TABLE', 'DROP DATABASE', 
  'DROP INDEX', 'DROP TABLE', 'RENAME TABLE', 'DELETE', 'DO', 'HANDLER', 'INSERT', 'LOAD DATA INFILE',  
  'REPLACE', 'SELECT', 'TRUNCATE', 'UPDATE', 'DESCRIBE', 'USE', 'START TRANSACTION', 'COMMIT', 'ROLLBACK', 
  'SAVEPOINT', 'ROLLBACK TO SAVEPOINT', 'LOCK TABLES', 'UNLOCK_TABLES', 'SET TRANACTIONS', 'SET', 'SHOW', 
  'CREATE PROCEDURE', 'CREATE FUNCTION', 'ALTER PROCEDURE', 'ALTER FUNCTION', 'DROP PROCEDURE', 'DROP FUNCTION',  
  'SHOW CREATE PROCEDURE', 'SHOW CREATE FUNCTION', 'SHOW PROCEDURE STATUS', 'SHOW FUNCTION STATUS',  
  'CALL', 'BEGIN', 'END', 'DECLARE', 'CREATE ROUTINE', 'ALTER ROUTINE', 'CREATE', 'ALTER', 'DROP', 
  'PRIMARY KEY', 'VALUES', 'INTO', 'FROM',  
  'ANALYZE', 'BDB', 'BERKELEYDB', 'BTREE', 'BY', 'CASCADE', 'CHECK', 'COLUMN', 'COLUMNS', 'CONSTRAINT', 
  'CROSS', 'DATABASES', 'DELAYED', 'DISTINCT', 'DISTINCTROW', 'ENCLOSED', 'ERRORS', 'ESCAPED', 'EXISTS', 
  'EXPLAIN', 'FALSE', 'FIELDS', 'FORCE', 'FOREIGN', 'FULLTEXT', 'GEOMETRY', 'GRANT', 'GROUP', 'HASH', 
  'HAVING', 'HELP', 'HIGH_PRIORITY', 'IGNORE', 'INNER', 'INNODB', 'INTERVAL', 'JOIN', 'KEYS', 'KILL', 
  'LINES', 'LOW_PRIORITY', 'MASTER_SERVER_ID', 'MATCH', 'MIDDLEINT', 'MRG_MYISAM', 'NATURAL', 'OPTIMIZE', 
  'OPTION', 'OPTIONALLY', 'ORDER', 'OUTER', 'OUTFILE', 'PRIVILEGES', 'PURGE', 'READ', 'REFERENCES', 
  'REQUIRE', 'RESTRICT', 'RETURNS', 'REVOKE', 'RLIKE', 'RTREE', 'SOME', 'SONAME', 'SPATIAL', 'SQL_BIG_RESULT',  
  'SQL_CALC_FOUND_ROWS', 'SQL_SMALL_RESULT', 'SSL', 'STARTING', 'STRAIGHT_JOIN', 'STRIPED', 'TERMINATED',  
  'TRUE', 'TYPES', 'UNION', 'USAGE', 'USER_RESOURCES', 'USING', 'VARCHARACTER', 'WARNINGS', 'WHERE', 'WRITE', 
  /* Control Flow Functions */ 
  'CASE', 'WHEN', 'THEN', 'ELSE', 'END', 
  /* String Functions */ 
  'BIN', 'BIT_LENGTH', 'CHAR_LENGTH', 'CHARACTER_LENGTH', 'COMPRESS', 'CONCAT', 
  'CONCAT_WS', 'CONV', 'ELT', 'EXPORT_SET', 'FIELD', 'FIND_IN_SET', 'FORMAT', 'HEX',  
  'INSERT', 'INSTR', 'LCASE', 'LEFT', 'LENGTH', 'LOAD_FILE', 'LOCATE', 'LOWER', 'LPAD', 
  'LTRIM', 'MAKE_SET', 'MID', 'OCT', 'OCTET_LENGTH', 'ORD', 'POSITION', 'QUOTE', 'REPEAT', 
  'REPLACE', 'REVERSE', 'RIGHT', 'RPAD', 'RTRIM', 'SOUNDEX', 'SPACE', 'SUBSTRING',  
  'SUBSTRING_INDEX', 'TRIM', 'UCASE', 'UPPER', 'UNCOMPRESS', 'UNCOMPRESSD_LENGTH', 'UNHEX',  
  /* Numeric Functions */ 
  'ABS', 'ACOS', 'ASIN', 'ATAN', 'ATAN2', 'CEILING', 'CEIL', 'COS', 'COT', 'CRC32', 'DEGREES', 
  'EXP', 'FLOOR', 'LN', 'LOG', 'LOG2', 'LOG10', 'MOD', 'PI', 'POW', 'POWER', 'RADIANS', 'RAND', 
  'ROUND', 'SIGN', 'SIN', 'SQRT', 'TAN', 'TRUNCATE', 
  /* Date and Time Functions */ 
  'ADDDATE', 'ADDTIME', 'CONVERT_TZ', 'CURDATE', 'CURRENT_DATE', 'CURTIME', 'CURRENT_TIME', 
  'CURRENT_TIMESTAMP', 'DATEDIFF', 'DATE_ADD', 'DATE_SUB', 'DATE_FORMAT', 'DAY',  
  'DAYNAME', 'DAYOFMONTH', 'DAYOFWEEK', 'DAYOFYEAR', 'EXTRACT', 'FROM_DAYS', 'FROM_UNIXTIME', 
  'GET_FORMAT', 'LAST_DAY', 'LOCALTIME', 'LOCALTIMESTAMP', 'MAKEDATE', 'MAKETIME',  
  'MICROSECOND', 'MONTHNAME', 'NOW', 'PERIOD_ADD', 'PERIOD_DIFF', 'QUARTER', 
  'SECOND', 'SEC_TO_TIME', 'STR_TO_DATE', 'SUBDATE', 'SUBTIME', 'SYSDATE', 'TIME', 'TIMEDIFF', 
  'TIMESTAMP', 'TIMESTAMPADD', 'TIMESTAMPDIFF', 'TIME_FORMAT', 'TIME_TO_SEC', 'TO_DAYS',  
  'UNIX_TIMESTAMP', 'UTC_DATE', 'UTC_TIME', 'UTC_TIMESTAMP', 'WEEKDAY', 'WEEKOFYEAR', 
  'YEARWEEK', 
   
   ), 
   2 => array( 
   'INTEGER', 'SMALLINT', 'DECIMAL', 'NUMERIC', 'FLOAT', 'REAL', 'DOUBLE PRECISION', 
   'DOUBLE', 'INT', 'DEC', 'BIT' ,'TINYINT', 'SMALLINT', 'MEDIUMINT', 'BIGINT',  
   'DATETIME', 'DATE', 'TIMESTAMP', 'TIME', 'YEAR',  
   'CHAR', 'VARCHAR', 'BINARY', 'CHARACTER VARYING', 'VARBINARY', 'TINYBLOB', 'TINYTEXT', 
   'BLOB', 'TEXT','MEDIUMBLOB', 'MEDIUMTEXT', 'LONGBLOB', 'LONGTEXT', 'ENUM', 'SET', 
   'SERIAL DEFAULT VALUE', 'SERIAL', 'FIXED' 
   ), 
   3 => array( 
   'ZEROFILL', 'NOT NULL', 'UNSIGNED', 'AUTO_INCREMENT', 'UNIQUE', 'NOT', 'NULL', 'CHARACTER SET', 'CHARSET', 
   'ASCII', 'UNICODE', 'NATIONAL', 'BOTH', 'LEADING', 'TRAILING','DEFAULT' 
   ), 
   4 => array( 
   'MICROSECOND', 'SECOND', 'MINUTE', 'HOUR', 'DAY', 'WEEK', 'MONTH', 'QUARTER', 'YEAR', 'SECOND_MICROSECOND',  
   'MINUTE_MICROSECOND', 'MINUTE_SECOND', 'HOUR_MICROSECOND', 'HOUR_SECOND', 'HOUR_MINUTE', 'DAY_MICROSECOND', 
   'DAY_SECOND', 'DAY_MINUTE', 'DAY_HOUR', 'YEAR_MONTH', 
   ), 
   ), 
   'SYMBOLS' => array( 
   ':=', 
   '||', 'OR', 'XOR', 
   '&&', 'AND', 
   'NOT', 
   'BETWEEN', 'CASE', 'WHEN', 'THEN', 'ELSE', 
   '=', '<=>', '>=', '>', '<=', '<', '<>', '!=', 'IS', 'LIKE', 'REGEXP', 'IN', 
   '|', 
   '&', 
   '<<', '>>', 
   '-', '+', 
   '*', '/', 'DIV', '%', 'MOD', 
   '^', 
   '-', '~', 
   '!', 
   'BINARY', 'COLLATE', 
   '(', ')', 
   ), 
   'CASE_SENSITIVE' => array( 
   GESHI_COMMENTS => false, 
   1 => false, 
   2 => false, 
   3 => false, 
   4 => false, 
   ), 
   'STYLES' => array( 
   'KEYWORDS' => array( 
   1 => 'color: #993333; font-weight: bold;', 
   2 => 'color: #aa9933; font-weight: bold;', 
   3 => 'color: #aa3399; font-weight: bold;', 
   4 => 'color: #33aa99; font-weight: bold;', 
   ), 
   'COMMENTS' => array( 
   1 => 'color: #808080; font-style: italic;', 
   2 => 'color: #808080; font-style: italic;' 
   ), 
   'ESCAPE_CHAR' => array( 
   0 => 'color: #000099; font-weight: bold;' 
   ), 
   'BRACKETS' => array( 
   0 => 'color: #66cc66;' 
   ), 
   'STRINGS' => array( 
   0 => 'color: #ff0000;' 
   ), 
   'NUMBERS' => array( 
   0 => 'color: #cc66cc;' 
   ), 
   'METHODS' => array( 
   ), 
   'SYMBOLS' => array( 
   0 => 'color: #66cc66;' 
   ), 
   'SCRIPT' => array( 
   ), 
   'REGEXPS' => array( 
   ) 
   ), 
   'OOLANG' => false, 
   'OBJECT_SPLITTERS' => array( 
   ), 
   'REGEXPS' => array( 
   ), 
   'STRICT_MODE_APPLIES' => GESHI_NEVER, 
   'SCRIPT_DELIMITERS' => array( 
   ), 
   'HIGHLIGHT_STRICT_BLOCK' => array( 
   ) 
);

?>
