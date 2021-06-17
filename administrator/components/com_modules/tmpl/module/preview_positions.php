<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('com_modules.admin-modules-preview_positions');

$isAdmin = Factory::getApplication()->input->get('client_id');

// Get the URL of the iframe that displays template preview.
$iframeBaseURL = Uri::root();
$iframeBaseURL .= $isAdmin ? 'administrator' : '';

// Conditionally render the admin or site template select field.
$templateField = 'template_style_';
$templateField .= $isAdmin ? 'admin' : 'site';

// Render the template select field.
$this->fieldsets = $this->form->getFieldsets();
echo $this->form->renderField($templateField);
?>
<div class="jviewport-height90">
	<iframe 
		src=<?php echo $iframeBaseURL . '?pm=1&edit=1&templateStyle='; ?>
		id="module-position-select" 
		name="module-position-select" 
		title="module-position-select"
		height="100%"width="100%">
	</iframe>
</div>
