SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for demo_groups
-- ----------------------------
CREATE TABLE `demo_groups` (
  `group_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_czech_ci default NULL,
  PRIMARY KEY  (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- ----------------------------
-- Table structure for demo_messages
-- ----------------------------
CREATE TABLE `demo_messages` (
  `message_id` int(11) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL,
  `author` varchar(255) collate utf8_czech_ci NOT NULL,
  `text` text collate utf8_czech_ci,
  `date` datetime default NULL,
  PRIMARY KEY  (`message_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;