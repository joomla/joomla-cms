<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( 'includes/framework.php' );

/**
* Joomla! Application class
*
* Provide many supporting API functions
* 
* @package Joomla
* @final
*/
class JSite extends JApplication {

	/**
	* Class constructor
	* 
	* @access protected
	* @param integer A client id
	*/
	function __construct($option) {
		parent::__construct($option, 0);
	}
}

class JErrorHandlerSite
{
   /**
	* Error handler that outputs pretty debugging HTML
	*
	* Displays:
	* - Error level
	* - Error Message
	* - Error info
	* - Error file
	* - Error line
	* - plus the call stack that lead to the error
	*
	* The output has been inspired by Schst's debug, updated for a
	* designer's eye.
	*
	* @access	public
	* @static
	* @param	object		error object
	* @return	object		error object
	*/
	function &siteDebug( &$error )
	{	
		echo '<style>';
		echo '.Frame{';
		echo '	background-color:#FEFCF3;';
		echo '	padding:8px;';
		echo '	border:solid 1px #000000;';
		echo '	margin-top:13px;';
		echo '	margin-bottom:25px;';
		echo '	width:100%;';
		echo '}';
		echo '.Table{';
		echo '	border-collapse:collapse;';
		echo '	margin-top:13px;';
		echo '	width:100%;';
		echo '}';
		echo '.TD{';
		echo '	padding:3px;';
		echo '	padding-left:5px;';
		echo '	padding-right:5px;';
		echo '	border:solid 1px #bbbbbb;';
		echo '}';
		echo '.Type{';
		echo '	background-color:#cc0000;';
		echo '	color:#ffffff;';
		echo '	font-weight:bold;';
		echo '	padding:3px;';
		echo '}';
		echo '</style>';
	
		echo	'<div class="Frame">';
		printf(
				'<div style="margin-bottom:8px;"><span class="Type">%s:</span> %s in %s on line %s</div>Details: %s',
				patErrorManager::translateErrorLevel( $error->getLevel() ),
				$error->getMessage(),
				$error->getFile(),
				$error->getLine(),
				$error->getInfo()
			);

		$backtrace	=	$error->getBacktrace();
		if( is_array( $backtrace ) )
		{
			$j	=	1;
			echo	'<table border="0" cellpadding="0" cellspacing="0" class="Table">';
			echo	'	<tr>';
			echo	'		<td colspan="3" align="left" class="TD"><strong>Call stack</strong></td>';
			echo	'	</tr>';
			echo	'	<tr>';
			echo	'		<td class="TD"><strong>#</strong></td>';
			echo	'		<td class="TD"><strong>Function</strong></td>';
			echo	'		<td class="TD"><strong>Location</strong></td>';
			echo	'	</tr>';
			for( $i = count( $backtrace )-1; $i >= 0 ; $i-- )
			{
				echo	'	<tr>';
				echo	'		<td class="TD">'.$j.'</td>';
				if( isset( $backtrace[$i]['class'] ) )
				{
					echo	'	<td class="TD">'.$backtrace[$i]['class'].$backtrace[$i]['type'].$backtrace[$i]['function'].'()</td>';
				}
				else
				{
					echo	'	<td class="TD">'.$backtrace[$i]['function'].'()</td>';
				}
				if( isset( $backtrace[$i]['file'] ) )
				{
					echo	'		<td class="TD">'.$backtrace[$i]['file'].':'.$backtrace[$i]['line'].'</td>';
				}
				else
				{
					echo	'		<td class="TD">&nbsp;</td>';
				}
				echo	'	</tr>';
				$j++;
			}
			echo	'</table>';
		}
		echo	'</div>';
		
		$level	=	$error->getLevel();
		
		if( $level != E_ERROR )
			return	$error;
			
		exit();
	}
}
	
// setup handler for each error-level
JError::setErrorHandling( E_ERROR, 'callback', array( new JErrorHandlerSite, 'siteDebug' ) );

/** 
 * @global $_VERSION 
 */
$_VERSION = new JVersion();

/**
 *  Legacy global
 * 	use JApplicaiton->registerEvent and JApplication->triggerEvent for event handling
 *  use JPlugingHelper::importGroup and JPluginHelper::import to load bot code
 *  @deprecated As of version 1.1
 */
$_MAMBOTS = new mosMambotHandler();
?>