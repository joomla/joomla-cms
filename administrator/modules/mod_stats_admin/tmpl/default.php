<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_stats_admin
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

\Joomla\CMS\Factory::getApplication()->getDocument()->addScriptDeclaration('
(function() {
	document.addEventListener(\'DOMContentLoaded\', function() {
		var elms = document.querySelectorAll("a.js-revert");

		if (elms.length) {
			elms.forEach(function(el) {
				el.addEventListener("click", function(e) {
					e.preventDefault();
					e.stopPropagation();

					var activeTab = [];
					activeTab.push("#" + e.target.href.split("#")[1]);
					var path = window.location.pathname;
					localStorage.removeItem(e.target.href.replace(/&return=[a-zA-Z0-9%]+/, "").replace(/&[a-zA-Z-_]+=[0-9]+/, ""));
					localStorage.setItem(path + e.target.href.split("index.php")[1].split("#")[0], JSON.stringify(activeTab));
					return window.location.href = e.target.href.split("#")[0];
				});
			});
		}
	});
})();
');
?>
<ul class="list-group list-group-flush stats-module">
	<?php foreach ($list as $item) : ?>
		<li class="list-group-item">
			<span class="me-2 icon-<?php echo $item->icon; ?> icon-fw" aria-hidden="true"></span> <?php echo $item->title; ?>

			<?php if(isset($item->link)) : ?>
				<a class="btn btn-info btn-sm js-revert" href="<?php echo $item->link; ?>"><?php echo $item->data; ?></a>
			<?php else : ?>
				<?php echo $item->data; ?>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
</ul>
