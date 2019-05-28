CREATE TABLE `entity_inventory` (
  `entity_id` varchar(36) NOT NULL,
  `item_id` varchar(36) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`entity_id`,`item_id`)
);
