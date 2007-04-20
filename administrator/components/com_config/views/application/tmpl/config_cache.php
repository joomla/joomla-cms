<fieldset class="adminform">
	<legend><?php echo JText::_( 'Cache Settings' ); ?></legend>
	<table class="admintable" cellspacing="1">

		<tbody>
		<tr>
			<td class="key">
				<?php echo JText::_( 'Cache' ); ?>
			</td>
			<td>
				<?php echo $lists['caching']; ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'Cache Time' ); ?>
			</td>
			<td>
				<input class="text_area" type="text" name="cachetime" size="5" value="<?php echo $row->cachetime; ?>" />
					<?php echo JText::_( 'seconds' ); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'Cache Handler' ); ?>
			</td>
			<td>
				<?php echo $lists['cache_handlers']; ?>
			</td>
		</tr>
		<?php if ($row->cache_handler == 'memcache' || $row->session_handler == 'memcache') : ?>
		<tr>
			<td class="key">
				<?php echo JText::_( 'Memcache Persistent' ); ?>
			</td>
			<td>
				<?php echo $lists['memcache_persist']; ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'Memcache Compression' ); ?>
			</td>
			<td>
				<?php echo $lists['memcache_compress']; ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'Memcache Server' ); ?>
			</td>
			<td>
				<?php echo JText::_( 'Host' ); ?>:
				<input class="text_area" type="text" name="memcache_settings[servers][0][host]" size="25" value="<?php echo @$row->memcache_settings['servers'][0]['host']; ?>" />
				<br /><br />
				<?php echo JText::_( 'Port' ); ?>:
				<input class="text_area" type="text" name="memcache_settings[servers][0][port]" size="6" value="<?php echo @$row->memcache_settings['servers'][0]['port']; ?>" />
			</td>
		</tr>
		<?php endif; ?>
		</tbody>
	</table>
</fieldset>
