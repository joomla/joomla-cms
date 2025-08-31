<?php

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

?>
<script>
    if(window.top && window.top.document.querySelector('dialog[open]')) {
        window.top.document.querySelector('dialog[open]').close();
    }
</script>
