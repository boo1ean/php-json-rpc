DROP DATABASE `car_business`;
CREATE DATABASE `car_business`;

USE `car_business`;

DROP USER 'car_business'@'localhost';

CREATE
    USER 'car_business'@'localhost'
    IDENTIFIED BY 'car_business';

GRANT ALL
    ON `car_business`.*
    TO 'car_business'@'localhost';

CREATE TABLE IF NOT EXISTS `users` (
    `id`           INT(11)      AUTO_INCREMENT,
    `email`        VARCHAR(80)  NOT NULL,
    `password`     VARCHAR(45)  NOT NULL DEFAULT '',
    `first_name`   varchar(80)  NULL DEFAULT '',
    `last_name`    varchar(80)  NULL DEFAULT '',
    `phone_number` VARCHAR(45)  NOT NULL DEFAULT '',
    `country`      VARCHAR(45)  NOT NULL DEFAULT '',
    `city`         VARCHAR(45)  NOT NULL DEFAULT '',
    `address`      VARCHAR(255) NOT NULL DEFAULT '',
    `created_at`   DATETIME NOT NULL,
    `updated_at`   DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `businesses` (
    `id`           INT(11)      NOT NULL AUTO_INCREMENT,
    `user_id`      INT(11)      NOT NULL,
    `name`         VARCHAR(255) NOT NULL DEFAULT '',
    `description`  TEXT         NOT NULL DEFAULT '',
    `phone_number` VARCHAR(45)  NOT NULL DEFAULT '',
    `country`      VARCHAR(80)  NOT NULL DEFAULT '',
    `city`         VARCHAR(80)  NOT NULL DEFAULT '',
    `address`      VARCHAR(255) NOT NULL,
    `photo`        VARCHAR(255),
    `created_at`   DATETIME NOT NULL,
    `updated_at`   DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `products` (
    `id`          INT(11)      NOT NULL AUTO_INCREMENT,
    `business_id` INT(11)      NOT NULL,
    `name`        VARCHAR(255) NOT NULL DEFAULT '',
    `description` TEXT         NOT NULL DEFAULT '',
    `price`       DOUBLE       NOT NULL,
    `photo`       VARCHAR(255),
    `created_at`  DATETIME NOT NULL,
    `updated_at`  DATETIME NOT NULL,
    `status`      ENUM('available', 'unavailable', 'sold') NOT NULL DEFAULT 'available',
    PRIMARY KEY (`id`),
    FOREIGN KEY (`business_id`) REFERENCES `businesses`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `product_orders` (
    `id`          INT(11)      NOT NULL AUTO_INCREMENT,
    `product_id`  INT(11)      NOT NULL,
    `user_id`     INT(11)      NOT NULL,
    `status`      ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `created_at`  DATETIME NOT NULL,
    `updated_at`  DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `bookings` (
    `id`          INT(11)  NOT NULL AUTO_INCREMENT,
    `product_id`  INT(11)  NOT NULL,
    `duration`    INT(5)   NOT NULL,
    `price`       DOUBLE   NOT NULL,
    `created_at`  DATETIME NOT NULL,
    `updated_at`  DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `product_bookings` (
    `id`         INT(11)  NOT NULL AUTO_INCREMENT,
    `booking_id` INT(11)  NOT NULL,
    `user_id`    INT(11)  NOT NULL,
    `start_time` DATETIME NOT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    `status`     ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    PRIMARY KEY (`id`),
    FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`),
    FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `reviews` (
    `id`          INT(11)      NOT NULL AUTO_INCREMENT,
    `business_id` INT(11)   NOT NULL,
    `user_id`     INT(11)      NOT NULL,
    `title`       VARCHAR(255) NOT NULL DEFAULT '',
    `body`        TEXT         NOT NULL DEFAULT '',
    `created_at`  DATETIME NOT NULL,
    `updated_at`  DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`business_id`) REFERENCES `businesses`(`id`),
    FOREIGN KEY (`user_id`)  REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;
