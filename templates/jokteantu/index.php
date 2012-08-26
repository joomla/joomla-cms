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
<?php
    /*
     * No se si evitar el modo quirk para xhtml afecte los Chromes de los módulos 
     * porque estos utilizan jxhtml, probaré eliminando el quirk y revisaré
     * los títulos h3. Un quirk es un capricho del navegador ;)
     */
?>

<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
    <head>
        <jdoc:include type="head" />	

        <?php
            /*
             * PARA HACER: Inicializar en las hojas de estilo las propiedades CSS
             * de los nuevos elementos HTML
             */
        ?>
        <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/html5.css" />

		<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/system.css" type="text/css" />
		<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/jokte.css" type="text/css" />
        <!--<script type="text/javascript" src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/javascript/menus.js"></script>-->		
        <link type="text/css" rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/template.css" />
		<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/menu/css2.css" type="text/css" />		
		<link type="text/css" rel="stylesheet" href="<?php echo $baseurlskin; ?>/color.css" />

        <?php
            /* 
             * - Se podría brindar mayor compatibilidad entre navegadores especialmente
             * para IE 6, 7, 8, 9 al crear un script en JavaScript que cree en el DOM
             * los nuevos elementos como header, section, aside, nav entre otros, 
             * correspondientes a la especificación HTML5 y sean renderizados correctamente.
             *
             * - Evitaríamos la línea siguiente
             * - ie9.css (404)
             */
        ?>
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
    
    <?php
        /*
         * - Conservo el body para respetar las propiedades de estilo preestablecidas
         * - Doy inicio a la implementación de los nuevos elementos HTML5 en el 
         * - documento
         */
    ?>
    <body>
   
        <div id="bodyBg" style="width:<?php echo $bodyw; ?>px;"><!-- ancho flexible según parametro -->
            
            <header> 
                    <?php 
                     /*
                      * Logo - Opciones
                      * - Por módulo HTML personalizado en la posición User1
                      * - Por Imagen seleccionable desde los parámetros de la plantilla
                      * - En texto plano dentro de la etiqueta H1 maquetable vía CSS de la plantilla  
                      */ ?>
                    <?php if (!empty($user1) && empty($logo)): ?>
                        <hgroup>
                            <section id="user1" style="width: <?php echo $user1w; ?>%;">
                                <jdoc:include type="modules" name="user1" style="xhtml" />
                            </section>
                    <?php elseif (!empty($logo)): ?>
                        <section id="logo">
                            <a href="index.php"><img src="<?php echo $this->baseurl ?>/<?php echo $logo; ?>" alt="<?php echo $sitename; ?>" title="<?php echo $siteslogan; ?>"/></a>
                        </section>
                    <?php else :?>
                        <section div id="logo">
                            <?php if ($tslogan == '0') : ?>                			
                                <h1><a href="index.php" title="<?php echo $siteslogan; ?>"><?php echo $sitename; ?></a></h1>
                            <?php else : ?>
                                <h1><a href="index.php"><?php echo $sitename; ?></a></h1>
                                <small><?php echo $siteslogan; ?></small>
                            <?php endif; ?>
                        </section>
                    <?php endif; ?>
                    
                    
                    <?php 
                      /*
                      * Posiciones para redes sociales
                      * Módulo en la posición socialnet 
                      */ ?>
                    <?php if (!empty($socialnet)) : ?>				
                        <aside id="micon">
                            <jdoc:include type="modules" name="socialnet" style="jxhtml"/>
                        </aside>
                    </hgroup>
                        <?php
                    endif;?>
            </header>
                    
                      <div id="cls"></div>

                    <?php 
                     /*
                      * Posiciones para banners
                      * Dos posiciones de ancho variable  
                      * El ancho de las posiciones se indica en los parámetros de la plantilla
                      */?>				
                    <?php if (!empty($banner1) || !empty($banner2)) : ?>
                        <hgroup id="banner">	
                         <?php if (!empty($banner1)) : ?>		
                            <aside id="banner1" style=" <?php echo $banner1w; ?>;">
                                <jdoc:include type="modules" name="banner1" style="jxhtml" />
                            </aside>
                    <?php
                      endif; 
                    if (!empty($banner2)) : ?>
                        <aside id="banner2" style="<?php echo $banner2w; ?>">
                            <jdoc:include type="modules" name="banner2" style="jxhtml" />
                        </aside>
                    <?php endif; ?>  
                    </hgroup>
                <?php endif; ?> 
               <div id="cls"></div>
            <?php 
			 /*
			  * Posición para menu superior				 
			  */?>
            <?php if (!empty($topmenu)) : ?> 
                <nav id="tmen"><jdoc:include type="modules" name="topmenu" style="jxmenu" /></nav> 
                <?php
            endif;
			
			 
			 /*
			  * Posición para slider / deslizante				 
			  */
            if (!empty($slide)) :
                ?> 
                <section id="slide"><jdoc:include type="modules" name="slide" style="jxhtml" /></section>
            <?php endif; ?>


            <?php 
			 /*
			  * Posiciones User 2 - User 3 y User 4				 
			  */?>
            <?php if (!empty($user2) || !empty($user3) || !empty($user4)) : ?>
                <hgroup id="topcentro">
                    <?php if (!empty($user2)) : ?>
                        <section id="user2" style="<?php echo $user2w; ?>">
                            <jdoc:include type="modules" name="user2" style="jxhtml" />
                        </section>
                        <?php
                    endif;

                    if (!empty($user3)) :
                        ?>
                        <section id="user3" style="<?php echo $user3w; ?>">
                            <jdoc:include type="modules" name="user3" style="jxhtml" />
                        </section>
                        <?php
                    endif;

                    if (!empty($user4)) :
                        ?>
                        <section id="user4" style="<?php echo $user4w; ?>">
                            <jdoc:include type="modules" name="user4" style="jxhtml" />
                        </section>
                    <?php endif; ?>
                </hgroup>
            <?php endif; ?>
            
            <?php 
			 /*
			  * Posiciones right - content y left				 
			  */?>
            <div id="centro" role="main">   
            	<?php if (!empty($left)) : ?> 
            	<aside role="izquierda" id="izquierda" style="<?php echo $leftw; ?>">
                   		<?php if (!empty($lefttop)) : ?>
						<section id="izq-ini">
							<jdoc:include type="modules" name="lefttop" style="jxhtml" />
						</section>
					<?php endif; ?>	
					<?php if (!empty($insetleft) or ($outsetleft)) : ?> 
						<hgroup id="centroizq">
							<?php if (!empty($insetleft)) : ?>
								<section id="izquierda1" >
									<jdoc:include type="modules" name="insetleft" style="jxhtml" />
								</section>
							<?php endif; ?>	
							<?php if (!empty($outsetleft)) : ?>
								<section id="izquierda2">					
									<jdoc:include type="modules" name="outsetleft" style="jxhtml" />
									</section>
								<?php endif; ?>
							</hgroup>
						<?php endif; ?>
					<?php if (!empty($leftbottom)) : ?>
						<div id="izq-fin">
							<jdoc:include type="modules" name="leftbottom" style="jxhtml" />
						</div>
					<?php endif; ?>
				</aside>
			  <?php endif; ?>
              <!-- breadcrumbs + SobreContenedor +Contenedor + bajocontenedor. Ajustado a ancho de contenedor -->  
             
              
                <div id="contenedor" style="<?php echo $contentw; ?>">
                <?php if (!empty($bread)) : ?>
             <nav id="breadcrumb">
				<jdoc:include type="modules" name="breadcrumb" /> 
             </nav>
              <?php endif; ?>
                <?php if (!empty($sobrecontent)) : ?>
             <section id="sobrecontent">
				<jdoc:include type="modules" name="sobrecontent" style="jxhtml" /> 
             </section>
              <?php endif; ?>
              <jdoc:include type="message" />
                <div id="margen">
                    <jdoc:include type="component" />
                     <div id="cls"></div>
                     </div>
                    
                
                 <?php if (!empty($bajocontent)) : ?>
             <section id="bajocontent">
				<jdoc:include type="modules" name="bajocontent" style="jxhtml" /> 
             </section>
              <?php endif; ?>
				</div>
				<!-- Derecha con dos posiciones interiores según publicación de modulos -->
				<aside id="derecha" style="<?php echo $rightw; ?>" role="derecha">
					<?php if (!empty($righttop)) : ?>
						<section id="d-ini">
							<jdoc:include type="modules" name="righttop" style="jxhtml" />
						</section>
					<?php endif; ?>	
					<?php if (!empty($inset) or ($outset)) : ?> 
						<hgroup id="centroder">
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
							</hgroup>
						<?php endif; ?>
					<?php if (!empty($rightbot)) : ?>
						<section id="d-fin">
							<jdoc:include type="modules" name="rightbottom" style="jxhtml" />
						</section>
				 <?php endif; ?>
				</aside>	

                <?php 
			 	/*
			  	 * Posiciones User 5 - User 6 y User 7				 
			  	*/?>
                <?php if (!empty($user5) || !empty($user6) || !empty($user7)) : ?>
                    <hgroup id="topcentro">
                        <?php if (!empty($user5)) : ?>
                            <section id="user5" style="<?php echo $user5w; ?>">
                                <jdoc:include type="modules" name="user5" style="jxhtml" />
                            </section>
                            <?php
                        endif;

                        if (!empty($user6)) :
                            ?>
                            <section id="user6" style="<?php echo $user6w; ?>">
                                <jdoc:include type="modules" name="user6" style="jxhtml" />
                            </section>
                            <?php
                        endif;

                        if (!empty($user7)) :
                            ?>
                            <section id="user7" style="<?php echo $user7w; ?>">
                                <jdoc:include type="modules" name="user7" style="jxhtml" />
                            </section>
                        <?php endif; ?> 
                    </hgroup>
                <?php endif; ?> 

                
                <?php 
			 	/*
			  	 * Posiciones footmenu y foot				 
			  	 */?>
                <?php if (!empty($footmenu) || !empty($foot)) : ?>
                    <footer id="piefin">
                        <hgroup>
                            <section id="piemenu">
                                <jdoc:include type="modules" name="footmenu" style="jxhtml" />
                            </section>
                            <section id="pie">
                                <jdoc:include type="modules" name="foot" style="jxhtml" />
                            </section>
                        </hgroup>
                    </footer>
                <?php endif; ?>  
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
