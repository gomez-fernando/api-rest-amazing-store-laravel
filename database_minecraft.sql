SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- CREATE DATABASE IF NOT EXISTS api_rest_laravel CHARACTER SET utf8 COLLATE utf8_general_ci;
-- USE api_rest_laravel;

--
-- Estructura de tabla para la tabla `posts`
--

DROP TABLE IF EXISTS `posts`;
CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `user_id` int(255) NOT NULL ,
  `category_id` int(255) NOT NULL ,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  CONSTRAINT pk_posts PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Estructura de tabla para la tabla `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `surname` varchar(100) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `nick` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  CONSTRAINT pk_users PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Estructura de tabla para la tabla `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  CONSTRAINT pk_categories PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Restricciones para las tablas
--
ALTER TABLE `users`
    ADD CONSTRAINT uk_users_email_nick UNIQUE(email, nick);

ALTER TABLE `categories`
    ADD CONSTRAINT uk_categories_name UNIQUE(name);

ALTER TABLE `posts`
    ADD CONSTRAINT uk_posts_name UNIQUE(title),
    ADD CONSTRAINT `fk_post_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE restrict ON UPDATE restrict,
    ADD CONSTRAINT `fk_post_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE restrict ON UPDATE restrict;

--
-- Volcado de datos para las tablas
--

INSERT INTO `users` (`id`, `name`, `surname`, `role`, `nick`, `email`, `password`, `description`, `image`, `created_at`, `updated_at`, `remember_token`) VALUES
(NULL, 'Javier', 'Estrada', 'ROLE_ADMIN', '@admin', 'admin@admin.com', '5eb564d261e9f6eb752ef212bdfe68584e142d2e4b42240e8f11f934d81c5517', 'descripción del administrador', null, '2020-03-04 21:13:35', null, null),
(NULL, 'Enrique', 'García', 'ROLE_USER', '@enrique', 'uno@uno.com', '5eb564d261e9f6eb752ef212bdfe68584e142d2e4b42240e8f11f934d81c5517', 'Yo no cumplo propósitos. Yo desbloqueo logros ;)', null, '2020-03-04 21:13:35', null, null);

INSERT INTO `categories` (`id`, `name`, `created_at`, `updated_at`) VALUES
(NULL, 'Modos de juego', '2020-03-04 21:13:35', null),
(NULL, 'Tutoriales', '2020-03-04 21:13:35', null),
(NULL, 'Recetas', '2020-03-04 21:13:35', null),
(NULL, 'Logros', '2020-03-04 21:13:35', null);

-- INSERT INTO `posts` (`id`, `user_id`, `category_id`, `title`, `content`, `image`, `created_at`, `updated_at`) VALUES
-- (NULL, 1, 1, 'Acer Aspire 500', 'contenido del post de Acer Aspire 500', null, '2020-03-04 21:13:35', null),
-- (NULL, 1, 1, 'Lenovo 8700', 'contenido del post de Lenovo 8700', null, '2020-03-04 21:13:35', null),
-- (NULL, 1, 2, 'Samsung Galaxy 8', 'contenido del post de Samsung Galaxy 8', null, '2020-03-04 21:13:35', null),
-- (NULL, 1, 2, 'Huawei P50', 'contenido del post de Huawei P50', null, '2020-03-04 21:13:35', null);


COMMIT;
