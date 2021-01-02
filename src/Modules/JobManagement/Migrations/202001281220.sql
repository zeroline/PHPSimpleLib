CREATE TABLE `jobType` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_unicode_ci,
  `mode` int(10) unsigned NOT NULL,
  `configuration` longtext COLLATE utf8mb4_unicode_ci,
  `locator` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `retryDelay` int(10) unsigned NOT NULL,
  `maxRetries` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` bigint(20) unsigned NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `parameter` longtext COLLATE utf8mb4_unicode_ci,
  `status` int(10) unsigned NOT NULL,
  `attempt` int(10) unsigned NOT NULL,
  `activeState` int(10) unsigned NOT NULL,
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `status` (`status`),
  KEY `activeState` (`activeState`),
  KEY `updated` (`updated`),
  KEY `created` (`created`),
  KEY `attempt` (`attempt`),
  CONSTRAINT `job_ibfk_1` FOREIGN KEY (`type`) REFERENCES `jobType` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobHistory` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jobId` bigint(20) unsigned NOT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci,
  `additionalData` longtext COLLATE utf8mb4_unicode_ci,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobId` (`jobId`),
  KEY `created` (`created`),
  CONSTRAINT `jobhistory_ibfk_1` FOREIGN KEY (`jobId`) REFERENCES `job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE VIEW vJobsForProcessing AS SELECT 
    j.id AS jobId, jt.id as jobType FROM job j 
    JOIN jobType jt ON ( j.type = jt.id )
    WHERE 
        j.attempt < jt.maxRetries AND
        j.status = 0 AND
        ((DATE_ADD(j.updated, INTERVAL jt.retryDelay SECOND)) <  NOW())
    ORDER BY j.updated ASC


