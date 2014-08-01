SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `maps` (
  `Id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `Uid` varchar(27) NOT NULL DEFAULT '',
  `Name` varchar(100) NOT NULL DEFAULT '',
  `Author` varchar(30) NOT NULL DEFAULT '',
  `Environment` varchar(10) NOT NULL DEFAULT '',
  `NbLaps` tinyint(1) unsigned NOT NULL,
  `NbCheckpoints` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Uid` (`Uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;



CREATE TABLE IF NOT EXISTS `players` (
  `Id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `Login` varchar(50) NOT NULL DEFAULT '',
  `Game` varchar(3) NOT NULL DEFAULT '',
  `NickName` varchar(100) NOT NULL DEFAULT '',
  `Continent` tinyint(3) NOT NULL DEFAULT '0',
  `Nation` varchar(3) NOT NULL DEFAULT '',
  `UpdatedAt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Wins` mediumint(9) NOT NULL DEFAULT '0',
  `TimePlayed` int(10) unsigned NOT NULL DEFAULT '0',
  `TeamName` char(60) NOT NULL DEFAULT '',
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Login` (`Login`),
  KEY `Game` (`Game`),
  KEY `Nation` (`Nation`),
  KEY `Wins` (`Wins`),
  KEY `UpdatedAt` (`UpdatedAt`),
  KEY `Continent` (`Continent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;



CREATE TABLE IF NOT EXISTS `players_extra` (
  `PlayerId` mediumint(9) NOT NULL DEFAULT '0',
  `Cps` smallint(3) NOT NULL DEFAULT '-1',
  `DediCps` smallint(3) NOT NULL DEFAULT '-1',
  `Donations` mediumint(9) NOT NULL DEFAULT '0',
  `Style` varchar(20) NOT NULL DEFAULT '',
  `Panels` varchar(255) NOT NULL DEFAULT '',
  `PanelBG` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`PlayerId`),
  KEY `Donations` (`Donations`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
