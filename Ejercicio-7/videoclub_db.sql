-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-12-2021 a las 18:03:08
-- Versión del servidor: 10.4.21-MariaDB
-- Versión de PHP: 8.0.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `videoclub_db`
--
CREATE DATABASE IF NOT EXISTS `videoclub_db` DEFAULT CHARACTER SET utf8 COLLATE utf8_spanish_ci;
USE `videoclub_db`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alquileres`
--

DROP TABLE IF EXISTS `alquileres`;
CREATE TABLE IF NOT EXISTS `alquileres` (
  `cliente_dni` varchar(9) COLLATE utf8_spanish_ci NOT NULL,
  `pelicula_referencia` varchar(9) COLLATE utf8_spanish_ci NOT NULL,
  `dia_alquilada` datetime NOT NULL,
  `dia_devuelta` datetime DEFAULT NULL,
  UNIQUE KEY `cliente_dni` (`cliente_dni`,`pelicula_referencia`),
  KEY `pelicula_referencia` (`pelicula_referencia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `alquileres`
--

INSERT INTO `alquileres` (`cliente_dni`, `pelicula_referencia`, `dia_alquilada`, `dia_devuelta`) VALUES
('00000000A', '000X', '2021-12-19 00:48:00', '2021-12-19 01:14:35'),
('00000000A', '001X', '2021-12-18 23:53:46', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` varchar(9) COLLATE utf8_spanish_ci NOT NULL,
  `tipo` varchar(32) COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `tipo`) VALUES
('AV', 'Aventuras'),
('AC', 'Acción'),
('BI', 'Biográfica'),
('CI', 'Ciencia Ficción'),
('CO', 'Comedia'),
('DR', 'Drama'),
('PO', 'Policíaca'),
('WE', 'Western');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

DROP TABLE IF EXISTS `clientes`;
CREATE TABLE IF NOT EXISTS `clientes` (
  `dni` varchar(9) COLLATE utf8_spanish_ci NOT NULL,
  `nombre` varchar(32) COLLATE utf8_spanish_ci NOT NULL,
  `apellidos` varchar(64) COLLATE utf8_spanish_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `telefono` varchar(13) COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`dni`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`dni`, `nombre`, `apellidos`, `email`, `telefono`) VALUES
('00000000A', 'Ángel', 'Iglesias Préstamo', 'uo270534@uniovi.es', '985000111');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `peliculas`
--

DROP TABLE IF EXISTS `peliculas`;
CREATE TABLE IF NOT EXISTS `peliculas` (
  `referencia` varchar(9) COLLATE utf8_spanish_ci NOT NULL,
  `titulo` varchar(32) COLLATE utf8_spanish_ci NOT NULL,
  `categoria_id` varchar(64) COLLATE utf8_spanish_ci NOT NULL,
  `director` varchar(64) COLLATE utf8_spanish_ci NOT NULL,
  `actor_principal` varchar(64) COLLATE utf8_spanish_ci NOT NULL,
  `portada` varchar(64) COLLATE utf8_spanish_ci DEFAULT NULL,
  `ha_ganado_oscar` int(11) NOT NULL,
  PRIMARY KEY (`referencia`),
  KEY `categoria_id` (`categoria_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `peliculas`
--

INSERT INTO `peliculas` (`referencia`, `titulo`, `categoria_id`, `director`, `actor_principal`, `portada`, `ha_ganado_oscar`) VALUES
('000X', 'El Bueno, el feo y el malo', 'WE', 'Sergio Leone', 'Clint Eastwood', 'media/el_bueno_el_feo_y_el_malo.jpg', 0),
('001X', 'Gran Torino', 'DR', 'Clint Eastwood', 'Clint Eastwood', 'media/gran_torino.jpg', 0),
('002X', 'Million Dollar Baby', 'DR', 'Clint Eastwood', 'Clint Eastwood', 'media/million_dollar_baby.jpg', 1),
('003X', 'Harry el Sucio', 'PO', 'Don Siegel', 'Clint Eastwood', 'media/harry_el_sucio.jpg', 0),
('004X', 'Sin Perdón', 'WE', 'Clint Eastwood', 'Clint Eastwood', 'media/sin_perdon.jpg', 1),
('005X', 'La fuga de alcatraz', 'AC', 'Don Siegel', 'Clint Eastwood', 'media/la_fuga_de_alcatraz.jpg', 0),
('006X', 'American Sniper', 'BI', 'Clint Eastwood', 'Bradley Cooper', 'media/american_sniper.jpg', 1),
('007X', 'Los juegos del hambre', 'CI', 'Gary Ross', 'Jennifer Lawrence', 'media/los_juegos_del_hambre.jpg', 1),
('008X', 'Timeline', 'CI', 'Richard Donner', 'Paul Walker', 'media/timeline.jpg', 0),
('009X', 'Django desencadenado', 'WE', 'Quentin Tarantino', 'Leonardo diCaprio', 'media/django.jpg', 1),
('010X', 'La momia', 'AV', 'Stephen Sommers', 'Brendan Fraser', 'media/la_momia.jpg', 0),
('011X', 'Los visitantes', 'CO', 'Jean-Marie Poiré', 'Jean Reno', 'media/los_visitantes.jpg', 0);

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alquileres`
--
ALTER TABLE `alquileres`
  ADD CONSTRAINT `alquileres_ibfk_1` FOREIGN KEY (`cliente_dni`) REFERENCES `clientes` (`dni`),
  ADD CONSTRAINT `alquileres_ibfk_2` FOREIGN KEY (`pelicula_referencia`) REFERENCES `peliculas` (`referencia`);

--
-- Filtros para la tabla `peliculas`
--
ALTER TABLE `peliculas`
  ADD CONSTRAINT `peliculas_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;