<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

?>
<div style="border: 1px solid red">
    <h1>! WIP ! Joomla! Debug ! WIP !</h1>

    <ul>
		<?php foreach ($this->item as $group => $item) : ?>

            <li>
				<?php echo $group ?>
            </li>

		<?php endforeach; ?>
    </ul>

	<?php
	dump($this->meta);
	dump($this->item);
	?>

</div>
