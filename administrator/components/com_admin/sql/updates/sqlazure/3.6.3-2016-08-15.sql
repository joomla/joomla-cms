---
--- Increasing size of the URL field in com_newsfeeds
---

ALTER TABLE [#__newsfeeds] ALTER COLUMN [link] NVARCHAR(2048) NOT NULL;
