CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%765_articles` (
  `id` int(11) NOT NULL auto_increment,
  `categories` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `keywords` text NOT NULL,
  `description` text NOT NULL,
  `fb_image` varchar(255) NOT NULL,
  `filelist` varchar(1024) NOT NULL,
  `clang` int(10) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `create_date` int(11) NOT NULL,
  `update_date` int(11) NOT NULL,
  `create_user` varchar(255) NOT NULL,
  `update_user` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%765_categories` (
  `id` int(10) NOT NULL auto_increment,
  `category_id` int(10) NOT NULL,
  `parent_id` int(10) NOT NULL,
  `clang` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `keywords` text NOT NULL,
  `description` text NOT NULL,
  `status` tinyint(1) NOT NULL,
  `create_date` int(11) NOT NULL,
  `update_date` int(11) NOT NULL,
  `create_user` varchar(255) NOT NULL,
  `update_user` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
