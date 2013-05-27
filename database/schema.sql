CREATE TABLE IF NOT EXISTS `system_event_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `time` decimal(20,6) NOT NULL,
  `event_identity` char(36) NOT NULL DEFAULT '',
  `event_name` varchar(255) NOT NULL DEFAULT '',
  `serialized_event` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `system_monitor` (
  `identity` char(36) NOT NULL DEFAULT '',
  `adapter` varchar(255) NOT NULL DEFAULT '',
  `class_name` varchar(255) NOT NULL DEFAULT '',
  `priority` int(10) NOT NULL,
  `time_added_to_queue` decimal(20,6) NOT NULL,
  `time_removed_from_queue` decimal(20,6) NOT NULL,
  `attributes` text NOT NULL,
  `events_fired` int(10) DEFAULT NULL,
  `commands_fired` int(10) DEFAULT NULL,
  `peak_memory_usage` decimal(10,2) NOT NULL,
  `time_took_method_to_process` decimal(20,6) NOT NULL,
  `time_took_publishing_events` decimal(20,6) NOT NULL,
  `time_took_saving_events` decimal(20,6) NOT NULL,
  `time_total` decimal(20,6) DEFAULT NULL,
  `date` datetime NOT NULL,
  `mobile` tinyint(1) unsigned NOT NULL,
  `user_id` INT(11) NOT NULL DEFAULT '0',
  `size` INT(11) ZEROFILL NOT NULL DEFAULT '0',
  `events` TEXT NOT NULL,
  `failed` TINYINT(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
 * TWITTER
 */
CREATE TABLE IF NOT EXISTS `adapter_twitter_queue` (
  `identity` varchar(255) NOT NULL DEFAULT '',
  `class_name` varchar(255) NOT NULL,
  `priority` int(10) NOT NULL,
  `time` decimal(20,6) NOT NULL,
  `attributes` text NOT NULL,
  `work_in_progress` enum('false','true') NOT NULL DEFAULT 'false',
  `begin_work_at` decimal(20,6) DEFAULT NULL,
  `failed` int(10) DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`identity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `twitter_adapter_data` (
  `identity` CHAR(36) NOT NULL DEFAULT '',
  `added` DATETIME NOT NULL,
  `data` TEXT NOT NULL,
  `key` CHAR(36) NOT NULL DEFAULT '',
  PRIMARY KEY (`identity`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `twitter_adapter_meta` (
  `key` char(36) NOT NULL DEFAULT '',
  `column` varchar(255) NOT NULL DEFAULT '',
  `type` enum('date','text','varchar','int','decimal') NOT NULL DEFAULT 'text'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `twitter_adapter` (
  `key` char(36) NOT NULL DEFAULT '',
  `user_id` INT(11) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `command_string` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
 * FACEBOOK
 */

CREATE TABLE IF NOT EXISTS `adapter_facebook_queue` (
  `identity` varchar(255) NOT NULL DEFAULT '',
  `class_name` varchar(255) NOT NULL,
  `priority` int(10) NOT NULL,
  `time` decimal(20,6) NOT NULL,
  `attributes` text NOT NULL,
  `work_in_progress` enum('false','true') NOT NULL DEFAULT 'false',
  `begin_work_at` decimal(20,6) DEFAULT NULL,
  `failed` int(10) DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`identity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `facebook_adapter_data` (
  `identity` CHAR(36) NOT NULL DEFAULT '',
  `added` DATETIME NOT NULL,
  `data` TEXT NOT NULL,
  `key` CHAR(36) NOT NULL DEFAULT '',
  PRIMARY KEY (`identity`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `facebook_adapter_meta` (
  `key` char(36) NOT NULL DEFAULT '',
  `column` varchar(255) NOT NULL DEFAULT '',
  `type` enum('date','text','varchar','int','decimal') NOT NULL DEFAULT 'text'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `facebook_adapter` (
  `key` char(36) NOT NULL DEFAULT '',
  `user_id` INT(11) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `command_string` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
 * GOOGLEPLUS
 */

CREATE TABLE IF NOT EXISTS `adapter_googleplus_queue` (
  `identity` varchar(255) NOT NULL DEFAULT '',
  `class_name` varchar(255) NOT NULL,
  `priority` int(10) NOT NULL,
  `time` decimal(20,6) NOT NULL,
  `attributes` text NOT NULL,
  `work_in_progress` enum('false','true') NOT NULL DEFAULT 'false',
  `begin_work_at` decimal(20,6) DEFAULT NULL,
  `failed` int(10) DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`identity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `googleplus_adapter_data` (
  `identity` CHAR(36) NOT NULL DEFAULT '',
  `added` DATETIME NOT NULL,
  `data` TEXT NOT NULL,
  `key` CHAR(36) NOT NULL DEFAULT '',
  PRIMARY KEY (`identity`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `googleplus_adapter_meta` (
  `key` char(36) NOT NULL DEFAULT '',
  `column` varchar(255) NOT NULL DEFAULT '',
  `type` enum('date','text','varchar','int','decimal') NOT NULL DEFAULT 'text'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `googleplus_adapter` (
  `key` char(36) NOT NULL DEFAULT '',
  `user_id` INT(11) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `command_string` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
 * MYSQL
 */

CREATE TABLE IF NOT EXISTS `adapter_mysql_queue` (
  `identity` varchar(255) NOT NULL DEFAULT '',
  `class_name` varchar(255) NOT NULL,
  `priority` int(10) NOT NULL,
  `time` decimal(20,6) NOT NULL,
  `attributes` text NOT NULL,
  `work_in_progress` enum('false','true') NOT NULL DEFAULT 'false',
  `begin_work_at` decimal(20,6) DEFAULT NULL,
  `failed` int(10) DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`identity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `mysql_adapter_data` (
  `identity` CHAR(36) NOT NULL DEFAULT '',
  `added` DATETIME NOT NULL,
  `data` TEXT NOT NULL,
  `key` CHAR(36) NOT NULL DEFAULT '',
  PRIMARY KEY (`identity`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `mysql_adapter_meta` (
  `key` char(36) NOT NULL DEFAULT '',
  `column` varchar(255) NOT NULL DEFAULT '',
  `type` enum('date','text','varchar','int','decimal') NOT NULL DEFAULT 'text'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `mysql_adapter` (
  `key` char(36) NOT NULL DEFAULT '',
  `user_id` INT(11) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `command_string` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
 * Oracle
 */

CREATE TABLE IF NOT EXISTS `adapter_oracle_queue` (
  `identity` varchar(255) NOT NULL DEFAULT '',
  `class_name` varchar(255) NOT NULL,
  `priority` int(10) NOT NULL,
  `time` decimal(20,6) NOT NULL,
  `attributes` text NOT NULL,
  `work_in_progress` enum('false','true') NOT NULL DEFAULT 'false',
  `begin_work_at` decimal(20,6) DEFAULT NULL,
  `failed` int(10) DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`identity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `oracle_adapter_data` (
  `identity` CHAR(36) NOT NULL DEFAULT '',
  `added` DATETIME NOT NULL,
  `data` TEXT NOT NULL,
  `key` CHAR(36) NOT NULL DEFAULT '',
  PRIMARY KEY (`identity`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `oracle_adapter_meta` (
  `key` char(36) NOT NULL DEFAULT '',
  `column` varchar(255) NOT NULL DEFAULT '',
  `type` enum('date','text','varchar','int','decimal') NOT NULL DEFAULT 'text'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `oracle_adapter` (
  `key` char(36) NOT NULL DEFAULT '',
  `user_id` INT(11) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `command_string` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
 * PostgreSQL
 */

CREATE TABLE IF NOT EXISTS `adapter_postgresql_queue` (
  `identity` varchar(255) NOT NULL DEFAULT '',
  `class_name` varchar(255) NOT NULL,
  `priority` int(10) NOT NULL,
  `time` decimal(20,6) NOT NULL,
  `attributes` text NOT NULL,
  `work_in_progress` enum('false','true') NOT NULL DEFAULT 'false',
  `begin_work_at` decimal(20,6) DEFAULT NULL,
  `failed` int(10) DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`identity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `postgresql_adapter_data` (
  `identity` CHAR(36) NOT NULL DEFAULT '',
  `added` DATETIME NOT NULL,
  `data` TEXT NOT NULL,
  `key` CHAR(36) NOT NULL DEFAULT '',
  PRIMARY KEY (`identity`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `postgresql_adapter_meta` (
  `key` char(36) NOT NULL DEFAULT '',
  `column` varchar(255) NOT NULL DEFAULT '',
  `type` enum('date','text','varchar','int','decimal') NOT NULL DEFAULT 'text'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `postgresql_adapter` (
  `key` char(36) NOT NULL DEFAULT '',
  `user_id` INT(11) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `command_string` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
 * FilesCsvTxt
 */

CREATE TABLE IF NOT EXISTS `adapter_filescsvtxt_queue` (
  `identity` varchar(255) NOT NULL DEFAULT '',
  `class_name` varchar(255) NOT NULL,
  `priority` int(10) NOT NULL,
  `time` decimal(20,6) NOT NULL,
  `attributes` text NOT NULL,
  `work_in_progress` enum('false','true') NOT NULL DEFAULT 'false',
  `begin_work_at` decimal(20,6) DEFAULT NULL,
  `failed` int(10) DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`identity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `filescsvtxt_adapter_data` (
  `identity` CHAR(36) NOT NULL DEFAULT '',
  `added` DATETIME NOT NULL,
  `data` TEXT NOT NULL,
  `key` CHAR(36) NOT NULL DEFAULT '',
  PRIMARY KEY (`identity`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `filescsvtxt_adapter_meta` (
  `key` char(36) NOT NULL DEFAULT '',
  `column` varchar(255) NOT NULL DEFAULT '',
  `type` enum('date','text','varchar','int','decimal') NOT NULL DEFAULT 'text'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `filescsvtxt_adapter` (
  `key` char(36) NOT NULL DEFAULT '',
  `user_id` INT(11) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `command_string` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
 * FilesXlsx
 */

CREATE TABLE IF NOT EXISTS `adapter_filesxlsx_queue` (
  `identity` varchar(255) NOT NULL DEFAULT '',
  `class_name` varchar(255) NOT NULL,
  `priority` int(10) NOT NULL,
  `time` decimal(20,6) NOT NULL,
  `attributes` text NOT NULL,
  `work_in_progress` enum('false','true') NOT NULL DEFAULT 'false',
  `begin_work_at` decimal(20,6) DEFAULT NULL,
  `failed` int(10) DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`identity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `filesxlsx_adapter_data` (
  `identity` CHAR(36) NOT NULL DEFAULT '',
  `added` DATETIME NOT NULL,
  `data` TEXT NOT NULL,
  `key` CHAR(36) NOT NULL DEFAULT '',
  PRIMARY KEY (`identity`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `filesxlsx_adapter_meta` (
  `key` char(36) NOT NULL DEFAULT '',
  `column` varchar(255) NOT NULL DEFAULT '',
  `type` enum('date','text','varchar','int','decimal') NOT NULL DEFAULT 'text'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `filesxlsx_adapter` (
  `key` char(36) NOT NULL DEFAULT '',
  `user_id` INT(11) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `command_string` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
 * SOAP
 */

CREATE TABLE IF NOT EXISTS `adapter_soap_queue` (
  `identity` varchar(255) NOT NULL DEFAULT '',
  `class_name` varchar(255) NOT NULL,
  `priority` int(10) NOT NULL,
  `time` decimal(20,6) NOT NULL,
  `attributes` text NOT NULL,
  `work_in_progress` enum('false','true') NOT NULL DEFAULT 'false',
  `begin_work_at` decimal(20,6) DEFAULT NULL,
  `failed` int(10) DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`identity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `soap_adapter_data` (
  `identity` CHAR(36) NOT NULL DEFAULT '',
  `added` DATETIME NOT NULL,
  `data` TEXT NOT NULL,
  `key` CHAR(36) NOT NULL DEFAULT '',
  PRIMARY KEY (`identity`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `soap_adapter_meta` (
  `key` char(36) NOT NULL DEFAULT '',
  `column` varchar(255) NOT NULL DEFAULT '',
  `type` enum('date','text','varchar','int','decimal') NOT NULL DEFAULT 'text'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `soap_adapter` (
  `key` char(36) NOT NULL DEFAULT '',
  `user_id` INT(11) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `command_string` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
 * Normalized
 */

CREATE TABLE IF NOT EXISTS `adapter_normalized_queue` (
  `identity` varchar(255) NOT NULL DEFAULT '',
  `class_name` varchar(255) NOT NULL,
  `priority` int(10) NOT NULL,
  `time` decimal(20,6) NOT NULL,
  `attributes` text NOT NULL,
  `work_in_progress` enum('false','true') NOT NULL DEFAULT 'false',
  `begin_work_at` decimal(20,6) DEFAULT NULL,
  `failed` int(10) DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`identity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `normalized_adapter_data` (
  `identity` CHAR(36) NOT NULL DEFAULT '',
  `added` DATETIME NOT NULL,
  `data` TEXT NOT NULL,
  `key` CHAR(36) NOT NULL DEFAULT '',
  PRIMARY KEY (`identity`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `normalized_adapter_meta` (
  `key` char(36) NOT NULL DEFAULT '',
  `column` varchar(255) NOT NULL DEFAULT '',
  `type` enum('date','text','varchar','int','decimal') NOT NULL DEFAULT 'text'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `normalized_adapter` (
  `key` char(36) NOT NULL DEFAULT '',
  `user_id` INT(11) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `command_string` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
 * PDF
 */

CREATE TABLE IF NOT EXISTS `adapter_pdf_queue` (
  `identity` varchar(255) NOT NULL DEFAULT '',
  `class_name` varchar(255) NOT NULL,
  `priority` int(10) NOT NULL,
  `time` decimal(20,6) NOT NULL,
  `attributes` text NOT NULL,
  `work_in_progress` enum('false','true') NOT NULL DEFAULT 'false',
  `begin_work_at` decimal(20,6) DEFAULT NULL,
  `failed` int(10) DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`identity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `pdf_adapter_data` (
  `identity` CHAR(36) NOT NULL DEFAULT '',
  `added` DATETIME NOT NULL,
  `data` TEXT NOT NULL,
  `key` CHAR(36) NOT NULL DEFAULT '',
  PRIMARY KEY (`identity`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `pdf_adapter` (
  `key` char(36) NOT NULL DEFAULT '',
  `user_id` INT(11) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `command_string` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
 * FILES REGISTRY
 */

CREATE TABLE `exported_files` (
  `identity` varchar(36) NOT NULL,
  `filename` varchar(100) NOT NULL,
  `generated` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `mime` varchar(80) NOT NULL,
  PRIMARY KEY (`identity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;