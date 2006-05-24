<jdoc:comment>
@copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
@license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
Joomla! is free software. This version may have been modified pursuant
to the GNU General Public License, and as distributed it includes or
is derivative of works licensed under the GNU General Public License or
other free or open source software licenses.
See COPYRIGHT.php for copyright notices and details.
</jdoc:comment>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{LANG_TAG}" lang="{LANG_TAG}" dir="{LANG_DIR}" >
	<head>
	<jdoc:include type="head" />
	<jdoc:tmpl name="loadcss" varscope="document" type="condition" conditionvar="LANG_DIR">
		<jdoc:sub condition="rtl">
			<link href="templates/{TEMPLATE}/css/login_rtl.css" rel="stylesheet" type="text/css" />
		</jdoc:sub>
		<jdoc:sub condition="ltr">
			<link href="templates/{TEMPLATE}/css/login.css" rel="stylesheet" type="text/css" />
		</jdoc:sub>
	</jdoc:tmpl>
	<link rel="stylesheet" type="text/css" href="templates/{TEMPLATE}/css/nifty.css" />
	<link rel="stylesheet" type="text/css" href="templates/{TEMPLATE}/css/nifty_print.css" media="print" />
	<script type="text/javascript" src="templates/{TEMPLATE}/js/nifty.js"></script>
	<script type="text/javascript">
	window.onload=function(){
		if(!NiftyCheck()) alert("hello");
		Rounded("div#login","all","#fff","#fff","border #ccc");
		Rounded("div#element-box","all","#fff","#fbfbfb","border #ccc");
		setFocus();
	}
	</script>
	<script language="javascript" type="text/javascript">
		function setFocus() {
			document.loginForm.username.select();
			document.loginForm.username.focus();
		}
	</script>
	</head>
	<body>
		<div id="border-top">
			<div>
				<div>
					<span class="title"><jdoc:translate>Administration</jdoc:translate></span>
				</div>
			</div>
		</div>
		<div id="content-box">
			<div class="padding"">
				<div id="login">
					<div class="padding">
						<h1><jdoc:translate>Joomla! Administration Login</jdoc:translate></h1>
						<div id="element-box">
							<div class="padding">
								<jdoc:include type="module" name="login" />
							</div>
						</div>
						<p><jdoc:translate>DESCUSEVALIDLOGIN</jdoc:translate></p>

						<p>
							<a href="<?php echo $mainframe->getSiteURL(); ?>"><jdoc:translate>Return to site Home Page</jdoc:translate></a>
						</p>
						<div class="clr"></div>
					</div>
				</div>
				<noscript>
					<jdoc:translate key="WARNJAVASCRIPT" />
				</noscript>
				<div class="clr"></div>
			</div>
		</div>
		<div id="border-bottom"><div><div></div></div>
		</div>

		<div id="footer">
			<p class="copyright">
				<a href="http://www.joomla.org" target="_blank">Joomla!</a>
				<jdoc:translate key="ISFREESOFTWARE">is Free Software released under the GNU/GPL License.</jdoc:translate>
			</p>
		</div>
	</body>
</html>