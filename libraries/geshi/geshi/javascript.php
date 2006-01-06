<?php
/*************************************************************************************
 * javascript.php
 * --------------
 * Author: Ben Keen (ben.keen@gmail.com)
 * Copyright: (c) 2004 Ben Keen (ben.keen@gmail.com), Nigel McNie (http://qbnz.com/highlighter)
 * Release Version: 1.0.7.5
 * CVS Revision Version: $Revision: 1.5 $
 * Date Started: 2004/06/20
 * Last Modified: $Date: 2005/10/22 07:52:59 $
 *
 * JavaScript language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2004/11/27 (1.0.1)
 *  -  Added support for multiple object splitters
 * 2004/10/27 (1.0.0)
 *  -  First Release
 *
 * TODO (updated 2004/11/27)
 * -------------------------
 *
 *************************************************************************************
 *
 *     This file is part of GeSHi.
 *
 *   GeSHi is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   GeSHi is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with GeSHi; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ************************************************************************************/

$language_data = array (
  'LANG_NAME' => 'JAVASCRIPT',
  'COMMENT_SINGLE' => array(1 => '//'),
  'COMMENT_MULTI' => array('/*' => '*/'),
  'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
  'QUOTEMARKS' => array("'", '"'),
  'ESCAPE_CHAR' => '\\',
  'KEYWORDS' => array(
    1 => array(
      'as', 'break', 'case', 'catch', 'continue', 'decodeURI', 'delete', 'do',
      'else', 'encodeURI', 'eval', 'finally', 'for', 'if', 'in', 'is', 'item',
      'instanceof', 'return', 'switch', 'this', 'throw', 'try', 'typeof', 'void',
      'while', 'write', 'with'
      ),
    2 => array(
      'class', 'const', 'default', 'debugger', 'export', 'extends', 'false',
      'function', 'import', 'namespace', 'new', 'null', 'package', 'private',
      'protected', 'public', 'super', 'true', 'use', 'var'
      ),
    3 => array(

      // common functions for Window object
      'alert', 'back', 'blur', 'close', 'confirm', 'focus', 'forward', 'home',
      'name', 'navigate', 'onblur', 'onerror', 'onfocus', 'onload', 'onmove',
      'onresize', 'onunload', 'open', 'print', 'prompt', 'scroll', 'status',
      'stop',
      )
    ),
  'SYMBOLS' => array(
    '(', ')', '[', ']', '{', '}', '!', '@', '%', '&', '*', '|', '/', '<', '>'
    ),
  'CASE_SENSITIVE' => array(
    GESHI_COMMENTS => false,
    1 => false,
    2 => false,
    3 => false
    ),
  'STYLES' => array(
    'KEYWORDS' => array(
      1 => 'color: #000066; font-weight: bold;',
      2 => 'color: #003366; font-weight: bold;',
      3 => 'color: #000066;'
      ),
    'COMMENTS' => array(
      1 => 'color: #009900; font-style: italic;',
      'MULTI' => 'color: #009900; font-style: italic;'
      ),
    'ESCAPE_CHAR' => array(
      0 => 'color: #000099; font-weight: bold;'
      ),
    'BRACKETS' => array(
      0 => 'color: #66cc66;'
      ),
    'STRINGS' => array(
      0 => 'color: #3366CC;'
      ),
    'NUMBERS' => array(
      0 => 'color: #CC0000;'
      ),
    'METHODS' => array(
      1 => 'color: #006600;'
      ),
    'SYMBOLS' => array(
      0 => 'color: #66cc66;'
      ),
    'REGEXPS' => array(
      0 => 'color: #0066FF;'
      ),
    'SCRIPT' => array(
      0 => '',
      1 => '',
      2 => '',
      3 => ''
      )
    ),
  'URLS' => array(
		1 => '',
		2 => '',
		3 => ''
  	),
  'OOLANG' => true,
  'OBJECT_SPLITTERS' => array(
  	1 => '.'
	),
  'REGEXPS' => array(
    0 => "/.*/([igm]*)?"         // matches js reg exps
    ),
  'STRICT_MODE_APPLIES' => GESHI_MAYBE,
  'SCRIPT_DELIMITERS' => array(
    0 => array(
      '<script type="text/javascript">' => '</script>'
      ),
    1 => array(
      '<script language="javascript">' => '</script>'
      )
    ),
  'HIGHLIGHT_STRICT_BLOCK' => array(
    0 => true,
    1 => true
  )
);

?>