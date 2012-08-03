<?php
if ($_GET['randomId'] != "23ht6KxrcUGriDc4Bc0WG4EUdBMMgDcJQ8WJwycBtDyztBKDysrY5ENzcmCOyZxL") {
    echo "Access Denied";
    exit();
}

// display the HTML code:
echo stripslashes($_POST['wproPreviewHTML']);

?>  
