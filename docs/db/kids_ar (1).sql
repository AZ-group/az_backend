-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 22, 2021 at 09:51 PM
-- Server version: 5.7.33-0ubuntu0.18.04.1
-- PHP Version: 7.4.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kids_ar`
--

-- --------------------------------------------------------

--
-- Table structure for table `kids_ar`
--

CREATE TABLE `kids_ar` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `date_birth` date DEFAULT NULL,
  `date_missing` date DEFAULT NULL,
  `age_photo` tinyint(4) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `ori_data` varchar(120) NOT NULL,
  `ori_id` varchar(240) NOT NULL,
  `found` tinyint(1) NOT NULL DEFAULT '0',
  `extras` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `kids_ar`
--

INSERT INTO `kids_ar` (`id`, `fullname`, `date_birth`, `date_missing`, `age_photo`, `location`, `ori_data`, `ori_id`, `found`, `extras`) VALUES
(165, 'DAISY ROCIO FLORENTIN VELAZQUEZ', NULL, '2021-03-08', 14, 'Moreno, Buenos Aires', 'missingchildren.org.ar', '2890', 0, NULL),
(166, 'MARCOS ROJAS', NULL, '2021-03-08', 17, 'Moreno, Buenos Aires', 'missingchildren.org.ar', '2889', 0, NULL),
(167, 'MARIA BELEN SOTO', NULL, '2021-02-23', 17, 'Mendoza', 'missingchildren.org.ar', '2886', 0, NULL),
(168, 'SANDRA CAMILA LUCERO', NULL, '2021-02-23', 16, 'Mendoza', 'missingchildren.org.ar', '2885', 0, NULL),
(169, 'GIANELLA VILLEGAS', NULL, '2021-02-23', 14, 'Mendoza', 'missingchildren.org.ar', '2884', 0, NULL),
(170, 'ZOE AMBAR MARINO', NULL, '2019-02-01', 5, 'Lavallol, Lomas de Zamora, Buenos Aires', 'missingchildren.org.ar', '2881', 0, NULL),
(171, 'MICAELA BELEN FERNANDEZ', NULL, '2020-11-01', 14, 'Jose C Paz, Buenos Aires', 'missingchildren.org.ar', '2879', 0, NULL),
(172, 'LAUTARO LOBOS', NULL, '2021-02-19', 14, 'Jose C Paz, Buenos Aires', 'missingchildren.org.ar', '2875', 0, NULL),
(173, 'LEANDRO GOMEZ', NULL, '2021-02-17', 10, 'Jose C paz, Buenos Aires', 'missingchildren.org.ar', '2873', 0, NULL),
(174, 'EZEQUIEL RESTIVO', NULL, '2021-01-18', 14, 'Mar del Plata, Buenos Aires', 'missingchildren.org.ar', '2850', 0, NULL),
(175, 'SORAYA LUZ SOSA', NULL, '2021-01-31', 13, 'La Plata, Buenos Aires', 'missingchildren.org.ar', '2848', 0, NULL),
(176, 'LUNA GIMENEZ PEREIRA', NULL, '2020-12-11', NULL, 'Misiones', 'missingchildren.org.ar', '2843', 0, NULL),
(177, 'EMANUEL MAXIMLIANO ESPINOSA', NULL, '2021-01-06', 15, 'San Vicente, Buenos Aires', 'missingchildren.org.ar', '2840', 0, NULL),
(178, 'GISELLA NOEMI GARNICA', NULL, '2021-01-07', 14, 'Temperley, Buenos Aires', 'missingchildren.org.ar', '2839', 0, NULL),
(179, 'JONATHAN EZEQUIEL ACOSTA', NULL, '2020-12-15', 16, 'Avellaneda, Buenos Aires', 'missingchildren.org.ar', '2833', 0, NULL),
(180, 'OLGA MABEL ESPINDOLA', NULL, '2020-12-25', 17, 'Moreno, Buenos Aires', 'missingchildren.org.ar', '2830', 0, NULL),
(181, 'DESIRE YAQUELINE O VALENTIN JOEL SILVA', NULL, '2020-11-11', 16, 'Lomas de Zamora, Buenos Aires', 'missingchildren.org.ar', '2802', 0, NULL),
(182, 'VICTORIA ABRIL ORTIZ', NULL, '2020-05-05', 15, 'San Vicente, Buenos Aires', 'missingchildren.org.ar', '2798', 0, NULL),
(183, 'LUCAS GABRIEL MALDONADO', NULL, '2020-10-23', 14, 'Moreno, Buenos Aires', 'missingchildren.org.ar', '2796', 0, NULL),
(184, 'RAMIRO URIEL AVALOS', NULL, '2020-09-26', 13, 'San Francisco Solano / Alte Brown, Buenos Aires', 'missingchildren.org.ar', '2780', 0, NULL),
(185, 'MELANIE AYLEN INES RIGAUDI', NULL, '2020-08-28', 15, 'Moreno, Buenos Aires', 'missingchildren.org.ar', '2771', 0, NULL),
(186, 'TIARA NAHIR ANDREOLI', NULL, '2020-09-23', 12, 'Moreno, Buenos Aires', 'missingchildren.org.ar', '2755', 0, NULL),
(187, 'CARLOS EZEQUIEL ANDREOLI', NULL, '2020-09-23', 13, 'Moreno, Buenos Aires', 'missingchildren.org.ar', '2754', 0, NULL),
(188, 'BRISA AYELEN MEYER', NULL, '2020-06-25', 15, 'Ciudad Autónoma de Buenos Aires', 'missingchildren.org.ar', '2732', 0, NULL),
(189, 'MAXIMILIANO DANIEL FALCONNAT', NULL, '2020-06-18', 16, 'Mar del Plata, Buenos Aires', 'missingchildren.org.ar', '2729', 0, NULL),
(190, 'ARIEL SERGIO MORINGO', NULL, '2020-02-04', 13, 'Avellaneda, Buenos Aires', 'missingchildren.org.ar', '2688', 0, NULL),
(191, 'MELISA CHAVEZ', NULL, '2020-02-29', 13, 'Lomas de Zamora, Buenos Aires', 'missingchildren.org.ar', '2678', 0, NULL),
(192, 'ABRIL ABIGAIL ARIAS', NULL, '2020-02-29', 13, 'Villa Fiorito, Lomas de Zamora, Buenos Aires', 'missingchildren.org.ar', '2677', 0, NULL),
(193, 'CAMILA MARILIN BARROSO NAVARRO', NULL, '2020-01-20', 17, 'San Rafel, Mendoza', 'missingchildren.org.ar', '2654', 0, NULL),
(194, 'MARTIN IGNACIO MORALES VERACIERTO', NULL, '2018-01-12', NULL, 'Ushuaia, Tierra del Fuego', 'missingchildren.org.ar', '2621', 0, NULL),
(195, 'MATIAS EZEQUIEL MORALES VERACIERTO', NULL, '2018-01-12', 3, 'Ushuaia, Tierra del Fuego', 'missingchildren.org.ar', '2620', 0, NULL),
(196, 'ABRAHAM MIGUEL RODRIGUEZ PERESTRELO', NULL, '2019-12-28', 10, 'Mar del Plata, Buenos Aires', 'missingchildren.org.ar', '2618', 0, NULL),
(197, 'ENOC EZEQUIEL RODRIGUEZ PERESTRELO', NULL, '2019-12-28', 9, 'Mar del Plata, Buenos Aires', 'missingchildren.org.ar', '2617', 0, NULL),
(198, 'ALESSANDRA KATERINA TUCCE', NULL, '2019-02-01', 4, 'Mar Del Plata, Buenos Aires', 'missingchildren.org.ar', '2615', 0, NULL),
(199, 'CAMILA BELEN CARDOZO', NULL, '2019-12-06', 17, 'Longchamps, Buenos Aires', 'missingchildren.org.ar', '2591', 0, NULL),
(200, 'YENNIFER ANN MARLENE BARREIRO', NULL, '2019-07-08', 16, 'Alte Brown, Buenos Aires', 'missingchildren.org.ar', '2524', 0, NULL),
(201, 'TIZIANO SEBASTIAN ECHEVERRIA', NULL, '2019-04-05', 4, 'Florencio Varela, Buenos Aires', 'missingchildren.org.ar', '2523', 0, NULL),
(202, 'IAN FACUNDO OVIEDO', NULL, '2019-06-18', 17, 'La Matanza, Buenos Aires', 'missingchildren.org.ar', '2516', 0, NULL),
(203, 'BENICIO CORONEL Y MILAGRO DE LOS ANGELES AVELLANEDA', NULL, '2016-10-28', NULL, 'Barrio Lola Mora, Tucumán', 'missingchildren.org.ar', '2446', 0, NULL),
(204, 'GIOVANNI BRIAN DE ROSA', NULL, '2019-07-26', 13, 'Moron, Buenos Aires', 'missingchildren.org.ar', '2435', 0, NULL),
(205, 'NAHIARA GERALDINE DE ROSA', NULL, '2019-07-26', 7, 'Moron, Buenos Aires', 'missingchildren.org.ar', '2434', 0, NULL),
(206, 'NAHIARA Y GIOVANI DE ROSA', NULL, '2019-07-26', 13, 'Moron, Buenos Aires', 'missingchildren.org.ar', '2433', 0, NULL),
(207, 'SOL GEMMA SALAZAR SALAS', NULL, '2019-01-23', 9, 'Salta, Capital, Salta', 'missingchildren.org.ar', '2354', 0, NULL),
(208, 'JACQUELINE TAMARA PEDROZA', NULL, '2019-05-08', 17, 'Quilmes, Buenos Aires', 'missingchildren.org.ar', '2327', 0, NULL),
(209, 'LUJAN INES JOSEFINA PEREIRO IBARRA', NULL, '2019-02-27', 15, 'Boulogne, Buenos Aires', 'missingchildren.org.ar', '2274', 0, NULL),
(210, 'NAZARENO AREVALO', NULL, '2019-03-04', 13, 'La Plata, Buenos Aires', 'missingchildren.org.ar', '2265', 0, NULL),
(211, 'MATIAS EZEQUIEL PEREIRA', NULL, '2019-01-21', 13, 'General Rodriguez, Buenos Aires', 'missingchildren.org.ar', '2215', 0, NULL),
(212, 'RODOLFO DALESSANDRO SOLANO VERA', NULL, '2018-11-22', 2, 'Valentin Alsina, Buenos Aires', 'missingchildren.org.ar', '2183', 0, NULL),
(213, 'THIAGO CRISTIANO EZEQUIEL DIAZ', NULL, '2017-05-19', 2, 'Tigre, Buenos Aires', 'missingchildren.org.ar', '2145', 0, NULL),
(214, 'JESUS ADRIAN FERNANDEZ', NULL, '2018-11-13', 13, 'Villa Centenario,Lomas de Zamora, Buenos Aires', 'missingchildren.org.ar', '2131', 0, NULL),
(215, 'DELIA GERONIMO POLIJO', NULL, '2018-09-18', 14, 'La Paz, Córdoba', 'missingchildren.org.ar', '2046', 0, NULL),
(216, 'JULIETA TOMASSELLA', NULL, '2016-06-08', 6, 'Goya, Corrientes', 'missingchildren.org.ar', '2004', 0, NULL),
(217, 'ABRIL AGUSTINA TOMASSELLA', NULL, '2016-06-08', 11, 'Goya, Corrientes', 'missingchildren.org.ar', '2003', 0, NULL),
(218, 'MARIA LISANDRA ALBORNOZ', NULL, '2014-09-14', 12, 'Dto. Rivadavia, Salta', 'missingchildren.org.ar', '1904', 0, NULL),
(219, 'CANDELA AGUSTINA CORREAS DE MELO', NULL, '2018-02-03', 15, 'San Vicente, Misiones', 'missingchildren.org.ar', '1880', 0, NULL),
(220, 'RODRIGO IBARRA', NULL, '2018-02-26', 16, 'Lomas del Tafi, ', 'missingchildren.org.ar', '1875', 0, NULL),
(221, 'LUCAS BOLOTTI', NULL, '2017-07-19', 24, 'Papagayos, San Luis', 'missingchildren.org.ar', '1777', 0, NULL),
(222, 'GALA CANCINOS', NULL, '2017-05-16', 14, 'Salta', 'missingchildren.org.ar', '1752', 0, NULL),
(223, 'MARIANELA GIMENEZ Y DILAN PEREZ', NULL, '2017-01-01', NULL, 'Marcos Paz, Buenos Aires', 'missingchildren.org.ar', '1695', 0, NULL),
(224, 'ALEXANDER NOZIGLIA', NULL, '2015-04-16', 9, 'Ciudad Autónoma de Buenos Aires', 'missingchildren.org.ar', '1563', 0, NULL),
(225, 'MAXIMILIANO SOSA', NULL, '2015-12-21', 5, 'Ceres, Santa Fe', 'missingchildren.org.ar', '1527', 0, NULL),
(226, 'JONE GIULIANA ABRIL GALIANO', NULL, '2012-01-01', 5, 'Santa Fe', 'missingchildren.org.ar', '1488', 0, NULL),
(227, 'BRISA AYELEN PEREYRA', NULL, '2015-06-21', 5, 'Ciudad Autónoma de Buenos Aires', 'missingchildren.org.ar', '1467', 0, NULL),
(228, 'ANGEL MARTIN MORE', NULL, '2015-08-23', 13, 'Glew, Buenos Aires', 'missingchildren.org.ar', '1450', 0, NULL),
(229, 'LUZ MORENA OLIVA', NULL, '2014-02-01', 2, 'Traslasierra, Córdoba', 'missingchildren.org.ar', '1282', 0, NULL),
(230, 'MATIAS DAMIAN HUANCO', NULL, '2011-03-02', 5, 'Jujuy', 'missingchildren.org.ar', '638', 0, NULL),
(231, 'AGUSTIN BATAGLIA', NULL, '2010-12-24', 8, 'Formosa', 'missingchildren.org.ar', '490', 0, NULL),
(232, 'CANDELA SOFIA MUNOZ', NULL, '2010-04-01', 2, 'El Palomar, Buenos Aires', 'missingchildren.org.ar', '436', 0, NULL),
(233, 'MILAGROS DE LOS ANGELES NUNEZ', NULL, '2009-12-17', 2, 'Escobar, Buenos Aires', 'missingchildren.org.ar', '395', 0, NULL),
(234, 'MILAGROS DE LOS ANGELES CORDERO', NULL, '2006-12-02', 5, 'Grand Bourg, Buenos Aires', 'missingchildren.org.ar', '159', 0, NULL),
(235, 'SOFIA YASMIN HERRERA', NULL, '2008-09-28', 13, 'Rio Grande, Tierra del Fuego', 'missingchildren.org.ar', '151', 0, NULL),
(236, 'CELESTE MARISA CALVO', NULL, '2001-10-23', 7, 'Lanus, Buenos Aires', 'missingchildren.org.ar', '106', 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kids_ar`
--
ALTER TABLE `kids_ar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ori_data` (`ori_data`,`ori_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kids_ar`
--
ALTER TABLE `kids_ar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=238;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
