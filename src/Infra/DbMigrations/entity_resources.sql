CREATE TABLE `entity_resources` (
  `entity_id` varchar(36) NOT NULL,
  `resource_id` varchar(36) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`entity_id`,`resource_id`)
);

ALTER TABLE `entity_resources` ADD COLUMN `last_consumed_variety_id` VARCHAR(36);
