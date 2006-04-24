<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/** Set flag that this is a parent file */
define( "_JEXEC", 1 );

define('JPATH_BASE', dirname(__FILE__) );

require_once ( JPATH_BASE .'/includes/application.php' );

//if (!$mainframe->getCfg('xmlrpc_server')) {
//	die( 'XML-RPC server not enabled.' );
//}

jimport('phpxmlrpc.xmlrpc');

error_reporting( E_ALL );

$uri 	= dirname( $_SERVER['PHP_SELF'] );

$host 	= JRequest::getVar( 'host', 'http://' . $_SERVER['HTTP_HOST'], 'post' );
$path 	= JRequest::getVar( 'path', $uri . '/xmlrpc.server.php', 'post' );
$debug 	= JRequest::getVar( 'debug', 0, 'post', 'int' );
$task 	= JRequest::getVar( 'task', 0, 'post' );

$output = '';
$array  = array();

if ($task)
{
	$client = new xmlrpc_client("joomla11/xmlrpc.server.php", "localhost", 80);
	$client->setDebug(true);

	switch ($task)
	{
		case 'list_methods':
			$msg = new xmlrpcmsg('system.listMethods');
			$xmlrpcdoc = $client->send($msg);

			if ($xmlrpcdoc->faultCode() == 0) {
				$result = $xmlrpcdoc->value();
				$array = $result->scalarval();
			} else {
				print $xmlrpcdoc->faultString();
			}

			for ($i=0; $i < sizeof($array); $i++)
			{
				$var = new xmlrpcval($array[$i]);
				$array_method = $var->scalarval();

				$methods[$i] = mosHTML::makeOption($array_method->scalarval());
			}

			$output = 'Methods<br />';
			$output .= mosHTML::selectList( $methods, 'method', 'size="10', 'value', 'text' );
			$output .= ' <input name="args" type="text" />';
			$output .= ' <input name="task" type="submit" value="exec" />';

			break;

		case 'exec':
			$method = JRequest::getVar( 'method' );
			$args 	= JRequest::getVar( 'args' );

			$message = new xmlrpcmsg($method, array(new xmlrpcval('okidoki', 'string')));

			$xmlrpcdoc = $client->send($message);

			if ($xmlrpcdoc->faultCode()== 0) {
				$scalar_var = $xmlrpcdoc->value();
				$output = var_export($scalar_var->scalarval(), true);
			} else {
				print $xmlrpcdoc->faultString();
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
