ALTER TABLE "#__cronjobs"
    ADD COLUMN "asset_id" INT NOT NULL DEFAULT '0';

ALTER TABLE "#__cronjobs"
    ADD COLUMN "created" INT NOT NULL DEFAULT '0';
ALTER TABLE "#__cronjobs"
    ADD COLUMN "created_by" INT NOT NULL DEFAULT '0';
