-- very simple mySQL table for contact info.
CREATE TABLE IF NOT EXISTS `user1` (
  `phonenumber` bigint(15) NOT NULL,
  `displayname` varchar(50) NOT NULL,
  KEY `phonenumber` (`phonenumber`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
