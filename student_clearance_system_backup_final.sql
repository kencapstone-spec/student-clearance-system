-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: student_clearance_system
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `app_settings`
--

DROP TABLE IF EXISTS `app_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_settings`
--

LOCK TABLES `app_settings` WRITE;
/*!40000 ALTER TABLE `app_settings` DISABLE KEYS */;
INSERT INTO `app_settings` VALUES (1,'active_semester','2nd Semester','2026-08-19 05:23:52','2026-08-19 05:23:53'),(2,'active_school_year','2026-2027','2026-08-19 05:23:52','2026-08-19 05:23:52');
/*!40000 ALTER TABLE `app_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-13d26d035281abf511281e5856c41be2','i:1;',1787117345),('laravel-cache-13d26d035281abf511281e5856c41be2:timer','i:1787117345;',1787117345),('laravel-cache-16aaa5c554054f2666fd08f5a0440a0b','i:2;',1787117541),('laravel-cache-16aaa5c554054f2666fd08f5a0440a0b:timer','i:1787117541;',1787117541),('laravel-cache-225f4dd0e9610434f51dee2de290d196','i:2;',1787117426),('laravel-cache-225f4dd0e9610434f51dee2de290d196:timer','i:1787117426;',1787117426),('laravel-cache-2bc745aa2c6193e2a5445c12fa658a2b','i:1;',1787117527),('laravel-cache-2bc745aa2c6193e2a5445c12fa658a2b:timer','i:1787117527;',1787117527),('laravel-cache-5cf6918d0ffce2cb501a1b0c1b31a600','i:2;',1787117394),('laravel-cache-5cf6918d0ffce2cb501a1b0c1b31a600:timer','i:1787117394;',1787117394),('laravel-cache-67b796799828b4984c898bf5f627acaf','i:1;',1787117249),('laravel-cache-67b796799828b4984c898bf5f627acaf:timer','i:1787117249;',1787117249),('laravel-cache-ab05c5787901efdb1d61ecd4b506c8d5','i:2;',1787117460),('laravel-cache-ab05c5787901efdb1d61ecd4b506c8d5:timer','i:1787117460;',1787117460),('laravel-cache-b2cce7ef1d2659b80d04eb423ee581ce','i:1;',1787117378),('laravel-cache-b2cce7ef1d2659b80d04eb423ee581ce:timer','i:1787117378;',1787117378),('laravel-cache-c4096a453062fba4ec93b55cb4cf896b','i:1;',1787117295),('laravel-cache-c4096a453062fba4ec93b55cb4cf896b:timer','i:1787117295;',1787117295),('laravel-cache-ef39294f6d488ba572edaf130d994d33','i:1;',1787117501),('laravel-cache-ef39294f6d488ba572edaf130d994d33:timer','i:1787117501;',1787117501);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clearance_approvals`
--

DROP TABLE IF EXISTS `clearance_approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clearance_approvals` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `clearance_request_id` bigint(20) unsigned NOT NULL,
  `office_id` bigint(20) unsigned NOT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `status` enum('not_requested','pending','approved','rejected') NOT NULL DEFAULT 'not_requested',
  `remarks` text DEFAULT NULL,
  `acted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clearance_approvals_clearance_request_id_office_id_unique` (`clearance_request_id`,`office_id`),
  KEY `clearance_approvals_approved_by_foreign` (`approved_by`),
  KEY `clearance_approvals_office_id_status_index` (`office_id`,`status`),
  CONSTRAINT `clearance_approvals_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `clearance_approvals_clearance_request_id_foreign` FOREIGN KEY (`clearance_request_id`) REFERENCES `clearance_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `clearance_approvals_office_id_foreign` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clearance_approvals`
--

LOCK TABLES `clearance_approvals` WRITE;
/*!40000 ALTER TABLE `clearance_approvals` DISABLE KEYS */;
INSERT INTO `clearance_approvals` VALUES (1,1,1,3,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(2,1,2,4,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(3,1,3,5,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(4,1,4,6,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(5,1,5,7,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(6,1,6,8,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(7,1,7,9,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(8,1,8,10,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(9,1,9,11,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(10,1,10,12,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(11,1,11,13,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(12,1,12,14,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(13,1,13,15,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(14,1,14,NULL,'pending',NULL,NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56'),(15,2,1,3,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(16,2,2,4,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(17,2,3,5,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(18,2,4,6,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(19,2,5,7,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(20,2,6,8,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(21,2,7,9,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(22,2,8,10,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(23,2,9,11,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(24,2,10,12,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(25,2,11,13,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(26,2,12,14,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(27,2,13,15,'approved',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56','2026-08-19 05:23:56'),(28,2,14,NULL,'pending',NULL,NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56'),(29,3,1,3,'approved',NULL,'2026-08-19 05:23:57','2026-08-19 05:23:57','2026-08-19 05:23:57'),(30,3,2,4,'approved',NULL,'2026-08-19 05:23:57','2026-08-19 05:23:57','2026-08-19 05:23:57'),(31,3,3,5,'approved',NULL,'2026-08-19 05:23:57','2026-08-19 05:23:57','2026-08-19 05:23:57'),(32,3,4,6,'approved',NULL,'2026-08-19 05:23:57','2026-08-19 05:23:57','2026-08-19 05:23:57'),(33,3,5,7,'approved',NULL,'2026-08-19 05:23:57','2026-08-19 05:23:57','2026-08-19 05:23:57'),(34,3,6,8,'approved',NULL,'2026-08-19 05:23:57','2026-08-19 05:23:57','2026-08-19 05:23:57'),(35,3,7,9,'approved',NULL,'2026-08-19 05:23:57','2026-08-19 05:23:57','2026-08-19 05:23:57'),(36,3,8,10,'approved',NULL,'2026-08-19 05:23:57','2026-08-19 05:23:57','2026-08-19 05:23:57'),(37,3,9,11,'approved',NULL,'2026-08-19 05:23:57','2026-08-19 05:23:57','2026-08-19 05:23:57'),(38,3,10,12,'approved',NULL,'2026-08-19 05:23:57','2026-08-19 05:23:57','2026-08-19 05:23:57'),(39,3,11,13,'approved',NULL,'2026-08-19 05:23:57','2026-08-19 05:23:57','2026-08-19 05:23:57'),(40,3,12,14,'approved',NULL,'2026-08-19 05:23:57','2026-08-19 05:23:57','2026-08-19 05:23:57'),(41,3,13,15,'approved',NULL,'2026-08-19 05:23:57','2026-08-19 05:23:57','2026-08-19 05:23:57'),(42,3,14,NULL,'pending',NULL,NULL,'2026-08-19 05:23:57','2026-08-19 05:23:57');
/*!40000 ALTER TABLE `clearance_approvals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clearance_requests`
--

DROP TABLE IF EXISTS `clearance_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clearance_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `semester` varchar(255) NOT NULL,
  `school_year` varchar(255) NOT NULL,
  `status` enum('pending','cleared') NOT NULL DEFAULT 'pending',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `cleared_at` timestamp NULL DEFAULT NULL,
  `receipt_number` varchar(255) DEFAULT NULL,
  `verification_code` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clearance_requests_user_id_semester_school_year_unique` (`user_id`,`semester`,`school_year`),
  UNIQUE KEY `clearance_requests_receipt_number_unique` (`receipt_number`),
  UNIQUE KEY `clearance_requests_verification_code_unique` (`verification_code`),
  KEY `clearance_requests_semester_school_year_index` (`semester`,`school_year`),
  CONSTRAINT `clearance_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clearance_requests`
--

LOCK TABLES `clearance_requests` WRITE;
/*!40000 ALTER TABLE `clearance_requests` DISABLE KEYS */;
INSERT INTO `clearance_requests` VALUES (1,16,'1st Semester','2026-2027','pending','2026-08-19 05:23:56',NULL,NULL,NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56'),(2,17,'1st Semester','2026-2027','pending','2026-08-19 05:23:56',NULL,NULL,NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56'),(3,18,'1st Semester','2026-2027','pending','2026-08-19 05:23:57',NULL,NULL,NULL,'2026-08-19 05:23:57','2026-08-19 05:23:57');
/*!40000 ALTER TABLE `clearance_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_office`
--

DROP TABLE IF EXISTS `course_office`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_office` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) unsigned NOT NULL,
  `office_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_office_course_id_office_id_unique` (`course_id`,`office_id`),
  KEY `course_office_office_id_foreign` (`office_id`),
  CONSTRAINT `course_office_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_office_office_id_foreign` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_office`
--

LOCK TABLES `course_office` WRITE;
/*!40000 ALTER TABLE `course_office` DISABLE KEYS */;
INSERT INTO `course_office` VALUES (1,1,1,NULL,NULL),(2,1,2,NULL,NULL),(3,1,3,NULL,NULL),(4,1,4,NULL,NULL),(5,1,5,NULL,NULL),(6,1,6,NULL,NULL),(7,1,7,NULL,NULL),(8,1,8,NULL,NULL),(9,1,9,NULL,NULL),(10,1,10,NULL,NULL),(11,1,11,NULL,NULL),(12,1,12,NULL,NULL),(13,1,13,NULL,NULL),(14,2,1,NULL,NULL),(15,2,2,NULL,NULL),(16,2,3,NULL,NULL),(17,2,4,NULL,NULL),(18,2,5,NULL,NULL),(19,2,6,NULL,NULL),(20,2,7,NULL,NULL),(21,2,8,NULL,NULL),(22,2,9,NULL,NULL),(23,2,10,NULL,NULL),(24,2,11,NULL,NULL),(25,2,12,NULL,NULL),(26,2,13,NULL,NULL),(27,3,1,NULL,NULL),(28,3,2,NULL,NULL),(29,3,3,NULL,NULL),(30,3,4,NULL,NULL),(31,3,5,NULL,NULL),(32,3,6,NULL,NULL),(33,3,7,NULL,NULL),(34,3,8,NULL,NULL),(35,3,9,NULL,NULL),(36,3,10,NULL,NULL),(37,3,11,NULL,NULL),(38,3,12,NULL,NULL),(39,3,13,NULL,NULL),(40,4,1,NULL,NULL),(41,4,2,NULL,NULL),(42,4,3,NULL,NULL),(43,4,4,NULL,NULL),(44,4,5,NULL,NULL),(45,4,6,NULL,NULL),(46,4,7,NULL,NULL),(47,4,8,NULL,NULL),(48,4,9,NULL,NULL),(49,4,10,NULL,NULL),(50,4,11,NULL,NULL),(51,4,12,NULL,NULL),(52,4,13,NULL,NULL),(53,5,1,NULL,NULL),(54,5,2,NULL,NULL),(55,5,3,NULL,NULL),(56,5,4,NULL,NULL),(57,5,5,NULL,NULL),(58,5,6,NULL,NULL),(59,5,7,NULL,NULL),(60,5,8,NULL,NULL),(61,5,9,NULL,NULL),(62,5,10,NULL,NULL),(63,5,11,NULL,NULL),(64,5,12,NULL,NULL),(65,5,13,NULL,NULL),(66,6,1,NULL,NULL),(67,6,2,NULL,NULL),(68,6,3,NULL,NULL),(69,6,4,NULL,NULL),(70,6,5,NULL,NULL),(71,6,6,NULL,NULL),(72,6,7,NULL,NULL),(73,6,8,NULL,NULL),(74,6,9,NULL,NULL),(75,6,10,NULL,NULL),(76,6,11,NULL,NULL),(77,6,12,NULL,NULL),(78,6,13,NULL,NULL),(79,7,1,NULL,NULL),(80,7,2,NULL,NULL),(81,7,3,NULL,NULL),(82,7,4,NULL,NULL),(83,7,5,NULL,NULL),(84,7,6,NULL,NULL),(85,7,7,NULL,NULL),(86,7,8,NULL,NULL),(87,7,9,NULL,NULL),(88,7,10,NULL,NULL),(89,7,11,NULL,NULL),(90,7,12,NULL,NULL),(91,7,13,NULL,NULL);
/*!40000 ALTER TABLE `course_office` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `courses_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (1,'Bachelor of Science in Information System','BSIS','2026-08-19 05:23:53','2026-08-19 05:23:53'),(2,'Bachelor of Arts in English Language','BAEL','2026-08-19 05:23:53','2026-08-19 05:23:53'),(3,'Bachelor of Science in Political Science','BAPS','2026-08-19 05:23:53','2026-08-19 05:23:53'),(4,'Bachelor of Science in Agriculture','BSA','2026-08-19 05:23:53','2026-08-19 05:23:53'),(5,'Bachelor of Science in Accounting Information System','BSAIS','2026-08-19 05:23:53','2026-08-19 05:23:53'),(6,'Bachelor of Early Childhood Education','BECED','2026-08-19 05:23:53','2026-08-19 05:23:53'),(7,'Bachelor of Science in Criminology','BSCRIM','2026-08-19 05:23:53','2026-08-19 05:23:53');
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0000_01_01_000001_create_courses_table',1),(2,'0000_01_01_000002_create_offices_table',1),(3,'0001_01_01_000000_create_users_table',1),(4,'0001_01_01_000001_create_cache_table',1),(5,'0001_01_01_000002_create_jobs_table',1),(6,'0001_01_01_000003_create_clearance_requests_table',1),(7,'0001_01_01_000004_create_clearance_approvals_table',1),(8,'0001_01_01_000005_create_notifications_table',1),(9,'2025_08_14_170933_add_two_factor_columns_to_users_table',1),(10,'2026_04_26_162917_add_active_status_to_users_table',1),(11,'2026_04_28_122440_add_not_requested_status_to_clearance_approvals_table',1),(12,'2026_04_30_152322_add_receipt_and_verification_fields_to_clearance_requests_table',1),(13,'2026_05_01_110605_create_course_office_table',1),(14,'2026_06_05_000001_create_app_settings_table',1),(15,'2026_06_06_023934_create_office_prerequisites_table',1),(16,'2026_08_13_161526_add_performance_indexes_to_clearance_tables',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_foreign` (`user_id`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,2,'Clearance Ready for Final Approval','Final Approval Test Student TESTFINAL001 has completed all regular office approvals and is ready for final approval.','/president/final-approvals',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56'),(2,2,'Clearance Ready for Final Approval','Final Approval Test Student TESTFINAL002 has completed all regular office approvals and is ready for final approval.','/president/final-approvals',NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56'),(3,2,'Clearance Ready for Final Approval','Final Approval Test Student TESTFINAL003 has completed all regular office approvals and is ready for final approval.','/president/final-approvals',NULL,'2026-08-19 05:23:57','2026-08-19 05:23:57');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `office_prerequisites`
--

DROP TABLE IF EXISTS `office_prerequisites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `office_prerequisites` (
  `office_id` bigint(20) unsigned NOT NULL,
  `prerequisite_office_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`office_id`,`prerequisite_office_id`),
  KEY `office_prerequisites_prerequisite_office_id_foreign` (`prerequisite_office_id`),
  CONSTRAINT `office_prerequisites_office_id_foreign` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `office_prerequisites_prerequisite_office_id_foreign` FOREIGN KEY (`prerequisite_office_id`) REFERENCES `offices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `office_prerequisites`
--

LOCK TABLES `office_prerequisites` WRITE;
/*!40000 ALTER TABLE `office_prerequisites` DISABLE KEYS */;
/*!40000 ALTER TABLE `office_prerequisites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offices`
--

DROP TABLE IF EXISTS `offices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `group` varchar(255) DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_final_approver` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offices`
--

LOCK TABLES `offices` WRITE;
/*!40000 ALTER TABLE `offices` DISABLE KEYS */;
INSERT INTO `offices` VALUES (1,'Guidance Office','Student Services',1,0,'2026-08-19 05:23:53','2026-08-19 05:23:53'),(2,'Medical-Dental Clinic','Student Services',2,0,'2026-08-19 05:23:53','2026-08-19 05:23:53'),(3,'Library','Student Services',3,0,'2026-08-19 05:23:53','2026-08-19 05:23:53'),(4,'Security and Safety Office','Student Services',4,0,'2026-08-19 05:23:53','2026-08-19 05:23:53'),(5,'Equipment and Facilities','Student Services',5,0,'2026-08-19 05:23:53','2026-08-19 05:23:53'),(6,'Supreme Student Government (SSG)','Student Services',6,0,'2026-08-19 05:23:53','2026-08-19 05:23:53'),(7,'Registrar','Student Services',7,0,'2026-08-19 05:23:53','2026-08-19 05:23:53'),(8,'PE/Sports','Student Services',8,0,'2026-08-19 05:23:53','2026-08-19 05:23:53'),(9,'Canteen','Student Services',9,0,'2026-08-19 05:23:53','2026-08-19 05:23:53'),(10,'Program Head','Offices and Administration',10,0,'2026-08-19 05:23:53','2026-08-19 05:23:53'),(11,'Office of Student Affairs and Services (OSAS)','Offices and Administration',11,0,'2026-08-19 05:23:53','2026-08-19 05:23:53'),(12,'Office of the Vice President for Academic Affairs','Offices and Administration',12,0,'2026-08-19 05:23:53','2026-08-19 05:23:53'),(13,'Office of the Vice President for Administration','Offices and Administration',13,0,'2026-08-19 05:23:53','2026-08-19 05:23:53'),(14,'Office of the College President','Offices and Administration',14,1,'2026-08-19 05:23:53','2026-08-19 05:23:53');
/*!40000 ALTER TABLE `offices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('9gLdXDcW108PcmlPY88JCLRFJMQifhUXmz6lSLHM',NULL,'2001:4454:18c:6a00:5b84:356e:2e7d:6ec7','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJZc1ZkRHQ2MzdncEowNjNsdGt3VG5uOTQzYld1OVdoa0R2Umh2OWJyIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1787117505),('KKt1gEiacrpauWq1CmoMCgqn7944bXPCZPV91wX0',NULL,'49.145.38.143','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJmYnNoYk42RER6Y2pxRTRmTHd1WWNEcURNb0ZSUHpVRmJ0N3Q0NzdJIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC90cGNjbGVhcmFuY2UubG9jYS5sdCIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1787117810);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `course_id` bigint(20) unsigned DEFAULT NULL,
  `office_id` bigint(20) unsigned DEFAULT NULL,
  `role` enum('student','staff','admin','president') NOT NULL DEFAULT 'student',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `deactivated_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `two_factor_secret` text DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_student_id_unique` (`student_id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_course_id_foreign` (`course_id`),
  KEY `users_office_id_foreign` (`office_id`),
  CONSTRAINT `users_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_office_id_foreign` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'ADMIN001','OSAS Director','admin@university.edu',NULL,11,'admin',1,NULL,NULL,'$2y$12$bApBx5x5/hNkn6f08O/QCuPcgCNRZXYKt5ElY3GKQACTD4FkboZ/S',NULL,NULL,NULL,NULL,'2026-08-19 05:23:53','2026-08-19 05:23:53'),(2,'PRES001','College President','president@university.edu',NULL,14,'president',1,NULL,NULL,'$2y$12$VAwWu5tSTEtnZBFmO8ixTul.1hK92.wPN4jQ8vrpvg67zPNHgxc9G',NULL,NULL,NULL,NULL,'2026-08-19 05:23:53','2026-08-19 05:23:53'),(3,'GUID001','Guidance Staff','guidance@university.edu',NULL,1,'staff',1,NULL,NULL,'$2y$12$YNeqlmIdhksQuzR9cy.1fOuAOtUe/6u.xuoDa9WniIzSsTGmGGAju',NULL,NULL,NULL,NULL,'2026-08-19 05:23:53','2026-08-19 05:23:53'),(4,'CLINIC001','Medical-Dental Clinic Staff','clinic@university.edu',NULL,2,'staff',1,NULL,NULL,'$2y$12$NOH96DsEYkxLncJ08va.q.onBLgu.GDrn.p4EEsSgwUh0GjgyhP/C',NULL,NULL,NULL,'jhqroM0TSana9jh18y2Kby9VZuUkht8rrhRowbAg7r4HSyvEpWOWdEFlWTcC','2026-08-19 05:23:53','2026-08-19 05:23:53'),(5,'LIB001','Library Staff','library@university.edu',NULL,3,'staff',1,NULL,NULL,'$2y$12$ft4fVokIpNs4gdVQLX/wKubuI4Dzxjl7QtJo076R6ExCYyuDlFSHu',NULL,NULL,NULL,NULL,'2026-08-19 05:23:54','2026-08-19 05:23:54'),(6,'SEC001','Security and Safety Staff','security@university.edu',NULL,4,'staff',1,NULL,NULL,'$2y$12$pte8LqK2GfVlvx2NTLpA5O2vfaPw/hIFSUHDVYonXv05ZiBjq82iq',NULL,NULL,NULL,'Al5dV4kJDry2qlzD9rak2Pz8oUxckqLhYsdaXRLEzjG39DgvVjCXHxn260Eg','2026-08-19 05:23:54','2026-08-19 05:23:54'),(7,'EQUIP001','Equipment and Facilities Staff','equipment@university.edu',NULL,5,'staff',1,NULL,NULL,'$2y$12$dkQsxHwuETr9tPBqzKkdnOFxGGdJHC9w7rFawMji2n/G5WY2qkmDG',NULL,NULL,NULL,'UxUDPcUwTxpNihRi4X9KVqCgN901XcTkTlzJZItzQruPGmzLwmhaO6GqklQW','2026-08-19 05:23:54','2026-08-19 05:23:54'),(8,'SSG001','SSG Staff','ssg@university.edu',NULL,6,'staff',1,NULL,NULL,'$2y$12$L05iB3hk9p1fSoXBMK79aeHVQNNYQJUxAM3Bh1Y2t8AWXMzIjpdpe',NULL,NULL,NULL,'GX2jcD3a00w3qCPr5wmXfFXRLVdkPKIw7FNcxD04cGYqVer1rpPMEsChiQCG','2026-08-19 05:23:54','2026-08-19 05:23:54'),(9,'REG001','Registrar Staff','registrar@university.edu',NULL,7,'staff',1,NULL,NULL,'$2y$12$aWsnjc2OV.nv2q4Jf75.UuV5JnazsbDCCybPqaH7yu0awc8kQUpK.',NULL,NULL,NULL,'nqOylBZIfz23DyjjrsjbSiAmuTd7MAAaQfD2zKIDiK0CPfNAP3ornYbFyRW8','2026-08-19 05:23:54','2026-08-19 05:23:54'),(10,'SPORTS001','PE/Sports Staff','sports@university.edu',NULL,8,'staff',1,NULL,NULL,'$2y$12$0YzDbR253opyRPHXZ6zfxuJAgPAyD2Du3Obb.VqU49J14cI0.Oxq6',NULL,NULL,NULL,NULL,'2026-08-19 05:23:55','2026-08-19 05:23:55'),(11,'CANTEEN001','Canteen Staff','canteen@university.edu',NULL,9,'staff',1,NULL,NULL,'$2y$12$SZIKhpul85bLrhDJCvjX..lPeF3DI37re3RNfFG7RNB/pR6h2lxHK',NULL,NULL,NULL,'VLZAZ1nJQEWe8EHyi3edz9PZpuyBOyl0TiGtUakv4AlkujC0CcyT8FSBgqrJ','2026-08-19 05:23:55','2026-08-19 05:23:55'),(12,'PROG001','Program Head Staff','program_head@university.edu',NULL,10,'staff',1,NULL,NULL,'$2y$12$fecJUy5Jo5YZeEtQuJ6dNuO0nSf7C/QsnodnxICgUBjOQFcbehJHW',NULL,NULL,NULL,'sE462lWBSAMDtW0FvvEZeRptVOuqbUAeMvJs92c6peK7EzO5e1E8iuOCxDAO','2026-08-19 05:23:55','2026-08-19 05:23:55'),(13,'OSAS001','OSAS Staff','osas_staff@university.edu',NULL,11,'staff',1,NULL,NULL,'$2y$12$KDOsJrh5p0mQXStg6KXlw.FjItOSjk.Ci73sZkHDlhCojBcZ5aVkS',NULL,NULL,NULL,NULL,'2026-08-19 05:23:55','2026-08-19 05:23:55'),(14,'VPAA001','VPAA Staff','vpaa@university.edu',NULL,12,'staff',1,NULL,NULL,'$2y$12$6Fi9RF2CtWssJoZ5E33wwO1u3jwDz5LG/pDiMTVKVX1u0.Uf5p7ua',NULL,NULL,NULL,'NgPgyxYChn4RInmLyDB2I8z3gHLoHVOiye53UWnFJOqfEZ7fDOm7IgfaulmM','2026-08-19 05:23:56','2026-08-19 05:23:56'),(15,'VPA001','VPA Staff','vpa@university.edu',NULL,13,'staff',1,NULL,NULL,'$2y$12$TYDRhtBpLRJHflrNFrAR2OKvRmfK92CvotNpPqO8bRt1eXLv4qxni',NULL,NULL,NULL,'Sw4LQIGiNo8shPB5Wnrrd5Phe9NgWIYpMyuhIGUbGNYFUiiwS7xcsIdkgTq1','2026-08-19 05:23:56','2026-08-19 05:23:56'),(16,'TESTFINAL001','Final Approval Test Student TESTFINAL001','studentTESTFINAL001@university.edu',1,NULL,'student',1,NULL,NULL,'$2y$12$4cbVYOoofaRuLMP0DFoT4.Inhgsi7jdBOo3uMLVidRClDBlmIqaHm',NULL,NULL,NULL,NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56'),(17,'TESTFINAL002','Final Approval Test Student TESTFINAL002','studentTESTFINAL002@university.edu',1,NULL,'student',1,NULL,NULL,'$2y$12$qxToc2nLQDbrFG85VJgPd.JQqYiwKMLpCenO9ADa6aqFdVVe0jgfW',NULL,NULL,NULL,NULL,'2026-08-19 05:23:56','2026-08-19 05:23:56'),(18,'TESTFINAL003','Final Approval Test Student TESTFINAL003','studentTESTFINAL003@university.edu',1,NULL,'student',1,NULL,NULL,'$2y$12$HavfwJz24CbPEe4T9k4p1.a5l8xxKS6TNMz4QFmp.wp67Mz6KBGoi',NULL,NULL,NULL,NULL,'2026-08-19 05:23:57','2026-08-19 05:23:57');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-19 13:56:52
