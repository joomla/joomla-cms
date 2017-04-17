<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=vote" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<?php
		$this->vote_ref_id_input = "data[vote][vote_ref_id]";
		$this->vote_type_input = "data[vote][vote_type]";
		$this->vote_pseudo_input = "data[vote][vote_pseudo]";
		$this->vote_email_input = "data[vote][vote_email]";
		$this->vote_rating_input = "data[vote][vote_rating]";
		$this->vote_comment_input = "data[vote][vote_comment]";

		$this->setLayout('normal');

		echo $this->loadTemplate();
	?>
	<table class="admintable table" width="100%">
		<tr>
			<td class="key">
				<label for="data[vote][vote_published]">
					<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
				</label>
			</td>
			<td>
				<?php echo JHTML::_('hikaselect.booleanlist', "data[vote][vote_published]" , '',@$this->element->vote_published); ?>
			</td>
		</tr>
	</table>
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->vote_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="vote" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
