--
-- Table structure for table "#__guidedtours"
--
--
CREATE TABLE IF NOT EXISTS "#__guidedtours"
(
 `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `ordering` int NOT NULL DEFAULT 0,
  `extensions` text NOT NULL,
  `url` varchar(255) NOT NULL,
  `overlay` tinyint NOT NULL  DEFAULT 0,
  `created` datetime NOT NULL,
  `created_by` int NOT NULL DEFAULT 0,
  `modified` datetime NOT NULL,
  `modified_by` int NOT NULL DEFAULT 0,
  `checked_out_time` datetime NOT NULL,
  `checked_out` int NOT NULL DEFAULT 0,
  `published` tinyint NOT NULL DEFAULT 0,
  `state` tinyint NOT NULL DEFAULT '1',
    PRIMARY KEY ("id")
);

CREATE INDEX "#__guidedtours_idx_asset_id" ON "#__guidedtours" (`asset_id`);
CREATE INDEX "#__guidedtours_idx_title" ON "#__guidedtours" (`title`(191));
CREATE INDEX "#__guidedtours_idx_created" ON "#__guidedtours" (`created`);
CREATE INDEX "#__guidedtours_idx_created_by" ON "#__guidedtours" (`created_by`);
CREATE INDEX "#__guidedtours_idx_modified" ON "#__guidedtours" (`modified`);
CREATE INDEX "#__guidedtours_idx_modified_by" ON "#__guidedtours" (`modified_by`);
CREATE INDEX "#__guidedtours_idx_checked_out" ON "#__guidedtours" (`checked_out`);

--
-- Table structure for table "#__guidedtour_steps"
--

CREATE TABLE IF NOT EXISTS "#__guidedtour_steps"
(
 `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `tour_id` int NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `published` tinyint NOT NULL DEFAULT 0,
  `description` text NOT NULL,
  `ordering` int NOT NULL DEFAULT 0,
  `step-no` int NOT NULL DEFAULT 0,
  `position` varchar(255) NOT NULL,
  `target` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `created_by` int unsigned NOT NULL DEFAULT 0,
  `modified` datetime NOT NULL,
  `modified_by` int unsigned NOT NULL DEFAULT 0,
  `state` tinyint NOT NULL DEFAULT '1'   
);
 CREATE INDEX "#__guidedtours_idx_tour_id" ON "#__guidedtour_steps" (`tour_id`);