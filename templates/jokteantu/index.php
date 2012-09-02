<?php
/**
 * @package     Jokte.Site
 * @subpackage	jokteantu
 * @author 	    Equipo de desarrollo juuntos.
 * @copyleft    (comparte igual)  Jokte!
 * @license     GNU General Public License version 3 o superior.
*/
// Previene el acceso directo.
defined('_JEXEC') or die;


JHtml::_('behavior.framework', true);

/*
 * Carga script para calculo posiciones
 * Since:	Jokte 1.0
 * 
 */
require_once (JPATH_BASE . DS . 'templates' . DS . $this->template . '/asset/asset.php');
/*
 * Carga script para skins CSS
 * Since:	Jokte 1.0
 * 
 */
require_once (JPATH_BASE . DS . 'templates' . DS . $this->template . '/asset/skins.php');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
    <head>
        <jdoc:include type="head" />	
		<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/system.css" type="text/css" />
		<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/jokte.css" type="text/css" />
        <!--<script type="text/javascript" src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/javascript/menus.js"></script>-->		
        <link type="text/css" rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/template.css" />
		<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/menu/css2.css" type="text/css" />		
		<link type="text/css" rel="stylesheet" href="<?php echo $baseurlskin; ?>/color.css" />
		<!--[if lte IE 9]>
			<link href="<?php echo $baseurlskin; ?>/ie9.css" rel="stylesheet" type="text/css" />
		<![endif]-->	
		
		
<?php if ($typobody != "no-google") { ?>
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=<?php echo $typobody; ?>" />
<?php } ?>
<?php if ($typoespecial != "no-google") { ?>
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=<?php echo $typoespecial; ?>" />
<?php } ?>

