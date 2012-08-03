<?php
/**
* @license Sin Licencia. Este Modulo es de Dominio Publico
* Creado por Hugo baronti para La Comunidad latinoamericana de tecnologia Web 
* Juuntos.org
*/
// no direct accesss
defined('_JEXEC') or die('Restricted access');

$user = & JFactory::getUser();
?>

<div style="text-align: center;">
	<?php if ($params->get('imageurl')) : ?>
        <p><a title="<?php echo $params->get('groupname'); ?>" href="<?php echo $params->get('groupurl'); ?>">
            <img alt="<?php echo $params->get('groupname'); ?>" src="<?php echo $params->get('imageurl'); ?>" border="0" />
        </a></p>
	<?php endif; ?>
	<form action="<?php echo $params->get('groupurl'); ?>/boxsubscribe">
        <?php if ($user->email){?>
        <input type="hidden" name="email" value="<?php echo $user->email;?>" />
        <?php } else {  ?>  
        <label><?php echo JText::_( 'YOUR_EMAIL'); ?></label>
        <input type="text" name="email" />
        <?php } ?>
		<input type="submit" value="<?php echo JText::_( 'BUTTON_SUBSCRIBE'); ?>" name="sub"/>
	</form>
	 
</div>
