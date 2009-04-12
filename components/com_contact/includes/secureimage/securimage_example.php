Out of the box example of Securimage CAPTCHA Class.<br /><br />

<img src="securimage_show.php?sid=<?php echo md5(uniqid(time())); ?>" id="image" align="middle" />
<a href="securimage_play.php" style="font-size: 13px">(Audio)</a><br /><br />

<a href="#" onclick="document.getElementById('image').src = 'securimage_show.php?sid=' + Math.random(); return false">Reload Image</a>
