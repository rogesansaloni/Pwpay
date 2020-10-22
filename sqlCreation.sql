CREATE DATABASE IF NOT EXISTS pwpay;

USE pwpay;

DROP TABLE IF EXISTS `transaction`;
DROP TABLE IF EXISTS `bank_details`;
DROP TABLE IF EXISTS `request`;
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL DEFAULT '',
  `password` VARCHAR(255) NOT NULL DEFAULT '',
  `birthdate` DATETIME NOT NULL,
  `cellphone` VARCHAR(13) NOT NULL DEFAULT  '',
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  `activated` BOOLEAN NOT NULL DEFAULT false,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `user` (`email`,`password`,`birthdate`,`cellphone`,`created_at`,`updated_at`,`activated`) VALUES ("admin@admin.admin","admin",now(),"12345",now(),now(),true);

CREATE TABLE `transaction` (
    `sender_id` INT(11) unsigned NOT NULL,
    `u_id` INT(11) unsigned NOT NULL,
    `transaction_time` DATETIME NOT NULL,
    `amount` FLOAT NOT NULL,
    PRIMARY KEY (`sender_id`,`u_id`,`transaction_time`),
    FOREIGN KEY (`sender_id`) REFERENCES `user`(`id`),
    FOREIGN KEY (`u_id`) REFERENCES `user`(`id`)
);

CREATE TABLE `bank_details`(
    `u_id` INT(11) unsigned NOT NULL,
    `IBAN`VARCHAR(255) NOT NULL,
    `IBAN_name` VARCHAR(255) NOT NULL DEFAULT '',
    PRIMARY KEY(`u_id`,`IBAN`),
    FOREIGN KEY(`u_id`) REFERENCES `user` (`id`)
);

CREATE TABLE `request` (
	`request_id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
    `u_requester_id` INT(11) unsigned NOT NULL,
    `u_requested_id` INT(11) unsigned NOT NULL,
    `money_requested` FLOAT NOT NULL,
    `already_paid` BOOLEAN NOT NULL,
    `request_time` DATETIME NOT NULL,

    PRIMARY KEY (`request_id`, `u_requester_id`,`u_requested_id`),
    FOREIGN KEY (`u_requester_id`) REFERENCES `user`(`id`),
    FOREIGN KEY (`u_requested_id`) REFERENCES `user`(`id`)
);