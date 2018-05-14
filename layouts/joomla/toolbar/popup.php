<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

HTMLHelper::_('behavior.core');

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

if (!empty($listCheck))
{
	Text::script('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
	Text::script('ERROR');
	$message = "{'error': [Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST')]}";
	$alert = 'Joomla.renderMessages(' . $message . ')';

	$modalAttrs['onclick'] = <<<JS
if (document.adminForm.boxchecked.value==0){ $alert } else { jQuery( '#$selector' ).modal('show'); return true; }
JS;
}
else
{
	$modalAttrs['data-target'] = '#' . $selector;
}
?>
<<?php echo $tagName; ?>
	id="<?php echo $id; ?>"
	value="<?php echo $doTask; ?>"
	class="<?php echo $btnClass; ?>"
	<?php echo $htmlAttributes; ?>
	<?php echo ArrayHelper::toString($modalAttrs); ?>
>
	<span class="<?php echo $class; ?>" aria-hidden="true"></span>
	<?php echo $text; ?>
</<?php echo $tagName; ?>>
