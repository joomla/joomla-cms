UPDATE "#__extensions" SET "name" = 'plg_fields_subform', "element" = 'subform' WHERE "#__extensions"."name" = 'plg_fields_subfields';
UPDATE "#__fields" SET "type" = "subform" WHERE "#__fields"."type" = 'subfields';

