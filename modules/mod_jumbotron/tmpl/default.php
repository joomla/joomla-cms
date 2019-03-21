<?php
/**
 * @version     1.4
 * @package     mod_jumbotron
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @author      Brad Traversy <support@joomdigi.com> - http://www.bootstrapjoomla.com
 */
//No Direct Access
defined('_JEXEC') or die;
?>
<style>
<?php
	if($foreground_image_width != 'auto'|| $foreground_image_width != ''){
		$foreground_image_width = $foreground_image_width.'px';
	}
?>

.foreground_image{
	width:<?php echo $foreground_image_width; ?>
}

<?php if(isset($background_image) && $background_image != '') : ?>
    .jumbotron{
        background:url(<?php echo JURI::base(); ?><?php echo $background_image; ?>) no-repeat <?php echo $x_pos; ?>px <?php echo $y_pos; ?>px;
	}
<?php endif; ?>

<?php if(!isset($background_image) && isset($background_color)) : ?>
    .jumbotron{
        background:<?php echo $background_color; ?>
	}
<?php endif; ?>

	.jumbotron h1{
		color:<?php echo $headingtextcolor; ?>;
		<?php echo ($center_text == 1 ? 'text-align:center;' : ''); ?>
	}
	.jumbotron p{
		color:<?php echo $paragraphtextcolor; ?>;
		<?php echo ($center_text == 1 ? 'text-align:center;' : ''); ?>
	}
	.jumbotron .btn{
		color:#fff !important;
		<?php echo ($center_text == 1 ? 'text-align:center;' : ''); ?>
	}
	
	.jumbotron .foreground_image_wrap{
		<?php echo ($center_text == 1 ? 'text-align:center;' : ''); ?>
	}
	
</style>
 <div class="jumbotron <?php echo $moduleclass_sfx; ?>">
  <div class="container">
  <h1><?php echo $header_text; ?></h1>
  <p><?php echo $paragraph_text; ?></p>
<?php if(isset($foreground_image)) : ?>
     <p class="foreground_image_wrap"><img class="foreground_image" src="<?php echo JURI::base(); ?><?php echo $foreground_image; ?>" alt="<?php echo $header_text; ?>" /></p>
<?php endif; ?>
  <?php if($show_read_more) : ?>
  <p><a class="<?php echo $buttonstyle; ?>" role="button" href="<?php echo $read_more_link; ?>"><?php echo $read_more_text; ?></a></p>
<?php endif; ?>
</div><!-- /.container -->
 </div><!-- /.jumbotron -->
