<?php /** $Id$ */ defined('_JEXEC') or die('Restricted access'); ?>
<fieldset class="adminform">
	<legend><?php echo JText::_( 'Parameters' ); ?></legend>
	<table class="admintable">
		<tr>
			<td>
				<?php
					jimport('joomla.html.pane');
					$pane	=& JPane::getInstance('sliders');
					$params = $this->user->getParameters(true);
					echo $pane->startPane("menu-pane");
					$groups = $params->getGroups();
					if(count($groups)) {
						foreach($groups as $groupname => $group) {
							if($groupname == '_default') {
								$title = 'General';
							} else {
								$title = ucfirst($groupname);
							}
							if($params->getNumParams($groupname)) {
								echo $pane->startPanel(JText :: _('Parameters - '.$title), $groupname.'-page');
								echo $params->render('params', $groupname);
								echo $pane->endPanel();
							}
						}
					}
					echo $pane->endPane();
				?>
			</td>
		</tr>
	</table>
</fieldset>
