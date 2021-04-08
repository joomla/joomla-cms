<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Module chrome for rendering the module in a submenu
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

$modulePosition = $displayData['modules'];

$renderer   = Factory::getDocument()->loadRenderer('module');
$modules    = ModuleHelper::getModules($modulePosition);
$moduleHtml = [];

foreach ($modules as $key => $mod)
{
	$out = $renderer->render($mod);

	if ($out !== '')
	{
		$moduleHtml[] = $out;
	}
}
?>
<div class="header-items d-flex ms-auto">
	<?php
		foreach ($moduleHtml as $mod)
		{
			echo '<div class="header-item d-flex">' . $mod . '</div>';
		}
	?>
	<div class="header-item-more d-flex d-none" id="header-more-items" >
		<div class="header-item-content dropdown header-more">
			<button class="header-more-btn dropdown-toggle d-flex align-items-center ps-0" type="button" title="<?php echo Text::_('TPL_ATUM_MORE_ELEMENTS'); ?>" data-bs-toggle="dropdown" aria-expanded="false">
				<div class="header-item-icon"><span class="icon-ellipsis-h" aria-hidden="true"></span></div>
				<div class="sr-only"><?php echo Text::_('TPL_ATUM_MORE_ELEMENTS'); ?></div>
			</button>
			<div class="header-dd-items dropdown-menu">
				<?php
				foreach ($moduleHtml as $key => $mod)
				{
					echo '<div class="header-dd-item dropdown-item" data-item="' . $key . '">' . $mod . '</div>';
				}
				?>
			</div>
		</div>
	</div>
</div>
