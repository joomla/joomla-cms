<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$data = $displayData;

if ($data['view'] instanceof LanguagesViewOverrides)
{
	// We will get the language_client filter & remove it from the form filters
	$langClient = $data['view']->filterForm->getField('language_client'); ?>
	<div class="js-stools-field-filter js-stools-selector">
		<?php echo $langClient->input; ?>
	</div>
<?php
}
// Display the main joomla layout
echo JLayoutHelper::render('joomla.searchtools.default.bar', $data, null, array('component' => 'none')); ?>
