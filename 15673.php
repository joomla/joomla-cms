<?php

define('_JEXEC', 1);
define('JPATH_BASE', __DIR__);
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';


var_dump(
	JComponentHelper::filterText(
		'<ul>
<li><a href="../">презентация</a>)</li>
<li>Елфимова О.Т. Разработка системы отделения космического аппарата Метеор-М в системе MSC.Adams<a style="color: maroon;" href="../../pub/diplom_labors/2016/2016_Elfimova_O_rpz.pdf">диплом</a></li>
</ul>'
	)
);