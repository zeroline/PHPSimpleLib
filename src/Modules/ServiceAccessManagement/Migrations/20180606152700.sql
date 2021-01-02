CREATE TABLE `serviceaccess` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `appKey` char(64) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `appSecret` char(64) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `description` longtext CHARACTER SET utf8,
  `active` tinyint(1) unsigned NOT NULL,
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `serviceaccessvalues` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `serviceaccessid` bigint(20) unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `content` longtext CHARACTER SET utf8,
  PRIMARY KEY (`id`),
  KEY `serviceaccessid` (`serviceaccessid`),
  CONSTRAINT `serviceaccessvalues_ibfk_1` FOREIGN KEY (`serviceaccessid`) REFERENCES `serviceaccess` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;