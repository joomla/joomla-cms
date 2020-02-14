<?php
/**
 * @version $Id$
 * @package DJ-ImageSlider
 * @subpackage DJ-ImageSlider Component
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 *
 * DJ-ImageSlider is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-ImageSlider is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-ImageSlider. If not, see <http://www.gnu.org/licenses/>.
 *
 */

// No direct access.
defined('_JEXEC') or die;

$fieldSets = $this->form->getFieldsets('params');
foreach ($fieldSets as $name => $fieldSet) : ?>
	
	<fieldset class="panelform well" >
		
			<h3><?php echo JText::_($fieldSet->label); ?></h3>
			<?php if (isset($fieldSet->description) && trim($fieldSet->description)) :
				echo '<p class="tip alert alert-info">'.$this->escape(JText::_($fieldSet->description)).'</p>';
			endif; ?>
			<?php foreach ($this->form->getFieldset($name) as $field) : ?>
				<?php echo $field->renderField(); ?>
			<?php endforeach; ?>
		
	</fieldset>
<?php endforeach; ?>