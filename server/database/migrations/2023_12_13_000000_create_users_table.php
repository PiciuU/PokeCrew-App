CREATE TABLE `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP,
    `updated_at` TIMESTAMP,
    PRIMARY KEY (`id`), UNIQUE `users_email_unique`(`email`)
) ENGINE = InnoDB;
