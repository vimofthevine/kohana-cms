CREATE TABLE IF NOT EXISTS `pages` (
	`id` int(11) NOT NULL auto_increment,
	`version` int(4) NOT NULL,
	`title` varchar(100) NOT NULL,
	`slug` varchhar(32) NOT NULL,
	`text` text NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

