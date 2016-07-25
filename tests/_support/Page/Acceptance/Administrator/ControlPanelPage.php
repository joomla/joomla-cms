<?php
namespace Page\Acceptance\Administrator;

use Page\Acceptance\Administrator\AdminPage;

class ControlPanelPage extends AdminPage
{
    public static $url = "/administrator/index.php";

    public static $pageTitle = 'Control Panel';

    public static $pageTitleContext = ['class' => 'page-title'];
}