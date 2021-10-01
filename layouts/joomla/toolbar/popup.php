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

Factory::getDocument()->getWebAssetManager()
	->useScript('core')
	->useScript('webcomponent.toolbar-button');

$tagName = $tagName ?? 'button';

$modalAttrs['data-bs-toggle'] = 'modal';
$modalAttrs['data-bs-target'] = '#' . $selector;

$idAttr   = !empty($id)        ? ' id="' . $id . '"' : '';
$listAttr = !empty($listCheck) ? ' list-selection' : '';

?>
<joomla-toolbar-button <?php echo $idAttr . $listAttr; ?>>
<<?php echo $tagName; ?>
	value="<?php echo $doTask; ?>"
	class="<?php echo $btnClass; ?>"
	<?php echo $htmlAttributes; ?>
	<?php echo ArrayHelper::toString($modalAttrs); ?>
>
	<span class="<?php echo $class; ?>" aria-hidden="true"></span>
	<?php echo $text; ?>
</<?php echo $tagName; ?>>
</joomla-toolbar-button>
