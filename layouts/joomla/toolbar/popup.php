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
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

extract($displayData, EXTR_OVERWRITE);

/**
 * Layout variables
 * -----------------
 * @var   int     $id
 * @var   string  $name
 * @var   string  $doTask
 * @var   string  $class
 * @var   string  $text
 * @var   string  $btnClass
 * @var   string  $tagName
 * @var   bool    $listCheck
 * @var   string  $htmlAttributes
 */

$wa = Factory::getDocument()->getWebAssetManager();

$wa->useScript('core');
$wa->registerAndUseScript('joomla-modal', 'system/joomla-modal.min.js', [], ['type' => 'module'], []);
$wa->registerAndUseStyle('joomla-modal', 'system/joomla-modal.min.css', [], [], []);

$tagName = $tagName ?? 'button';
$idAttr   = !empty($id)        ? ' id="' . $id . '"' : '';
$listAttr = !empty($listCheck) ? ' list-selection' : '';
?>
<joomla-modal-button <?php echo $idAttr . $listAttr . ' url="' . $url . '" title="' . $text . '"'; ?>>
<<?php echo $tagName; ?>
    class="<?php echo $btnClass; ?>"
    <?php echo $htmlAttributes; ?>
>
    <span class="<?php echo $class; ?>" aria-hidden="true"></span>
    <?php echo $text; ?>
</<?php echo $tagName; ?>>
</joomla-modal-button>