<style type="text/css"> 
<?php if ($typobody != "no-google") { ?>
body {font-family: '<?php echo $typobody; ?>',FreeSans,Verdana, Geneva,Helvetica,Arial,Sans-Serif;
} 
<?php } ?>
<?php if ($typoespecial != "no-google") { ?>
h2, h3 ,h1,#slide, .moduletable .menu,span.dia, span.mes, span.año,.item-title,.content_vote, span.content_rating,.content_vote input.button,.moduletable_menu li,#izquierda .moduletable ul,#derecha .moduletable ul,.weblink-category .title a,.contador .weblink-count dd,.contador .weblink-count dt,label,input,legend,button,span.capital,#search-results .result-title{
font-family:'<?php echo $typoespecial; ?>',gargi,Verdana, Geneva, sans-serif;}
<?php } ?>
</style> 

		
    </head>

    <body>
   
        <div id="bodyBg" style="width:<?php echo $bodyw; ?>px;"><!-- ancho flexible según parametro -->
            
            <div id="top"> 
            	<?php 
				 /*
				  * Logo - Opciones
				  * - Por módulo HTML personalizado en la posición User1
				  * - Por Imagen seleccionable desde los parámetros de la plantilla
				  * - En texto plano dentro de la etiqueta H1 maquetable vía CSS de la plantilla  
				  */ ?>
                <?php if (!empty($user1) && empty($logo)): ?>
                    <div id="user1" style="width: <?php echo $user1w; ?>%;">
                        <jdoc:include type="modules" name="user1" style="xhtml" />
                    </div>
                <?php elseif (!empty($logo)): ?>
                    <div id="logo">
                        <a href="index.php"><img src="<?php echo $this->baseurl ?>/<?php echo $logo; ?>" alt="<?php echo $sitename; ?>" title="<?php echo $siteslogan; ?>"/></a>
                    </div>
                <?php else :?>
                	<div id="logo">
                		<?php if ($tslogan == '0') : ?>                			
                			<h1><a href="index.php" title="<?php echo $siteslogan; ?>"><?php echo $sitename; ?></a></h1>
                		<?php else : ?>
                			<h1><a href="index.php"><?php echo $sitename; ?></a></h1>
                			<small><?php echo $siteslogan; ?></small>
                		<?php endif; ?>
                	</div>
                <?php endif; ?>
                
                
                <?php 
				  /*
				  * Posiciones para redes sociales
				  * Módulo en la posición socialnet 
				  */ ?>
                <?php if (!empty($socialnet)) : ?>				
                    <div id="micon">
                        <jdoc:include type="modules" name="socialnet" style="jxhtml"/>
                    </div>
                    <?php
                endif;?>
				
				  <div id="cls"></div>
				<?php 
				 /*
				  * Posiciones para banners
				  * Dos posiciones de ancho variable  
				  * El ancho de las posiciones se indica en los parámetros de la plantilla
				  */?>				
                <?php if (!empty($banner1) || !empty($banner2)) : ?>
                   	<div id="banner">	
                   	 <?php if (!empty($banner1)) : ?>		
                        <div id="banner1" style=" <?php echo $banner1w; ?>;">
                           	<jdoc:include type="modules" name="banner1" style="jxhtml" />
                        </div>
				<?php
				endif;
                if (!empty($banner2)) : ?>
                   	<div id="banner2" style="<?php echo $banner2w; ?>">
                       	<jdoc:include type="modules" name="banner2" style="jxhtml" />
                   	</div>
               
				<?php endif; ?>  
				</div>
               
            <?php endif; ?> 
               <div id="cls"></div>
            <?php 
			 /*
			  * Posición para menu superior				 
			  */?>
            <?php if (!empty($topmenu)) : ?> 
                <div id="tmen"><jdoc:include type="modules" name="topmenu" style="jxmenu" /></div> 
                <?php
            endif;
			
			 
			 /*
			  * Posición para slider / deslizante				 
			  */
            if (!empty($slide)) :
                ?> 
                <div id="slide"><jdoc:include type="modules" name="slide" style="jxhtml" /></div>
            <?php endif; ?>


            <?php 
			 /*
			  * Posiciones User 2 - User 3 y User 4				 
			  */?>
            <?php if (!empty($user2) || !empty($user3) || !empty($user4)) : ?>
                <div id="topcentro">
                    <?php if (!empty($user2)) : ?>
                        <div id="user2" style="<?php echo $user2w; ?>">
                            <jdoc:include type="modules" name="user2" style="jxhtml" />
                        </div>
                        <?php
                    endif;

                    if (!empty($user3)) :
                        ?>
                        <div id="user3" style="<?php echo $user3w; ?>">
                            <jdoc:include type="modules" name="user3" style="jxhtml" />
                        </div>
                        <?php
                    endif;

                    if (!empty($user4)) :
                        ?>
                        <div id="user4" style="<?php echo $user4w; ?>">
                            <jdoc:include type="modules" name="user4" style="jxhtml" />
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php 
			 /*
			  * Posiciones right - content y left				 
			  */?>
            <div id="centro">   
            	<?php if (!empty($left)) : ?> 
            	<div id="izquierda" style="<?php echo $leftw; ?>">
                   		<?php if (!empty($lefttop)) : ?>
						<div id="izq-ini">
							<jdoc:include type="modules" name="lefttop" style="jxhtml" />
						</div>
					<?php endif; ?>	
					<?php if (!empty($insetleft) or ($outsetleft)) : ?> 
						<div id="centroizq">
							<?php if (!empty($insetleft)) : ?>
								<div id="izquierda1" >
									<jdoc:include type="modules" name="insetleft" style="jxhtml" />
								</div>
							<?php endif; ?>	
							<?php if (!empty($outsetleft)) : ?>
								<div id="izquierda2">					
									<jdoc:include type="modules" name="outsetleft" style="jxhtml" />
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					<?php if (!empty($leftbottom)) : ?>
						<div id="izq-fin">
							<jdoc:include type="modules" name="leftbottom" style="jxhtml" />
						</div>
					<?php endif; ?>
				</div>
			  <?php endif; ?>
              <!-- breadcrumbs + SobreContenedor +Contenedor + bajocontenedor. Ajustado a ancho de contenedor -->  
             
              
                <div id="contenedor" style="<?php echo $contentw; ?>">
                <?php if (!empty($bread)) : ?>
             <div id="breadcrumb">
				<jdoc:include type="modules" name="breadcrumb" /> 
             </div>
              <?php endif; ?>
                <?php if (!empty($sobrecontent)) : ?>
             <div id="sobrecontent">
				<jdoc:include type="modules" name="sobrecontent" style="jxhtml" /> 
             </div>
              <?php endif; ?>
              <jdoc:include type="message" />
                <div id="margen">
                    <jdoc:include type="component" />
                     <div id="cls"></div>
                     </div>
                    
                
                 <?php if (!empty($bajocontent)) : ?>
             <div id="bajocontent">
				<jdoc:include type="modules" name="bajocontent" style="jxhtml" /> 
             </div>
              <?php endif; ?>
				</div>
				<!-- Derecha con dos posiciones interiores según publicación de modulos -->
				<div id="derecha" style="<?php echo $rightw; ?>">
					<?php if (!empty($righttop)) : ?>
						<div id="d-ini">
							<jdoc:include type="modules" name="righttop" style="jxhtml" />
						</div>
					<?php endif; ?>	
					<?php if (!empty($inset) or ($outset)) : ?> 
						<div id="centroder">
							<?php if (!empty($inset)) : ?>
								<div id="derecha1">
									<jdoc:include type="modules" name="inset" style="jxhtml" />
								</div>
							<?php endif; ?>	
							<?php if (!empty($outset)) : ?>
								<div id="derecha2">					
									<jdoc:include type="modules" name="outset" style="jxhtml" />
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					<?php if (!empty($rightbot)) : ?>
						<div id="d-fin">
							<jdoc:include type="modules" name="rightbottom" style="jxhtml" />
						</div>
				 <?php endif; ?>
				</div>	

                <?php 
			 	/*
			  	 * Posiciones User 5 - User 6 y User 7				 
			  	*/?>
                <?php if (!empty($user5) || !empty($user6) || !empty($user7)) : ?>
                    <div id="topcentro">
                        <?php if (!empty($user5)) : ?>
                            <div id="user5" style="<?php echo $user5w; ?>">
                                <jdoc:include type="modules" name="user5" style="jxhtml" />
                            </div>
                            <?php
                        endif;

                        if (!empty($user6)) :
                            ?>
                            <div id="user6" style="<?php echo $user6w; ?>">
                                <jdoc:include type="modules" name="user6" style="jxhtml" />
                            </div>
                            <?php
                        endif;

                        if (!empty($user7)) :
                            ?>
                            <div id="user7" style="<?php echo $user7w; ?>">
                                <jdoc:include type="modules" name="user7" style="jxhtml" />
                            </div>
                        <?php endif; ?> 
                    </div>
                <?php endif; ?> 

                
                <?php 
			 	/*
			  	 * Posiciones footmenu y foot				 
			  	 */?>
                <?php if (!empty($footmenu) || !empty($foot)) : ?>
                    <div id="piefin">
                        <div id="piemenu">
                            <jdoc:include type="modules" name="footmenu" style="jxhtml" />
                        </div>
                        <div id="pie">
                            <jdoc:include type="modules" name="foot" style="jxhtml" />
                        </div>
                    </div>
                <?php endif; ?>  
            </div>
            <?php
            	if ($ga == '1'): ?>
            	<script type="text/javascript">
  					var _gaq = _gaq || [];
					_gaq.push(['_setAccount', '<?php echo $gacode ; ?>']);
  					_gaq.push(['_trackPageview']);

  					(function() {
    					var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    					ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    					var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  					})();
				</script>            	
           <?php endif; ?>
           	<jdoc:include type="modules" name="debug" />
    	</body>
</html>
