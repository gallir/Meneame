ALTER TABLE `links` ADD `link_nsfw` BOOLEAN NOT NULL DEFAULT 0;
ALTER TABLE `links` MODIFY `link_status` CHAR(20) NOT NULL DEFAULT 'discard';