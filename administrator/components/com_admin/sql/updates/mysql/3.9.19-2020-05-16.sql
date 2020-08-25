-- Add back the default value which might have been lost with utf8mb4 conversion on certain CMS versions
ALTER TABLE `#__ucm_content` MODIFY `core_title` varchar(400) NOT NULL DEFAULT '';
