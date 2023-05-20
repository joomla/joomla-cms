<?php

/**
 * @package       JED
 *
 * @subpackage    VEL
 *
 * @copyright     (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


use Jed\Component\Jed\Site\Helper\JedHelper;

?>

<div class="page-header">
    <h2 itemprop="headline"><?php echo JedHelper::reformatTitle($this->item->title); ?></h2>
</div>
<div itemprop="articleBody">
    <p><?php echo JedHelper::reformatTitle($this->item->public_description); ?></p>
</div>

