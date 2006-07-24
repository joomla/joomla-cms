<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');

?>
<form action="index.php" method="get">
	<div class="search<?php echo $params->get('moduleclass_sfx') ?>">
		<?php echo modSearchHelper::renderInputField($params) ?>
	</div>

	<input type="hidden" name="option" value="com_search" />
	<input type="hidden" name="Itemid" value="<?php echo modSearchHelper::getItemid($params); ?>" />
</form>