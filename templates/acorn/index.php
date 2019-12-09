<?php
/**
 * @package        acorn.Framework
 * @subpackage     acorn
 *
 * @copyright      Copyright (C) 2019 Troy T. Hall All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */
/** @var string $frontpageshow */
/** @var string $topnavbar */
/** @var string $topnavbarAffix */
/** @var string $menu_module */
/** @var string $copyright */
/** @var string $copyrightModule */
/** @var string $gotopCustomize */
/** @var string $gotopiconClass */
/** @var string $gotopText */
/** @var string $nav_Location */
/** @var string $gaId */
/** @var string $left */
/** @var string $right */
/** @var string $copytext */
/** @var string $bodyclass */
/** @var string $socialiconsLocation */


defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

require_once 'templates/' . $this->template . '/framework/init.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<?php
	if ($gaId)
	{
		// required to be here by Google Analytics
		require_once("{$frameworkPath}analyticstracking.php");
	}
	?>
	<jdoc:include type="head" />
</head>

<!--  add bodyclass and make sure no extra spaces -->
<body id="main" class="<?php echo trim(str_replace('  ', ' ', ($mainbodyclass . ' ' . $bodyclass))); ?>">
<div id="page">
	<?php
	if ($this->countModules('pagetop') || $socialiconsLocation === 'pagetop'): ?>
		<!-- Page Top Positions -->
		<div id="pagetop col-xs-12 no-padding-row">
			<div class="container">
				<div class="row">
					<div class="pagetop-wrapper">
						<jdoc:include type="modules" name="pagetop" style="html5" />
					<?php
					if ($socialiconsLocation === 'pagetop')
					{ ?>
						<div class="pagetop icons">
							<?php echo $socialiconsData; ?>
						</div>
					<?php } ?>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php if ($this->countModules('pagetop-wide')): ?>
		<div id="pagetop-wide col-xs-12 no-padding-row">
			<div class="pagetop-wide-wrapper"
			<jdoc:include type="modules" name="pagetop-wide" style="html5" />
			<?php
			if ($socialiconsLocation === 'pagetop')
			{ ?>
				<div class="pagetop-wide icons">
					<?php echo $socialiconsData; ?>
				</div>
			<?php } ?>
		</div>
		</div>
		<!-- End Page Top Positions -->
	<?php endif; ?>


	<?php
	if ($menu_module && $nav_Location !== "navbar-standard") :
		// Where should nav go?
		include_once($frameworkPath . $nav_Location . '.php');
	endif;
	?>

	<!-- Above Content Module Positions -->
	<?php if ($this->countModules('above-media')): ?>
		<div id="above-media">
			<div class="container">
				<div class="row">
					<jdoc:include type="modules" name="above-media" style="html5" />
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($this->countModules('above-media-wide')): ?>
		<div id="above-media-wide col-xs-12 no-padding-row">
			<jdoc:include type="modules" name="above-media-wide" style="html5" />
		</div>
	<?php endif; ?>

	<?php if ($this->countModules('slideshow')): ?>
		<div id="slider">
			<div class="container">
				<div class="row">
					<jdoc:include type="modules" name="slideshow" style="html5" />
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($this->countModules('slideshow-wide')): ?>
		<div id="slider-wide">
			<jdoc:include type="modules" name="slideshow-wide" style="html5" />
		</div>
	<?php endif; ?>

	<?php if ($this->countModules('jumbotron')): ?>
		<div id="jumbotron" class="jumbotron">
			<div class="container">
				<div class="row">
					<jdoc:include type="modules" name="jumbotron" style="html5" />
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($this->countModules('jumbotron-wide')): ?>
		<div id="jumbotron-wide" class="jumbotron">
			<jdoc:include type="modules" name="jumbotron-wide" style="html5" />
		</div>
	<?php endif; ?>

	<!-- Standard menu position -->
	<?php
	if ($menu_module && $nav_Location == 'navbar-standard') :
		// Where should nav go?
		include_once($frameworkPath . $nav_Location . '.php');
	endif;
	?>
	<?php if ($this->countModules('breadcrumb')): ?>
		<div id="breadcrumb">
			<div class="container">
				<div class="row">
					<div class="breadcrumb">
						<jdoc:include type="modules" name="breadcrumb" style="html5" />
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php if ($this->countModules('breadcrumb-wide')): ?>
		<div id="breadcrumb-wide">
			<div class="breadcrumb">
				<jdoc:include type="modules" name="breadcrumb-wide" style="html5" />
			</div>
		</div>
	<?php endif; ?>

	<?php if ($this->countModules('floor')): ?>
		<div id="floor">
			<div class="container">
				<div class="row">
					<jdoc:include type="modules" name="floor" style="html5" />
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($this->countModules('floor-wide')): ?>
		<div id="floor-wide">
			<jdoc:include type="modules" name="floor-wide" style="html5" />
		</div>
	<?php endif; ?>

	<?php if ($this->countModules('row1')): ?>
		<div id="row1" class="clearfix">
			<div class="container">
				<div class="row">
					<jdoc:include type="modules" name="row1" style="html5" />
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($this->countModules('row1-wide')): ?>
		<div id="row1-wide">
			<jdoc:include type="modules" name="row1-wide" style="html5" />
		</div>
	<?php endif; ?>

	<?php if ($this->countModules('row2')): ?>
		<div id="row2" class="clearfix">
			<div class="container">
				<div class="row">
					<jdoc:include type="modules" name="row2" style="html5" />
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($this->countModules('row2-wide')): ?>
		<div id="row2-wide">
			<jdoc:include type="modules" name="row2-wide" style="html5" />
		</div>
	<?php endif; ?>

	<?php if ($this->countModules('above')): ?>
		<div id="above">
			<div class="container">
				<div class="row">
					<jdoc:include type="modules" name="above" style="html5" />
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($this->countModules('above-wide')): ?>
		<div id="above-wide">
			<jdoc:include type="modules" name="above-wide" style="html5" />
		</div>
	<?php endif; ?>
	<!-- END Above Content Module Positions -->

	<!-- Mainbody -->
	<div id="mainbody" class="clearfix">
		<div class="container">
			<div class="row">
				<?php if ($this->countModules('left')): ?>
					<div class="sidebar sidebar-left col-md-<?php echo $left; ?> no-padding-left">
						<jdoc:include type="modules" name="left" style="html5" />
					</div>
				<?php endif; ?>

				<!-- Content Block -->
				<div id="content" class="col-md-<?php echo $cols; ?> no-padding-row">
					<jdoc:include type="message" />
					<?php if ($this->countModules('above-content')): ?>
						<div id="above-content">
							<jdoc:include type="modules" name="above-content" style="html5" />
						</div>
					<?php endif; ?>
					<?php
					$app  = JFactory::getApplication();
					$menu = $app->getMenu();

					if ($frontpageshow)
					{
						// show on all pages
						?>
						<div id="content-area">
							<jdoc:include type="component" />
						</div>
						<?php
					}
					else
					{
						if ($menu->getActive() !== $menu->getDefault())
						{
							// show on all pages but the default page
							?>
							<div id="content-area">
								<jdoc:include type="component" />
							</div>
							<?php
						}
					}
					?>
					<!-- END Content Block -->

					<?php if ($this->countModules('below-content')): ?>
						<div id="below-content">
							<jdoc:include type="modules" name="below-content" style="html5" />
						</div>
					<?php endif; ?>
				</div>

				<!-- This needs to be outside the content wrapper -->
				<?php if ($this->countModules('right')) : ?>
					<div class="sidebar sidebar-right col-md-<?php echo $right; ?> no-padding-right">
						<jdoc:include type="modules" name="right" style="html5" />
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
<!-- Below Content Testimonial Position -->
<?php if ($this->countModules('testimonials')): ?>
	<div id="testimonials">
		<div class="container">
			<div class="row">
				<jdoc:include type="modules" name="testimonials" style="bootstrap" />
			</div>
		</div>
	</div>
