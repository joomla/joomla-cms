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

/** Set flag that this is a parent file */
define( "_VALID_MOS", 1 );

require_once( 'configuration.php' );

//if (!$mosConfig_xmlrpc_server) {
//	die( 'XML-RPC server not enabled.' );
//}
require_once( JPATH_SITE .'includes/joomla.php' );

jimport('domit.dom_xmlrpc_client.php' );

//$_LANG = JFactory::getLanguage();
//$_LANG->debug( $mosConfig_debug );

error_reporting( E_ALL );

$uri 	= dirname( $_SERVER['PHP_SELF'] );

$host 	= mosGetParam( $_POST, 'host', 'http://' . $_SERVER['HTTP_HOST'] );
$path 	= mosGetParam( $_POST, 'path', $uri . '/xmlrpc.server.php' );
$debug 	= mosGetParam( $_POST, 'debug', 0 );
$task 	= mosGetParam( $_POST, 'task', 0 );

$output = '';
if ($task) {
	$client = new dom_xmlrpc_client( $host, $path );
	$client->setResponseType( 'array' );

	if ($debug) {
		$client->setHTTPEvent( 'onRequest', true );
		$client->setHTTPEvent( 'onResponse', true );
	}

	switch ($task) {
		case 'list_methods':
			$myXmlRpc = new dom_xmlrpc_methodcall( 'system.listMethods' );
			$xmlrpcdoc = $client->send( $myXmlRpc );

			if (!$xmlrpcdoc->isFault()) {
				$methods = $xmlrpcdoc->getParam(0);
			} else {
				print $xmlrpcdoc->getFaultString();
			}

			foreach ($methods as $k=>$v) {
				$methods[$k] = mosHTML::makeOption( $v );
			}
			$output = 'Methods<br />';
			$output .= mosHTML::selectList( $methods, 'method', 'size="10', 'value', 'text' );
			$output .= ' <input name="args[]" type="text" />';
			$output .= ' <input name="task" type="submit" value="exec" />';

			break;
		case 'exec':
			$method = mosGetParam( $_POST, 'method', '' );
			$args = mosGetParam( $_POST, 'args', array() );

			$myXmlRpc = new dom_xmlrpc_methodcall( $method, $args[0] );
			$xmlrpcdoc = $client->send( $myXmlRpc );

			if (!$xmlrpcdoc->isFault()) {
				$output .= var_export( $xmlrpcdoc->getParam(0), true );
			} else {
				print $xmlrpcdoc->getFaultString();
			}


			break;
	}

}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
	<meta http-equiv="content-type" content="text/html; charset=windows-1250">
	<meta name="generator" content="PSPad editor, www.pspad.com">
	<title>Joomla! XML-RPC Test Client</title>
	<style type="text/css">
	body {
		margin: 0px;
		padding: 0px;
		border: 0px;
		background-color: #A69A76;
	}
	form {
		margin: 0px;
		padding: 0px;
		border: 0px;
	}
	.page {
		margin-left: auto;
		margin-right: auto;
		margin-top: 10px;
		margin-bottom: 10px;
		padding: 5px;
		width:80%;
		background-color: #F2EBDD;
		text-align: left;
		font-family: Verdana, Arial, Helvetica, sans-serif;
		font-size: 11px;
	}
	td {
		font-size: 11px;
		color: #000000;
		text-decoration: none;
		line-height: 14px;
	}
	input {
		border: 1px solid #AD5900;
		background-color: #fff;
		padding: 2px;
	}
	.int_h1 {
		font-family: verdana;
		font-size: 18px;
		font-weight: bold;
	}
	.section_colour_bar{
		height: 2px;
		background-color:#AD5900;
	}
	.ctr {
		text-align: center;
	}
	</style>
	</head>
	<body>
		<form method="post">
		<div class="ctr" align="center">
			<div class="page" align="center">
				<div style="background-color:#fff">
					<div class="int_h1" style="padding: 3px 0 8px 5px;">Joomla! XML-RPC Test Client</div>
				</div>
				<div class="section_colour_bar"></div>
				<table>
					<tr>
						<td>XML-RPC Host</td>
						<td>
							<input name="host" type="text" size="50" value="<?= $host ?>" />
						</td>
					</tr>
					<tr>
						<td>Server File</td>
						<td>
							<input name="path" type="text" size="50" value="<?= $path ?>" />
							<input name="task" type="submit" value="list_methods" />
							Debug:
							<input name="debug" type="checkbox" value="1" <?= $debug ? 'checked="yes"' : '' ?>/>
						</td>
					</tr>
				</table>
				<?= $output ?>
			</div>
		</div>
		</form>
	</body>
</html>
