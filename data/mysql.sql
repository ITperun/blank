-- Adminer 4.8.1 MySQL 8.2.0 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `user_id` int NOT NULL,
  `content` text NOT NULL,
  `is_announcement` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_comments_event` (`event_id`),
  KEY `fk_comments_user` (`user_id`),
  CONSTRAINT `fk_comments_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `event_participants`;
CREATE TABLE `event_participants` (
  `event_id` int NOT NULL,
  `user_id` int NOT NULL,
  `joined_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`event_id`,`user_id`),
  KEY `fk_ep_user` (`user_id`),
  CONSTRAINT `fk_ep_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ep_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `event_participants` (`event_id`, `user_id`, `joined_at`) VALUES
(21,	1,	'2025-11-21 08:15:31');

DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `location` varchar(255) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `game_id` int DEFAULT NULL,
  `organizer_id` int NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_events_game` (`game_id`),
  KEY `fk_events_organizer` (`organizer_id`),
  CONSTRAINT `fk_events_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_events_organizer` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `events` (`id`, `title`, `description`, `location`, `start_time`, `end_time`, `game_id`, `organizer_id`, `status`, `created_at`) VALUES
(15,	'Carcassonne Rodinný',	'Hra pro rodiny a přátele.',	'Brno, Dům her',	'2025-11-16 13:00:00',	'2025-11-16 16:00:00',	2,	1,	'approved',	'2025-11-04 13:24:43'),
(16,	'Catan Turnaj',	'Turnaj pro zkušené hráče.',	'Praha, klubovna č.3',	'2025-11-17 18:00:00',	'2025-11-17 22:00:00',	1,	1,	'pending',	'2025-11-04 13:24:43'),
(18,	'Carcassonne Týmový',	'Hra v týmech.',	'Brno, Dům her',	'2025-11-19 14:00:00',	'2025-11-19 17:00:00',	2,	1,	'approved',	'2025-11-04 13:24:43'),
(19,	'Catan Maraton',	'Hraní až do noci.',	'Praha, klubovna č.1',	'2025-11-20 18:00:00',	'2025-11-21 02:00:00',	1,	1,	'approved',	'2025-11-04 13:24:43'),
(20,	'Magic Duel 3',	'Deck-building duel.',	'Praha, klubovna č.2',	'2025-11-21 18:00:00',	'2025-11-21 21:00:00',	NULL,	1,	'pending',	'2025-11-04 13:24:43'),
(21,	'Carcassonne Evening',	'Relax s hrou.',	'Brno, Dům her',	'2025-11-22 14:00:00',	'2025-11-22 17:00:00',	2,	1,	'approved',	'2025-11-04 13:24:43'),
(22,	'Catan Challenge',	'Výzva pro zkušené hráče.',	'Praha, klubovna č.3',	'2025-11-23 18:00:00',	'2025-11-23 21:00:00',	1,	1,	'approved',	'2025-11-04 13:24:43'),
(26,	'1212',	'12215215',	'12214235',	'2025-11-20 13:54:00',	'2025-11-20 13:54:00',	NULL,	1,	'approved',	'2025-11-18 12:54:50'),
(27,	'ewg',	'325',	'wegdsg',	'2025-11-29 13:55:00',	'2025-11-22 13:55:00',	NULL,	1,	'approved',	'2025-11-18 12:55:23'),
(28,	'1251',	'dg',	'326346',	'2025-11-30 13:56:00',	'2025-11-29 13:56:00',	NULL,	1,	'approved',	'2025-11-18 12:56:32'),
(29,	'rehey',	'bvvbbv',	'fbdbdbfb',	'2025-11-24 13:56:00',	'2025-12-02 13:56:00',	NULL,	1,	'approved',	'2025-11-18 12:56:55'),
(30,	'e2555',	'gjgjj',	'575777',	'2025-12-07 13:57:00',	'2025-12-07 13:57:00',	NULL,	1,	'approved',	'2025-11-18 12:57:11'),
(31,	'dsg',	'wdg',	'dgdd',	'2025-11-19 14:25:00',	'2025-11-17 14:25:00',	NULL,	1,	'approved',	'2025-11-18 13:26:00'),
(32,	'testov',	'testov',	'testov',	'2025-11-19 14:29:00',	'2025-11-18 14:29:00',	NULL,	1,	'approved',	'2025-11-18 13:29:33'),
(33,	'testov1',	'testov1',	'testov1',	'2025-11-19 14:29:00',	'2025-11-18 14:29:00',	NULL,	1,	'approved',	'2025-11-18 13:32:38'),
(34,	'testě',	'dsfsf',	'testě',	'2025-11-28 14:34:00',	'2025-11-17 14:34:00',	NULL,	1,	'approved',	'2025-11-18 13:34:26'),
(35,	'еуыа',	'ыфаф',	'цаафа',	'2025-11-19 14:55:00',	'2025-11-18 14:42:00',	NULL,	1,	'approved',	'2025-11-18 13:55:25'),
(36,	'test-start-end1',	'tedg',	'test-start-end',	'2025-11-22 09:30:00',	'2025-11-23 09:30:00',	NULL,	1,	'approved',	'2025-11-21 08:30:51');

DROP TABLE IF EXISTS `games`;
CREATE TABLE `games` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `games` (`id`, `name`, `genre`, `description`, `image`) VALUES
(1,	'Catan',	'Strategy',	'Classic resource trading board game.',	NULL),
(2,	'Carcassonne',	'Tile Placement',	'Build cities and roads with tiles.',	NULL);

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `event_id` int NOT NULL,
  `message` text NOT NULL,
  `send_at` datetime NOT NULL,
  `is_sent` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_notifications_user` (`user_id`),
  KEY `fk_notifications_event` (`event_id`),
  CONSTRAINT `fk_notifications_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- Новая таблица ролей
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Заполнение ролей: admin, user, moderator, organizer
INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'admin'),
(2, 'user'),
(3, 'moderator'),
(4, 'organizer');

-- Изменяем таблицу users: удаляем role, добавляем role_id с внешним ключом
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `firstname` varchar(100) DEFAULT NULL,
  `lastname` varchar(100) DEFAULT NULL,
  `password` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role_id` int NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `fk_users_role` (`role_id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Пример пользователей с role_id (1=admin, 2=user, 3=moderator, 4=organizer)
INSERT INTO `users` (`id`, `username`, `firstname`, `lastname`, `password`, `email`, `role_id`, `image`) VALUES
(1,	'dummy',	'dummy',	'mister',	'$2y$10$5G2JwrkryjUPHulleOE83.iHfWamgedwvOhjvkHg2BAuW7x7jI5ny',	'dummy@gmail.com',	1,	'upload/avatars/68f771eed4396_home.png'),
(2,	'kumi',	'Kumi',	'Fox',	'$2y$10$examplehashhere',	'kumi@example.com',	2,	NULL),
(3,	'Josef',	'Josef',	'Joster',	'$2y$10$ECA3GO3LGCLGsLGVp6Jsl.9P1g6NDb7f1hZ9OjABzeSL/GpjCfLL6',	'Josef@gmail.com',	2,	NULL);

-- 2025-11-21 08:34:41