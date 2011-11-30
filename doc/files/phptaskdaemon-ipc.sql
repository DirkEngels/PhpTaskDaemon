-- phpMyAdmin SQL Dump
-- version 3.4.5deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 26, 2011 at 02:36 AM
-- Server version: 5.1.58
-- PHP Version: 5.3.6-13ubuntu3.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `phptaskdaemon`
--

-- --------------------------------------------------------

--
-- Table structure for table `ipc`
--

CREATE TABLE IF NOT EXISTS `ipc` (
  `ipcCreated` timestamp NOT NULL,
  `ipcUpdated` datetime NOT NULL,
  `ipcId` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `ipcId` (`ipcId`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TRIGGER ipc_insert BEFORE INSERT ON `ipc` FOR EACH ROW SET
NEW.ipcCreated = IFNULL(NEW.ipcCreated, NOW()),
NEW.ipcUpdated = IFNULL(NEW.ipcUpdated, '0000-00-00 00:00:00');

CREATE TRIGGER blog_entries_update BEFORE UPDATE ON `blog_entries` FOR EACH ROW SET
NEW.updated = IF(NEW.updated = OLD.updated OR NEW.updated IS NULL, NOW(), NEW.updated),
NEW.published = IFNULL(NEW.published, OLD.published);


CREATE TRIGGER ipc_update BEFORE UPDATE ON `ipc` FOR EACH ROW SET
NEW.ipcUpdated = CASE
                  WHEN NEW.ipcUpdated IS NULL THEN OLD.ipcUpdated
                  WHEN NEW.ipcUpdated = OLD.ipcUpdated THEN NOW()
                  ELSE NEW.ipcUpdated
              END,
NEW.ipcCreated = IFNULL(NEW.ipcCreated, OLD.ipcCreated);


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
