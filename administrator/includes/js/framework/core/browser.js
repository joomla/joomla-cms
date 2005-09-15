// <?php !! This fools phpdocumentor into parsing this file
/**
* @version $Id: browser.js 4 2005-09-06 19:22:37Z akede $
* @package Mambo
* @subpackage javascript
* @copyright (C) 2000 - 2005 Miro International Pty Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Mambo is Free Software
*/

// -- Browser information -----------------------
Browser = new Object();
Browser.agt    = navigator.userAgent.toLowerCase();
Browser.is_ie	= ((Browser.agt.indexOf("msie") != -1) && (Browser.agt.indexOf("opera") == -1));