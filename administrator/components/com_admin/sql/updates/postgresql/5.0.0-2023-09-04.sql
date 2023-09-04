-- Reset default style in administrator
UPDATE "#__template_styles" SET "home" = '0' WHERE "client_id" = 1;

-- Insert new style for Joomla! 5
INSERT INTO "#__template_styles" ("template", "client_id", "home", "title", "inheritable", "parent", "params") VALUES
  ('atum', 1, '1', 'Atum - Joomla! 5', 1, '', '{"hue":"hsl(178, 63%, 20%)","bg-light":"#f0f4fb","text-dark":"#495057","text-light":"#ffffff","link-color":"#2a69b8","special-color":"#001b4c","monochrome":"0","loginLogo":"","loginLogoAlt":"","logoBrandLarge":"","logoBrandLargeAlt":"","logoBrandSmall":"","logoBrandSmallAlt":""}'),
