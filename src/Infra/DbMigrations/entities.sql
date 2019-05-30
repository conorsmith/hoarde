CREATE TABLE `entities` (
  `id` varchar(36) NOT NULL,
  `game_id` varchar(36) NOT NULL,
  `intact` tinyint(1) NOT NULL DEFAULT '1'
);

ALTER TABLE `entities` ADD COLUMN `label` VARCHAR(256) NOT NULL AFTER `game_id`;
ALTER TABLE `entities` ADD COLUMN `icon` VARCHAR(256) NOT NULL AFTER `label`;

UPDATE `entities` SET `label` = "Entity", `icon` = "user";
