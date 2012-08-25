<?php
/**
 * @package     Jokte.Site
 * @subpackage	joktekyju
 * @copyright   Copyright (C) 2012 - 2014 Open Jokte, Inc. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */
// Previene el acceso directo.
defined('_JEXEC') or die;
JHtml::_('behavior.framework', true);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
    <head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <jdoc:include type="head" />
		<link type="text/css" rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/template.css" media="all"/>
		<link type="text/css" rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/base.css" media="all"/>
		<!--[if lt IE 9 ]><link rel="stylesheet" href="./css/720_grid.css" type="text/css"><![endif]-->
		<link type="text/css" rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/720_grid.css" media="screen and (min-width: 720px)"/>
		<link type="text/css" rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/986_grid.css" media="screen and (min-width: 986px)"/>
		<link type="text/css" rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/1236_grid.css" media="screen and (min-width: 1236px)"/>
		<link type="text/css" rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/flexnav.css"/>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
		<script type="text/javascript" src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/js/jquery.flexnav.js"></script>	
		<script type="text/javascript" src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/js/imgSizer.js"></script>
		<script type="text/javascript" charset="utf-8">
		addLoadEvent(function() {
			imgSizer.collate();
		});

		function addLoadEvent(func) {
			var oldonload = window.onload;
			if (typeof window.onload != 'function') {
				window.onload = func;
			} else {
				window.onload = function() {
					if (oldonload) {
						oldonload();
					}
					func();
				}
			}
		}
		</script>
    </head>	
	<body>		
		<div class="grid">
			<div class="banner">
				<div class="logoimg">
					<a href="index.php"><img src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/images/jokte-kyju-logo.png" alt=""/></a>
					<div class="topright">						
						<div class="socialnet">
							<jdoc:include type="modules" name="socialnet" style="xhtml" />
						</div>	
						<div class="search">
							<jdoc:include type="modules" name="search" style="xhtml" />
						</div>
					</div>
				</div>					
				<p class="intro">Una plantilla responsiva para Jokte! con licencia <strong>GNU/GPL</strong>.</p>
			</div>
			<div style="clear:both"></div>
			<div class='menu-button'>Menu</div>	
			<nav><jdoc:include type="modules" name="menutop" style="none" /></nav>
			<?php 
				//  USER2 - USER3 - USER4
				// Debe existir al menos el USER2 o nada		
			?>
			<?php if ($this->countModules('user2') && $this->countModules('user3') && $this->countModules('user4')){?>
				<div class="row">
					<div class="slot-0-1"><jdoc:include type="modules" name="user2" style="xhtml" /></div>
					<div class="slot-2-3"><jdoc:include type="modules" name="user3" style="xhtml" /></div>
					<div class="slot-4-5"><jdoc:include type="modules" name="user4" style="xhtml" /></div>
				</div>
				<div class="sep-user"></div>
			<?php } elseif ($this->countModules('user2') && $this->countModules('user3')) { ?>
				<div class="row">
					<div class="slot-0-1-2"><jdoc:include type="modules" name="user2" style="xhtml" /></div>
					<div class="slot-3-4-5"><jdoc:include type="modules" name="user3" style="xhtml" /></div>
				</div>
				<div class="sep-user"></div>
			<?php } elseif ($this->countModules('user2')){ ?>
				<div class="row">
					<div class="slot-0-1-2-3-4"><jdoc:include type="modules" name="user2" style="xhtml" /></div>
				</div>
				<div class="sep-user"></div>
			<?php } ?>
			
			<?php 
				//  TOP1 - TOP2 - TOP3 - TOP4
				// Debe existir al menos el TOP1 o nada		
			?>
			<?php if ($this->countModules('top1') && $this->countModules('top2') && $this->countModules('top3') && $this->countModules('top4')){?>
				<div class="row">
					<div class="slot-6"><jdoc:include type="modules" name="top1" style="xhtml" /></div>
					<div class="slot-7"><jdoc:include type="modules" name="top2" style="xhtml" /></div>
					<div class="slot-7"><jdoc:include type="modules" name="top3" style="xhtml" /></div>
					<div class="slot-9"><jdoc:include type="modules" name="top4" style="xhtml" /></div>
				</div>
				<div class="sep-top"></div>
			<?php } elseif ($this->countModules('top1') && $this->countModules('top2') && $this->countModules('top3')) { ?>
				<div class="row">
					<div class="slot-0-1"><jdoc:include type="modules" name="top1" style="xhtml" /></div>
					<div class="slot-2-3"><jdoc:include type="modules" name="top2" style="xhtml" /></div>
					<div class="slot-4-5"><jdoc:include type="modules" name="top3" style="xhtml" /></div>
				</div>
				<div class="sep-top"></div>
			<?php } elseif ($this->countModules('top1') && $this->countModules('top2')) { ?>
				<div class="row">
					<div class="slot-0-1-2"><jdoc:include type="modules" name="top1" style="xhtml" /></div>
					<div class="slot-3-4-5"><jdoc:include type="modules" name="top2" style="xhtml" /></div>
				</div>
				<div class="sep-top"></div>
			<?php } elseif ($this->countModules('top1')) { ?>
				<div class="row">
					<div class="slot-0-1-2-3-4"><jdoc:include type="modules" name="top1" style="xhtml" /></div>
				</div>
				<div class="sep-top"></div>
			<?php } ?>
			
			<div class="maincontainer">
				<div class="row">
					<div class="slot-0"> 
						<jdoc:include type="modules" name="left" style="xhtml" />
					</div> 
		 
					<div class="slot-0-1-2-3"> 
						 <jdoc:include type="component" />
					</div> 
					<div class="slot-4-5"> 
						<jdoc:include type="modules" name="right" style="xhtml" />
					</div> 
				</div><!-- /.row -->
			
				<div class="row">
					<div class="modsbottom">
						<div class="slot-6">
							<jdoc:include type="modules" name="bot1" style="xhtml" />
						</div>
						<div class="slot-7">
							<jdoc:include type="modules" name="bot2" style="xhtml" />
						</div>
						<div class="slot-8">
							<jdoc:include type="modules" name="bot3" style="xhtml" />
						</div>
						<div class="slot-9 mod mod-download">
							<jdoc:include type="modules" name="bot4" style="xhtml" />
						</div>
					</div>
				</div>
				<div class="row footer">
					<p>Copyleft <a href="http://jokte.org">Jokte! Kyju Plantilla Responsiva</a></p>
				</div>
			</div><!-- / .grid -->
		</div>
		<script> 
			jQuery.noConflict();
			jQuery("[role='navigation']").flexNav(); 
		</script>
	</body> 
</html>