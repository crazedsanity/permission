
DROP TABLE IF EXISTS `permission_table`;
CREATE TABLE `permission_table` (
  `permission_id` INT NOT NULL AUTO_INCREMENT,
  `object` varchar(120) NOT NULL,
  `user_id` INT NOT NULL,
  `group_id` VARCHAR(45) NOT NULL,
  `perms` VARCHAR(3) NOT NULL DEFAULT '744',
  PRIMARY KEY (`permission_id`),
  UNIQUE INDEX `permission_id_UNIQUE` (`permission_id` ASC),
  UNIQUE INDEX `object_UNIQUE` (`object` ASC));
