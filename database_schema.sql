SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `apisettings` (
  `id` int(9) NOT NULL auto_increment,
  `userid` int(9) NOT NULL,
  `fb_oauth_token` varchar(100) default NULL,
  `fb_oauth_token_secret` varchar(100) default NULL,
  `hrurl` varchar(100) default '',
  `hrtoken` varchar(100) default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `users` (
  `id` int(9) NOT NULL auto_increment,
  `fb_url` varchar(100) NOT NULL,
  `password` varchar(256) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

