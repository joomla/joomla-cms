<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var  string  $id     DOM id of the field.
 * @var  string  $label  Label of the field.
 * @var  string  $name   Name of the input field.
 * @var  string  $value  Value attribute of the field.
 */

Text::script('ERROR');
Text::script('MESSAGE');
Text::script('PLG_USER_TOKEN_COPY_SUCCESS');
Text::script('PLG_USER_TOKEN_COPY_FAIL');

Factory::getApplication()->getDocument()->getWebAssetManager()
	->registerAndUseScript('plg_user_token.token', 'plg_user_token/token.js', [], ['defer' => true], ['core']);
?>
<div class="input-group">
	<input
		type="text"
		class="form-control"
		name="<?php echo $name; ?>"
		id="<?php echo $id; ?>"
		readonly
		value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>">
	<button
		class="btn btn-primary"
		type="button"
		id="token-copy"
		title="<?php echo Text::_('PLG_USER_TOKEN_COPY_DESC'); ?>"><?php echo Text::_('PLG_USER_TOKEN_COPY'); ?></button>
</div>
