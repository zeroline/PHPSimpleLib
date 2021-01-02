CREATE TABLE `migrationstatus` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `moduleName` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `migrationFile` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `migrationDate` datetime NOT NULL,
  `migrationData` longtext CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;