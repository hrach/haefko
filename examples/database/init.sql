/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50136
Source Host           : localhost:3306
Source Database       : haefko_examples

Target Server Type    : MYSQL
Target Server Version : 50136
File Encoding         : 65001

Date: 2009-08-09 16:46:27
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `albums`
-- ----------------------------
DROP TABLE IF EXISTS `albums`;
CREATE TABLE `albums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `artist_id` int(100) NOT NULL,
  `disk` enum('klasický','dvoj','troj','více') COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- ----------------------------
-- Records of albums
-- ----------------------------
INSERT INTO `albums` VALUES ('1', 'Album A', '1', 'klasický');
INSERT INTO `albums` VALUES ('2', 'Beta', '1', 'klasický');
INSERT INTO `albums` VALUES ('3', 'Common problems', '2', 'klasický');

-- ----------------------------
-- Table structure for `artists`
-- ----------------------------
DROP TABLE IF EXISTS `artists`;
CREATE TABLE `artists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- ----------------------------
-- Records of artists
-- ----------------------------
INSERT INTO `artists` VALUES ('1', 'Cally McDown');
INSERT INTO `artists` VALUES ('2', 'Peter File');
