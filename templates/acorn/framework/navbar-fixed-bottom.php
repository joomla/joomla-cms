<?php
/**
 * @package    ${NAMESPACE}
 * @subpackage
 * @version    30-Sep-19
 * @copyright  2019 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license    GPL2
 */
defined('_JEXEC') or die;

/** @var $nav_Location */
/** @var $menu_module */
/** @var $nav_wide */
/** @var $navbarBrand */
/** @var $nav_style */

?>
<script type="text/javascript">
    "use strict";
    <!-- find out how high the header is -->
    jQuery(document).ready(function () {
        var bottom = jQuery('nav.navbar').outerHeight(true) + 5;
        jQuery('#page').css({'margin-bottom': bottom});
        jQuery('.go-top').css({'margin-bottom': bottom});

    });
    <!--When window is resized we need to recompute the height-->
    jQuery(window).resize(function () {
        var bottom = jQuery('nav.navbar').outerHeight(true) + 5;
        jQuery('#page').css({'margin-bottom': bottom});
        jQuery('.go-top').css({'margin-bottom': bottom});
    })
</script>


<nav class="navbar <?php echo $nav_style . ' ' . $nav_Location; ?>">
	<div class="<?php echo $nav_wide; ?>">
		<div class="navigation">
			<!-- Brand and toggle get grouped for better mobile display -->
			<div class="navbar-header">
				<button type="button"  class="navbar-toggle"
				        data-toggle="offcanvas" data-target=".navbar-offcanvas"
				        data-canvas="body" aria-expanded="false" aria-controls="navbar">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>

				<?php if ($navbarBrand) { ?>
					<!-- Brand -->
					<div class="navbar-brand">
						<jdoc:include type="modules" name="navbar-brand" style="html5" />
					</div>
				<?php } elseif ($logo) { ?>
					<div class="logo">
						<?php echo $logo; ?>
					</div>
				<?php } ?>
			</div>
			<!-- Collapsible Navbar -->
			<div class="navbar-offcanvas offcanvas collapse navbar-collapse">
				<jdoc:include type="modules" name="menu" style="none" />
			</div>

			<!-- social icons -->
			<?php
			if($socialiconsNav){
				echo $socialiconsData;
			};
			?>

			<?php if ($this->countModules('navbar')) : ?>
				<div class="navbar-search hidden-xs">
					<jdoc:include type="modules" name="navbar" style="bootstrap" />
				</div>
			<?php endif; ?>
		</div>
	</div>
</nav>
<div id='end-header' class="clearfix"></div>
