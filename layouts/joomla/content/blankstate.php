<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$textPrefix = $displayData['textPrefix'] ?? '';

if (!$textPrefix)
{
	$textPrefix = strtoupper(Factory::getApplication()->input->get('option'));
}

$formURL    = $displayData['formURL'] ?? '';
$createURL  = $displayData['createURL'] ?? '';
$helpURL    = $displayData['helpURL'] ?? '';
?>

<form action="<?php echo Route::_($formURL); ?>" method="post" name="adminForm" id="adminForm">

	<div class="px-4 py-5 my-5 text-center">
		<span class="fa-8x icon-copy mb-4 article" aria-hidden="true"></span>
		<h1 class="display-5 fw-bold"><?php echo Text::_($textPrefix . '_BLANKSTATE_TITLE'); ?></h1>
		<div class="col-lg-6 mx-auto">
			<p class="lead mb-4">
				<?php echo Text::_($textPrefix . '_BLANKSTATE_CONTENT'); ?>
			</p>
			<div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
				<?php if ($createURL) : ?>
					<a href="<?php echo Route::_($createURL); ?>"
					   class="btn btn-primary btn-lg px-4 me-sm-3"><?php echo Text::_($textPrefix . '_BLANKSTATE_BUTTON_ADD'); ?></a>
				<?php endif; ?>
				<?php if ($helpURL) : ?>
					<a href="<?php echo $helpURL; ?>" target="_blank"
					   class="btn btn-outline-secondary btn-lg px-4"><?php echo Text::_('JGLOBAL_LEARNMORE'); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<input type="hidden" name="task" value="">
	<input type="hidden" name="boxchecked" value="0">
</form>
