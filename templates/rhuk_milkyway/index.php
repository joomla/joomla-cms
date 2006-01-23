<jdoc:comment>
@copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
@license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
Joomla! is free software. This version may have been modified pursuant
to the GNU General Public License, and as distributed it includes or
is derivative of works licensed under the GNU General Public License or
other free or open source software licenses.
See COPYRIGHT.php for copyright notices and details.
</jdoc:comment><?php echo '<?xml version="1.0" encoding="utf-8"?' .'>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{LANG_TAG}" lang="{LANG_TAG}" dir="{LANG_DIR}" >
<head>
<jdoc:placeholder type="head" />
<link href="templates/{TEMPLATE}/css/template.css" rel="stylesheet" type="text/css" />
<!--[if lte IE 6]>
<link href="templates/{TEMPLATE}/css/ieonly.css" rel="stylesheet" type="text/css" />
<![endif]-->
<jdoc:tmpl name="isRTL" varscope="index.php" type="condition" conditionvar="LANG_ISRTL">
	<jdoc:sub condition="1">
		<link href="templates/{TEMPLATE}/css/template_rtl.css" rel="stylesheet" type="text/css" />
	</jdoc:sub>
</jdoc:tmpl>
</head>
<body id="page_bg">
<a name="up" id="up"></a>

<div class="center" align="center">
	<div id="wrapper">
		<div id="wrapper_r">
			<div id="header">
				<div id="header_l">
					<div id="header_r">
						<div id="logo"></div>
						<jdoc:placeholder type="modules" name="top" style="-1" />
					</div>
				</div>
			</div>
			<div id="tabarea">
				<div id="tabarea_l">
					<div id="tabarea_r">
						<div id="tabmenu">
			  	    	<table cellpadding="0" cellspacing="0" class="pill">
	    				    <tr>
	    				      <td class="pill_l">&nbsp;</td>
	    				      <td class="pill_m">
	    				        <div id="pillmenu">
	    				        	<jdoc:placeholder type="modules" name="user3" style="-1" />
	    				        </div>
	    				      </td>
	    				      <td class="pill_r">&nbsp;</td>
	    				    </tr>
	    				  </table>
						</div>
					</div>
				</div>
			</div>
			<div id="search">
				<jdoc:placeholder type="modules" name="user4" style="-1" />
			</div>
			<div id="pathway">
				<jdoc:placeholder type="module" name="breadcrumbs" style="-1" />
			</div>
			<div class="clr"></div>
			<div id="whitebox">
				<div id="whitebox_t">
					<div id="whitebox_tl">
						<div id="whitebox_tr"></div>
					</div>
				</div>
				<div id="whitebox_m">
					<div id="area">
						<div id="leftcolumn">
							<jdoc:placeholder type="modules" name="left" style="-3" />
						</div>
						<div id="maincolumn">
							<?php if(mosCountModules('user1') || mosCountModules('user2')) { ?>
							<table class="nopad user1user2">
								<tr valign="top">
									<?php if(mosCountModules('user1')) { ?>
									<td>
										<jdoc:placeholder type="modules" name="user1" style="-2" />
									</td>
									<?php } ?>
									<?php if(mosCountModules('user1') && mosCountModules('user2')) { ?>
									<td class="greyline">&nbsp;</td>
									<?php } ?>
									<?php if(mosCountModules('user2')) { ?>
									<td>
										<jdoc:placeholder type="modules" name="user2" style="-2" />
									</td>
									<?php } ?>
								</tr>
							</table>
							<div id="maindivider"></div>
							<?php } ?>
							<table class="nopad">
								<tr valign="top">
									<td>
										<jdoc:tmpl name="showComponent" varscope="index.php" type="condition" conditionvar="PARAM_SHOWCOMPONENT">
											<jdoc:sub condition="1">
												<jdoc:placeholder type="component" />
											</jdoc:sub>
											<jdoc:sub condition="0">
												&nbsp;
											</jdoc:sub>
										</jdoc:tmpl>
									</td>
									<?php if(mosCountModules('right') && $task != 'edit' ) { ?>
									<td class="greyline">&nbsp;</td>
									<td width="170">
										<jdoc:placeholder type="modules" name="right" style="-2"/>
									</td>
									<?php } ?>
								</tr>
							</table>

						</div>
						<div class="clr"></div>
					</div>
					<div class="clr"></div>
				</div>
				<div id="whitebox_b">
					<div id="whitebox_bl">
						<div id="whitebox_br"></div>
					</div>
				</div>
			</div>
			<div id="footerspacer"></div>
		</div>
		<div id="footer">
			<div id="footer_l">
				<div id="footer_r">
					<jdoc:placeholder type="modules" name="footer" style="-1" />
				</div>
			</div>
		</div>
	</div>
</div>
<jdoc:placeholder type="modules" name="debug" style="-1"/>
</body>
</html>