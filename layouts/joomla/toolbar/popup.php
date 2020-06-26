<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;

Factory::getDocument()->getWebAssetManager()
	->useScript('core')
	->useScript('webcomponent.toolbar-button');

/**
 * @var  int     $id
 * @var  string  $name
 * @var  string  $doTask
 * @var  string  $class
 * @var  string  $text
 * @var  string  $btnClass
 * @var  string  $tagName
 * @var  bool    $listCheck
 * @var  string  $htmlAttributes
 */
extract($displayData, EXTR_OVERWRITE);

$tagName = $tagName ?? 'button';

$modalAttrs['data-toggle'] = 'modal';
$modalAttrs['data-target'] = '#' . $selector;

$idAttr   = !empty($id)        ? ' id="' . $id . '"' : '';
$listAttr = !empty($listCheck) ? ' list-selection' : '';

?>
<joomla-toolbar-button <?php echo $idAttr.$listAttr; ?>>
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
