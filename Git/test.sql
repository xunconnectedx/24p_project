-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Фев 28 2017 г., 13:21
-- Версия сервера: 5.7.15-0ubuntu0.16.04.1
-- Версия PHP: 7.0.8-0ubuntu0.16.04.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `test`
--

-- --------------------------------------------------------

--
-- Структура таблицы `Objects`
--

CREATE TABLE `Objects` (
  `id` int(11) NOT NULL,
  `url` text NOT NULL,
  `hash` text NOT NULL,
  `sites_id` int(11) NOT NULL,
  `datech` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Дамп данных таблицы `Objects`
--

INSERT INTO `Objects` (`id`, `url`, `hash`, `sites_id`, `datech`) VALUES
(1, 'http://kristall-kino.ru/images/favicon.ico', '6a036aab1d4c08f3d8b3a1adb6c989419edaa7f7', 1, '2017-02-26'),
(2, 'http://kristall-kino.ru/style/bootstrap.css', '44b2a695f91084696d708cce1d0693d8666f68b7', 1, '2017-02-26'),
(3, 'http://kristall-kino.ru/assets/dfec42b8/css/jquery.fancybox-1.3.4.css', 'aa8db15f19c52b41e89f2e10578fe02688501c2c', 1, '2017-02-26'),
(4, 'http://kristall-kino.ru/style/style.css', 'b6ce5ee93fb10ace23dfa6b157c134ec6adcc7bb', 1, '2017-02-26'),
(5, 'http://kristall-kino.ru/assets/afa87e2f/jquery.min.js', '06e872300088b9ba8a08427d28ed0efcdf9c6ff5', 1, '2017-02-26'),
(6, 'http://kristall-kino.ru/assets/afa87e2f/jui/js/jquery-ui.min.js', 'a4082cd3950848f2b1b6125a509a8b028f4dcf31', 1, '2017-02-26'),
(7, 'http://kristall-kino.ru/js/modernizr.js', '86051ff3f018c1a475162597dab27079eef2ec7a', 1, '2017-02-26'),
(8, 'http://kristall-kino.ru/style/images/logo_new2.jpg', '043912092ba1d6a4209587c2a24015b4f01b0242', 1, '2017-02-26'),
(9, 'http://kristall-kino.ru/flash/baner.swf', '4ad3b90cc4e4f3dfdb03dc425f6a64a2c866738c', 1, '2017-02-26'),
(10, 'http://kristall-kino.ru/files/images/f87918057921acfb88ae3b785ca9e5b7.jpg', '14377b9c5d1502412938e8ca55ef49a2821cb4d7', 1, '2017-02-26'),
(11, 'http://kristall-kino.ru/files/images/b466f10d8358aebefe2603a159a7e4ef.jpg', 'ac2f1e6338f4078357a126f080ee16ad9f9bca6c', 1, '2017-02-26'),
(12, 'http://kristall-kino.ru/files/images/b29a3ef4eabc1bda2a9a915415777219.jpg', 'e1cfbf05f343254a1a8764c1ac5ecccfe15e30d2', 1, '2017-02-26');

-- --------------------------------------------------------

--
-- Структура таблицы `sites`
--

CREATE TABLE `sites` (
  `id` int(11) NOT NULL,
  `url` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Дамп данных таблицы `sites`
--

INSERT INTO `sites` (`id`, `url`) VALUES
(1, 'http://kristall-kino.ru');

-- --------------------------------------------------------

--
-- Структура таблицы `Urls`
--

CREATE TABLE `Urls` (
  `id` int(11) NOT NULL,
  `url` text NOT NULL,
  `hash` text NOT NULL,
  `parsed` tinyint(1) NOT NULL DEFAULT '0',
  `ping` varchar(20) NOT NULL,
  `sites_id` int(11) NOT NULL,
  `datech` date DEFAULT NULL,
  `DownlTime` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Дамп данных таблицы `Urls`
--

INSERT INTO `Urls` (`id`, `url`, `hash`, `parsed`, `ping`, `sites_id`, `datech`, `DownlTime`) VALUES
(1, 'http://kristall-kino.ru', '661fd77627b6e060e8fb26bcf1821af8acc28606', 0, '1671.886', 1, '2017-02-26', 6.18676),
(2, 'http://kristall-kino.ru/', '661fd77627b6e060e8fb26bcf1821af8acc28606', 0, '967.9169', 1, '2017-02-26', 3.89651),
(3, 'http://kristall-kino.ru/cinema/soon', 'ea01dc81ed07d07f6b68099588e1e5d122e9300c', 0, '863.7509', 1, '2017-02-26', 3.25833),
(4, 'http://kristall-kino.ru/cinemaTicket/showtimes', '27cefc47c99c9ef402e283b7894980509c40d6e2', 0, '4164.573', 1, '2017-02-26', 6.96825),
(5, 'http://kristall-kino.ru/pages/zaly', '1ab34786a454c3179cf6dcf6978c7c1a9ea26f68', 0, '1437.844', 1, '2017-02-26', 4.11028),
(6, 'http://kristall-kino.ru/pages/aktsii', '64b3180f1ab74c8aea36bcefb27b64af581300a4', 0, '898.0188', 1, '2017-02-26', 2.65305),
(7, 'http://kristall-kino.ru/pages/kontakty', 'd01ea207f2f9f9252c2665eab485d119e2cf0f42', 0, '1107.638', 1, '2017-02-26', 2.88644),
(8, 'http://kristall-kino.ru/user/login', 'cdddefa64e6d3d37ceeb97e2479831ef82111e8c', 0, '1041.682', 1, '2017-02-26', 3.61416),
(9, 'http://kristall-kino.ru/cinema/film/859', '514134e95c4d8855d35e50f31bda8e0587482c96', 0, '862.1780', 1, '2017-02-26', 2.77484),
(10, 'http://kristall-kino.ru/cinemaTicket/hall/52769', '03f968507720a769d8753d15f35d7d9668907f5f', 0, '1558.485', 1, '2017-02-26', 3.53171),
(11, 'http://kristall-kino.ru/cinema/film/863', '874f69c0f3eb019c061bed503d9e7938486a782f', 0, '1127.851', 1, '2017-02-26', 3.08492),
(12, 'http://kristall-kino.ru/cinema/film/866', '83673937b52b068d3f0808048a92ad794ee59379', 0, '909.7509', 1, '2017-02-26', 2.84267);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `Objects`
--
ALTER TABLE `Objects`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `sites`
--
ALTER TABLE `sites`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `Urls`
--
ALTER TABLE `Urls`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `Objects`
--
ALTER TABLE `Objects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT для таблицы `sites`
--
ALTER TABLE `sites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT для таблицы `Urls`
--
ALTER TABLE `Urls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
