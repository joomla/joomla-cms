<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$app = Factory::getApplication();
$doc = JFactory::getDocument();
$params = $app->getTemplate(true)->params;
$theme_url = URI::base(true) . '/templates/'. $this->template;
?>

<!doctype html>
<html class="error-page" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<title><?php echo $this->title; ?> <?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></title>

		<?php if ($favicon = $params->get('favicon')) : ?>
			<link rel="icon" href="<?php echo URI::base(true) . '/' . $favicon; ?>" />
		<?php else: ?>
			<link rel="icon" href="<?php echo $theme_url .'/images/favicon.ico'; ?>" />
		<?php endif; ?>

		<?php if(file_exists( \JPATH_THEMES . '/' . $this->template . '/css/bootstrap.min.css' )) : ?>
			<link href="<?php echo $theme_url . '/css/bootstrap.min.css'; ?>" rel="stylesheet">
		<?php else: ?>
			<link href="<?php echo URI::base(true) . '/plugins/system/helixultimate/css/bootstrap.min.css'; ?>" rel="stylesheet">
		<?php endif; ?>

		<?php if(file_exists( \JPATH_THEMES . '/' . $this->template . '/css/custom.css' )) : ?>
			<link href="<?php echo $theme_url . '/css/custom.css'; ?>" rel="stylesheet">
		<?php endif; ?>

		<link href="<?php echo $theme_url . '/css/font-awesome.min.css'; ?>" rel="stylesheet">
		<link href="<?php echo $theme_url . '/css/template.css'; ?>" rel="stylesheet">

		<?php $preset = (isset(json_decode($params->get('preset', (object) ['preset' => 'preset1']))->preset) && json_decode($params->get('preset'))->preset) ? json_decode($params->get('preset'))->preset : 'preset1'; ?>
		<link href="<?php echo $theme_url . '/css/presets/' . $preset . '.css'; ?>" rel="stylesheet">
		
	</head>
	<body>
		<div class="container">
			<?php if($params->get('error_logo')) : ?>
				<a href="<?php echo $this->baseurl; ?>/index.php">
					<img class="error-logo" src="<?php echo URI::base(true) . '/' . $params->get('error_logo'); ?>" alt="<?php echo htmlspecialchars($this->title); ?>">
				</a>
			<?php endif; ?>

			<h1 class="error-code"><?php echo $this->error->getCode(); ?></h1>
			<h2 class="error-message"><?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></h2>

			<a href="<?php echo $this->baseurl; ?>/index.php" class="btn btn-secondary"><span class="fa fa-home" aria-hidden="true"></span> <?php echo Text::_('JERROR_LAYOUT_HOME_PAGE'); ?></a>

			<?php if ($this->debug) : ?>
				<div class="error-debug mt-3">
					<?php echo $this->renderBacktrace(); ?>
					<?php if ($this->error->getPrevious()) : ?>
						<?php $loop = true; ?>
						<?php $this->setError($this->_error->getPrevious()); ?>
						<?php while ($loop === true) : ?>
							<p><strong><?php echo Text::_('JERROR_LAYOUT_PREVIOUS_ERROR'); ?></strong></p>
							<p><?php echo htmlspecialchars($this->_error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></p>
							<?php echo $this->renderBacktrace(); ?>
							<?php $loop = $this->setError($this->_error->getPrevious()); ?>
						<?php endwhile; ?>
						<?php // Reset the main error object to the base error ?>
						<?php $this->setError($this->error); ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if($params->get('error_bg')) : ?>
			<style>
				body {
					background-image: url(<?php echo JURI::base(true) . '/' . $params->get('error_bg'); ?>)
				}
			</style>
		<?php endif; ?>
	</body>

</html>
