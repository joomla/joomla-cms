<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_search
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>

<dl class="search_results<?php echo $this->params->get('pageclass_sfx'); ?>">

		<?php
		foreach($this->results as $result) : ?>


					<?php if ($result->href) :
					    echo '<dt class="result-title">';
						if ($result->browsernav == 1) : ?>
								<?php echo $this->pagination->limitstart + $result->count.'. ';?>
							<a href="<?php echo JRoute::_($result->href); ?>" target="_blank">
<<<<<<< .working
=======
							<?php echo $this->escape($result->title); ?>
							</a>
>>>>>>> .merge-right.r14383
						<?php else : ?>
							<a href="<?php echo JRoute::_($result->href); ?>">

						<?php endif;

						echo $this->escape($result->title);

						if ($result->href) : ?>
							</a>
<<<<<<< .working
							</dt>
						<?php endif;
						if ($result->section) : ?>
						<dd class="result-category">

=======
						<?php endif;?>

						<?php if ($result->section) : ?>
							<br />
>>>>>>> .merge-right.r14383
							<span class="small<?php echo $this->params->get('pageclass_sfx'); ?>">
								(<?php echo $this->escape($result->section); ?>)
							</span>
							</dd>
						<?php endif; ?>
					<?php endif; ?>

				<dd class="result-text">
					<?php echo $result->text; ?>
				</dd>
				<?php
					if ($this->params->get('show_date')) : ?>
				<dd class="result-created<?php echo $this->params->get('pageclass_sfx'); ?>">
					<?php echo $result->created; ?>
				</dd>
				<?php endif; ?>


		<?php endforeach; ?>
</dl>

<div class="pagination">
				<?php echo $this->pagination->getPagesLinks(); ?>
				</div>
