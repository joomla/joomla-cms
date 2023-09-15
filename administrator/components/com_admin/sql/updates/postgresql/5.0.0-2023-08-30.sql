UPDATE "#__extensions"
   SET "locked" = 0
 WHERE "type" = 'plugin' AND "element" = 'recaptcha_invisible' AND "folder" = 'captcha';
