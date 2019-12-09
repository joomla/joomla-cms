<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 * @version     30-Sep-19
 * @copyright   Copyright(c) 2016-2019 Troy T. Hall
 * @license     GPL2
 */
defined('_JEXEC') or die;

/** @var $nav_Location */
/** @var $menu_module */
/** @var $nav_wide */
/** @var $navbarBrand */
/** @var $nav_style */

?>

<nav class="navbar <?php echo $nav_style . ' ' . $nav_Location; ?>">
	<div class="<?php echo $nav_wide; ?>">
		<div class="navigation">
			<!-- Brand and toggle get grouped for better mobile display -->
			<div class="navbar-header">
				<button type="button" class="navbar-toggler collapsed"
				        data-toggle="offcanvas"
				        data-target=".navmenu"
				        data-canvas="body"
				        aria-expanded="false"
				        aria-controls="navbar">
					<span class="sr-only">Toggle navigation</span>
					<span class="navbar-toggler-icon"></span>
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
			<div class="navmenu offcanvas collapse navbar-collapse flex-row">
				<jdoc:include type="modules" name="menu" style="none" />
			</div>

			<!-- social icons -->
			<?php
			if ($socialiconsNav)
			{
				echo $socialiconsData;
			};
			?>

			<?php if ($this->countModules('search')) : ?>
				<div class="search pull-right hidden-xs">
					<jdoc:include type="modules" name="search" style="bootstrap" />
				</div>
			<?php endif; ?>
		</div>
	</div>
</nav>
<div id='end-header' class="clearfix"></div>
