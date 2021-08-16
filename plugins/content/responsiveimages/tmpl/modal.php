<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.responsiveimages
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;

defined('_JEXEC') or die;

?>

<?php
	$document = Factory::getApplication()->getDocument();

	// @TODO: Render a modal with images and checkboxes for checking and deleting them.
	$document->addScriptDeclaration('
		document.addEventListener("DOMContentLoaded", function() {
			alert("Unused images: ' . implode(',', json_decode($images)) . '");
		});
	');
?>
