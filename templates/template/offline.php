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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\AuthenticationHelper;

$twofactormethods 	= AuthenticationHelper::getTwoFactorMethods();
$doc 				= JFactory::getDocument();
$app              	= Factory::getApplication();

ob_start();
?>	
<div class="coming-soon-login">
	<form action="<?php echo JRoute::_('index.php', true); ?>" method="post" id="form-login" class="mt-5">
		<div class="form-row align-items-center">
			<div class="col-auto">
				<label class="sr-only" for="username"><?php echo JText::_('JGLOBAL_USERNAME'); ?></label>
				<div class="input-group mb-2">
					<div class="input-group-prepend">
						<div class="input-group-text"><span class="fa fa-user"></span></div>
					</div>
					<input name="username" type="text" class="form-control" id="username" placeholder="<?php echo JText::_('JGLOBAL_USERNAME'); ?>">
				</div>
			</div>

			<div class="col-auto">
				<label class="sr-only" for="password"><?php echo JText::_('JGLOBAL_PASSWORD'); ?></label>
				<div class="input-group mb-2">
					<div class="input-group-prepend">
						<div class="input-group-text"><span class="fa fa-key"></span></div>
					</div>
					<input name="password" type="password" class="form-control" id="password" placeholder="<?php echo JText::_('JGLOBAL_PASSWORD'); ?>">
				</div>
			</div>
			
			<?php if (count($twofactormethods) > 1) : ?>
				<div class="col-auto">
					<label class="sr-only" for="secretkey"><?php echo JText::_('JGLOBAL_SECRETKEY'); ?></label>
					<div class="input-group mb-2">
						<div class="input-group-prepend">
							<div class="input-group-text"><span class="fa fa-user-secret"></span></div>
						</div>
						<input name="secretkey" type="text" class="form-control" id="secretkey" placeholder="<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>">
					</div>
				</div>
			<?php endif; ?>

			<div class="col-auto">
				<input type="submit" name="Submit" class="btn btn-success mb-2 login" value="<?php echo JText::_('JLOGIN'); ?>" />
				<input type="hidden" name="option" value="com_users" />
				<input type="hidden" name="task" value="user.login" />
				<input type="hidden" name="return" value="<?php echo base64_encode(JUri::base()); ?>" />
				<?php echo JHtml::_('form.token'); ?>
			</div>

		</div>
	</form>
</div>	
<?php
$login_form = ob_get_clean();
echo JLayoutHelper::render('comingsoon', array('language' => $this->language, 'direction' => $this->direction, 'params' => $this->params, 'login' => true, 'login_form' => $login_form));