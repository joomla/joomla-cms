<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

HTMLHelper::_('bootstrap.dropdown');

$token     = Session::getFormToken();
$wa        = Factory::getDocument()->getWebAssetManager();
$user      = Factory::getUser();
$clientId  = (int) $this->state->get('client_id', 0);
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$wa->useStyle('com_templates.admin-templates')
	->useScript('com_templates.admin-templates')
?>
<form action="<?php echo Route::_('index.php?option=com_templates&view=templates'); ?>" method="post" name="adminForm" id="adminForm" data-token="<?php echo $token; ?>">
	<div class="row">
		<div class="col-md-12">
			<div id="main-container" class="main-container">
				<?php
					/**
					 * @see: layouts/joomla/searchtools/default.php
					 */
					echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options' => array('selectorFieldName' => 'client_id')));
				?>
				<div class="clearfix mt-5 mb-4"></div>
				<?php if ($this->total > 0) : ?>
					<div id="styleList">
						<div class="row">
							<?php foreach ($this->items as $i => $item) :
								$canCreate = $user->authorise('core.create',     'com_templates');
								$canDelete = $user->authorise('core.delete',     'com_templates');
								$canEdit   = $user->authorise('core.edit',       'com_templates');
								$canChange = $user->authorise('core.edit.state', 'com_templates');
							?>
								<div class="col-lg-6 col-xl-4">
									<?php
										/**
										 * @see: administrator/components/com_templates/layouts/card-header.php
										 */
										echo LayoutHelper::render('card', [
												'clientId'  => $clientId,
												'canChange' => $canChange,
												'canDelete' => $canDelete,
												'canEdit'   => $canEdit,
												'canCreate' => $canCreate,
												'item'      => $item,
												'i'         => $i
										]);
									?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<!-- load the pagination. -->
					<div class="pagination-footer mt-4">
						<?php
							/**
							 * @see: layouts/joomla/searchtools/default/list.php
							 */
							echo LayoutHelper::render('joomla.searchtools.default.listlimit', array('view' => $this));
						?>
						<?php echo $this->pagination->getListFooter(); ?>
					</div>
				<?php endif; ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="<?php echo $token; ?>" value="1">
			</div>
		</div>
	</div>
</form>

<!-- Load template install modal  -->
<?php
if ($canCreate)
{
	echo HTMLHelper::_(
			'bootstrap.renderModal',
			'ModalInstallTemplate',
			[
				'title'       => Text::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_INSTALL_JOOMLA_EXTENSION'),
				'height'      => '75vh',
				'width'       => '85vw',
				'bodyHeight'  => 60,
				'modalWidth'  => 80,
				'footer'      => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
						. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
			],
			/**
			 * @see: administrator/components/com_templates/layouts/card-header.php
			 */
			LayoutHelper::render('install-template', [])
	);
}
?>