<?php endif; ?>
<!-- Testimonials-wide Module Positions -->
<?php if ($this->countModules('testimonials-wide')): ?>
	<div id="testimonials-wide">
		<jdoc:include type="modules" name="testimonials-wide" style="bootstrap" />
	</div>
<?php endif; ?>
<div class="clearfix"></div>

<!-- Below Content Module Positions -->
<?php if ($this->countModules('bottom')): ?>
	<div id="bottom" class="clearfix">
		<div class="container">
			<div class="row">
				<jdoc:include type="modules" name="bottom" style="html5" />
			</div>
		</div>
	</div>
<?php endif; ?>
<?php if ($this->countModules('bottom-wide')): ?>
	<div id="bottom-wide" class="clearfix">
		<jdoc:include type="modules" name="bottom-wide" style="html5" />
	</div>
<?php endif; ?>

<!-- footer -->
<footer class="footer clearfix">
	<?php if ($this->countModules('footer')): ?>
		<div id="footer" class="clearfix">
			<div class="container">
				<div class="row">
					<jdoc:include type="modules" name="footer" style="html5" />
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($this->countModules('footer-wide')): ?>
		<div id="footer-wide" class="clearfix">
			<div class="footer-wide">
				<jdoc:include type="modules" name="footer-wide" style="html5" />
			</div>
		</div>
	<?php endif; ?>
</footer>
<!-- END footer -->
<!-- FOOTER SOCIAL ICONS -->
<?php if ($socialiconsFooter) : ?>
	<div id="socialiconsfooter">
		<div class="container">
			<div class="row">
				<div class="socialicons">
					<?php echo $socialiconsData; ?>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>
<div class="clearfix"></div>
<?php
if ($copyright || $copyrightModule): ?>
	<div id="copyright">
		<div class="container">
			<div class="row">
				<div class="copyright">

					<!-- COPYRIGHT MODULE -->
					<?php if ($copyrightModule) : ?>
						<div class="copyrightmodules">
							<jdoc:include type="modules" name="copyright" style="html5" />
						</div>
						<div class="clearfix"></div>
					<?php endif;
					if ($copyright) : ?>
						<div class="copyrightbar">
							<?php if ($copyright): ?>
								<div class="copyrighttext">
									<?php echo $copytext; ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>
<!-- END Below Content Module Positions -->

<!-- add debug modules -->
<jdoc:include type="modules" name="debug" style="none" />
<!--  If JDEBUG is defined, load the css -->
<?php if (defined('JDEBUG') && JDEBUG)
{
	HTMLHelper::_('stylesheet', 'framework/debug.css', $HTMLHelperDebug);
	?>
	<script
			type="text/javascript"
			src="https://use.fontawesome.com/releases/v5.11.2/js/conflict-detection.js">
	</script>
	<?php
}
include_once $frameworkPath . 'footer.include.php';
?>
<!-- SCROLL TO TOP -->
<a href="#" class="go-top btn<?php echo $gotopbuttonClass ?>" target="_self" alt="Go To Top"
   aria-hidden="true"><?php echo $gotopText; ?></a>

</body>
</html>
