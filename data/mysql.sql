-- Adminer 5.4.1 MySQL 9.5.0 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

INSERT INTO `posts` (`id`, `title`, `content`, `created_at`) VALUES
(1,	'Первый хвост',	'Знакомство с магией Nette',	'2026-01-07 12:56:53'),
(2,	'Второй хвост',	'Как установить Composer и не сойти с ума',	'2026-01-07 12:56:53'),
(3,	'Третий хвост',	'Секреты DataGrid',	'2026-01-07 12:56:53'),
(4,	'Четвертый хвост',	'Почему Tailwind лучше Bootstrap',	'2026-01-07 12:56:53'),
(5,	'Пятый хвост',	'Любимые сладости Куми',	'2026-01-07 12:56:53'),
(6,	'Шестой хвост',	'Финальный босс: Деплой',	'2026-01-07 12:56:53');

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `firstname` varchar(100) DEFAULT NULL,
  `lastname` varchar(100) DEFAULT NULL,
  `password` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `users` (`id`, `username`, `firstname`, `lastname`, `password`, `email`, `role`, `image`) VALUES
(1,	'dummy',	'dummy',	'mister',	'$2y$10$5G2JwrkryjUPHulleOE83.iHfWamgedwvOhjvkHg2BAuW7x7jI5ny',	'dummy@gmail.com',	'admin',	'upload/avatars/68f771eed4396_home.png'),
(2,	'Teresa',	'Teresa',	'Teresa',	'$2y$10$QAj4t6L3sYZ0LHI0YHuOD.H8maXBGDmuxtJDtRN8ygjpZfwCPTnGm',	'test@test.test',	'user',	NULL);

-- 2026-01-07 13:05:47 UTC