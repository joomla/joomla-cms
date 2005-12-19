<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
echo '<?xml version="1.0" encoding="UTF-8"?' .'>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php mosShowHead(); ?>
<?php
$collspan_offset = ( mosCountModules( 'right' ) + mosCountModules( 'user2' ) ) ? 2 : 1;
//script to determine which div setup for layout to use based on module configuration
$user1 = 0;
$user2 = 0;
$colspan = 0;
$right = 0;
// banner combos

//user1 combos
if ( mosCountModules( 'user1' ) + mosCountModules( 'user2' ) == 2) {
	$user1 = 2;
	$user2 = 2;
	$colspan = 3;
} elseif ( mosCountModules( 'user1' ) == 1 ) {
	$user1 = 1;
	$colspan = 1;
} elseif ( mosCountModules( 'user2' ) == 1 ) {
	$user2 = 1;
	$colspan = 1;
}

//right based combos
if ( mosCountModules( 'right' ) and ( empty( $_REQUEST['task'] ) || $_REQUEST['task'] != 'edit' ) ) {
	$right = 1;
}
?>
<link href="<?php echo JURL_SITE;?>/templates/rhuk_solarflare_ii/css/template_css.css" rel="stylesheet" type="text/css"/>
</head>
<body>

<div align="center">
	<table border="0" cellpadding="0" cellspacing="0" width="808">
		<tr>
			<td class="outline">
		  		<div id="buttons_outer">
		  		  <div id="buttons_inner">
						<div id="buttons">
						<?php mosLoadModules ( 'user3', -1); ?>
						</div>
					</div>
		  		</div>
		  		<div id="search_outer">
		  		  <div id="search_inner">
		  		  <?php mosLoadModules ( 'user4', -1 ); ?>
		  		  </div>
		  		</div>
		  		<div class="clr"></div>
		  		<div id="header_outer">
		  			<div id="header">
		  			&nbsp;
		  			</div>
		  			<div id="top_outer">
						<div id="top_inner">
						<?php
			  			if ( mosCountModules( 'top' ) ) {
			  				mosLoadModules ( 'top', -2 );
			  			} else {
			  				?>
			  				<span class="error">Top Module Empty</span>
			  				<?php
			  			}
			  			?>
						 </div>
				  </div>
		  		</div>
		  		<div id="left_outer">
		  			<div id="left_inner">
		  			<?php mosLoadModules ( 'left', -2 ); ?>
		  			</div>
		  		</div>
		  		<div id="content_outer">
					<div id="content_inner">
					<?php
		  			if ( mosCountModules ('banner') ) {
		  				?>
		  				<table border="0" cellpadding="0" cellspacing="0" width="100%" class="content_table">
						<tr>
								<td>
									<div id="banner_inner">
									<img src="<?php echo JURL_SITE;?>/templates/rhuk_solarflare_ii/images/advertisement.png" alt="advertisement.png, 0 kB" title="advertisement" border="0" height="8" width="468"/><br />
			  					<?php mosLoadModules( 'banner', -1 ); ?><br />
									</div>
									<div id="poweredby_inner">
										<img src="<?php echo JURL_SITE;?>/templates/rhuk_solarflare_ii/images/powered_by.png" alt="powered_by.png, 1 kB" title="powered_by" border="0" height="68" width="165"/><br />
									</div>
								</td>
							</tr>
							</table>
							<?php
		  			}
		  			?>
		  			<table border="0" cellpadding="0" cellspacing="0" width="100%" class="content_table">
						<tr valign="top">
							<td width="99%">
								<table border="0" cellpadding="0" cellspacing="0" width="100%" class="content_table">

								<?php
								if ($colspan > 0) {
								?>
									<tr valign="top">
										<?php
				  					if ( $user1 > 0 ) {
				  						?>
				  						<td width="50%">
				  							<div class="user1_inner">
				  							<?php mosLoadModules ( 'user1', -2 ); ?>
				  							</div>
				  						</td>
				  						<?php
				  					}
				  					if ( $colspan == 3) {
										 ?>
											<td width="2">
												<img src="<?php echo JURL_SITE;?>/templates/rhuk_solarflare_ii/images/spacer.png" alt="" title="spacer" border="0" height="10" width="2"/>
											</td>
										<?php
										}
				  					if ( $user2 > 0 ) {
				  						?>
				  						<td width="50%">
				  							<div class="user2_inner">
				  							<?php mosLoadModules ( 'user2', -2 ); ?>
				  							</div>
				  						</td>
				  						<?php
				  					}
										?>
									</tr>
									<tr>
										<td colspan="<?php echo $colspan; ?>">
											<img src="<?php echo JURL_SITE;?>/templates/rhuk_solarflare_ii/images/spacer.png" alt="" title="spacer" border="0" height="2" width="100"/><br />
										</td>
									</tr>
									<?php
									}
								?>
								<tr>
									<td colspan="<?php echo $colspan; ?>">
										<div id="pathway_text">
											<?php mosPathWay(); ?>
										</div>
									</td>
								</tr>
								<tr>
									<td colspan="<?php echo $colspan; ?>" class="body_outer">
				  					 <?php mosMainBody(); ?>
									</td>
								</tr>
								</table>


							</td>
							<?php
							if ( $right > 0 ) {
		  				?>
		  				<td>
			  				<div id="right_outer">
			  					<div id="right_inner">
			  					<?php mosLoadModules ( 'right', -2 ); ?>
			  					</div>
			  				</div>
		  				</td>
		  				<?php
		  			}
		  			?>

						</tr>
						</table>
		  		</div>
		  	</div>
		  </td>
	  </tr>
  </table>
</div>
<div align="center">
	<?php mosLoadModules( 'footer', -1 );?>
</div>
<?php mosLoadModules( 'debug', -1 );?>
</body>
</html>