<jdoc:comment>
@copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
@license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
Joomla! is free software. This version may have been modified pursuant
to the GNU General Public License, and as distributed it includes or
is derivative of works licensed under the GNU General Public License or
other free or open source software licenses.
See COPYRIGHT.php for copyright notices and details.
</jdoc:comment>
<?php
global $mosConfig_lang;
$languages = array();
$languages = JLanguageHelper::createLanguageList( $mosConfig_lang );
array_unshift( $languages, mosHTML::makeOption( '', JText::_( 'Default' ) ) );
$lists['langs'] = mosHTML::selectList( $languages, 'lang', ' class="inputbox"', 'value', 'text', '' );
$lang = $mainframe->getLanguage();
$tstart = JProfiler::getmicrotime();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{LANG_TAG}" lang="{LANG_TAG}" dir="{LANG_DIR}" >
	<head>
	<jdoc:include type="head" />
	<jdoc:tmpl name="isRTL" varscope="login.php" type="condition" conditionvar="LANG_ISRTL">
		<jdoc:sub condition="1">
			<link href="templates/{TEMPLATE}/css/template_rtl.css" rel="stylesheet" type="text/css" />
		</jdoc:sub>
		<jdoc:sub condition="0">
			<link href="templates/{TEMPLATE}/css/template.css" rel="stylesheet" type="text/css" />
		</jdoc:sub>
	</jdoc:tmpl>
	<link rel="stylesheet" type="text/css" href="templates/{TEMPLATE}/css/nifty.css" />
	<link rel="stylesheet" type="text/css" href="templates/{TEMPLATE}/css/nifty_print.css" media="print" />
	<script type="text/javascript" src="templates/{TEMPLATE}/js/nifty.js"></script>
	<script type="text/javascript">
	window.onload=function(){
		if(!NiftyCheck()) alert("hello");
		Rounded("div.component","all","#fff","#fff","border #ccc");
		Rounded("div.element-box","all","#fff","#fbfbfb","border #ccc");
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
		<div id="header1">
			<div id="header2">
				<div id="header3">
					<span><jdoc:translate>Administration</jdoc:translate></span>
				</div>
			</div>
		</div>
		<div id="content-box">
			<div id="content-pad">
				<form action="index.php" method="post" name="loginForm" id="loginForm">	
					<div id="login-content-pad">
						<div id="login" class="component">
							<div id="loginpad">
								<h1><jdoc:translate>Joomla! Administration Login</jdoc:translate></h1>
								<div class="element-box" id="login-form">
									<div class="element-pad">
										<table class="login">
											<tr>
												<td>
													<label for="username"><jdoc:translate>Username</jdoc:translate></label>
												</td>
												<td>
													<input name="username" id="username" type="text" class="inputbox" size="15" />
												</td>
											</tr>
											<tr>
												<td>
													<label for="password"><jdoc:translate>Password</jdoc:translate></label>
												</td>
												<td>
													<input name="passwd" id="password" type="password" class="inputbox" size="15" />
												</td>
											</tr>
											<tr>
												<td>
													<label for="language"><jdoc:translate>Language</jdoc:translate></label>
												</td>
												<td>
													<?php echo $lists['langs']; ?>
												</td>
											</tr>
											<tr>
												<td colspan="2">
													<div class="far-right">
														<div class="button1-left">
															<div class="next">
																<a onclick="loginForm.submit();">
																	<jdoc:translate>Login</jdoc:translate></a>
															</div>
														</div>
														<input type="hidden" name="option" value="login" />
													</div>
												</td>
											</tr>
										</table>														
									</div>
								</div>

								<p><jdoc:translate>DESCUSEVALIDLOGIN</jdoc:translate></p>
								
								<p><a href="<?php echo $mainframe->getSiteURL(); ?>"><jdoc:translate>Return to site Home Page</jdoc:translate></a></p>
								<div class="clr"></div>
							</div>
						</div>
					</div>
				</form>
				<noscript>
					<jdoc:translate key="WARNJAVASCRIPT" />
				</noscript>
			</div>
		</div>		
		<div id="footer1">
			<div id="footer2">
				<div id="footer3"></div>
			</div>
		</div>	
		
		<div id="copyright"><a href="http://www.joomla.org" target="_blank">Joomla!</a>
			<jdoc:translate key="isFreeSoftware">is Free Software released under the GNU/GPL License.</jdoc:translate>	
		</div>
	</body>
</html>







