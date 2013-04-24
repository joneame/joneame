-- MySQL dump 10.13  Distrib 5.5.30, for Linux (x86_64)
--
-- Host: localhost    Database: joneame
-- ------------------------------------------------------
-- Server version	5.5.30-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `annotations`
--

DROP TABLE IF EXISTS `annotations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `annotations` (
  `annotation_key` char(40) NOT NULL,
  `annotation_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `annotation_text` text,
  PRIMARY KEY (`annotation_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `answers`
--

DROP TABLE IF EXISTS `answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `answers` (
  `answer_post_id` int(11) NOT NULL,
  `answer_from` int(11) NOT NULL,
  PRIMARY KEY (`answer_post_id`,`answer_from`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auths`
--

DROP TABLE IF EXISTS `auths`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auths` (
  `user_id` int(10) unsigned NOT NULL,
  `service` char(32) NOT NULL,
  `uid` bigint(10) unsigned NOT NULL,
  `username` char(32) NOT NULL DEFAULT '''''',
  `token` char(64) NOT NULL DEFAULT '''''',
  `secret` char(64) NOT NULL DEFAULT '''''',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `service` (`service`,`uid`),
  KEY `user_id` (`user_id`),
  KEY `service_2` (`service`,`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `avatars`
--

DROP TABLE IF EXISTS `avatars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `avatars` (
  `avatar_id` int(11) NOT NULL DEFAULT '0',
  `avatar_image` blob NOT NULL,
  `avatar_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`avatar_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bans`
--

DROP TABLE IF EXISTS `bans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bans` (
  `ban_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ban_type` enum('email','punished_hostname','hostname','ip','words','proxy') NOT NULL DEFAULT 'email',
  `ban_text` char(64) NOT NULL DEFAULT '',
  `ban_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ban_expire` timestamp NULL DEFAULT NULL,
  `ban_comment` char(100) DEFAULT NULL,
  PRIMARY KEY (`ban_id`),
  UNIQUE KEY `ban_type` (`ban_type`,`ban_text`)
) ENGINE=MyISAM AUTO_INCREMENT=566 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blogs`
--

DROP TABLE IF EXISTS `blogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blogs` (
  `blog_id` int(20) NOT NULL AUTO_INCREMENT,
  `blog_key` varchar(35) COLLATE utf8_spanish_ci DEFAULT NULL,
  `blog_type` enum('normal','blog') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'normal',
  `blog_rss` varchar(64) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `blog_rss2` varchar(64) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `blog_atom` varchar(64) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `blog_url` varchar(64) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`blog_id`),
  UNIQUE KEY `key` (`blog_key`)
) ENGINE=MyISAM AUTO_INCREMENT=11326 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `busquedas_guardadas`
--

DROP TABLE IF EXISTS `busquedas_guardadas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `busquedas_guardadas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `texto` text NOT NULL,
  `usuario` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` char(32) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `category_lang` text COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=208 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chats`
--

DROP TABLE IF EXISTS `chats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chats` (
  `chat_time` int(10) unsigned NOT NULL DEFAULT '0',
  `chat_uid` int(10) unsigned NOT NULL DEFAULT '0',
  `chat_room` enum('all','friends','admin','devel') NOT NULL DEFAULT 'all',
  `chat_user` char(32) NOT NULL,
  `chat_text` char(255) NOT NULL,
  KEY `chat_time` (`chat_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 MAX_ROWS=2000;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chats_logs`
--

DROP TABLE IF EXISTS `chats_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chats_logs` (
  `chat_time` int(10) unsigned NOT NULL DEFAULT '0',
  `chat_uid` int(10) unsigned NOT NULL DEFAULT '0',
  `chat_room` enum('all','friends','admin','devel') NOT NULL DEFAULT 'all',
  `chat_user` char(32) NOT NULL,
  `chat_text` char(255) NOT NULL,
  KEY `chat_time` (`chat_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clones`
--

DROP TABLE IF EXISTS `clones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clones` (
  `clon_from` int(10) unsigned NOT NULL,
  `clon_to` int(10) unsigned NOT NULL,
  `clon_ip` char(24) NOT NULL,
  `clon_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`clon_from`,`clon_to`,`clon_ip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `comment_id` int(20) NOT NULL AUTO_INCREMENT,
  `comment_type` text COLLATE utf8_spanish_ci NOT NULL,
  `comment_randkey` int(11) NOT NULL DEFAULT '0',
  `comment_parent` int(20) DEFAULT '0',
  `comment_link_id` int(20) NOT NULL DEFAULT '0',
  `comment_user_id` int(20) NOT NULL DEFAULT '0',
  `comment_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comment_ip` varchar(24) COLLATE utf8_spanish_ci DEFAULT NULL,
  `comment_order` smallint(6) NOT NULL DEFAULT '0',
  `comment_votes` smallint(4) NOT NULL DEFAULT '0',
  `comment_karma` smallint(6) NOT NULL DEFAULT '0',
  `comment_content` text COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `comment_link_id_2` (`comment_link_id`,`comment_date`),
  KEY `comment_date` (`comment_date`),
  KEY `comment_user_id` (`comment_user_id`,`comment_date`),
  KEY `comment_link_id` (`comment_link_id`,`comment_order`)
) ENGINE=MyISAM AUTO_INCREMENT=103740 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `conversations`
--

DROP TABLE IF EXISTS `conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conversations` (
  `conversation_user_to` int(10) unsigned NOT NULL,
  `conversation_type` enum('comment','post','link') NOT NULL,
  `conversation_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `conversation_from` int(10) unsigned NOT NULL,
  `conversation_to` int(10) unsigned NOT NULL,
  PRIMARY KEY (`conversation_user_to`,`conversation_type`,`conversation_time`),
  KEY `conversation_type` (`conversation_type`,`conversation_from`),
  KEY `conversation_time` (`conversation_time`),
  KEY `conversation_type_2` (`conversation_type`,`conversation_to`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cortos`
--

DROP TABLE IF EXISTS `cortos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cortos` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `texto` text,
  `por` varchar(100) NOT NULL,
  `activado` int(2) NOT NULL,
  `votos` tinyint(4) NOT NULL,
  `carisma` tinyint(4) NOT NULL,
  `ediciones` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6120 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `devel_avisos`
--

DROP TABLE IF EXISTS `devel_avisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `devel_avisos` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `titulua` text,
  `testua` text,
  `nork` varchar(100) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `devel_changelog`
--

DROP TABLE IF EXISTS `devel_changelog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `devel_changelog` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `titulua` text,
  `testua` text,
  `data` varchar(100) DEFAULT '',
  `nork` varchar(100) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=88 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `devel_notas`
--

DROP TABLE IF EXISTS `devel_notas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `devel_notas` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `titulua` text,
  `testua` text,
  `data` varchar(100) DEFAULT '',
  `nork` varchar(100) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `devel_todo`
--

DROP TABLE IF EXISTS `devel_todo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `devel_todo` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `titulua` text,
  `testua` text,
  `nork` varchar(100) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=42 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dictionary`
--

DROP TABLE IF EXISTS `dictionary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dictionary` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `palabra` text,
  `definicion` text,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `por` varchar(100) NOT NULL,
  `diccionario` int(11) NOT NULL DEFAULT '1',
  `activado` int(2) NOT NULL,
  `votos` tinyint(4) NOT NULL,
  `carisma` tinyint(4) NOT NULL,
  `ip` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `duplicates`
--

DROP TABLE IF EXISTS `duplicates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `duplicates` (
  `link_id` int(11) NOT NULL,
  `duplicate` varchar(250) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
  PRIMARY KEY (`link_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `edicion_corto`
--

DROP TABLE IF EXISTS `edicion_corto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `edicion_corto` (
  `autoid` int(11) NOT NULL AUTO_INCREMENT,
  `id_corto` int(11) NOT NULL,
  `texto_copia` text NOT NULL,
  `texto_propuesta` text NOT NULL,
  `autor` int(11) NOT NULL,
  PRIMARY KEY (`autoid`)
) ENGINE=MyISAM AUTO_INCREMENT=164 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `encuestas`
--

DROP TABLE IF EXISTS `encuestas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `encuestas` (
  `encuesta_id` int(11) NOT NULL AUTO_INCREMENT,
  `encuesta_start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `encuesta_finish` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `encuesta_user_id` int(11) NOT NULL DEFAULT '0',
  `encuesta_ip` varchar(24) DEFAULT NULL,
  `encuesta_title` text NOT NULL,
  `encuesta_content` text NOT NULL,
  `encuesta_total_votes` int(11) NOT NULL DEFAULT '0',
  `encuesta_multiple` tinyint(1) NOT NULL DEFAULT '0',
  `comentarios` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`encuesta_id`),
  KEY `encuesta_user_id` (`encuesta_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=968 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `encuestas_opts`
--

DROP TABLE IF EXISTS `encuestas_opts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `encuestas_opts` (
  `id` int(127) NOT NULL AUTO_INCREMENT,
  `encid` int(255) NOT NULL,
  `info` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4852 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `encuestas_votes`
--

DROP TABLE IF EXISTS `encuestas_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `encuestas_votes` (
  `id` int(127) NOT NULL AUTO_INCREMENT,
  `uid` int(255) NOT NULL,
  `optid` int(255) NOT NULL,
  `pollid` int(200) NOT NULL,
  `date` varchar(100) NOT NULL,
  `ip` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=29910 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `favorites` (
  `favorite_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `favorite_type` enum('link','post','comment') NOT NULL DEFAULT 'link',
  `favorite_link_id` int(10) unsigned NOT NULL DEFAULT '0',
  `favorite_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `favorite_user_id` (`favorite_user_id`,`favorite_link_id`),
  KEY `favorite_link_id` (`favorite_link_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fisban`
--

DROP TABLE IF EXISTS `fisban`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fisban` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `log_name` enum('cotiban_error','cotiban','cotiunban_error','cotiunban') NOT NULL DEFAULT 'cotiban',
  `razon` text,
  `por` varchar(100) NOT NULL,
  `uid` int(255) NOT NULL,
  `vigente` tinyint(4) NOT NULL DEFAULT '1',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` char(24) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7054 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `friends`
--

DROP TABLE IF EXISTS `friends`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `friends` (
  `friend_type` enum('affiliate','manual','hide','affinity') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'affiliate',
  `friend_from` int(10) NOT NULL DEFAULT '0',
  `friend_to` int(10) NOT NULL DEFAULT '0',
  `friend_value` smallint(3) NOT NULL DEFAULT '0',
  UNIQUE KEY `friend_type` (`friend_type`,`friend_from`,`friend_to`),
  KEY `friend_type_2` (`friend_type`,`friend_to`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gconfig`
--

DROP TABLE IF EXISTS `gconfig`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gconfig` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `value1` int(255) NOT NULL,
  `value2` varchar(200) NOT NULL,
  `status` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `geo_links`
--

DROP TABLE IF EXISTS `geo_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `geo_links` (
  `geo_id` int(11) NOT NULL,
  `geo_text` char(80) DEFAULT NULL,
  `geo_pt` point NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `geo_users`
--

DROP TABLE IF EXISTS `geo_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `geo_users` (
  `geo_id` int(11) NOT NULL DEFAULT '0',
  `geo_text` varchar(80) DEFAULT NULL,
  `geo_pt` point NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `historial`
--

DROP TABLE IF EXISTS `historial`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `historial` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `texto` text,
  `por` int(200) NOT NULL,
  `uid` int(200) NOT NULL,
  `fecha` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=52 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jonevision`
--

DROP TABLE IF EXISTS `jonevision`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jonevision` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` text NOT NULL,
  `link` text NOT NULL,
  `votos` int(11) NOT NULL,
  `puntos` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jonevision_votes`
--

DROP TABLE IF EXISTS `jonevision_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jonevision_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jonevision_id` tinyint(4) NOT NULL,
  `user_id` int(11) NOT NULL,
  `valor` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=353 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `languages` (
  `language_id` int(11) NOT NULL AUTO_INCREMENT,
  `language_name` varchar(64) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`language_id`),
  UNIQUE KEY `language_name` (`language_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `link_clicks`
--

DROP TABLE IF EXISTS `link_clicks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `link_clicks` (
  `id` int(11) NOT NULL,
  `counter` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `link_visits`
--

DROP TABLE IF EXISTS `link_visits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `link_visits` (
  `id` int(11) NOT NULL,
  `counter` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `links`
--

DROP TABLE IF EXISTS `links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `links` (
  `link_id` int(20) NOT NULL AUTO_INCREMENT,
  `link_author` int(20) NOT NULL DEFAULT '0',
  `link_blog` int(20) DEFAULT '0',
  `link_status` enum('discard','queued','published','abuse','duplicated','autodiscard','metapublished') CHARACTER SET utf8 NOT NULL DEFAULT 'discard',
  `link_randkey` int(20) NOT NULL DEFAULT '0',
  `link_votes` int(20) NOT NULL DEFAULT '0',
  `link_negatives` int(11) NOT NULL DEFAULT '0',
  `link_anonymous` int(10) unsigned NOT NULL DEFAULT '0',
  `link_votes_avg` float NOT NULL DEFAULT '0',
  `link_aleatorios_positivos` int(2) NOT NULL DEFAULT '0',
  `link_aleatorios_negativos` int(2) NOT NULL DEFAULT '0',
  `link_comments` int(11) unsigned NOT NULL DEFAULT '0',
  `link_karma` decimal(10,2) NOT NULL DEFAULT '0.00',
  `link_geo_id` int(11) NOT NULL,
  `link_lat` float NOT NULL,
  `link_lng` float NOT NULL,
  `link_text` text COLLATE utf8_spanish_ci NOT NULL,
  `link_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `link_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `link_sent_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `link_sent` tinyint(1) NOT NULL,
  `link_category` int(11) NOT NULL DEFAULT '0',
  `link_lang` varchar(2) CHARACTER SET utf8 NOT NULL DEFAULT 'es',
  `link_ip` varchar(24) COLLATE utf8_spanish_ci DEFAULT NULL,
  `link_content_type` varchar(12) COLLATE utf8_spanish_ci DEFAULT NULL,
  `link_uri` varchar(100) COLLATE utf8_spanish_ci DEFAULT NULL,
  `link_url` varchar(250) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `link_url_title` text COLLATE utf8_spanish_ci,
  `link_title` text COLLATE utf8_spanish_ci NOT NULL,
  `link_content` text COLLATE utf8_spanish_ci NOT NULL,
  `link_tags` text COLLATE utf8_spanish_ci,
  `link_thumb_status` enum('unknown','checked','error','local','remote','deleted') COLLATE utf8_spanish_ci NOT NULL,
  `link_thumb_x` tinyint(3) unsigned NOT NULL,
  `link_thumb_y` tinyint(3) unsigned NOT NULL,
  `link_thumb` tinytext COLLATE utf8_spanish_ci NOT NULL,
  `link_comentarios_permitidos` tinyint(4) NOT NULL DEFAULT '1',
  `link_votos_permitidos` tinyint(4) NOT NULL DEFAULT '1',
  `link_broken_link` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`link_id`),
  KEY `link_url` (`link_url`),
  KEY `link_uri` (`link_uri`),
  KEY `link_blog` (`link_blog`),
  KEY `link_status` (`link_status`,`link_sent`),
  KEY `link_status_2` (`link_status`,`link_date`),
  KEY `link_author` (`link_author`,`link_date`)
) ENGINE=MyISAM AUTO_INCREMENT=53528 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_type` enum('link_new','comment_new','link_publish','link_discard','comment_edit','link_edit','post_new','post_edit','login_failed','spam_warn','link_geo_edit','user_new','user_delete','link_depublished','encuesta_new','bsc_new','opinion_new') NOT NULL DEFAULT 'link_new',
  `log_ref_id` int(11) unsigned NOT NULL DEFAULT '0',
  `log_user_id` int(11) NOT NULL DEFAULT '0',
  `log_ip` char(24) CHARACTER SET utf8 COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `log_date` (`log_date`),
  KEY `log_type` (`log_type`,`log_ref_id`),
  KEY `log_type_2` (`log_type`,`log_date`)
) ENGINE=MyISAM AUTO_INCREMENT=419893 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mezuak`
--

DROP TABLE IF EXISTS `mezuak`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mezuak` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `nork` int(255) DEFAULT NULL,
  `nori` int(255) NOT NULL,
  `posta` enum('sender','recipient') NOT NULL,
  `irakurrita` int(1) NOT NULL,
  `data` varchar(120) NOT NULL,
  `testua` text NOT NULL,
  `titulua` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=21036 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mezuak_nork`
--

DROP TABLE IF EXISTS `mezuak_nork`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mezuak_nork` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `idusr` int(255) DEFAULT NULL,
  `nori` varchar(120) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `normativa`
--

DROP TABLE IF EXISTS `normativa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `normativa` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `texto` text,
  `por` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=51 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pageloads`
--

DROP TABLE IF EXISTS `pageloads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pageloads` (
  `date` date NOT NULL DEFAULT '0000-00-00',
  `type` enum('html','ajax','other','rss','image','api','sneaker','redirection','geo') NOT NULL DEFAULT 'html',
  `counter` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`date`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `polls_comments`
--

DROP TABLE IF EXISTS `polls_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `polls_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `autor` int(11) NOT NULL,
  `encuesta_id` int(11) NOT NULL,
  `contenido` text NOT NULL,
  `votos` int(11) NOT NULL DEFAULT '0',
  `carisma` int(11) NOT NULL DEFAULT '0',
  `ip` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `orden` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2282 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posts` (
  `post_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `post_randkey` int(11) NOT NULL DEFAULT '0',
  `post_src` enum('web','api','im','mobile') CHARACTER SET utf8 NOT NULL DEFAULT 'web',
  `post_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `post_user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `post_visible` enum('all','friends') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'all',
  `post_ip_int` int(11) unsigned NOT NULL DEFAULT '0',
  `post_votes` smallint(4) NOT NULL DEFAULT '0',
  `post_karma` smallint(6) NOT NULL DEFAULT '0',
  `post_content` text COLLATE utf8_spanish_ci NOT NULL,
  `post_type` enum('normal','admin','encuesta') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'normal',
  `post_is_answer` int(11) NOT NULL DEFAULT '0',
  `post_last_answer` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`post_id`),
  KEY `post_date` (`post_date`),
  KEY `post_user_id` (`post_user_id`,`post_date`)
) ENGINE=MyISAM AUTO_INCREMENT=60117 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prefs`
--

DROP TABLE IF EXISTS `prefs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prefs` (
  `pref_user_id` int(11) NOT NULL,
  `pref_key` char(16) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `pref_value` char(10) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  KEY `pref_user_id` (`pref_user_id`,`pref_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sneakers`
--

DROP TABLE IF EXISTS `sneakers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sneakers` (
  `sneaker_id` char(24) NOT NULL,
  `sneaker_time` int(10) unsigned NOT NULL DEFAULT '0',
  `sneaker_user` int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `sneaker_id` (`sneaker_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 MAX_ROWS=1000;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sph_counter`
--

DROP TABLE IF EXISTS `sph_counter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sph_counter` (
  `counter_id` int(11) NOT NULL,
  `max_doc_id` int(11) NOT NULL,
  PRIMARY KEY (`counter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `tag_link_id` int(11) NOT NULL DEFAULT '0',
  `tag_lang` char(4) COLLATE utf8_spanish_ci NOT NULL DEFAULT 'es',
  `tag_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tag_words` char(40) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  UNIQUE KEY `tag_link_id` (`tag_link_id`,`tag_lang`,`tag_words`),
  KEY `tag_lang` (`tag_lang`,`tag_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trackbacks`
--

DROP TABLE IF EXISTS `trackbacks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trackbacks` (
  `trackback_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `trackback_link_id` int(11) NOT NULL DEFAULT '0',
  `trackback_user_id` int(11) NOT NULL DEFAULT '0',
  `trackback_type` enum('in','out') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'in',
  `trackback_status` enum('ok','pendent','error') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'pendent',
  `trackback_date` timestamp NULL DEFAULT NULL,
  `trackback_ip_int` int(10) unsigned NOT NULL DEFAULT '0',
  `trackback_link` varchar(250) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `trackback_url` varchar(250) COLLATE utf8_spanish_ci DEFAULT NULL,
  `trackback_title` text COLLATE utf8_spanish_ci,
  `trackback_content` text COLLATE utf8_spanish_ci,
  PRIMARY KEY (`trackback_id`),
  UNIQUE KEY `trackback_link_id_2` (`trackback_link_id`,`trackback_type`,`trackback_link`),
  KEY `trackback_link_id` (`trackback_link_id`),
  KEY `trackback_url` (`trackback_url`),
  KEY `trackback_date` (`trackback_date`)
) ENGINE=MyISAM AUTO_INCREMENT=11253 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_new_status`
--

DROP TABLE IF EXISTS `user_new_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_new_status` (
  `user_new_status_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_new_status_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_new_status_id`)
) ENGINE=MyISAM AUTO_INCREMENT=52 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(20) NOT NULL AUTO_INCREMENT,
  `user_login` char(32) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `user_level` enum('disabled','devel','normal','special','blogger','admin','god') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'normal',
  `user_avatar` int(10) unsigned NOT NULL DEFAULT '0',
  `user_modification` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_validated_date` timestamp NULL DEFAULT NULL,
  `user_ip` char(32) COLLATE utf8_spanish_ci DEFAULT NULL,
  `user_pass` char(64) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `user_email` char(64) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `user_names` char(60) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `user_estado` char(60) COLLATE utf8_spanish_ci NOT NULL,
  `user_sex` enum('A ti que te importa','Hetero','Gay','Lesbi','Bisepsuá') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'A ti que te importa',
  `user_login_register` char(32) COLLATE utf8_spanish_ci DEFAULT NULL,
  `user_email_register` char(64) COLLATE utf8_spanish_ci DEFAULT NULL,
  `user_lang` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `user_prev_carisma` decimal(10,2) unsigned NOT NULL DEFAULT '7.00',
  `user_karma` decimal(10,2) DEFAULT '7.00',
  `user_public_info` char(64) COLLATE utf8_spanish_ci DEFAULT NULL,
  `user_url` char(128) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `user_birth` text COLLATE utf8_spanish_ci,
  `user_adchannel` char(12) COLLATE utf8_spanish_ci DEFAULT NULL,
  `user_phone` char(16) COLLATE utf8_spanish_ci DEFAULT NULL,
  `user_thumb` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_login` (`user_login`),
  KEY `user_email` (`user_email`),
  KEY `user_karma` (`user_karma`),
  KEY `user_public_info` (`user_public_info`),
  KEY `user_phone` (`user_phone`)
) ENGINE=MyISAM AUTO_INCREMENT=5426 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `votes`
--

DROP TABLE IF EXISTS `votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `votes` (
  `vote_id` int(20) NOT NULL AUTO_INCREMENT,
  `vote_type` enum('links','comments','posts','cortos','poll_comment') CHARACTER SET utf8 NOT NULL DEFAULT 'links',
  `vote_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `vote_link_id` int(20) NOT NULL DEFAULT '0',
  `vote_user_id` int(20) NOT NULL DEFAULT '0',
  `vote_value` smallint(11) NOT NULL DEFAULT '1',
  `vote_ip_int` int(10) unsigned NOT NULL DEFAULT '0',
  `vote_aleatorio` enum('normal','aleatorio') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'normal',
  PRIMARY KEY (`vote_id`),
  UNIQUE KEY `vote_type` (`vote_type`,`vote_link_id`,`vote_user_id`,`vote_ip_int`),
  KEY `vote_type_2` (`vote_type`,`vote_user_id`),
  KEY `vote_type_4` (`vote_type`,`vote_date`,`vote_user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=891416 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci PACK_KEYS=0;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `votes_summary`
--

DROP TABLE IF EXISTS `votes_summary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `votes_summary` (
  `votes_year` smallint(4) NOT NULL DEFAULT '0',
  `votes_month` tinyint(2) NOT NULL DEFAULT '0',
  `votes_type` char(10) NOT NULL DEFAULT '',
  `votes_maxid` int(11) NOT NULL DEFAULT '0',
  `votes_count` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `votes_year` (`votes_year`,`votes_month`,`votes_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-04-24 19:40:55
