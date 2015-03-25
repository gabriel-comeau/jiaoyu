<?php

	/*
	  ##########################################################################
	  ####################### Example SQL for Users table ######################
	  ##########################################################################

	  DROP TABLE IF EXISTS `Users`;
		CREATE TABLE `Users` (
  			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  			`username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  			`password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
	*/


	/**
	 * Represents a user.
	 */
	class User extends Atlas {
		protected static $table = "Users";
	}
