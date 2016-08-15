---
--- Increasing size of the URL field in com_newsfeeds
---

ALTER TABLE `#__newsfeeds` ALTER COLUMN `link` TYPE varchar(2048);
