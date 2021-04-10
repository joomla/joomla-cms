<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * @var $displayData []
 * @var $terms []
 * @var $options []
 */
extract($displayData);

$terms = array_filter($terms, 'strlen');

// Nothing to Highlight
if (empty($terms))
{
	return;
}

$doc = Factory::getDocument();

$doc->getWebAssetManager()->useScript('js-highlight');
$doc->addScriptOptions(
	'js-highlight',
	[
		'class'          => !empty($options['class']) ? $options['class'] : 'js-highlight',
		'iframes'        => !empty($options['iframes']) ? $options['iframes'] : false,
		'iframesTimeout' => !empty($options['iframesTimeout']) ? $options['iframesTimeout'] : 5000,
		'debug'          => !empty($options['debug']) ? $options['debug'] : false,
		'highLight'      => $terms,
		"accuracy"       => "partially",
		"diacritics"     => true,
		"exclude"        => !empty($options['exclude']) ? $options['exclude'] : [],
		"done"           => !empty($options['done']) ? $options['done'] : function(){},

		// For B/C with the old code!!!! will be remove @5.0
		'compatibility'  => !empty($options['compatibility']) ? $options['compatibility'] : false,
		'start'          => !empty($options['start']) ? $options['start'] : '',
		'end'            => !empty($options['end']) ? $options['end'] : '',
	]
);
