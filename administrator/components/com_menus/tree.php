<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title>Unobtrusive JS Tree Test</title>
	<link rel="stylesheet" href="assets/tree.css" type="text/css">
	<script type="text/javascript" src="../../../includes/js/joomla/common.js"></script>
	<script type="text/javascript" src="../../../includes/js/joomla/cookie.js"></script>
	<script type="text/javascript" src="assets/tree.js"></script>
<?php
	// Require the xajax library
	require_once('assets/xajax/xajax.inc.php');

	/*
	 * Instantiate the xajax object and register the functions
	 */
	$xajax = new xajax('http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/jajax.php');
	$xajax->registerFunction(array('tree', 'JAJAXHandler', 'getTree'));
	$xajax->debugOn();
	echo $xajax->getJavascript('', 'assets/xajax.js', 'assets/xajax.js');
?>
</head>
<body>
	<p>Menu Selection</p>
	<ul id="tree2" class="jtree">
		<li><a href="#" id="node_com">Component</a>
			<ul>
				<li><a href="index.php?option=com_menus&task=type&menutype=mainmenu&cid[]=<?php echo $this->item->id; ?>&amp;expand=content" id="content" onclick="xajax_tree(this.parentNode.id,this.id);">Articles</a></li>
				<li><a href="contact" id="contact" onclick="xajax_tree(this.parentNode.id,this.id);">Contact</a></li>
				<li><a href="weblinks" id="weblinks" onclick="xajax_tree(this.parentNode.id,this.id);">Weblinks</a></li>
			</ul>
		</li>
		<li><a href="#" id="node_url">URL</a></li>
		<li><a href="#" id="node_sep">Separator</a></li>
		<li><a href="#" id="node_link">Menulink</a></li>	
	</ul>
</body>
</html>