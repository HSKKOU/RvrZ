INSERT INTO `items`
SELECT * FROM `items_1`
UNION SELECT * FROM `items_2`;

ALTER TABLE `items` ADD `id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEYS FIRST;
