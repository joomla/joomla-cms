<?php
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );
$lang =& $mainframe->getLanguage();
// needed to seperate the ISO number from the language file constant _ISO
$iso = split( '=', _ISO );
// xml prolog
echo '<?xml version="1.0" encoding="'. $iso[1] .'"?' .'>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php mosShowHead();?>
<link href="<?php echo JURL_SITE;?>/templates/<?php echo $mainframe->getTemplate(); ?>/css/template_css.css" rel="stylesheet" type="text/css" />
<!--[if lte IE 6]>
<link href="<?php echo JURL_SITE;?>/templates/<?php echo $mainframe->getTemplate(); ?>/css/template_ie_only.css" rel="stylesheet" type="text/css" />
<![endif]-->
<?php if ($lang->isRTL()){ ?>
<link href="<?php echo JURL_SITE;?>/templates/<?php echo $mainframe->getTemplate(); ?>/css/template_css_rtl.css" rel="stylesheet" type="text/css" />
<?php } ?>
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
						<?php mosLoadModules('top', -1); ?>
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
	    				          <?php mosLoadModules('user3', -1); ?>
	    				        </div>
	    				      </td>
	    				      <td class="pill_r">&nbsp;</td>
	    				    </tr>
	    				  </table>
						</div>
					</div>
				</div>
			</div>
			<div id="search"><?php mosLoadModules('user4', -1); ?></div>
			<div id="pathway"><?php mosLoadModule('breadcrumbs'); ?></div>
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
							<?php mosLoadModules('left', -3); ?>
						</div>
						<div id="maincolumn">
							<?php if(mosCountModules('user1') || mosCountModules('user2')) { ?>
							<table class="nopad user1user2">
								<tr valign="top">
									<?php if(mosCountModules('user1')) { ?>
									<td>
										<?php mosLoadModules('user1', -2); ?>
									</td>
									<?php } ?>
									<?php if(mosCountModules('user1') && mosCountModules('user2')) { ?>
									<td class="greyline">&nbsp;</td>
									<?php } ?>
									<?php if(mosCountModules('user2')) { ?>
									<td>
										<?php mosLoadModules('user2', -2); ?>
									</td>
									<?php } ?>
								</tr>
							</table>
							<div id="maindivider"></div>
							<?php } ?>
							<table class="nopad">
								<tr valign="top">
									<td>
										<?php mosMainBody(); ?>
									</td>
									<?php if(mosCountModules('right')) { ?>
									<td class="greyline">&nbsp;</td>
									<td width="170">
										<?php mosLoadModules('right', -2); ?>
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
					<?php mosLoadModules( 'footer', -1);?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php mosLoadModules( 'debug', -1 ); ?>
</body>
</html>
