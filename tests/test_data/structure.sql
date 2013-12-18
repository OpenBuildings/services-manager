CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(254) NOT NULL,
  `username` varchar(32) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL,
  `logins` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `last_login` int(10) UNSIGNED,
  `facebook_uid` varchar(100),
  `twitter_uid` varchar(100),
  `last_login_ip` VARCHAR(40),
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uniq_username` (`username`),
  UNIQUE KEY `uniq_email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `users` (`id`, `email`, `username`)
VALUES
  (1,'test@example.com', 'username-example'),
  (2,'test-2@example.com', 'username2-example');
