<?php
/**
 * @package Helix3 Framework
 * Template Name - Shaper Helix - iii
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2015 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('resticted aceess');

$doc = JFactory::getDocument();
$app = JFactory::getApplication();

require_once JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php';

$twofactormethods = UsersHelper::getTwoFactorMethods();
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
    if($favicon = $this->params->get('favicon')) {
        $doc->addFavicon( JURI::base(true) . '/' .  $favicon);
    } else {
        $doc->addFavicon( $this->baseurl . '/templates/'. $this->template .'/images/favicon.ico' );
    }
    ?>

    <jdoc:include type="head" />
   	<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/bootstrap.min.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/template.css" type="text/css" />
<body>
	<div class="container">
		<div class="row">
			<div class="col-sm-6 col-sm-offset-3">
				<div class="offline-inner">
					<jdoc:include type="message" />

					<div id="frame" class="outline">
						<?php if ($app->get('offline_image') && file_exists($app->get('offline_image'))) : ?>
							<img src="<?php echo $app->get('offline_image'); ?>" alt="<?php echo htmlspecialchars($app->get('sitename')); ?>" />
						<?php endif; ?>
						<h1>
							<?php echo htmlspecialchars($app->get('sitename')); ?>
						</h1>
						<?php if ($app->get('display_offline_message', 1) == 1 && str_replace(' ', '', $app->get('offline_message')) != '') : ?>
							<p>
								<?php echo $app->get('offline_message'); ?>
							</p>
						<?php elseif ($app->get('display_offline_message', 1) == 2 && str_replace(' ', '', JText::_('JOFFLINE_MESSAGE')) != '') : ?>
							<p>
								<?php echo JText::_('JOFFLINE_MESSAGE'); ?>
							</p>
						<?php endif; ?>
						<form action="<?php echo JRoute::_('index.php', true); ?>" method="post" id="form-login">
							<div class="form-group" id="form-login-username">
								<input name="username" id="username" type="text" class="form-control" placeholder="<?php echo JText::_('JGLOBAL_USERNAME'); ?>" size="18" />
							</div>
							
							<div class="form-group" id="form-login-password">
								<input type="password" name="password" class="form-control" size="18" placeholder="<?php echo JText::_('JGLOBAL_PASSWORD'); ?>" id="passwd" />
							</div>
							<?php if (count($twofactormethods) > 1) : ?>
							<div class="form-group" id="form-login-secretkey">
								<input type="text" name="secretkey" class="form-control" size="18" placeholder="<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>" id="secretkey" />
							</div>
							<?php endif; ?>
							<div class="form-group" id="submit-buton">
								<input type="submit" name="Submit" class="btn btn-success login" value="<?php echo JText::_('JLOGIN'); ?>" />
							</div>

							<input type="hidden" name="option" value="com_users" />
							<input type="hidden" name="task" value="user.login" />
							<input type="hidden" name="return" value="<?php echo base64_encode(JUri::base()); ?>" />
							<?php echo JHtml::_('form.token'); ?>
						</form>
					</div>

				</div>
			</div>
		</div>
	</div>
</body>
</html>
