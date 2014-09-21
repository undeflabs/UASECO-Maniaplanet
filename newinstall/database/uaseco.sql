SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `maps` (
  `Id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `Uid` varchar(27) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `Filename` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `Name` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `Comment` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `Author` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `AuthorNickname` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `AuthorZone` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `AuthorContinent` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `AuthorNation` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `AuthorScore` int(4) UNSIGNED NOT NULL,
  `AuthorTime` int(4) UNSIGNED NOT NULL,
  `GoldTime` int(4) UNSIGNED NOT NULL,
  `SilverTime` int(4) UNSIGNED NOT NULL,
  `BronzeTime` int(4) UNSIGNED NOT NULL,
  `Environment` varchar(10) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `Mood` enum('unknown', 'Sunrise', 'Day', 'Sunset', 'Night') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `Cost` mediumint(3) unsigned NOT NULL,
  `Type` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `Style` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `MultiLap` enum('false', 'true') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `NbLaps` tinyint(1) UNSIGNED NOT NULL,
  `NbCheckpoints` tinyint(1) UNSIGNED NOT NULL,
  `Validated` enum('null','false','true') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ExeVersion` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `ExeBuild` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `ModName` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `ModFile` varchar(256) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `ModUrl` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `SongFile` varchar(256) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `SongUrl` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Uid` (`Uid`),
  Key `Author` (`Author`),
  Key `AuthorScore` (`AuthorScore`),
  Key `AuthorTime` (`AuthorTime`),
  Key `GoldTime` (`GoldTime`),
  Key `SilverTime` (`SilverTime`),
  Key `BronzeTime` (`BronzeTime`),
  Key `Environment` (`Environment`),
  Key `Mood` (`Mood`),
  Key `MultiLap` (`MultiLap`),
  Key `NbLaps` (`NbLaps`),
  Key `NbCheckpoints` (`NbCheckpoints`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE 'utf8_bin' AUTO_INCREMENT=1;



CREATE TABLE IF NOT EXISTS `players` (
  `Id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `Login` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `Game` varchar(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `NickName` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `Continent` tinyint(3) NOT NULL DEFAULT '0',
  `Nation` varchar(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `UpdatedAt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Wins` mediumint(9) NOT NULL DEFAULT '0',
  `Visits` mediumint(9) UNSIGNED NOT NULL DEFAULT '0',
  `TimePlayed` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `TeamName` char(60) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Login` (`Login`),
  KEY `Game` (`Game`),
  KEY `Continent` (`Continent`),
  KEY `Nation` (`Nation`),
  KEY `UpdatedAt` (`UpdatedAt`),
  KEY `Wins` (`Wins`),
  KEY `Visits` (`Visits`),
  KEY `TimePlayed` (`TimePlayed`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE 'utf8_bin' AUTO_INCREMENT=1;



CREATE TABLE IF NOT EXISTS `players_extra` (
  `PlayerId` mediumint(9) NOT NULL DEFAULT '0',
  `Cps` smallint(3) NOT NULL DEFAULT '-1',
  `DediCps` smallint(3) NOT NULL DEFAULT '-1',
  `Donations` mediumint(9) NOT NULL DEFAULT '0',
  `Style` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `Panels` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `PanelBG` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`PlayerId`),
  KEY `Donations` (`Donations`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE 'utf8_bin';



CREATE TABLE IF NOT EXISTS `records` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `MapId` mediumint(9) NOT NULL DEFAULT '0',
  `PlayerId` mediumint(9) NOT NULL DEFAULT '0',
  `Score` int(11) NOT NULL DEFAULT '0',
  `Date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Checkpoints` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `PlayerId` (`PlayerId`,`MapId`),
  KEY `MapId` (`MapId`),
  KEY `Score` (`Score`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE 'utf8_bin' AUTO_INCREMENT=1;



CREATE TABLE IF NOT EXISTS `rs_karma` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `MapId` mediumint(9) NOT NULL DEFAULT '0',
  `PlayerId` mediumint(9) NOT NULL DEFAULT '0',
  `Score` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Id`),
  UNIQUE KEY `PlayerMapId` (`PlayerId`,`MapId`),
  KEY `MapId` (`MapId`),
  KEY `Score` (`Score`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE 'utf8_bin' AUTO_INCREMENT=1;



CREATE TABLE IF NOT EXISTS `rs_rank` (
  `PlayerId` mediumint(9) NOT NULL DEFAULT '0',
  `Avg` float NOT NULL DEFAULT '0',
  KEY `PlayerId` (`PlayerId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE 'utf8_bin';



CREATE TABLE IF NOT EXISTS `rs_times` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `MapId` mediumint(9) NOT NULL DEFAULT '0',
  `PlayerId` mediumint(9) NOT NULL DEFAULT '0',
  `Score` int(11) NOT NULL DEFAULT '0',
  `Date` int(10) unsigned NOT NULL default 0,
  `Checkpoints` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `PlayerMapId` (`PlayerId`,`MapId`),
  KEY `MapId` (`MapId`),
  KEY `Score` (`Score`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE 'utf8_bin' AUTO_INCREMENT=1;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
