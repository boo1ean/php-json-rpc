DROP DATABASE `car_business`;
CREATE DATABASE `car_business`;

USE `car_business`;

CREATE
    USER 'car_business'@'localhost'
    IDENTIFIED BY 'car_business';

GRANT ALL
    ON `car_business`.*
    TO 'car_business'@'localhost';

CREATE TABLE IF NOT EXISTS `users` (
    `id`       INT(11)      AUTO_INCREMENT,
    `email`    VARCHAR(80)  NOT NULL,
    `password` VARCHAR(45)  NOT NULL DEFAULT '',
    `country`  VARCHAR(45)  NOT NULL DEFAULT '',
    `city`     VARCHAR(45)  NOT NULL DEFAULT '',
    `address`  VARCHAR(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;
