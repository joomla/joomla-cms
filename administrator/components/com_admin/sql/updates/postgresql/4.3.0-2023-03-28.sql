ALTER TABLE "#__guidedtours" DROP COLUMN "asset_id";

DELETE FROM "#__assets" WHERE "name" LIKE 'com_guidedtours.tour.%';
