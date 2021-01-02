CREATE TABLE `sector` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `validationschema` longtext CHARACTER SET utf8,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `identifier` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `section` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sectorid` bigint(20) unsigned NOT NULL,
  `identifier` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sectionid` (`sectorid`),
  KEY `identifier` (`identifier`),
  CONSTRAINT `section_ibfk_1` FOREIGN KEY (`sectorid`) REFERENCES `sector` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sectionfield` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sectionid` bigint(20) unsigned NOT NULL,
  `identifier` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `typeinformation` longtext CHARACTER SET utf8,
  `content` longtext CHARACTER SET utf8,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sectionid` (`sectionid`),
  KEY `identifier` (`identifier`),
  CONSTRAINT `sectionfield_ibfk_1` FOREIGN KEY (`sectionid`) REFERENCES `section` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;