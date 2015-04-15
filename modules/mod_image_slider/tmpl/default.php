<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_image_slider
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Initialise Bootstrap Carousel Component
JHtml::_('bootstrap.carousel', '#slider-' . $id, array('interval' => $interval));

?>

<div class="image-slider<?php echo $moduleclass_sfx ?>">
	<div id="slider-<?php echo $id; ?>" class="carousel slide">
		<?php if ($navigation): ?>
			<ol class="carousel-indicators">
				<?php foreach ($slideSet as $index => $slide) : ?>
					<?php if ($slide->image != null) : ?>
						<?php if ($index == 1) : ?>
							<li data-target="#slider-<?php echo $id; ?>" data-slide-to="0" class="active"></li>
						<?php else : ?>
							<li data-target="#slider-<?php echo $id; ?>" data-slide-to="<?php echo $index - 1 ?>"></li>
						<?php endif; ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>

		<div class="carousel-inner">
			<?php foreach ($slideSet as $index => $slide) : ?>
		<?php if ($slide->image != null) : ?>
		<?php if ($index == 1) : ?>
			<div class="item active">
				<?php else : ?>
				<div class="item">
					<?php endif; ?>

					<img src="<?php echo $slide->image ?>">
					<?php if ($slide->heading != null or $slide->description != null) : ?>
						<div class="carousel-caption">
							<?php if ($slide->link != null) : ?>
								<h4><a href="<?php echo $slide->link ?>"><?php echo $slide->heading ?></a></h4>
							<?php else : ?>
								<h4><?php echo $slide->heading ?></h4>
							<?php endif; ?>
							<p><?php echo $slide->description ?></p>
						</div>
					<?php endif; ?>

				</div>
				<?php endif; ?>
				<?php endforeach; ?>
			</div>
			<?php if ($controls): ?>
				<a class="carousel-control left" href="#slider-<?php echo $id; ?>" data-slide="prev">&lsaquo;</a>
				<a class="carousel-control right" href="#slider-<?php echo $id; ?>" data-slide="next">&rsaquo;</a>
			<?php endif; ?>
		</div>
	</div>