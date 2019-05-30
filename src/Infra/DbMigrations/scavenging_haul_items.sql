CREATE TABLE `scavenging_haul_items` (
  `haul_id` varchar(36) NOT NULL,
  `variety_id` varchar(36) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`haul_id`,`variety_id`)
);
