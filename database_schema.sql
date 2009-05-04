SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `apisettings` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) NOT NULL,
  `fburl` varchar(100) NOT NULL default '',
  `fbtoken` varchar(100) NOT NULL default '',
  `hrurl` varchar(100) NOT NULL default '',
  `hrtoken` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


CREATE TABLE `users` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


SET FOREIGN_KEY_CHECKS = 1;
