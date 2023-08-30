UPDATE "#__extensions"
   SET "locked" = 0
 WHERE "element" IN ('recaptcha', 'recaptcha_invisible') AND "folder" = 'captcha';
