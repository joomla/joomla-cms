<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
ksort($this->files, SORT_STRING);
?>

<ul class='nav nav-list directory-tree'>
	<?php foreach($this->files as $key => $value): ?>
		<?php if(is_array($value)): ?>
			<?php
			$keyArray  = explode('/', $key);
			$fileArray = explode('/', $this->fileName);
			$count     = 0;

			if (count($fileArray) >= count($keyArray))
			{
				for ($i = 0; $i < count($keyArray); $i++)
				{
					if ($keyArray[$i] === $fileArray[$i])
					{
						$count = $count + 1;
					}
				}

				if ($count == count($keyArray))
				{
					$class = "folder show";
				}
				else
				{
					$class = "folder";
				}
			}
			else
			{
				$class = "folder";
			}

			?>
			<li class="<?php echo $class; ?>">
				<a class='folder-url nowrap' href=''>
					<i class='icon-folder-close'>&nbsp;<?php $explodeArray = explode('/', $key); echo end($explodeArray); ?></i>
				</a>
				<?php echo $this->directoryTree($value); ?>
			</li>
		<?php endif; ?>
		<?php if(is_object($value)): ?>
			<li>
				<a class="file nowrap" href='<?php echo JRoute::_('index.php?option=com_templates&view=template&id=' . $this->id . '&file=' . $value->id) ?>'>
					<i class='icon-file'>&nbsp;<?php echo $value->name; ?></i>
				</a>
			</li>
		<?php endif; ?>
	<?php endforeach; ?>
</ul>
