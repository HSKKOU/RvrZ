INSERT INTO `items`
SELECT * FROM `items_1`
UNION SELECT * FROM `items_2`;

ALTER TABLE `reviews_` DROP `id`;
ALTER TABLE `reviews_` ADD `id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;


SELECT * FROM `reviews_01`
WHERE `user_name` IN (
  SELECT `user_name`
	FROM `reviews_01`
	WHERE `item_id`='penroom:sheaffer_s002' OR `item_id`='2-itamae:sandannp'
	GROUP BY `user_name`
	HAVING COUNT(*) = 2
)
AND (`item_id`='penroom:sheaffer_s002' OR `item_id`='2-itamae:sandannp');

ALTER TABLE `reviews_` DROP `id`;
ALTER TABLE `reviews_` ADD `id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
UPDATE `reviews_` SET `id`=`id`+;
RENAME TABLE `vrshopping03`.`reviews_` TO `vrshopping03`.`reviews_0`;

SELECT *
FROM
`reviews_01`R1,
`reviews_02`R2,
`reviews_03`R3,
`reviews_04`R4,
`reviews_05`R5,
`reviews_06`R6,
`reviews_07`R7,
`reviews_08`R8,
`reviews_09`R9,
`reviews_10`R10,
`reviews_11`R11,
`reviews_12`R12,
`reviews_13`R13,
`reviews_14`R14,
`reviews_15`R15,
`reviews_16`R16,
`reviews_17`R17,
`reviews_18`R18,
`reviews_19`R19,
`reviews_20`R20,
`reviews_21`R21,
`reviews_22`R22,
`reviews_23`R23,
`reviews_24`R24,
`reviews_25`R25,
`reviews_26`R26,
`reviews_27`R27,
`reviews_28`R28,
`reviews_29`R29,
`reviews_30`R30,
`reviews_31`R31,
`reviews_32`R32,
`reviews_33`R33,
`reviews_34`R34,
`reviews_35`R35,
`reviews_36`R36
WHERE
R1.`user_name`


SELECT *
FROM
`reviews_01`R1,
`reviews_02`R2
WHERE
R1.`user_name`=R2.`user_name`
AND R1.`item_id`=R2.`item_id`;
