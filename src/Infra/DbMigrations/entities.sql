CREATE TABLE `entities` (
  `id` varchar(36) NOT NULL,
  `game_id` varchar(36) NOT NULL,
  `intact` tinyint(1) NOT NULL DEFAULT '1'
);

ALTER TABLE `entities` ADD COLUMN `label` VARCHAR(256) NOT NULL AFTER `game_id`;
ALTER TABLE `entities` ADD COLUMN `icon` VARCHAR(256) NOT NULL AFTER `label`;

UPDATE `entities` SET `label` = "Entity", `icon` = "user";

ALTER TABLE `entities` ADD COLUMN `variety_id` VARCHAR(36) NOT NULL AFTER `game_id`;

UPDATE `entities` SET variety_id = "fde2146a-c29d-4262-b96f-ec7b696eccad";

ALTER TABLE `entities` ADD COLUMN `is_constructed` TINYINT(1) NOT NULL DEFAULT 1 AFTER `intact`;
ALTER TABLE `entities` ADD COLUMN `construction_level` INT(11) NOT NULL DEFAULT 0 AFTER `is_constructed`;
