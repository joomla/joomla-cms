<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
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
