UPDATE `#__extensions`
   SET `locked` = 0
 WHERE `element` = 'recaptcha_invisible' AND `folder` = 'captcha';
