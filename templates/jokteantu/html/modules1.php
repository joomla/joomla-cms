<?php
/**
 * @package     Jokte.Site
 * @subpackage	jokteantu
 * @author 	    Equipo de desarrollo juuntos.
 * @copyleft    (comparte igual)  Jokte!
 * @license     GNU General Public License version 3 o superior.
*/
// Sin acceso directo
defined('_JEXEC') or die;


/*
 * jxhtml (Variación de xhtml que nos permite mayor sensibilidad para agrupar los titulos h3 dentreo de una div personalizable)
 */
function modChrome_jxhtml($module, &$params, &$attribs)
{
	if (!empty ($module->content)) : ?>
		<div class="moduletable<?php echo htmlspecialchars($params->get('moduleclass_sfx')); ?>">
		<?php if ($module->showtitle != 0) : ?>
		<div>
		<h3><?php echo $module->title; ?></h3>
		</div>
		<?php endif; ?>
			<?php echo $module->content; ?>
		</div>
	<?php endif;
}

?>
