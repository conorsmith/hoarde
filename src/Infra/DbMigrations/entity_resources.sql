CREATE TABLE `entity_resources` (
  `entity_id` varchar(36) NOT NULL,
  `resource_id` varchar(36) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`entity_id`,`resource_id`)
);
