-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: mhakim_billing
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_activity_logs`
--

DROP TABLE IF EXISTS `admin_activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_activity_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_name` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `ip_address` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_activity_logs`
--

LOCK TABLES `admin_activity_logs` WRITE;
/*!40000 ALTER TABLE `admin_activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_roles`
--

DROP TABLE IF EXISTS `admin_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('super_admin','cashier','technician','noc_operator','sales_agent') COLLATE utf8mb4_general_ci DEFAULT 'noc_operator',
  `username` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('active','disabled') COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_roles`
--

LOCK TABLES `admin_roles` WRITE;
/*!40000 ALTER TABLE `admin_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_settings`
--

DROP TABLE IF EXISTS `admin_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_name` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `admin_name` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `admin_email` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `logo_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_settings`
--

LOCK TABLES `admin_settings` WRITE;
/*!40000 ALTER TABLE `admin_settings` DISABLE KEYS */;
INSERT INTO `admin_settings` VALUES (1,'Hakim Networks','M. Hakim','admin@hakimnetworks.local',NULL,'2026-05-22 00:00:09','uploads/company_logo_1779408009.png');
/*!40000 ALTER TABLE `admin_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client_portal_sessions`
--

DROP TABLE IF EXISTS `client_portal_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_portal_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int DEFAULT NULL,
  `login_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_portal_sessions`
--

LOCK TABLES `client_portal_sessions` WRITE;
/*!40000 ALTER TABLE `client_portal_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `client_portal_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client_profiles`
--

DROP TABLE IF EXISTS `client_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_profiles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `whatsapp` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `location` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `national_id` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `package_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('active','suspended','expired','pending') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `router_id` int DEFAULT NULL,
  `ip_address` varchar(60) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mac_address` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expiry_date` datetime DEFAULT NULL,
  `notes` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_profiles`
--

LOCK TABLES `client_profiles` WRITE;
/*!40000 ALTER TABLE `client_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `client_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client_session_actions`
--

DROP TABLE IF EXISTS `client_session_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_session_actions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_user` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `client_ip` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `result` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_session_actions`
--

LOCK TABLES `client_session_actions` WRITE;
/*!40000 ALTER TABLE `client_session_actions` DISABLE KEYS */;
/*!40000 ALTER TABLE `client_session_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `package_id` int DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_general_ci DEFAULT 'active',
  `starts_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `client_ip` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `client_mac` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `connection_type` varchar(30) COLLATE utf8mb4_general_ci DEFAULT 'hotspot',
  `company_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_clients_status` (`status`),
  KEY `idx_clients_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=146 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` VALUES (8,'hakim004','254708374140','user56','1234567',3,'active',NULL,NULL,'2026-05-06 14:00:00',NULL,NULL,'hotspot',NULL),(9,'test net','0704467653','hakim009','1234',5,'active',NULL,NULL,'2026-05-06 15:35:02',NULL,NULL,'hotspot',NULL),(10,'vic','0704467654','user1','1234',2,'active',NULL,NULL,'2026-05-06 16:04:28',NULL,NULL,'hotspot',NULL),(12,'Test Ajax9','254708374136','admin4','hakim',7,'active',NULL,NULL,'2026-05-06 16:10:37',NULL,NULL,'hotspot',NULL),(13,'Hotspot Client','254704467699','0704467699','7699',12,'pending','2026-05-15 16:04:54','2026-05-15 21:04:54','2026-05-07 05:32:38','$(ip)',NULL,'hotspot',NULL),(15,'Hotspot Client','0700000999','0700000999','0999',1,'expired','2026-05-07 08:34:16','2026-05-07 04:34:16','2026-05-07 05:34:16',NULL,NULL,'hotspot',NULL),(16,'Hotspot Client','0704467696','0704467696','7696',3,'expired','2026-05-07 08:39:26','2026-05-06 19:39:26','2026-05-07 05:39:26',NULL,NULL,'hotspot',NULL),(18,'Hotspot Client','0704467697','0704467697','7697',3,'expired','2026-05-07 08:43:47','2026-05-06 19:43:47','2026-05-07 05:43:47',NULL,NULL,'hotspot',NULL),(19,'Test Ajap','0704467634','admin45','1234h',1,'active',NULL,NULL,'2026-05-07 06:09:56',NULL,NULL,'hotspot',NULL),(20,'Hotspot Client','0704467643','0704467643','7643',1,'expired','2026-05-07 09:57:34','2026-05-07 16:12:33','2026-05-07 06:12:33',NULL,NULL,'hotspot',NULL),(22,'Hotspot Client','0704467600','0704467600','7600',3,'expired','2026-05-07 09:58:23','2026-05-07 02:58:23','2026-05-07 06:57:07',NULL,NULL,'hotspot',NULL),(23,'hakim567','0704467609','admin543','1234F',1,'active',NULL,NULL,'2026-05-07 07:04:16',NULL,NULL,'hotspot',NULL),(24,'hakim566r','0704467689','admin3909','1234GF',1,'expired','2026-05-07 10:13:53','2026-05-07 12:13:53','2026-05-07 07:13:53',NULL,NULL,'hotspot',NULL),(25,'morgan','0703467699','hakim200','1234dat',1,'expired','2026-05-07 10:26:32','2026-05-07 12:26:32','2026-05-07 07:26:32',NULL,NULL,'hotspot',NULL),(26,'hakime43','0704567699','admin24636','hakim2342',1,'expired','2026-05-07 10:39:36','2026-05-07 11:39:36','2026-05-07 07:39:36',NULL,NULL,'hotspot',NULL),(27,'Hotspot Client','0704469699','0704469699','9699',11,'expired','2026-05-07 10:40:36','2026-05-07 15:40:36','2026-05-07 07:40:36',NULL,NULL,'hotspot',NULL),(28,'Hotspot Client','0704468699','0704468699','8699',2,'expired','2026-05-07 10:42:17','2026-05-07 10:42:17','2026-05-07 07:42:17',NULL,NULL,'hotspot',NULL),(29,'Hotspot Client','0704469656','0704469656','9656',1,'expired','2026-05-07 11:05:34','2026-05-07 12:05:34','2026-05-07 08:05:34',NULL,NULL,'hotspot',NULL),(30,'Hotspot Client','0704469649','0704469649','9649',11,'expired','2026-05-07 11:07:16','2026-05-07 11:08:16','2026-05-07 08:07:16',NULL,NULL,'hotspot',NULL),(31,'Hotspot Client','0704469643','0704469643','9643',11,'expired','2026-05-07 11:35:29','2026-05-07 11:36:29','2026-05-07 08:35:29',NULL,NULL,'hotspot',NULL),(32,'Voucher User','','MH-B24A6ECF','MH-B24A6ECF',7,'expired','2026-05-07 13:00:38','2026-05-07 08:40:38','2026-05-07 10:00:38',NULL,NULL,'hotspot',NULL),(33,'Voucher User','','MH-4056ABC0','MH-4056ABC0',1,'expired','2026-05-07 13:02:44','2026-05-07 15:02:44','2026-05-07 10:02:44',NULL,NULL,'hotspot',NULL),(34,'ron','0704467677','adminy765','ron12233',1,'expired','2026-05-07 13:05:07','2026-05-07 14:05:07','2026-05-07 10:05:07',NULL,NULL,'hotspot',NULL),(35,'Hotspot Client','0714292147','0714292147','2147',12,'expired','2026-05-10 12:14:17','2026-05-10 12:15:17','2026-05-07 11:52:21',NULL,NULL,'hotspot',NULL),(36,'Hotspot Client','0704477699','0704477699','7699',1,'expired','2026-05-07 15:31:54','2026-05-07 16:31:54','2026-05-07 12:31:54',NULL,NULL,'hotspot',NULL),(37,'Voucher User','','MH-970DF910','MH-970DF910',7,'expired','2026-05-07 15:34:08','2026-05-07 11:14:08','2026-05-07 12:34:08',NULL,NULL,'hotspot',NULL),(38,'Hotspot Client','0704919887','0704919887','9887',12,'pending','2026-05-10 12:23:49','2026-05-10 13:23:49','2026-05-07 13:16:07',NULL,NULL,'hotspot',NULL),(41,'Voucher User','','MH-D28C86A0','MH-D28C86A0',1,'expired','2026-05-07 18:42:19','2026-05-07 20:42:19','2026-05-07 15:42:19',NULL,NULL,'hotspot',NULL),(42,'Voucher User','','MH-3B538619','MH-3B538619',2,'expired','2026-05-07 18:44:25','2026-05-07 18:44:25','2026-05-07 15:44:25',NULL,NULL,'hotspot',NULL),(43,'Voucher User','','MH-7E2BEAE1','MH-7E2BEAE1',1,'expired','2026-05-07 18:55:41','2026-05-07 20:55:41','2026-05-07 15:55:41',NULL,NULL,'hotspot',NULL),(44,'Voucher User','','MH-1557410C','MH-1557410C',1,'expired','2026-05-07 19:01:23','2026-05-07 21:01:23','2026-05-07 16:01:23',NULL,NULL,'hotspot',NULL),(45,'Voucher User','','MH-252689AD','MH-252689AD',1,'expired','2026-05-07 19:05:35','2026-05-07 21:05:35','2026-05-07 16:05:35',NULL,NULL,'hotspot',NULL),(46,'Voucher User','','MH-7BDFC673','MH-7BDFC673',1,'expired','2026-05-07 19:09:27','2026-05-07 21:09:27','2026-05-07 16:09:27',NULL,NULL,'hotspot',NULL),(47,'Voucher User','','MH-3B11C6D9','MH-3B11C6D9',1,'expired','2026-05-07 19:11:09','2026-05-07 21:11:09','2026-05-07 16:11:09',NULL,NULL,'hotspot',NULL),(48,'Voucher User','','MH-43D268DA','MH-43D268DA',1,'expired','2026-05-07 19:12:03','2026-05-07 21:12:03','2026-05-07 16:12:03',NULL,NULL,'hotspot',NULL),(49,'Voucher User','','MH-3C2DAB44','MH-3C2DAB44',1,'expired','2026-05-07 20:19:46','2026-05-07 21:19:46','2026-05-07 17:19:46',NULL,NULL,'hotspot',NULL),(50,'Voucher User','','MH-F10F55A5','MH-F10F55A5',1,'expired','2026-05-07 20:25:00','2026-05-07 21:25:00','2026-05-07 17:25:00',NULL,NULL,'hotspot',NULL),(51,'Voucher User','','MH-4FB87F4B','MH-4FB87F4B',1,'expired','2026-05-07 20:41:32','2026-05-07 21:41:32','2026-05-07 17:41:32',NULL,NULL,'hotspot',NULL),(52,'Voucher User','','MH-F15C2A8E','MH-F15C2A8E',1,'expired','2026-05-07 20:43:29','2026-05-07 21:43:29','2026-05-07 17:43:29',NULL,NULL,'hotspot',NULL),(53,'Voucher User','','MH-24B87317','MH-24B87317',1,'expired','2026-05-07 21:37:14','2026-05-07 22:37:14','2026-05-07 18:37:14',NULL,NULL,'hotspot',NULL),(55,'Voucher User','','MH-4E656D08','MH-4E656D08',1,'expired','2026-05-07 23:42:08','2026-05-08 00:42:08','2026-05-07 20:42:08',NULL,NULL,'hotspot',NULL),(56,'Voucher User','','MH-AC4E68E3','MH-AC4E68E3',1,'expired','2026-05-08 00:46:10','2026-05-08 01:46:10','2026-05-07 21:46:10',NULL,NULL,'hotspot',NULL),(57,'Voucher User','','MH-5487FAEB','MH-5487FAEB',5,'expired','2026-05-08 03:49:40','2026-05-09 03:49:40','2026-05-08 00:49:40',NULL,NULL,'hotspot',NULL),(58,'Voucher User','','MH-BE81F04F','MH-BE81F04F',2,'expired','2026-05-08 05:27:08','2026-05-08 08:27:08','2026-05-08 02:27:08',NULL,NULL,'hotspot',NULL),(60,'Hotspot Client','0714673095','0714673095','3095',12,'pending',NULL,NULL,'2026-05-09 15:18:55',NULL,NULL,'hotspot',NULL),(61,'Hotspot Client','254704919887','254704919887','9887',12,'pending','2026-05-19 00:19:51','2026-05-19 05:19:51','2026-05-09 15:21:32','$(ip)',NULL,'hotspot',NULL),(62,'Voucher User','','MH-8C6990BD','MH-8C6990BD',12,'expired','2026-05-10 04:07:46','2026-05-10 04:08:46','2026-05-10 01:07:46',NULL,NULL,'hotspot',NULL),(63,'Voucher User','','MH-67B045FB','MH-67B045FB',12,'expired','2026-05-10 10:41:57','2026-05-10 10:42:57','2026-05-10 07:41:57',NULL,NULL,'hotspot',NULL),(64,'Voucher User','','MH-65741580','MH-65741580',1,'expired','2026-05-10 10:49:21','2026-05-10 11:49:21','2026-05-10 07:49:21',NULL,NULL,'hotspot',NULL),(65,'Voucher User','','MH-ADF8BB3F','MH-ADF8BB3F',5,'expired','2026-05-10 22:01:18','2026-05-11 22:01:18','2026-05-10 19:01:18',NULL,NULL,'hotspot',NULL),(66,'Voucher User','','MH-E5933E98','MH-E5933E98',1,'expired','2026-05-10 22:17:44','2026-05-10 23:17:44','2026-05-10 19:17:44',NULL,NULL,'hotspot',NULL),(67,'Voucher User','','MH-16D9FABB','MH-16D9FABB',1,'expired','2026-05-10 22:22:46','2026-05-10 23:22:46','2026-05-10 19:22:46',NULL,NULL,'hotspot',NULL),(68,'admin -wifi','','admin','1234',8,'active','2026-05-12 01:40:07','2026-06-11 01:40:07','2026-05-11 22:40:07',NULL,NULL,'pppoe',NULL),(69,'test-wifi','','test net','12345',7,'active','2026-05-12 03:02:27','2026-06-11 03:02:27','2026-05-12 00:02:27',NULL,NULL,'pppoe',NULL),(70,'test-wifih','','user','1234',8,'active','2026-05-12 04:15:21','2026-06-11 04:15:21','2026-05-12 00:16:10',NULL,NULL,'pppoe',NULL),(75,'Voucher User','','MH-5E643429','MH-5E643429',5,'expired','2026-05-12 19:44:55','2026-05-13 19:44:55','2026-05-12 16:44:55',NULL,NULL,'hotspot',NULL),(76,'Voucher User','','MH-BACCD936','MH-BACCD936',5,'expired','2026-05-13 00:01:11','2026-05-14 00:01:11','2026-05-12 21:01:11',NULL,NULL,'hotspot',NULL),(77,'Voucher User','','MH-7A878BCF','MH-7A878BCF',5,'expired','2026-05-14 14:57:35','2026-05-15 14:57:35','2026-05-14 11:57:35','192.168.88.199',NULL,'hotspot',NULL),(78,'Voucher User','','MH-4CC875FE','MH-4CC875FE',5,'expired','2026-05-14 20:01:31','2026-05-15 20:01:31','2026-05-14 17:01:31','192.168.88.190',NULL,'hotspot',NULL),(79,'Voucher User','','MH-5D07F976','MH-5D07F976',4,'expired','2026-05-15 10:52:04','2026-05-16 00:52:04','2026-05-15 07:52:04',NULL,NULL,'hotspot',NULL),(92,'Voucher User','','MH-6AFB8D84','MH-6AFB8D84',6,'active','2026-05-15 11:36:39','2026-05-22 11:36:39','2026-05-15 08:36:39','::1',NULL,'hotspot',NULL),(93,'Voucher User','','MH-193EBD23','MH-193EBD23',6,'active','2026-05-15 12:34:03','2026-05-22 12:34:02','2026-05-15 09:34:03','192.168.88.199',NULL,'hotspot',NULL),(94,'Voucher User','','MH-8EFD49CF','MH-8EFD49CF',4,'expired','2026-05-15 13:08:26','2026-05-16 03:08:26','2026-05-15 10:08:26',NULL,NULL,'hotspot',NULL),(95,'Voucher User','','MH-9AC42E9B','MH-9AC42E9B',4,'expired','2026-05-15 13:44:50','2026-05-16 03:44:50','2026-05-15 10:44:50',NULL,NULL,'hotspot',NULL),(100,'Voucher User','','MH-980C29B7','MH-980C29B7',4,'expired','2026-05-15 14:45:26','2026-05-16 04:45:26','2026-05-15 11:45:26','192.168.88.199',NULL,'hotspot',NULL),(103,'Voucher User','','MH-77B9B73C','MH-77B9B73C',4,'expired','2026-05-15 16:06:07','2026-05-16 06:06:07','2026-05-15 13:06:07',NULL,NULL,'hotspot',NULL),(104,'254704467699','254704467699','254704467699','7699',12,'expired','2026-05-19 09:44:59','2026-05-19 14:44:59','2026-05-15 14:45:09','192.168.88.199',NULL,'hotspot',NULL),(107,'Voucher User','','MH-0A4B439F','MH-0A4B439F',2,'expired','2026-05-15 18:01:35','2026-05-15 21:01:35','2026-05-15 15:01:35',NULL,NULL,'hotspot',NULL),(109,'Voucher User','','MH-9CAE960D','MH-9CAE960D',2,'expired','2026-05-15 18:22:05','2026-05-15 21:22:05','2026-05-15 15:22:05',NULL,NULL,'hotspot',NULL),(110,'Voucher User','','MH-C3C3AD0B','MH-C3C3AD0B',2,'expired','2026-05-15 18:52:07','2026-05-15 21:52:07','2026-05-15 15:52:07','$(ip)',NULL,'hotspot',NULL),(114,'Voucher User','','MH-5CA5EE7A','MH-5CA5EE7A',2,'expired','2026-05-15 19:12:24','2026-05-15 22:12:24','2026-05-15 16:12:24',NULL,NULL,'hotspot',NULL),(120,'Voucher User','','MH-9699EECB','MH-9699EECB',1,'expired','2026-05-15 21:42:14','2026-05-15 22:42:14','2026-05-15 18:42:14',NULL,NULL,'hotspot',NULL),(122,'Voucher User','','MH-F1596522','MH-F1596522',5,'expired','2026-05-16 01:00:02','2026-05-17 01:00:01','2026-05-15 22:00:02',NULL,NULL,'hotspot',NULL),(123,'Voucher User','','MH-D1859DC7','MH-D1859DC7',6,'active','2026-05-16 11:25:32','2026-05-23 11:25:31','2026-05-16 08:25:32',NULL,NULL,'hotspot',NULL),(124,'Voucher User','','MH-100C13DB','MH-100C13DB',3,'expired','2026-05-17 00:46:39','2026-05-17 10:46:38','2026-05-16 21:46:39',NULL,NULL,'hotspot',NULL),(125,'Voucher User','','MH-0F8731DF','MH-0F8731DF',1,'expired','2026-05-17 00:51:50','2026-05-17 01:51:49','2026-05-16 21:51:50',NULL,NULL,'hotspot',NULL),(126,'Voucher User','','MH-823BDBC9','MH-823BDBC9',1,'expired','2026-05-17 01:09:06','2026-05-17 02:09:06','2026-05-16 22:09:06',NULL,NULL,'hotspot',NULL),(127,'admin -wifii','','admin5','1234',7,'active','2026-05-17 01:10:25','2026-06-16 01:10:25','2026-05-16 22:10:25',NULL,NULL,'pppoe',NULL),(128,'Voucher User','','MH-14659A66','MH-14659A66',2,'expired','2026-05-17 01:50:09','2026-05-17 04:50:08','2026-05-16 22:50:09',NULL,NULL,'hotspot',NULL),(129,'Voucher User','','MH-A8E60B7E','MH-A8E60B7E',2,'expired','2026-05-17 02:06:00','2026-05-17 05:06:00','2026-05-16 23:06:00',NULL,NULL,'hotspot',NULL),(130,'Voucher User','','MH-53991D6A','MH-53991D6A',1,'expired','2026-05-17 02:14:40','2026-05-17 03:14:40','2026-05-16 23:14:40',NULL,NULL,'hotspot',NULL),(131,'Voucher User','','MH-242ED04D','MH-242ED04D',1,'expired','2026-05-17 03:11:43','2026-05-17 04:11:43','2026-05-17 00:11:43',NULL,NULL,'hotspot',NULL),(132,'Voucher User','','MH-DF7CE60C','MH-DF7CE60C',5,'expired','2026-05-17 04:13:43','2026-05-18 04:13:43','2026-05-17 01:13:43',NULL,NULL,'hotspot',NULL),(133,'254714673095','254714673095','254714673095','3095',12,'expired','2026-05-17 21:32:57','2026-05-18 02:32:57','2026-05-17 14:14:17','$(ip)',NULL,'hotspot',NULL),(134,'Voucher User','','MH-1339F6BF','MH-1339F6BF',1,'expired','2026-05-17 17:30:03','2026-05-17 18:30:03','2026-05-17 14:30:03',NULL,NULL,'hotspot',NULL),(135,'Voucher User','','MH-22A0239A','MH-22A0239A',5,'expired','2026-05-17 21:34:37','2026-05-18 21:34:37','2026-05-17 18:34:37',NULL,NULL,'hotspot',NULL),(136,'Voucher User','','MH-0AC121E8','MH-0AC121E8',1,'expired','2026-05-18 04:49:26','2026-05-18 05:49:26','2026-05-18 01:49:26',NULL,NULL,'hotspot',NULL),(137,'Voucher User','','MH-D17EAED7','MH-D17EAED7',5,'expired','2026-05-18 22:30:23','2026-05-19 22:30:23','2026-05-18 19:30:23',NULL,NULL,'hotspot',NULL),(138,'Voucher User','','MH-F9AE177F','MH-F9AE177F',12,'expired','2026-05-18 23:35:05','2026-05-19 04:35:05','2026-05-18 20:35:05',NULL,NULL,'hotspot',NULL),(139,'Voucher User','','MH-808D5A26','MH-808D5A26',12,'expired','2026-05-18 23:58:36','2026-05-19 04:58:36','2026-05-18 20:58:36',NULL,NULL,'hotspot',NULL),(140,'Voucher User','','MH-7766D521','MH-7766D521',12,'expired','2026-05-19 00:04:35','2026-05-19 05:04:34','2026-05-18 21:04:35',NULL,NULL,'hotspot',NULL),(141,'Voucher User','','MH-C429D58D','MH-C429D58D',12,'expired','2026-05-19 00:17:49','2026-05-19 05:17:49','2026-05-18 21:17:49',NULL,NULL,'hotspot',NULL),(142,'Voucher User','','MH-46ABC1E8','MH-46ABC1E8',12,'expired','2026-05-19 00:45:22','2026-05-19 05:45:22','2026-05-18 21:45:22',NULL,NULL,'hotspot',NULL),(143,'Voucher User','','MH-7449FDD5','MH-7449FDD5',12,'expired','2026-05-19 09:12:11','2026-05-19 14:12:11','2026-05-19 06:12:11',NULL,NULL,'hotspot',NULL),(144,'Voucher User','','MH-2FA5B558','MH-2FA5B558',1,'expired','2026-05-19 09:46:26','2026-05-19 10:46:26','2026-05-19 06:46:26',NULL,NULL,'hotspot',NULL),(145,'Voucher User','','MH-3ABD8703','MH-3ABD8703',1,'expired','2026-05-19 09:46:59','2026-05-19 10:46:58','2026-05-19 06:46:59',NULL,NULL,'hotspot',NULL);
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `companies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `owner_name` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('pending','active','suspended','expired') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `router_ip` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `router_user` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `router_pass` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `router_port` varchar(20) COLLATE utf8mb4_general_ci DEFAULT '8728',
  `router_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `router_connected` tinyint DEFAULT '1',
  `online_status` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'offline',
  `last_seen` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `companies`
--

LOCK TABLES `companies` WRITE;
/*!40000 ALTER TABLE `companies` DISABLE KEYS */;
INSERT INTO `companies` VALUES (1,'HAKIM WIFI','MORGAN NGAASI','0704919887','moganhakim2@gmail.com','pending','2026-05-16 13:56:40',NULL,NULL,NULL,'8728',NULL,0,'online','2026-05-19 16:15:19'),(3,'HAKIM WIFII','MORGAN NGAASI','0704467699','moganhakim@gmail.com','active','2026-05-16 16:29:43',NULL,NULL,NULL,'8728',NULL,0,'offline',NULL),(4,'Hakim LTD','Hakim James','0700000001','mogangaasi@gmail.com','active','2026-05-17 03:16:48',NULL,NULL,NULL,'8728',NULL,0,'offline',NULL),(5,'Hakim LTD','Hakim James','0700000001','moganngaasi@gmil.com','active','2026-05-17 03:18:52',NULL,NULL,NULL,'8728',NULL,0,'offline',NULL),(6,'Hakim LTD','Hakim James','0700000001','moganngaasi@gmil.com','active','2026-05-17 03:31:40',NULL,NULL,NULL,'8728',NULL,0,'offline',NULL),(7,'Demo Tenant','Tenant Owner','0700000000','tenant@example.com','active','2026-05-17 03:44:07',NULL,NULL,NULL,'8728',NULL,0,'offline',NULL),(8,'Hakim LTDD','MORGAN NGAASii','0704919888','moganhakim@gmail.com','active','2026-05-17 14:50:23',NULL,NULL,NULL,'8728',NULL,0,'offline',NULL),(9,'Hakim LTD7','MORGAN NGAASII','0704919889','moganhakim3@gmail.com','active','2026-05-17 15:08:23',NULL,NULL,NULL,'8728',NULL,0,'offline',NULL),(10,'Hakim LTDD9','MORGAN NGAAS','0704919882','moganhakim1@gmail.com','active','2026-05-17 16:12:20',NULL,NULL,NULL,'8728',NULL,0,'offline',NULL),(11,'Hakim LTDC','MORGAN NGAASI','0704919887','moganhakim4@gmail.com','active','2026-05-17 16:16:32',NULL,NULL,NULL,'8728',NULL,0,'offline',NULL);
/*!40000 ALTER TABLE `companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `connected_routers`
--

DROP TABLE IF EXISTS `connected_routers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `connected_routers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `device_name` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ip_address` varchar(60) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mac_address` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `device_type` varchar(80) COLLATE utf8mb4_general_ci DEFAULT 'Router/AP',
  `location` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_general_ci,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `connected_routers`
--

LOCK TABLES `connected_routers` WRITE;
/*!40000 ALTER TABLE `connected_routers` DISABLE KEYS */;
INSERT INTO `connected_routers` VALUES (1,'techbuilder-Latitude-E7240','192.168.88.199','D8:FC:93:74:23:6D','Router/AP',NULL,NULL,'bound','2026-05-19 21:42:08');
/*!40000 ALTER TABLE `connected_routers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coverage_zones`
--

DROP TABLE IF EXISTS `coverage_zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coverage_zones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `zone_name` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `location` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ap_name` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `signal_notes` text COLLATE utf8mb4_general_ci,
  `status` enum('active','planned','maintenance') COLLATE utf8mb4_general_ci DEFAULT 'planned',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coverage_zones`
--

LOCK TABLES `coverage_zones` WRITE;
/*!40000 ALTER TABLE `coverage_zones` DISABLE KEYS */;
/*!40000 ALTER TABLE `coverage_zones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expenses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `amount` decimal(10,2) DEFAULT '0.00',
  `expense_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expiry_rules`
--

DROP TABLE IF EXISTS `expiry_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expiry_rules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int DEFAULT NULL,
  `action_type` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `grace_minutes` int DEFAULT '0',
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `executed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expiry_rules`
--

LOCK TABLES `expiry_rules` WRITE;
/*!40000 ALTER TABLE `expiry_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `expiry_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hotspot_packages`
--

DROP TABLE IF EXISTS `hotspot_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotspot_packages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `duration_hours` int DEFAULT NULL,
  `speed_down` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `speed_up` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hotspot_packages`
--

LOCK TABLES `hotspot_packages` WRITE;
/*!40000 ALTER TABLE `hotspot_packages` DISABLE KEYS */;
INSERT INTO `hotspot_packages` VALUES (1,'1 Hour',1,'8M','2M',10.00,'active'),(2,'3 Hours',3,'8M','2M',20.00,'active'),(3,'10 Hours',10,'8M','2M',30.00,'active'),(4,'15 Hours',15,'10M','3M',40.00,'active'),(5,'24 Hours',24,'8M','2M',50.00,'active'),(6,'7 Days',168,'5M','2M',250.00,'active'),(7,'Monthly 10Mbps',720,'10M','3M',600.00,'active'),(8,'Monthly 20Mbps',720,'20M','5M',1000.00,'active');
/*!40000 ALTER TABLE `hotspot_packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hotspot_payments`
--

DROP TABLE IF EXISTS `hotspot_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotspot_payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `phone` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `package_id` int DEFAULT NULL,
  `username` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `checkout_request_id` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mpesa_receipt` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('pending','paid','failed') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `client_ip` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `paid_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hotspot_payments`
--

LOCK TABLES `hotspot_payments` WRITE;
/*!40000 ALTER TABLE `hotspot_payments` DISABLE KEYS */;
INSERT INTO `hotspot_payments` VALUES (1,'254704467699',10.00,1,NULL,NULL,'ws_CO_20052026054216471704467699',NULL,'pending','127.0.0.1','2026-05-20 02:42:16',NULL,NULL),(2,'254704467699',10.00,1,NULL,NULL,'ws_CO_20052026055355504704467699',NULL,'failed','127.0.0.1','2026-05-20 02:53:55',NULL,NULL),(3,'254704467699',10.00,1,NULL,NULL,'ws_CO_20052026055727739704467699',NULL,'failed','127.0.0.1','2026-05-20 02:57:28',NULL,NULL),(4,'254704467699',1.00,1,'254704467699','254704467699','ws_CO_20052026110523211704467699',NULL,'pending','127.0.0.1','2026-05-20 08:05:24',NULL,NULL),(5,'254704467699',1.00,1,'254704467699','254704467699','ws_CO_20052026111051625704467699',NULL,'pending','127.0.0.1','2026-05-20 08:10:51',NULL,NULL),(6,'254704467699',1.00,1,'254704467699','254704467699','ws_CO_20052026111146217704467699','TESTRECEIPT','paid','127.0.0.1','2026-05-20 08:11:47','2026-05-20 11:29:24',NULL),(7,'254704467699',1.00,1,'254704467699','254704467699','ws_CO_20052026113841358704467699',NULL,'failed','127.0.0.1','2026-05-20 08:38:41',NULL,NULL),(8,'254704467699',1.00,1,'254704467699','254704467699','ws_CO_20052026115540308704467699',NULL,'failed','127.0.0.1','2026-05-20 08:55:40',NULL,NULL),(9,'254704467699',1.00,1,'254704467699','254704467699','ws_CO_20052026115852041704467699','UEK2B55KPN','paid','127.0.0.1','2026-05-20 08:58:53','2026-05-20 11:59:19',NULL),(10,'254704919887',1.00,12,'254704919887','254704919887','ws_CO_20052026120854990704919887',NULL,'failed','192.168.88.179','2026-05-20 09:08:54',NULL,NULL),(11,'254704467699',1.00,12,'254704467699','254704467699','ws_CO_20052026120923572704467699',NULL,'failed','192.168.88.199','2026-05-20 09:09:24',NULL,NULL),(12,'254704467699',1.00,12,'254704467699','254704467699','ws_CO_20052026120945878704467699',NULL,'failed','192.168.88.199','2026-05-20 09:09:45',NULL,NULL),(13,'254704919887',10.00,1,'254704919887','254704919887','ws_CO_20052026121312408704919887','UEKD34UXNS','paid','192.168.88.179','2026-05-20 09:13:12','2026-05-20 12:13:24',NULL),(14,'254704467699',1.00,12,'254704467699','254704467699','ws_CO_20052026121519332704467699',NULL,'failed','192.168.88.179','2026-05-20 09:15:19',NULL,NULL),(15,'254704467699',10.00,1,'254704467699','254704467699','ws_CO_20052026122125911704467699',NULL,'failed','192.168.88.199','2026-05-20 09:21:26',NULL,NULL),(16,'254704467699',1.00,12,'254704467699','254704467699','ws_CO_20052026123157397704467699','UEK2B55M95','paid','192.168.88.179','2026-05-20 09:31:58','2026-05-20 12:32:20',NULL),(17,'254704919887',1.00,15,'254704919887','254704919887','ws_CO_20052026124628460704919887','UEKD34V3VR','paid','192.168.88.179','2026-05-20 09:46:28','2026-05-20 12:46:39',NULL),(18,'254704919887',1.00,12,'254704919887','254704919887','ws_CO_20052026130406647704919887','UEKD34VAQX','paid','192.168.88.179','2026-05-20 10:04:07','2026-05-20 13:04:17',NULL),(19,'254704919887',1.00,14,'254704919887','254704919887','ws_CO_20052026132344710704919887','UEKD34V9KB','paid','192.168.88.179','2026-05-20 10:23:44','2026-05-20 13:23:57',NULL),(20,'254704919887',1.00,14,'254704919887','254704919887','ws_CO_20052026133624652704919887','UEKD34VBCN','paid','192.168.88.179','2026-05-20 10:36:25','2026-05-20 13:36:39',NULL),(21,'254704467699',1.00,12,'254704467699','254704467699','ws_CO_20052026145433863704467699',NULL,'failed','192.168.88.199','2026-05-20 11:54:33',NULL,NULL),(22,'254704467699',1.00,15,'254704467699','254704467699','ws_CO_20052026172103647704467699',NULL,'pending','192.168.88.199','2026-05-20 14:21:04',NULL,NULL),(23,'254704467699',1.00,15,'254704467699','254704467699','ws_CO_20052026172124013704467699',NULL,'pending','192.168.88.199','2026-05-20 14:21:24',NULL,NULL),(24,'254704467699',1.00,15,'254704467699','254704467699','ws_CO_20052026172500091704467699',NULL,'pending','192.168.88.177','2026-05-20 14:25:00',NULL,NULL),(25,'254704467699',1.00,15,'254704467699','254704467699','ws_CO_20052026173846174704467699',NULL,'pending','192.168.88.177','2026-05-20 14:38:47',NULL,NULL),(26,'254704919887',1.00,15,'254704919887','254704919887','ws_CO_20052026173957599704919887',NULL,'pending','192.168.88.177','2026-05-20 14:39:57',NULL,NULL),(27,'254704919887',1.00,15,'254704919887','254704919887','ws_CO_20052026175326048704919887',NULL,'pending','192.168.88.177','2026-05-20 14:53:27',NULL,NULL),(28,'254704919887',1.00,15,'254704919887','254704919887','ws_CO_20052026175450770704919887',NULL,'pending','192.168.88.177','2026-05-20 14:54:50',NULL,NULL),(29,'254704467699',1.00,15,'254704467699','254704467699','ws_CO_20052026190336748704467699',NULL,'pending','192.168.88.177','2026-05-20 16:03:36',NULL,NULL),(30,'254704919887',1.00,15,'254704919887','254704919887','ws_CO_20052026190420734704919887',NULL,'pending','192.168.88.177','2026-05-20 16:04:21',NULL,NULL),(31,'254704919887',1.00,15,'254704919887','254704919887','ws_CO_20052026193901268704919887',NULL,'pending','192.168.88.177','2026-05-20 16:39:01',NULL,NULL),(32,'254704467699',10.00,1,'254704467699','254704467699','ws_CO_20052026194949582704467699',NULL,'pending','192.168.88.199','2026-05-20 16:49:50',NULL,NULL),(33,'254704919887',1.00,15,'254704919887','254704919887','ws_CO_20052026195048585704919887',NULL,'pending','192.168.88.199','2026-05-20 16:50:49',NULL,NULL),(34,'254704467699',20.00,2,'254704467699','254704467699','ws_CO_20052026195125569704467699',NULL,'pending','192.168.88.199','2026-05-20 16:51:25',NULL,NULL),(35,'254704467699',1.00,15,'254704467699','254704467699','ws_CO_20052026195137649704467699',NULL,'pending','192.168.88.199','2026-05-20 16:51:38',NULL,NULL),(36,'254704919887',1.00,15,'254704919887','254704919887','ws_CO_20052026195150672704919887',NULL,'pending','192.168.88.199','2026-05-20 16:51:50',NULL,NULL),(37,'254704467699',10.00,1,'254704467699','254704467699','ws_CO_20052026195815842704467699',NULL,'pending','192.168.88.199','2026-05-20 16:58:15',NULL,NULL),(38,'254704919887',1.00,15,'254704919887','254704919887','ws_CO_20052026201253735704919887','UEKD34XCHM','paid','192.168.88.199','2026-05-20 17:12:53','2026-05-20 20:13:03',NULL),(39,'254704919887',1.00,15,'254704919887','254704919887','ws_CO_20052026202107981704919887','UEKD34XFQ7','paid','192.168.88.179','2026-05-20 17:21:08','2026-05-20 20:21:20',NULL),(40,'254704467699',1.00,15,'254704467699','254704467699','ws_CO_20052026231354518704467699',NULL,'failed','192.168.88.177','2026-05-20 20:13:55',NULL,NULL),(41,'254704919887',1.00,15,'254704919887','254704919887','ws_CO_20052026231417811704919887','UEKD34XZHP','paid','192.168.88.177','2026-05-20 20:14:17','2026-05-20 23:14:47',NULL),(42,'254704919887',1.00,15,'254704919887','254704919887','ws_CO_20052026232753060704919887','UEKD34XVYK','paid','192.168.88.177','2026-05-20 20:27:53','2026-05-20 23:28:23','2026-05-21 00:28:23'),(43,'254704919887',1.00,15,'254704919887','254704919887','ws_CO_20052026233832070704919887','UEKD34XT0B','paid','192.168.88.177','2026-05-20 20:38:32','2026-05-20 23:39:12','2026-05-21 00:39:11'),(44,'254704919887',1.00,15,'254704919887','254704919887','ws_CO_21052026012302151704919887',NULL,'pending','192.168.88.179','2026-05-20 22:23:02',NULL,NULL),(45,'254704919887',1.00,15,'254704919887','254704919887','ws_CO_21052026020010079704919887',NULL,'pending','::1','2026-05-20 23:00:10',NULL,NULL),(46,'254704919887',1.00,15,'254704919887','254704919887','ws_CO_21052026020318310704919887',NULL,'pending','::1','2026-05-20 23:03:18',NULL,NULL),(47,'254704919887',1.00,15,'254704919887','254704919887','ws_CO_21052026021952831704919887',NULL,'pending','192.168.88.199','2026-05-20 23:19:53',NULL,NULL),(48,'254704919887',1.00,15,'254704919887','254704919887','ws_CO_21052026023028395704919887',NULL,'pending','192.168.88.199','2026-05-20 23:30:28',NULL,NULL),(49,'254704919887',1.00,15,'254704919887','254704919887','ws_CO_21052026023440725704919887',NULL,'pending','192.168.88.179','2026-05-20 23:34:40',NULL,NULL),(50,'254704919887',1.00,15,'254704919887','254704919887','ws_CO_21052026030101229704919887',NULL,'pending','192.168.88.179','2026-05-21 00:01:02',NULL,NULL),(51,'254704919887',1.00,15,'254704919887','254704919887','ws_CO_21052026030718932704919887',NULL,'pending','192.168.88.179','2026-05-21 00:07:19',NULL,NULL),(52,'254704919887',1.00,15,'254704919887','254704919887','ws_CO_21052026031330383704919887',NULL,'pending','192.168.88.179','2026-05-21 00:13:30',NULL,NULL),(53,'254704919887',1.00,15,'254704919887','254704919887','ws_CO_21052026031814130704919887',NULL,'pending','192.168.88.179','2026-05-21 00:18:14',NULL,NULL),(54,'254704919887',1.00,15,'254704919887','254704919887','ws_CO_21052026031930779704919887',NULL,'pending','192.168.88.179','2026-05-21 00:19:31',NULL,NULL);
/*!40000 ALTER TABLE `hotspot_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `installations`
--

DROP TABLE IF EXISTS `installations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `installations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_name` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `location` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `package_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `installation_fee` decimal(10,2) DEFAULT '0.00',
  `technician` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('pending','assigned','installed','cancelled') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `installations`
--

LOCK TABLES `installations` WRITE;
/*!40000 ALTER TABLE `installations` DISABLE KEYS */;
/*!40000 ALTER TABLE `installations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `isps`
--

DROP TABLE IF EXISTS `isps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `isps` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `subscription_status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `isps`
--

LOCK TABLES `isps` WRITE;
/*!40000 ALTER TABLE `isps` DISABLE KEYS */;
/*!40000 ALTER TABLE `isps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mikrotik_settings`
--

DROP TABLE IF EXISTS `mikrotik_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mikrotik_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `router_ip` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `router_username` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `router_password` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `api_port` int DEFAULT '8728',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mikrotik_settings`
--

LOCK TABLES `mikrotik_settings` WRITE;
/*!40000 ALTER TABLE `mikrotik_settings` DISABLE KEYS */;
INSERT INTO `mikrotik_settings` VALUES (10,'10.10.10.1','mhakimapi','12345678',8728,'2026-05-20 20:03:11');
/*!40000 ALTER TABLE `mikrotik_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mpesa_settings`
--

DROP TABLE IF EXISTS `mpesa_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mpesa_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `environment` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'sandbox',
  `consumer_key` text COLLATE utf8mb4_general_ci,
  `consumer_secret` text COLLATE utf8mb4_general_ci,
  `shortcode` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `passkey` text COLLATE utf8mb4_general_ci,
  `transaction_type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'CustomerPayBillOnline',
  `till_number` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `store_number` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `operator_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `callback_url` text COLLATE utf8mb4_general_ci,
  `status` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mpesa_settings`
--

LOCK TABLES `mpesa_settings` WRITE;
/*!40000 ALTER TABLE `mpesa_settings` DISABLE KEYS */;
INSERT INTO `mpesa_settings` VALUES (4,'production','KQPJL2ixkv3GtqQhsACLXXjA3Kvobw7oEKlXPaSVQ9BgTzdw','d3dO5bVvyEkFRwSKfrsS9mj8BZibwxXltPMH8YvTvqxkQa9obRo7NGC0EDNyrMab','4579315','91ef4ad533edc28ab26ade1311a2bb8e9b5ba3ece7c78fa057c7fdbb3461aefa','CustomerBuyGoodsOnline','8713916','6466060',NULL,'https://pancreas-remix-jelly.ngrok-free.dev/mhakim-hotspot/callback.php','active','2026-05-20 02:33:23');
/*!40000 ALTER TABLE `mpesa_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `network_alerts`
--

DROP TABLE IF EXISTS `network_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `network_alerts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `alert_type` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `title` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_general_ci,
  `severity` enum('info','warning','critical') COLLATE utf8mb4_general_ci DEFAULT 'info',
  `status` enum('new','seen','resolved') COLLATE utf8mb4_general_ci DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `network_alerts`
--

LOCK TABLES `network_alerts` WRITE;
/*!40000 ALTER TABLE `network_alerts` DISABLE KEYS */;
/*!40000 ALTER TABLE `network_alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `noc_alerts`
--

DROP TABLE IF EXISTS `noc_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `noc_alerts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `alert_type` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('new','seen','resolved') COLLATE utf8mb4_general_ci DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `noc_alerts`
--

LOCK TABLES `noc_alerts` WRITE;
/*!40000 ALTER TABLE `noc_alerts` DISABLE KEYS */;
/*!40000 ALTER TABLE `noc_alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `packages`
--

DROP TABLE IF EXISTS `packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `packages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `duration_hours` decimal(10,2) NOT NULL,
  `speed_down` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `speed_up` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `company_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `packages`
--

LOCK TABLES `packages` WRITE;
/*!40000 ALTER TABLE `packages` DISABLE KEYS */;
INSERT INTO `packages` VALUES (1,'1 Hour',1.00,'2M','6M',10.00,'active','2026-05-06 08:01:59',NULL),(2,'3 Hours',3.00,'3M','6M',20.00,'active','2026-05-06 08:01:59',NULL),(3,'10 Hours',10.00,'3M','6M',30.00,'active','2026-05-06 08:01:59',NULL),(4,'14 Hours',14.00,'3M','6M',40.00,'active','2026-05-06 08:01:59',NULL),(5,'24 Hours',24.00,'3M','6M',50.00,'active','2026-05-06 08:01:59',NULL),(6,'7 Days',168.00,'3M','6M',250.00,'active','2026-05-06 08:01:59',NULL),(7,'Monthly 10Mbps',720.00,'5M','10M',600.00,'active','2026-05-06 08:01:59',NULL),(8,'Monthly 15Mbps',720.00,'7M','15M',1000.00,'active','2026-05-06 08:01:59',NULL),(15,'admin',1.00,'3M','7M',1.00,'active','2026-05-20 09:30:40',NULL);
/*!40000 ALTER TABLE `packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int DEFAULT NULL,
  `package_id` int DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'cash',
  `reference` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime DEFAULT NULL,
  `checkout_id` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mpesa_receipt` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `client_ip` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `client_mac` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_payments_status` (`status`),
  KEY `idx_payments_checkout` (`checkout_id`)
) ENGINE=InnoDB AUTO_INCREMENT=291 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,NULL,7,600.00,'cash','','paid','2026-05-06 13:12:04',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(2,7,6,250.00,'cash','','paid','2026-05-06 13:17:02',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(3,NULL,2,700.00,'voucher','','paid','2026-05-06 13:46:25',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(4,12,3,30.00,'mpesa','','paid','2026-05-06 19:58:28',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(5,13,1,10.00,'mpesa','SIMULATED-STK-1778131958','paid','2026-05-07 05:32:38',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(6,15,1,10.00,'mpesa','SIMULATED-STK-1778132056','paid','2026-05-07 05:34:16',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(7,16,3,30.00,'mpesa','SIMULATED-STK-1778132366','paid','2026-05-07 05:39:26',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(8,18,3,30.00,'mpesa','SIMULATED-STK-1778132627','paid','2026-05-07 05:43:47',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(9,20,9,1.00,'mpesa','SIMULATED-STK-1778134353','paid','2026-05-07 06:12:33',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(10,22,3,30.00,'mpesa','SIMULATED-STK-1778137027','paid','2026-05-07 06:57:07',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(11,20,1,10.00,'mpesa','SIMULATED-STK-1778137054','paid','2026-05-07 06:57:34',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(12,22,3,30.00,'mpesa','SIMULATED-STK-1778137085','paid','2026-05-07 06:58:05',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(13,22,3,30.00,'mpesa','SIMULATED-STK-1778137103','paid','2026-05-07 06:58:23',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(14,27,11,1.00,'mpesa','SIMULATED-STK-1778139636','paid','2026-05-07 07:40:36',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(15,28,2,20.00,'mpesa','SIMULATED-STK-1778139737','paid','2026-05-07 07:42:17',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(16,29,1,10.00,'mpesa','SIMULATED-STK-1778141134','paid','2026-05-07 08:05:34',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(17,30,11,1.00,'mpesa','SIMULATED-STK-1778141236','paid','2026-05-07 08:07:16',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(18,31,11,1.00,'mpesa','SIMULATED-STK-1778142929','paid','2026-05-07 08:35:29',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(19,NULL,8,3000.00,'cash','','paid','2026-05-07 09:01:41',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(20,32,7,600.00,'voucher','MH-B24A6ECF','paid','2026-05-07 10:00:38',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(21,33,1,10.00,'voucher','MH-4056ABC0','paid','2026-05-07 10:02:44',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(22,35,1,10.00,'mpesa','SIMULATED-STK-1778154741','paid','2026-05-07 11:52:21',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(23,36,1,10.00,'mpesa','SIMULATED-STK-1778157114','paid','2026-05-07 12:31:54',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(24,37,7,600.00,'voucher','MH-970DF910','paid','2026-05-07 12:34:08',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(25,38,1,10.00,'mpesa','SIMULATED-STK-1778159767','paid','2026-05-07 13:16:07',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(26,13,1,10.00,'mpesa','SIMULATED-STK-1778159791','paid','2026-05-07 13:16:31',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(29,41,1,10.00,'voucher','MH-D28C86A0','paid','2026-05-07 15:42:19',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(30,42,2,20.00,'voucher','MH-3B538619','paid','2026-05-07 15:44:25',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(31,43,1,10.00,'voucher','MH-7E2BEAE1','paid','2026-05-07 15:55:41',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(32,44,1,10.00,'voucher','MH-1557410C','paid','2026-05-07 16:01:23',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(33,45,1,10.00,'voucher','MH-252689AD','paid','2026-05-07 16:05:35',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(34,46,1,10.00,'voucher','MH-7BDFC673','paid','2026-05-07 16:09:27',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(35,47,1,10.00,'voucher','MH-3B11C6D9','paid','2026-05-07 16:11:09',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(36,48,1,10.00,'voucher','MH-43D268DA','paid','2026-05-07 16:12:03',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(37,49,1,10.00,'voucher','MH-3C2DAB44','paid','2026-05-07 17:19:46',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(38,50,1,10.00,'voucher','MH-F10F55A5','paid','2026-05-07 17:25:00',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(39,51,1,10.00,'voucher','MH-4FB87F4B','paid','2026-05-07 17:41:32',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(40,52,1,10.00,'voucher','MH-F15C2A8E','paid','2026-05-07 17:43:29',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(41,53,1,10.00,'voucher','MH-24B87317','paid','2026-05-07 18:37:14',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(42,54,1,10.00,'voucher','MH-98576D2F','paid','2026-05-07 19:53:36',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(43,38,NULL,1000.00,'cash','','paid','2026-05-07 19:54:54',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(44,13,5,50.00,'mpesa','DEMO-STK-1778185113','paid','2026-05-07 20:18:33',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(45,13,2,20.00,'mpesa','DEMO-STK-1778185450','paid','2026-05-07 20:24:10',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(46,13,1,10.00,'mpesa','DEMO-STK-1778185881','paid','2026-05-07 20:31:21',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(47,13,1,10.00,'mpesa','DEMO-STK-1778185934','paid','2026-05-07 20:32:14',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(48,13,1,10.00,'mpesa','DEMO-STK-1778185935','paid','2026-05-07 20:32:15',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(49,13,7,600.00,'mpesa','DEMO-STK-1778185970','paid','2026-05-07 20:32:50',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(50,13,8,1000.00,'mpesa','DEMO-STK-1778185998','paid','2026-05-07 20:33:18',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(51,38,1,10.00,'mpesa','DEMO-STK-1778186055','paid','2026-05-07 20:34:15',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(52,55,1,10.00,'voucher','MH-4E656D08','paid','2026-05-07 20:42:08',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(53,56,1,10.00,'voucher','MH-AC4E68E3','paid','2026-05-07 21:46:11',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(54,13,6,250.00,'mpesa','ws_CO_08052026021621116704467699','failed','2026-05-07 23:16:21',NULL,'ws_CO_08052026021621116704467699',NULL,'254704467699',NULL,NULL,NULL),(55,13,12,1.00,'mpesa','ws_CO_08052026021748923704467699','paid','2026-05-07 23:17:49',NULL,'ws_CO_08052026021748923704467699','UE82B3R8MM','254704467699',NULL,NULL,NULL),(56,13,12,1.00,'mpesa','ws_CO_08052026022057005704467699','paid','2026-05-07 23:20:57',NULL,'ws_CO_08052026022057005704467699','UE82B3RGN1','254704467699',NULL,NULL,NULL),(57,13,12,1.00,'mpesa','ws_CO_08052026022626077704467699','paid','2026-05-07 23:26:26',NULL,'ws_CO_08052026022626077704467699','UE82B3RGNF','254704467699',NULL,NULL,NULL),(58,13,12,1.00,'mpesa','ws_CO_08052026030436844704467699','failed','2026-05-08 00:04:37',NULL,'ws_CO_08052026030436844704467699',NULL,'254704467699',NULL,NULL,NULL),(59,38,12,1.00,'mpesa','ws_CO_08052026030551739704919887','paid','2026-05-08 00:05:52',NULL,'ws_CO_08052026030551739704919887','UE8D33HF8S','254704919887',NULL,NULL,NULL),(60,38,12,1.00,'mpesa','ws_CO_08052026032310635704919887','paid','2026-05-08 00:23:11',NULL,'ws_CO_08052026032310635704919887','UE8D33HHTY','254704919887',NULL,NULL,NULL),(61,38,12,1.00,'mpesa','ws_CO_08052026032726992704919887','paid','2026-05-08 00:27:27',NULL,'ws_CO_08052026032726992704919887','UE8D33HF9L','254704919887',NULL,NULL,NULL),(62,38,12,1.00,'mpesa','ws_CO_08052026032811759704919887','failed','2026-05-08 00:28:12',NULL,'ws_CO_08052026032811759704919887',NULL,'254704919887',NULL,NULL,NULL),(63,38,12,1.00,'mpesa','ws_CO_08052026032932403704919887','paid','2026-05-08 00:29:32',NULL,'ws_CO_08052026032932403704919887','UE8D33HKQ8','254704919887',NULL,NULL,NULL),(64,38,12,1.00,'mpesa','ws_CO_08052026033352139704919887','paid','2026-05-08 00:33:52',NULL,'ws_CO_08052026033352139704919887','UE8D33HKQG','254704919887',NULL,NULL,NULL),(65,38,12,1.00,'mpesa','ws_CO_08052026033938525704919887','paid','2026-05-08 00:39:38',NULL,'ws_CO_08052026033938525704919887','UE8D33HKQW','254704919887',NULL,NULL,NULL),(66,57,5,50.00,'voucher','MH-5487FAEB','paid','2026-05-08 00:49:40',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(67,13,12,1.00,'mpesa','ws_CO_08052026051432735704467699','pending','2026-05-08 02:14:33',NULL,'ws_CO_08052026051432735704467699',NULL,'254704467699',NULL,NULL,NULL),(68,38,12,1.00,'mpesa','ws_CO_08052026051455616704919887','pending','2026-05-08 02:14:55',NULL,'ws_CO_08052026051455616704919887',NULL,'254704919887',NULL,NULL,NULL),(69,38,12,1.00,'mpesa','ws_CO_08052026051504641704919887','pending','2026-05-08 02:15:04',NULL,'ws_CO_08052026051504641704919887',NULL,'254704919887',NULL,NULL,NULL),(70,38,12,1.00,'mpesa','ws_CO_08052026051536158704919887','pending','2026-05-08 02:15:36',NULL,'ws_CO_08052026051536158704919887',NULL,'254704919887',NULL,NULL,NULL),(71,38,12,1.00,'mpesa','ws_CO_08052026051803357704919887','pending','2026-05-08 02:18:03',NULL,'ws_CO_08052026051803357704919887',NULL,'254704919887',NULL,NULL,NULL),(72,58,2,20.00,'voucher','MH-BE81F04F','paid','2026-05-08 02:27:08',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(73,38,12,1.00,'mpesa','ws_CO_08052026053959485704919887','pending','2026-05-08 02:39:59',NULL,'ws_CO_08052026053959485704919887',NULL,'254704919887',NULL,NULL,NULL),(74,38,12,1.00,'mpesa','ws_CO_08052026055417967704919887','pending','2026-05-08 02:54:18',NULL,'ws_CO_08052026055417967704919887',NULL,'254704919887',NULL,NULL,NULL),(75,38,12,1.00,'mpesa','ws_CO_08052026055611711704919887','paid','2026-05-08 02:56:11',NULL,'ws_CO_08052026055611711704919887','UE8D33HMHJ','254704919887',NULL,NULL,NULL),(79,38,12,1.00,'mpesa','ws_CO_08052026191122617704919887','paid','2026-05-08 16:11:23',NULL,'ws_CO_08052026191122617704919887','UE8D33KJV0','254704919887',NULL,NULL,NULL),(80,38,12,1.00,'mpesa','STK-1778263822','paid','2026-05-08 18:10:24',NULL,'STK-1778263822',NULL,'254704919887',NULL,NULL,NULL),(81,38,12,1.00,'mpesa','STK-1778263850','paid','2026-05-08 18:10:55',NULL,'STK-1778263850',NULL,'254704919887',NULL,NULL,NULL),(82,38,12,1.00,'mpesa','ws_CO_08052026211326215704919887','paid','2026-05-08 18:13:26',NULL,'ws_CO_08052026211326215704919887',NULL,'254704919887',NULL,NULL,NULL),(83,13,12,1.00,'mpesa','ws_CO_08052026224101316704467699','paid','2026-05-08 19:41:01',NULL,'ws_CO_08052026224101316704467699',NULL,'254704467699',NULL,NULL,NULL),(84,59,12,1.00,'mpesa','STK-1778269789','paid','2026-05-08 19:49:50',NULL,'STK-1778269789',NULL,'25470491987',NULL,NULL,NULL),(85,38,12,1.00,'mpesa','ws_CO_08052026225103041704919887','paid','2026-05-08 19:51:03',NULL,'ws_CO_08052026225103041704919887',NULL,'254704919887',NULL,NULL,NULL),(86,38,12,1.00,'mpesa','ws_CO_08052026225602559704919887','paid','2026-05-08 19:56:02',NULL,'ws_CO_08052026225602559704919887',NULL,'254704919887',NULL,NULL,NULL),(87,38,12,1.00,'mpesa','ws_CO_08052026230007571704919887','paid','2026-05-08 20:00:07',NULL,'ws_CO_08052026230007571704919887',NULL,'254704919887',NULL,NULL,NULL),(88,38,12,1.00,'mpesa','ws_CO_08052026232820716704919887','paid','2026-05-08 20:28:20',NULL,'ws_CO_08052026232820716704919887',NULL,'254704919887',NULL,NULL,NULL),(89,38,12,1.00,'mpesa','ws_CO_08052026232854233704919887','paid','2026-05-08 20:28:54',NULL,'ws_CO_08052026232854233704919887',NULL,'254704919887',NULL,NULL,NULL),(90,38,12,1.00,'mpesa','ws_CO_08052026232922851704919887','paid','2026-05-08 20:29:23',NULL,'ws_CO_08052026232922851704919887',NULL,'254704919887',NULL,NULL,NULL),(91,38,12,1.00,'mpesa','ws_CO_08052026233022639704919887','paid','2026-05-08 20:30:23',NULL,'ws_CO_08052026233022639704919887',NULL,'254704919887',NULL,NULL,NULL),(92,13,12,1.00,'mpesa','ws_CO_09052026104141113704467699','pending','2026-05-09 07:41:41',NULL,'ws_CO_09052026104141113704467699',NULL,'254704467699',NULL,NULL,NULL),(93,38,12,1.00,'mpesa','ws_CO_09052026104252587704919887','pending','2026-05-09 07:42:52',NULL,'ws_CO_09052026104252587704919887',NULL,'254704919887',NULL,NULL,NULL),(94,38,12,1.00,'mpesa','ws_CO_09052026104544474704919887','pending','2026-05-09 07:45:44',NULL,'ws_CO_09052026104544474704919887',NULL,'254704919887',NULL,NULL,NULL),(95,38,1,10.00,'mpesa','ws_CO_09052026142458890704919887','paid','2026-05-09 11:24:59',NULL,'ws_CO_09052026142458890704919887','UE9D33NET1','254704919887',NULL,NULL,NULL),(96,38,12,1.00,'mpesa','ws_CO_09052026145314819704919887','paid','2026-05-09 11:53:15',NULL,'ws_CO_09052026145314819704919887','UE9D33NKTS','254704919887',NULL,NULL,NULL),(97,13,12,1.00,'mpesa','ws_CO_09052026151513766704467699','paid','2026-05-09 12:15:14',NULL,'ws_CO_09052026151513766704467699','UE92B3XJWN','254704467699',NULL,NULL,NULL),(98,38,12,1.00,'mpesa','ws_CO_09052026152027490704919887','paid','2026-05-09 12:20:27',NULL,'ws_CO_09052026152027490704919887','UE9D33NQBJ','254704919887',NULL,NULL,NULL),(99,38,1,10.00,'mpesa','ws_CO_09052026152335298704919887','paid','2026-05-09 12:23:35',NULL,'ws_CO_09052026152335298704919887','UE9D33NMSC','254704919887',NULL,NULL,NULL),(100,60,12,1.00,'mpesa','ws_CO_09052026181854762714673095','pending','2026-05-09 15:18:55',NULL,'ws_CO_09052026181854762714673095',NULL,'254714673095',NULL,NULL,NULL),(101,61,12,1.00,'mpesa','ws_CO_09052026182131782704919887','pending','2026-05-09 15:21:32',NULL,'ws_CO_09052026182131782704919887',NULL,'254704919887',NULL,NULL,NULL),(102,38,12,1.00,'mpesa','ws_CO_09052026182220378704919887','pending','2026-05-09 15:22:20',NULL,'ws_CO_09052026182220378704919887',NULL,'254704919887',NULL,NULL,NULL),(103,38,12,1.00,'mpesa','ws_CO_09052026182511474704919887','pending','2026-05-09 15:25:11',NULL,'ws_CO_09052026182511474704919887',NULL,'254704919887',NULL,NULL,NULL),(104,8,12,10.00,'cash','','paid','2026-05-09 15:35:29',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(105,13,12,1.00,'mpesa','ws_CO_09052026203539859704467699','pending','2026-05-09 17:35:40',NULL,'ws_CO_09052026203539859704467699',NULL,'254704467699',NULL,NULL,NULL),(106,38,12,1.00,'mpesa','ws_CO_09052026204259661704919887','pending','2026-05-09 17:42:59',NULL,'ws_CO_09052026204259661704919887',NULL,'254704919887',NULL,NULL,NULL),(107,38,12,1.00,'mpesa','ws_CO_09052026204345198704919887','pending','2026-05-09 17:43:45',NULL,'ws_CO_09052026204345198704919887',NULL,'254704919887',NULL,NULL,NULL),(108,38,12,1.00,'mpesa','ws_CO_09052026210849272704919887','pending','2026-05-09 18:08:49',NULL,'ws_CO_09052026210849272704919887',NULL,'254704919887',NULL,NULL,NULL),(109,38,12,1.00,'mpesa','ws_CO_09052026220819426704919887','pending','2026-05-09 19:08:19',NULL,'ws_CO_09052026220819426704919887',NULL,'254704919887','192.168.88.194',NULL,NULL),(110,38,12,1.00,'mpesa','ws_CO_09052026220902588704919887','pending','2026-05-09 19:09:02',NULL,'ws_CO_09052026220902588704919887',NULL,'254704919887','192.168.88.194',NULL,NULL),(111,13,12,1.00,'mpesa','ws_CO_09052026221111237704467699','pending','2026-05-09 19:11:11',NULL,'ws_CO_09052026221111237704467699',NULL,'254704467699','192.168.88.1',NULL,NULL),(112,13,12,1.00,'mpesa','ws_CO_09052026221142875704467699','pending','2026-05-09 19:11:43',NULL,'ws_CO_09052026221142875704467699',NULL,'254704467699','192.168.88.1',NULL,NULL),(113,13,12,1.00,'mpesa','ws_CO_09052026221203929704467699','pending','2026-05-09 19:12:04',NULL,'ws_CO_09052026221203929704467699',NULL,'254704467699','192.168.88.1',NULL,NULL),(114,13,12,1.00,'mpesa','ws_CO_10052026035419604704467699','pending','2026-05-10 00:54:19',NULL,'ws_CO_10052026035419604704467699',NULL,'254704467699','::1',NULL,NULL),(115,13,12,1.00,'mpesa','ws_CO_10052026035513188704467699','pending','2026-05-10 00:55:13',NULL,'ws_CO_10052026035513188704467699',NULL,'254704467699','::1',NULL,NULL),(116,13,12,1.00,'mpesa','ws_CO_10052026040405228704467699','pending','2026-05-10 01:04:05',NULL,'ws_CO_10052026040405228704467699',NULL,'254704467699','192.168.88.1',NULL,NULL),(117,62,12,1.00,'voucher','MH-8C6990BD','paid','2026-05-10 01:07:46',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(118,13,12,1.00,'mpesa','ws_CO_10052026044338397704467699','expired','2026-05-10 01:43:38','2026-05-11 04:43:38','ws_CO_10052026044338397704467699',NULL,'254704467699','192.168.88.1',NULL,NULL),(119,13,12,1.00,'mpesa','ws_CO_10052026052711169704467699','failed','2026-05-10 02:27:11',NULL,'ws_CO_10052026052711169704467699',NULL,'254704467699','192.168.88.1',NULL,NULL),(120,13,12,1.00,'mpesa','ws_CO_10052026052713768704467699','pending','2026-05-10 02:27:13',NULL,'ws_CO_10052026052713768704467699',NULL,'254704467699','192.168.88.1',NULL,NULL),(121,38,12,1.00,'mpesa','ws_CO_10052026052845895704919887','failed','2026-05-10 02:28:46',NULL,'ws_CO_10052026052845895704919887',NULL,'254704919887','192.168.88.1',NULL,NULL),(122,38,1,10.00,'mpesa','ws_CO_10052026052942271704919887','failed','2026-05-10 02:29:42',NULL,'ws_CO_10052026052942271704919887',NULL,'254704919887','192.168.88.1',NULL,NULL),(123,38,12,1.00,'mpesa','ws_CO_10052026053001138704919887','pending','2026-05-10 02:30:01',NULL,'ws_CO_10052026053001138704919887',NULL,'254704919887','192.168.88.1',NULL,NULL),(124,38,12,1.00,'mpesa','ws_CO_10052026053143347704919887','failed','2026-05-10 02:31:43',NULL,'ws_CO_10052026053143347704919887',NULL,'254704919887','192.168.88.1',NULL,NULL),(125,13,12,1.00,'mpesa','ws_CO_10052026053240827704467699','failed','2026-05-10 02:32:41',NULL,'ws_CO_10052026053240827704467699',NULL,'254704467699','192.168.88.1',NULL,NULL),(126,13,12,1.00,'mpesa','ws_CO_10052026053305966704467699','pending','2026-05-10 02:33:06',NULL,'ws_CO_10052026053305966704467699',NULL,'254704467699','::1',NULL,NULL),(127,13,12,1.00,'mpesa','ws_CO_10052026054401198704467699','pending','2026-05-10 02:44:01',NULL,'ws_CO_10052026054401198704467699',NULL,'254704467699','192.168.88.1',NULL,NULL),(128,13,12,1.00,'mpesa','ws_CO_10052026095714483704467699','failed','2026-05-10 06:57:14',NULL,'ws_CO_10052026095714483704467699',NULL,'254704467699','192.168.88.1',NULL,NULL),(129,13,12,1.00,'mpesa','ws_CO_10052026101809989704467699','failed','2026-05-10 07:18:10',NULL,'ws_CO_10052026101809989704467699',NULL,'254704467699','::1',NULL,NULL),(130,63,12,1.00,'voucher','MH-67B045FB','paid','2026-05-10 07:41:57',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(131,13,12,1.00,'mpesa','ws_CO_10052026104604923704467699','pending','2026-05-10 07:46:05',NULL,'ws_CO_10052026104604923704467699',NULL,'254704467699','192.168.88.1',NULL,NULL),(132,13,12,1.00,'mpesa','ws_CO_10052026104644922704467699','pending','2026-05-10 07:46:45',NULL,'ws_CO_10052026104644922704467699',NULL,'254704467699','192.168.88.194',NULL,NULL),(133,13,12,1.00,'mpesa','ws_CO_10052026104656940704467699','pending','2026-05-10 07:46:57',NULL,'ws_CO_10052026104656940704467699',NULL,'254704467699','192.168.88.194',NULL,NULL),(134,38,1,10.00,'mpesa','ws_CO_10052026104822466704919887','pending','2026-05-10 07:48:22',NULL,'ws_CO_10052026104822466704919887',NULL,'254704919887','192.168.88.194',NULL,NULL),(135,64,1,10.00,'voucher','MH-65741580','paid','2026-05-10 07:49:21',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(136,13,12,1.00,'mpesa','ws_CO_10052026110435605704467699','pending','2026-05-10 08:04:35',NULL,'ws_CO_10052026110435605704467699',NULL,'254704467699','192.168.88.1',NULL,NULL),(137,13,12,1.00,'mpesa','ws_CO_10052026110556281704467699','pending','2026-05-10 08:05:56',NULL,'ws_CO_10052026110556281704467699',NULL,'254704467699','::1',NULL,NULL),(138,13,12,1.00,'mpesa','ws_CO_10052026112546151704467699','pending','2026-05-10 08:25:46',NULL,'ws_CO_10052026112546151704467699',NULL,'254704467699','192.168.88.1',NULL,NULL),(139,13,12,1.00,'mpesa','ws_CO_10052026113552748704467699','pending','2026-05-10 08:35:53',NULL,'ws_CO_10052026113552748704467699',NULL,'254704467699','192.168.88.194',NULL,NULL),(140,38,12,1.00,'mpesa','ws_CO_10052026114535695704919887','pending','2026-05-10 08:45:36',NULL,'ws_CO_10052026114535695704919887',NULL,'254704919887','192.168.88.1',NULL,NULL),(141,38,12,1.00,'mpesa','ws_CO_10052026115413739704919887','expired','2026-05-10 08:54:14','2026-05-11 11:54:14','ws_CO_10052026115413739704919887','UEAD33R4DK','254704919887','192.168.88.1',NULL,NULL),(142,13,12,1.00,'mpesa','ws_CO_10052026121238604704467699','expired','2026-05-10 09:12:39','2026-05-11 12:12:39','ws_CO_10052026121238604704467699','UEA2B40X3T','254704467699','192.168.88.1',NULL,NULL),(143,35,12,1.00,'mpesa','ws_CO_10052026121405556714292147','expired','2026-05-10 09:14:05','2026-05-11 12:14:05','ws_CO_10052026121405556714292147','UEAGV3H7UX','254714292147','192.168.88.1',NULL,NULL),(144,13,12,1.00,'mpesa','ws_CO_10052026122031489704467699','expired','2026-05-10 09:20:31','2026-05-11 12:20:31','ws_CO_10052026122031489704467699','UEA2B40YPL','254704467699','192.168.88.1',NULL,NULL),(145,38,1,10.00,'mpesa','ws_CO_10052026122338930704919887','expired','2026-05-10 09:23:39','2026-05-10 13:23:39','ws_CO_10052026122338930704919887','UEAD33R68V','254704919887','192.168.88.1',NULL,NULL),(146,13,12,1.00,'mpesa','ws_CO_10052026174532630704467699','pending','2026-05-10 14:45:32',NULL,'ws_CO_10052026174532630704467699',NULL,'254704467699','192.168.88.1',NULL,NULL),(147,13,12,1.00,'mpesa','ws_CO_10052026174946348704467699','pending','2026-05-10 14:49:46',NULL,'ws_CO_10052026174946348704467699',NULL,'254704467699','192.168.88.1',NULL,NULL),(148,13,12,1.00,'mpesa','ws_CO_10052026175150044704467699','pending','2026-05-10 14:51:50',NULL,'ws_CO_10052026175150044704467699',NULL,'254704467699','::1',NULL,NULL),(149,13,12,1.00,'mpesa','ws_CO_10052026183455756704467699','expired','2026-05-10 15:34:56','2026-05-11 18:34:56','ws_CO_10052026183455756704467699','UEA2B42NM0','254704467699','192.168.88.1',NULL,NULL),(150,13,12,1.00,'mpesa','ws_CO_10052026193550191704467699','failed','2026-05-10 16:35:52',NULL,'ws_CO_10052026193550191704467699',NULL,'254704467699','192.168.88.1',NULL,NULL),(151,13,12,1.00,'mpesa','ws_CO_10052026193644451704467699','failed','2026-05-10 16:36:47',NULL,'ws_CO_10052026193644451704467699',NULL,'254704467699','192.168.88.1',NULL,NULL),(152,13,12,1.00,'mpesa','ws_CO_10052026194059647704467699','pending','2026-05-10 16:41:00',NULL,'ws_CO_10052026194059647704467699',NULL,'254704467699','192.168.88.1',NULL,NULL),(153,13,12,1.00,'mpesa','ws_CO_10052026203213753704467699','pending','2026-05-10 17:32:14',NULL,'ws_CO_10052026203213753704467699',NULL,'254704467699','192.168.88.194',NULL,NULL),(154,13,12,1.00,'mpesa','ws_CO_10052026203559531704467699','pending','2026-05-10 17:35:59',NULL,'ws_CO_10052026203559531704467699',NULL,'254704467699','192.168.88.194',NULL,NULL),(155,13,12,1.00,'mpesa','ws_CO_10052026203918784704467699','pending','2026-05-10 17:39:19',NULL,'ws_CO_10052026203918784704467699',NULL,'254704467699','192.168.88.194',NULL,NULL),(156,13,12,1.00,'mpesa','ws_CO_10052026213604488704467699','expired','2026-05-10 18:36:04','2026-05-11 21:36:04','ws_CO_10052026213604488704467699','UEA2B43PXV','254704467699','192.168.88.1',NULL,NULL),(157,13,12,1.00,'mpesa','ws_CO_10052026215603411704467699','expired','2026-05-10 18:56:03','2026-05-11 21:56:03','ws_CO_10052026215603411704467699','UEA2B43RLO','254704467699','192.168.88.1',NULL,NULL),(158,65,5,50.00,'voucher','MH-ADF8BB3F','paid','2026-05-10 19:01:18',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(159,66,1,10.00,'voucher','MH-E5933E98','paid','2026-05-10 19:17:44',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(160,67,1,10.00,'voucher','MH-16D9FABB','paid','2026-05-10 19:22:46',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(161,13,12,1.00,'mpesa','ws_CO_10052026234314020704467699','expired','2026-05-10 20:43:14','2026-05-11 23:43:14','ws_CO_10052026234314020704467699','UEA2B43UWP','254704467699','192.168.88.1',NULL,NULL),(162,13,12,1.00,'mpesa','ws_CO_11052026012545322704467699','expired','2026-05-10 22:25:45','2026-05-12 01:25:45','ws_CO_11052026012545322704467699','UEB2B43V7U','254704467699','192.168.88.1',NULL,NULL),(163,13,12,1.00,'mpesa','ws_CO_11052026023547977704467699','expired','2026-05-10 23:35:48','2026-05-12 02:35:48','ws_CO_11052026023547977704467699','UEB2B43U6Q','254704467699','192.168.88.1',NULL,NULL),(164,13,12,1.00,'mpesa','ws_CO_11052026033055814704467699','expired','2026-05-11 00:30:56','2026-05-12 03:30:56','ws_CO_11052026033055814704467699','UEB2B43VDL','254704467699','::1',NULL,NULL),(165,13,12,1.00,'mpesa','ws_CO_11052026053025177704467699','pending','2026-05-11 02:30:25',NULL,'ws_CO_11052026053025177704467699',NULL,'254704467699','192.168.88.1',NULL,NULL),(166,38,12,1.00,'mpesa','ws_CO_11052026053331940704919887','pending','2026-05-11 02:33:32',NULL,'ws_CO_11052026053331940704919887',NULL,'254704919887','192.168.88.1',NULL,NULL),(167,13,12,1.00,'mpesa','ws_CO_11052026054320127704467699','expired','2026-05-11 02:43:20','2026-05-12 05:43:20','ws_CO_11052026054320127704467699','UEB2B442JV','254704467699','192.168.88.1',NULL,NULL),(168,13,12,1.00,'mpesa','ws_CO_11052026155318773704467699','pending','2026-05-11 12:53:19',NULL,'ws_CO_11052026155318773704467699',NULL,'254704467699','192.168.88.1',NULL,NULL),(169,13,12,1.00,'mpesa','ws_CO_12052026194304075704467699','pending','2026-05-12 16:43:04',NULL,'ws_CO_12052026194304075704467699',NULL,'254704467699','::1',NULL,NULL),(170,75,5,50.00,'voucher','MH-5E643429','paid','2026-05-12 16:44:55',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(171,76,5,50.00,'voucher','MH-BACCD936','paid','2026-05-12 21:01:11',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(172,13,12,1.00,'mpesa','ws_CO_14052026170522098704467699','pending','2026-05-14 14:05:22',NULL,'ws_CO_14052026170522098704467699',NULL,'254704467699','::1',NULL,NULL),(173,13,12,1.00,'mpesa','ws_CO_15052026105013385704467699','pending','2026-05-15 07:50:13',NULL,'ws_CO_15052026105013385704467699',NULL,'254704467699','192.168.88.1',NULL,NULL),(174,NULL,NULL,1.00,'cash',NULL,'pending','2026-05-15 10:40:54',NULL,'ws_CO_15052026134053854704467699',NULL,'254704467699',NULL,NULL,NULL),(175,NULL,NULL,1.00,'cash',NULL,'paid','2026-05-15 10:59:02',NULL,'ws_CO_15052026135902158704467699','UEF2B4M4F8','254704467699',NULL,NULL,NULL),(177,NULL,NULL,1.00,'cash',NULL,'paid','2026-05-15 11:12:45',NULL,'ws_CO_15052026141244760704467699','UEF2B4M356','254704467699',NULL,NULL,NULL),(178,NULL,NULL,1.00,'cash',NULL,'paid','2026-05-15 11:12:45',NULL,'ws_CO_15052026141244760704467699','UEF2B4M356','254704467699',NULL,NULL,NULL),(179,NULL,NULL,1.00,'cash',NULL,'paid','2026-05-15 11:34:48',NULL,'ws_CO_15052026143448063704467699','UEF2B4M764','254704467699',NULL,NULL,NULL),(180,NULL,NULL,1.00,'cash',NULL,'failed','2026-05-15 11:36:28',NULL,'ws_CO_15052026143628533704467699',NULL,'254704467699',NULL,NULL,NULL),(181,NULL,NULL,1.00,'cash',NULL,'failed','2026-05-15 11:36:36',NULL,'ws_CO_15052026143636498704467699',NULL,'254704467699',NULL,NULL,NULL),(182,NULL,NULL,1.00,'cash',NULL,'paid','2026-05-15 11:36:56',NULL,'ws_CO_15052026143656237704467699','UEF2B4M8N3','254704467699',NULL,NULL,NULL),(183,NULL,NULL,1.00,'cash',NULL,'paid','2026-05-15 11:48:58',NULL,'ws_CO_15052026144858534704467699','UEF2B4M8VB','254704467699',NULL,NULL,NULL),(184,NULL,NULL,1.00,'cash',NULL,'pending','2026-05-15 13:04:54',NULL,'ws_CO_15052026160454436704467699',NULL,'254704467699',NULL,NULL,NULL),(185,NULL,NULL,1.00,'cash',NULL,'pending','2026-05-15 14:45:09',NULL,'ws_CO_15052026174508859704467699',NULL,'254704467699',NULL,NULL,NULL),(186,NULL,NULL,1.00,'cash',NULL,'pending','2026-05-15 14:45:25',NULL,'ws_CO_15052026174524779704467699',NULL,'254704467699',NULL,NULL,NULL),(187,NULL,NULL,1.00,'cash',NULL,'pending','2026-05-15 14:50:06',NULL,'ws_CO_15052026175005993704467699',NULL,'254704467699',NULL,NULL,NULL),(188,NULL,NULL,1.00,'cash',NULL,'pending','2026-05-15 15:20:23',NULL,'ws_CO_15052026182023550704467699',NULL,'254704467699',NULL,NULL,NULL),(189,NULL,NULL,20.00,'cash',NULL,'pending','2026-05-15 15:54:20',NULL,'ws_CO_15052026185419733704467699',NULL,'254704467699',NULL,NULL,NULL),(190,NULL,NULL,1.00,'cash',NULL,'paid','2026-05-15 16:01:08',NULL,'ws_CO_15052026190108644704467699','STK-190','254704467699',NULL,NULL,NULL),(191,NULL,NULL,1.00,'cash',NULL,'pending','2026-05-15 16:11:32',NULL,'ws_CO_15052026191132027704467699',NULL,'254704467699',NULL,NULL,NULL),(192,NULL,NULL,1.00,'cash',NULL,'pending','2026-05-15 17:10:34',NULL,'ws_CO_15052026201034467704467699',NULL,'254704467699',NULL,NULL,NULL),(193,NULL,NULL,1.00,'cash',NULL,'paid','2026-05-15 17:33:26',NULL,'ws_CO_15052026203325791704467699','STK-193','254704467699',NULL,NULL,NULL),(194,NULL,NULL,1.00,'cash',NULL,'pending','2026-05-15 17:44:14',NULL,'ws_CO_15052026204414229704467699',NULL,'254704467699',NULL,NULL,NULL),(195,NULL,NULL,1.00,'cash',NULL,'pending','2026-05-15 18:22:46',NULL,'ws_CO_15052026212246107704467699',NULL,'254704467699',NULL,NULL,NULL),(196,NULL,NULL,1.00,'cash',NULL,'pending','2026-05-15 18:34:15',NULL,'ws_CO_15052026213415107704467699',NULL,'254704467699',NULL,NULL,NULL),(197,NULL,NULL,1.00,'cash',NULL,'pending','2026-05-15 18:50:16',NULL,'ws_CO_15052026215016201704467699',NULL,'254704467699',NULL,NULL,NULL),(198,NULL,NULL,1.00,'mpesa',NULL,'pending','2026-05-15 19:20:56',NULL,'ws_CO_15052026222056424704467699',NULL,'254704467699',NULL,NULL,NULL),(199,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-15 19:21:13',NULL,'ws_CO_15052026222112859704467699','SANDBOX-199','254704467699',NULL,NULL,NULL),(200,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-15 19:24:21',NULL,'ws_CO_15052026222421307704467699','SANDBOX-200','254704467699',NULL,NULL,NULL),(201,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-15 21:37:51',NULL,'ws_CO_16052026003751366704467699','SANDBOX-201','254704467699',NULL,NULL,NULL),(202,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-15 21:48:42',NULL,'ws_CO_16052026004842296704467699','SANDBOX-202','254704467699',NULL,NULL,NULL),(203,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-16 07:48:17',NULL,'ws_CO_16052026104817335704919887','SANDBOX-203','254704919887',NULL,NULL,NULL),(204,NULL,NULL,1000.00,'mpesa',NULL,'pending','2026-05-16 11:41:44',NULL,'ws_CO_16052026144143426704467699',NULL,'254704467699',NULL,NULL,NULL),(205,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-16 21:47:48',NULL,'ws_CO_17052026004748605704919887','SANDBOX-205','254704919887',NULL,NULL,NULL),(206,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-16 21:56:35',NULL,'ws_CO_17052026005635458704919887','SANDBOX-206','254704919887',NULL,NULL,NULL),(207,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-16 22:19:52',NULL,'ws_CO_17052026011951926704919887','SANDBOX-207','254704919887',NULL,NULL,NULL),(208,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-16 22:27:01',NULL,'ws_CO_17052026012700666704919887','SANDBOX-208','254704919887',NULL,NULL,NULL),(209,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-16 22:50:36',NULL,'ws_CO_17052026015036362704919887','SANDBOX-209','254704919887',NULL,NULL,NULL),(210,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-16 23:04:32',NULL,'ws_CO_17052026020431666704919887','SANDBOX-210','254704919887',NULL,NULL,NULL),(211,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-16 23:17:17',NULL,'ws_CO_17052026021717163704919887','SANDBOX-211','254704919887',NULL,NULL,NULL),(212,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-16 23:47:59',NULL,'ws_CO_17052026024758986704919887','SANDBOX-212','254704919887',NULL,NULL,NULL),(213,NULL,1,10.00,'voucher',NULL,'paid','2026-05-17 00:11:43',NULL,NULL,'VOUCHER-MH-242ED04D','MH-242ED04D',NULL,NULL,NULL),(214,NULL,5,50.00,'voucher',NULL,'paid','2026-05-17 01:13:43',NULL,NULL,'VOUCHER-MH-DF7CE60C','MH-DF7CE60C',NULL,NULL,NULL),(215,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-17 14:14:19',NULL,'ws_CO_17052026171419227714673095','SANDBOX-215','254714673095',NULL,NULL,NULL),(216,NULL,1,10.00,'voucher',NULL,'paid','2026-05-17 14:30:03',NULL,NULL,'VOUCHER-MH-1339F6BF','MH-1339F6BF',NULL,NULL,NULL),(217,NULL,NULL,1000.00,'tenant_subscription','TENANT-11','paid','2026-05-17 16:27:24',NULL,NULL,NULL,NULL,NULL,NULL,11),(218,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-17 18:31:15',NULL,'ws_CO_17052026213115498714673095','SANDBOX-218','254714673095',NULL,NULL,NULL),(219,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-17 18:32:35',NULL,'ws_CO_17052026213234643714673095','SANDBOX-219','254714673095',NULL,NULL,NULL),(220,NULL,5,50.00,'voucher',NULL,'paid','2026-05-17 18:34:37',NULL,NULL,'VOUCHER-MH-22A0239A','MH-22A0239A',NULL,NULL,NULL),(221,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-18 01:38:36',NULL,'ws_CO_18052026043836437704467699','SANDBOX-221','254704467699',NULL,NULL,NULL),(222,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-18 05:56:11',NULL,'ws_CO_18052026085610861704467699','SANDBOX-222','254704467699',NULL,NULL,NULL),(223,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-18 18:43:38',NULL,'ws_CO_18052026214337891704919887','SANDBOX-223','254704919887',NULL,NULL,NULL),(224,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-18 19:11:07',NULL,'ws_CO_18052026221106809704919887','SANDBOX-224','254704919887',NULL,NULL,NULL),(225,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-18 19:29:09',NULL,'ws_CO_18052026222909024704919887','SANDBOX-225','254704919887',NULL,NULL,NULL),(226,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-18 20:01:27',NULL,'ws_CO_18052026230127509704467699','SANDBOX-226','254704467699',NULL,NULL,NULL),(227,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-18 20:15:00',NULL,'ws_CO_18052026231500285704467699','SANDBOX-227','254704467699',NULL,NULL,NULL),(228,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-18 20:28:41',NULL,'ws_CO_18052026232840860704467699','SANDBOX-228','254704467699',NULL,NULL,NULL),(229,NULL,NULL,1.00,'mpesa',NULL,'pending','2026-05-18 20:31:24',NULL,'ws_CO_18052026233124075704467699',NULL,'254704467699',NULL,NULL,NULL),(230,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-18 20:36:27',NULL,'ws_CO_18052026233627159704467699','SANDBOX-230','254704467699',NULL,NULL,NULL),(231,NULL,NULL,1.00,'mpesa',NULL,'pending','2026-05-18 20:36:29',NULL,'ws_CO_18052026233628831704467699',NULL,'254704467699',NULL,NULL,NULL),(232,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-18 20:37:20',NULL,'ws_CO_18052026233720702704467699','SANDBOX-232','254704467699',NULL,NULL,NULL),(233,NULL,NULL,1.00,'mpesa',NULL,'pending','2026-05-18 20:45:43',NULL,'ws_CO_18052026234543109704467699',NULL,'254704467699',NULL,NULL,NULL),(234,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-18 20:46:23',NULL,'ws_CO_18052026234623336704919887','SANDBOX-234','254704919887',NULL,NULL,NULL),(235,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-18 20:49:16',NULL,'ws_CO_18052026234916651704919887','SANDBOX-235','254704919887',NULL,NULL,NULL),(236,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-18 20:55:16',NULL,'ws_CO_18052026235516261704919887','SANDBOX-236','254704919887',NULL,NULL,NULL),(237,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-18 21:02:38',NULL,'ws_CO_19052026000237744704919887','SANDBOX-237','254704919887',NULL,NULL,NULL),(238,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-18 21:16:24',NULL,'ws_CO_19052026001624088704919887','SANDBOX-238','254704919887',NULL,NULL,NULL),(239,NULL,NULL,1.00,'mpesa',NULL,'pending','2026-05-18 21:20:02',NULL,'ws_CO_19052026002001847704919887',NULL,'254704919887',NULL,NULL,NULL),(240,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-18 21:20:29',NULL,'ws_CO_19052026002029286704467699','SANDBOX-240','254704467699',NULL,NULL,NULL),(241,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-18 21:32:55',NULL,'ws_CO_19052026003255504704467699','SANDBOX-241','254704467699',NULL,NULL,NULL),(242,NULL,NULL,1.00,'mpesa',NULL,'pending','2026-05-18 22:00:19',NULL,'ws_CO_19052026010019087704467699',NULL,'254704467699',NULL,NULL,NULL),(243,NULL,NULL,1.00,'mpesa',NULL,'pending','2026-05-18 22:12:59',NULL,'ws_CO_19052026011259215704467699',NULL,'254704467699',NULL,NULL,NULL),(244,NULL,NULL,1.00,'mpesa',NULL,'paid','2026-05-19 06:44:26',NULL,'ws_CO_19052026094426388704467699','SANDBOX-244','254704467699',NULL,NULL,NULL),(245,NULL,12,1.00,'mpesa',NULL,'failed','2026-05-19 13:00:24',NULL,'ws_CO_19052026160024695704467699',NULL,'254704467699','::1',NULL,NULL),(246,NULL,12,1.00,'mpesa',NULL,'failed','2026-05-19 13:00:44',NULL,'ws_CO_19052026160044510704467699',NULL,'254704467699','::1',NULL,NULL),(247,NULL,12,1.00,'mpesa',NULL,'failed','2026-05-19 13:00:57',NULL,'ws_CO_19052026160057537704467699',NULL,'254704467699','::1',NULL,NULL),(248,NULL,12,1.00,'mpesa',NULL,'pending','2026-05-19 13:07:34',NULL,'ws_CO_19052026160733832704467699',NULL,'254704467699','::1',NULL,NULL),(249,NULL,12,1.00,'mpesa',NULL,'failed','2026-05-19 13:07:44',NULL,'ws_CO_19052026160744107704467699',NULL,'254704467699','::1',NULL,NULL),(250,13,12,1.00,'mpesa','ws_CO_19052026165058967704467699','failed','2026-05-19 13:50:58',NULL,'ws_CO_19052026165058967704467699',NULL,'254704467699','$(ip)',NULL,NULL),(251,13,12,1.00,'mpesa','ws_CO_19052026165109755704467699','failed','2026-05-19 13:51:10',NULL,'ws_CO_19052026165109755704467699',NULL,'254704467699','$(ip)',NULL,NULL),(252,13,12,1.00,'mpesa','ws_CO_19052026165122182704467699','failed','2026-05-19 13:51:23',NULL,'ws_CO_19052026165122182704467699',NULL,'254704467699','$(ip)',NULL,NULL),(253,NULL,1,1.00,'cash',NULL,'expired','2026-05-21 00:53:39','2026-05-21 04:53:39','ws_CO_21052026035338856704919887','TEST123','254704919887','::1',NULL,NULL),(254,NULL,15,1.00,'cash',NULL,'pending','2026-05-21 00:54:53',NULL,'ws_CO_21052026035453329704919887',NULL,'254704919887','192.168.88.179',NULL,NULL),(255,NULL,15,1.00,'cash',NULL,'pending','2026-05-21 00:55:48',NULL,'ws_CO_21052026035548846704919887',NULL,'254704919887','192.168.88.179',NULL,NULL),(256,NULL,15,1.00,'cash',NULL,'pending','2026-05-21 01:07:40',NULL,'ws_CO_21052026040739909704919887',NULL,'254704919887','192.168.88.179',NULL,NULL),(257,NULL,15,1.00,'cash',NULL,'expired','2026-05-21 01:14:24','2026-05-21 05:14:24','ws_CO_21052026041423733704919887','TEST123','254704919887','192.168.88.179',NULL,NULL),(258,NULL,15,1.00,'cash',NULL,'pending','2026-05-21 01:17:24',NULL,'ws_CO_21052026041724863704467699',NULL,'254704467699','192.168.88.199',NULL,NULL),(259,NULL,15,1.00,'cash',NULL,'expired','2026-05-21 01:37:57','2026-05-21 05:37:57','ws_CO_21052026043757872704919887','UELD34XX8U','254704919887','192.168.88.179',NULL,NULL),(260,NULL,15,1.00,'cash',NULL,'expired','2026-05-21 02:24:56','2026-05-21 06:24:56','ws_CO_21052026052456912704919887','UELD34Y5TE','254704919887','192.168.88.179',NULL,NULL),(261,NULL,15,1.00,'cash',NULL,'expired','2026-05-21 02:33:22','2026-05-21 06:33:22','ws_CO_21052026053322915704919887','UELD34Y1LQ','254704919887','192.168.88.179',NULL,NULL),(262,NULL,NULL,50.00,'voucher','MH-1455B166','expired','2026-05-21 03:09:22','2026-05-22 06:09:22','VOUCHER-MH-1455B166',NULL,'MH-1455B166','192.168.88.179',NULL,NULL),(263,NULL,NULL,50.00,'voucher','MH-FB5E15C6','expired','2026-05-21 07:32:10','2026-05-22 10:32:10','VOUCHER-MH-FB5E15C6',NULL,'MH-FB5E15C6','192.168.88.199',NULL,NULL),(264,NULL,NULL,50.00,'voucher','MH-C9901CB6','expired','2026-05-21 08:14:02','2026-05-22 11:14:02','VOUCHER-MH-C9901CB6',NULL,'MH-C9901CB6','192.168.88.179',NULL,NULL),(265,NULL,NULL,50.00,'voucher','MH-08657E4F','expired','2026-05-21 08:42:34','2026-05-22 11:42:34','VOUCHER-MH-08657E4F',NULL,'MH-08657E4F','192.168.88.179',NULL,NULL),(266,NULL,NULL,50.00,'voucher','MH-8866C9D9','expired','2026-05-21 08:43:29','2026-05-22 11:43:29','VOUCHER-MH-8866C9D9',NULL,'MH-8866C9D9','192.168.88.199',NULL,NULL),(267,NULL,NULL,50.00,'voucher','MH-75AD8D48','expired','2026-05-21 08:48:48','2026-05-22 11:48:48','VOUCHER-MH-75AD8D48',NULL,'MH-75AD8D48','192.168.88.179',NULL,NULL),(268,NULL,15,1.00,'cash',NULL,'expired','2026-05-21 08:50:19','2026-05-21 12:50:19','ws_CO_21052026115019602704919887','UELD34YZWL','254704919887','192.168.88.179',NULL,NULL),(269,NULL,15,1.00,'cash',NULL,'expired','2026-05-21 08:52:34','2026-05-21 12:52:34','ws_CO_21052026115234350704919887','UELD34Z1G2','254704919887','192.168.88.179',NULL,NULL),(270,NULL,15,1.00,'cash',NULL,'expired','2026-05-21 08:55:44','2026-05-21 12:55:44','ws_CO_21052026115543936704919887','UELD34Z1IN','254704919887','192.168.88.179',NULL,NULL),(271,NULL,NULL,50.00,'voucher','MH-E0195E7C','expired','2026-05-21 08:59:44','2026-05-22 11:59:44','VOUCHER-MH-E0195E7C',NULL,'MH-E0195E7C','192.168.88.199',NULL,NULL),(272,NULL,NULL,50.00,'voucher','MH-318E543D','paid','2026-05-21 09:34:21','2026-05-22 12:34:21','VOUCHER-MH-318E543D',NULL,'MH-318E543D','192.168.88.179',NULL,NULL),(273,NULL,NULL,50.00,'voucher','MH-8C870756','paid','2026-05-21 09:37:54','2026-05-22 12:37:54','VOUCHER-MH-8C870756',NULL,'MH-8C870756','192.168.88.199',NULL,NULL),(274,NULL,15,1.00,'cash',NULL,'expired','2026-05-21 13:27:16','2026-05-21 17:27:16','ws_CO_21052026162715540704467699','UEL2B5AJ4C','254704467699','192.168.88.177',NULL,NULL),(275,NULL,15,1.00,'cash',NULL,'failed','2026-05-21 15:25:32',NULL,'ws_CO_21052026182530156704467699',NULL,'254704467699','192.168.88.199',NULL,NULL),(276,NULL,3,30.00,'cash',NULL,'expired','2026-05-21 19:03:45','2026-05-22 08:03:45','ws_CO_21052026220345060704919887','UELD351KYF','254704919887','192.168.88.177',NULL,NULL),(277,NULL,2,20.00,'cash',NULL,'expired','2026-05-21 19:08:13','2026-05-22 01:08:13','ws_CO_21052026220812613704919887','UELD351SWO','254704919887','192.168.88.177',NULL,NULL),(278,NULL,NULL,50.00,'voucher','MH-4A5E088C','paid','2026-05-21 19:15:50','2026-05-22 22:15:50','VOUCHER-MH-4A5E088C',NULL,'MH-4A5E088C','192.168.88.177',NULL,NULL),(279,NULL,15,1.00,'cash',NULL,'failed','2026-05-21 20:50:13',NULL,'ws_CO_21052026235012165704467699',NULL,'254704467699','192.168.88.199',NULL,NULL),(280,NULL,NULL,50.00,'voucher','MH-DA891EB8','paid','2026-05-22 00:17:52','2026-05-23 03:17:52','VOUCHER-MH-DA891EB8',NULL,'MH-DA891EB8','192.168.88.199',NULL,NULL),(281,NULL,15,1.00,'cash',NULL,'expired','2026-05-22 00:19:38','2026-05-22 04:19:38','ws_CO_22052026031938391704919887','UEMD351WYG','254704919887','192.168.88.179',NULL,NULL),(282,NULL,NULL,50.00,'voucher','MH-FFDDDD4E','paid','2026-05-22 01:04:47','2026-05-23 04:04:47','VOUCHER-MH-FFDDDD4E',NULL,'MH-FFDDDD4E','192.168.88.199',NULL,NULL),(283,NULL,2,20.00,'cash',NULL,'failed','2026-05-22 01:28:40',NULL,'ws_CO_22052026042840672704919887',NULL,'254704919887','192.168.88.179',NULL,NULL),(284,NULL,2,20.00,'cash',NULL,'failed','2026-05-22 01:29:30',NULL,'ws_CO_22052026042930242704467699',NULL,'254704467699','192.168.88.179',NULL,NULL),(285,NULL,2,20.00,'cash',NULL,'expired','2026-05-22 01:30:07','2026-05-22 07:30:07','ws_CO_22052026043007436704467699','UEM2B5CKVD','254704467699','192.168.88.179',NULL,NULL),(286,NULL,15,1.00,'cash',NULL,'expired','2026-05-22 01:40:01','2026-05-22 05:40:01','ws_CO_22052026044000377704467699','UEM2B5CMB8','254704467699','192.168.88.179',NULL,NULL),(287,NULL,15,1.00,'cash',NULL,'paid','2026-05-22 08:18:11',NULL,'ws_CO_22052026111810968706958169','UEMK251AQJ','254706958169','192.168.88.170',NULL,NULL),(288,NULL,15,1.00,'cash',NULL,'paid','2026-05-22 08:52:05',NULL,'ws_CO_22052026115204803704467699','UEM2B5DNEG','254704467699','192.168.88.177',NULL,NULL),(289,NULL,NULL,50.00,'voucher','MH-445A5713','paid','2026-05-22 09:35:51',NULL,'VOUCHER-MH-445A5713',NULL,'MH-445A5713','192.168.88.199',NULL,NULL),(290,NULL,NULL,50.00,'voucher','MH-513E7CC4','paid','2026-05-22 12:18:56',NULL,'VOUCHER-MH-513E7CC4',NULL,'MH-513E7CC4','192.168.88.170',NULL,NULL);
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pppoe_client_expiry`
--

DROP TABLE IF EXISTS `pppoe_client_expiry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pppoe_client_expiry` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pppoe_client_expiry`
--

LOCK TABLES `pppoe_client_expiry` WRITE;
/*!40000 ALTER TABLE `pppoe_client_expiry` DISABLE KEYS */;
/*!40000 ALTER TABLE `pppoe_client_expiry` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `realtime_bandwidth`
--

DROP TABLE IF EXISTS `realtime_bandwidth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `realtime_bandwidth` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ip_address` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rx_rate` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tx_rate` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total_rx` bigint DEFAULT '0',
  `total_tx` bigint DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `realtime_bandwidth`
--

LOCK TABLES `realtime_bandwidth` WRITE;
/*!40000 ALTER TABLE `realtime_bandwidth` DISABLE KEYS */;
INSERT INTO `realtime_bandwidth` VALUES (1,'254704467699','192.168.88.196','5.06 MB','43.25 MB',5308416,45351311,'2026-05-18 19:08:55');
/*!40000 ALTER TABLE `realtime_bandwidth` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `router_alerts`
--

DROP TABLE IF EXISTS `router_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `router_alerts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `router_ip` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `router_alerts`
--

LOCK TABLES `router_alerts` WRITE;
/*!40000 ALTER TABLE `router_alerts` DISABLE KEYS */;
INSERT INTO `router_alerts` VALUES (1,'192.168.88.1','online','Router reachable','2026-05-12 09:25:21');
/*!40000 ALTER TABLE `router_alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `router_backup_logs`
--

DROP TABLE IF EXISTS `router_backup_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `router_backup_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `message` text COLLATE utf8mb4_general_ci,
  `status` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'success',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `router_backup_logs`
--

LOCK TABLES `router_backup_logs` WRITE;
/*!40000 ALTER TABLE `router_backup_logs` DISABLE KEYS */;
INSERT INTO `router_backup_logs` VALUES (1,'Router backup created successfully via 192.168.88.1: hakim_backup_2026-05-21_14-38','success','2026-05-21 11:38:27');
/*!40000 ALTER TABLE `router_backup_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `router_backups`
--

DROP TABLE IF EXISTS `router_backups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `router_backups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `router_id` int DEFAULT NULL,
  `backup_name` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'saved',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `router_backups`
--

LOCK TABLES `router_backups` WRITE;
/*!40000 ALTER TABLE `router_backups` DISABLE KEYS */;
/*!40000 ALTER TABLE `router_backups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `router_bandwidth`
--

DROP TABLE IF EXISTS `router_bandwidth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `router_bandwidth` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_id` int DEFAULT NULL,
  `rx_mbps` float DEFAULT '0',
  `tx_mbps` float DEFAULT '0',
  `active_users` int DEFAULT '0',
  `cpu_load` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `checked_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=192 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `router_bandwidth`
--

LOCK TABLES `router_bandwidth` WRITE;
/*!40000 ALTER TABLE `router_bandwidth` DISABLE KEYS */;
INSERT INTO `router_bandwidth` VALUES (1,7,34,62,7,'11%','2026-05-18 06:59:01'),(2,7,38,77,7,'88%','2026-05-18 07:00:02'),(3,7,24,41,4,'32%','2026-05-18 07:01:01'),(4,7,29,36,37,'58%','2026-05-18 07:02:01'),(5,7,10,19,31,'28%','2026-05-18 07:03:01'),(6,7,58,56,21,'32%','2026-05-18 07:03:40'),(7,7,50,17,24,'15%','2026-05-18 07:04:01'),(8,7,51,76,8,'78%','2026-05-18 07:05:02'),(9,7,74,15,4,'67%','2026-05-18 07:06:01'),(10,7,116,34,15,'82%','2026-05-18 07:07:02'),(11,7,106,6,32,'10%','2026-05-18 07:08:01'),(12,7,44,25,14,'24%','2026-05-18 07:09:01'),(13,7,40,80,9,'40%','2026-05-18 07:10:01'),(14,7,47,12,12,'25%','2026-05-18 07:11:01'),(15,7,118,49,13,'16%','2026-05-18 07:12:01'),(16,7,46,64,30,'43%','2026-05-18 07:13:01'),(17,7,87,47,5,'9%','2026-05-18 07:14:01'),(18,7,40,8,2,'85%','2026-05-18 07:15:02'),(19,7,96,40,21,'43%','2026-05-18 07:16:01'),(20,7,35,35,35,'12%','2026-05-18 07:17:02'),(21,7,106,29,24,'5%','2026-05-18 07:18:01'),(22,7,59,51,12,'79%','2026-05-18 07:19:01'),(23,7,89,30,7,'30%','2026-05-18 07:20:01'),(24,7,33,65,23,'65%','2026-05-18 07:21:01'),(25,7,46,30,5,'66%','2026-05-18 07:22:02'),(26,7,27,12,35,'61%','2026-05-18 07:23:01'),(27,7,25,65,35,'45%','2026-05-18 07:24:01'),(28,7,109,6,23,'20%','2026-05-18 07:25:01'),(29,7,114,44,14,'78%','2026-05-18 07:27:02'),(30,7,67,27,34,'35%','2026-05-18 07:28:01'),(31,7,10,47,28,'86%','2026-05-18 07:29:01'),(32,7,100,13,4,'69%','2026-05-18 07:30:01'),(33,7,16,34,14,'22%','2026-05-18 07:31:01'),(34,7,14,20,21,'36%','2026-05-18 07:32:01'),(35,7,67,22,6,'66%','2026-05-18 07:33:01'),(36,7,36,47,19,'34%','2026-05-18 07:34:01'),(37,7,116,60,5,'13%','2026-05-18 07:35:02'),(38,7,55,75,28,'58%','2026-05-18 07:36:01'),(39,7,36,58,22,'31%','2026-05-18 07:37:01'),(40,7,98,41,2,'49%','2026-05-18 07:38:01'),(41,7,49,58,29,'57%','2026-05-18 07:41:01'),(42,7,35,20,27,'87%','2026-05-18 07:42:01'),(43,7,101,72,40,'54%','2026-05-18 07:43:01'),(44,7,62,32,6,'59%','2026-05-18 07:44:01'),(45,7,60,8,39,'42%','2026-05-18 07:45:01'),(46,7,100,28,21,'86%','2026-05-18 07:46:01'),(47,7,95,67,37,'66%','2026-05-18 07:47:01'),(48,7,111,23,18,'83%','2026-05-18 07:48:01'),(49,7,120,46,30,'20%','2026-05-18 07:49:01'),(50,7,36,38,27,'40%','2026-05-18 07:50:01'),(51,7,24,8,30,'79%','2026-05-18 07:51:01'),(52,7,54,77,31,'27%','2026-05-18 07:52:01'),(53,7,33,66,11,'42%','2026-05-18 07:53:01'),(54,7,89,12,27,'36%','2026-05-18 07:54:01'),(55,7,7,63,4,'77%','2026-05-18 07:55:01'),(56,7,78,40,10,'7%','2026-05-18 07:56:01'),(57,7,88,66,31,'71%','2026-05-18 07:57:01'),(58,7,29,26,28,'27%','2026-05-18 07:58:01'),(59,7,43,46,29,'69%','2026-05-18 07:59:01'),(60,7,58,4,15,'22%','2026-05-18 08:00:01'),(61,7,19,61,23,'64%','2026-05-18 08:01:01'),(62,7,9,27,36,'17%','2026-05-18 08:02:02'),(63,7,85,24,11,'58%','2026-05-18 08:03:01'),(64,7,40,45,38,'29%','2026-05-18 08:04:01'),(65,7,29,71,31,'72%','2026-05-18 08:05:01'),(66,7,37,60,38,'52%','2026-05-18 08:06:01'),(67,7,104,71,38,'53%','2026-05-18 08:07:01'),(68,7,87,4,22,'73%','2026-05-18 08:08:01'),(69,7,36,58,29,'60%','2026-05-18 08:09:01'),(70,7,91,69,15,'73%','2026-05-18 08:10:02'),(71,7,79,62,21,'42%','2026-05-18 08:11:01'),(72,7,6,37,16,'41%','2026-05-18 08:12:01'),(73,7,120,29,38,'19%','2026-05-18 08:13:01'),(74,7,105,77,32,'23%','2026-05-18 08:14:01'),(75,7,114,37,28,'88%','2026-05-18 08:15:01'),(76,7,102,14,25,'13%','2026-05-18 08:16:01'),(77,7,64,73,4,'54%','2026-05-18 08:17:01'),(78,7,104,19,20,'63%','2026-05-18 08:18:01'),(79,7,104,75,23,'17%','2026-05-18 08:19:01'),(80,7,28,52,27,'70%','2026-05-18 08:20:01'),(81,7,32,15,30,'89%','2026-05-18 08:21:01'),(82,7,96,11,11,'82%','2026-05-18 08:22:01'),(83,7,117,78,35,'76%','2026-05-18 08:23:01'),(84,7,93,76,40,'8%','2026-05-18 08:24:01'),(85,7,76,19,38,'10%','2026-05-18 08:25:01'),(86,7,68,68,19,'78%','2026-05-18 08:26:01'),(87,7,83,13,36,'76%','2026-05-18 08:27:01'),(88,7,12,6,9,'23%','2026-05-18 08:28:01'),(89,7,23,71,25,'56%','2026-05-18 08:29:01'),(90,7,63,27,32,'36%','2026-05-18 08:30:01'),(91,7,78,61,31,'32%','2026-05-18 08:31:01'),(92,7,108,2,37,'90%','2026-05-18 08:32:01'),(93,7,112,49,3,'45%','2026-05-18 08:33:01'),(94,7,9,56,22,'76%','2026-05-18 08:34:02'),(95,7,101,9,34,'33%','2026-05-18 08:35:01'),(96,7,46,33,8,'22%','2026-05-18 08:36:01'),(97,7,11,68,27,'59%','2026-05-18 08:37:01'),(98,7,40,13,19,'50%','2026-05-18 08:38:02'),(99,7,25,19,25,'43%','2026-05-18 08:39:01'),(100,7,117,77,14,'63%','2026-05-18 08:40:01'),(101,7,102,17,4,'83%','2026-05-18 08:41:02'),(102,7,46,63,31,'59%','2026-05-18 08:42:01'),(103,7,96,70,16,'26%','2026-05-18 08:43:01'),(104,7,78,50,17,'11%','2026-05-18 08:44:01'),(105,7,39,60,24,'35%','2026-05-18 08:45:02'),(106,7,90,27,9,'17%','2026-05-18 08:46:02'),(107,7,106,50,15,'32%','2026-05-18 08:47:01'),(108,7,33,34,32,'26%','2026-05-18 08:48:01'),(109,7,49,26,13,'6%','2026-05-18 08:49:01'),(110,7,46,78,11,'67%','2026-05-18 08:50:01'),(111,7,60,6,32,'52%','2026-05-18 08:51:01'),(112,7,8,23,22,'87%','2026-05-18 08:52:01'),(113,7,14,5,16,'47%','2026-05-18 08:53:02'),(114,7,82,76,5,'58%','2026-05-18 08:54:01'),(115,7,25,62,11,'48%','2026-05-18 08:55:01'),(116,7,98,49,33,'22%','2026-05-18 08:56:01'),(117,7,15,8,24,'59%','2026-05-18 08:59:01'),(118,7,49,24,15,'90%','2026-05-18 09:00:02'),(119,7,110,40,25,'89%','2026-05-18 09:01:01'),(120,7,86,3,19,'59%','2026-05-18 09:02:01'),(121,7,101,43,40,'60%','2026-05-18 09:03:01'),(122,7,52,19,22,'55%','2026-05-18 09:04:01'),(123,7,68,64,21,'35%','2026-05-18 09:05:01'),(124,7,44,55,9,'64%','2026-05-18 09:06:01'),(125,7,30,30,2,'82%','2026-05-18 09:07:01'),(126,7,73,67,31,'19%','2026-05-18 09:08:01'),(127,7,102,78,20,'72%','2026-05-18 09:09:01'),(128,7,100,40,7,'11%','2026-05-18 09:10:01'),(129,7,71,41,1,'85%','2026-05-18 09:11:02'),(130,7,70,20,38,'79%','2026-05-18 09:12:01'),(131,7,14,22,10,'22%','2026-05-18 09:13:01'),(132,7,119,59,28,'37%','2026-05-18 09:14:01'),(133,7,22,38,3,'65%','2026-05-18 09:15:01'),(134,7,96,66,24,'37%','2026-05-18 09:16:01'),(135,7,81,34,26,'66%','2026-05-18 09:17:01'),(136,7,90,7,29,'55%','2026-05-18 09:18:01'),(137,7,39,34,11,'69%','2026-05-18 09:19:01'),(138,7,10,57,37,'21%','2026-05-18 09:20:01'),(139,7,78,14,9,'83%','2026-05-18 09:21:01'),(140,7,7,31,8,'68%','2026-05-18 09:22:02'),(141,7,115,21,21,'34%','2026-05-18 09:23:01'),(142,7,31,80,8,'21%','2026-05-18 09:24:01'),(143,7,53,80,16,'12%','2026-05-18 09:25:01'),(144,7,17,43,27,'21%','2026-05-18 09:26:01'),(145,7,44,56,21,'84%','2026-05-18 09:27:02'),(146,7,17,65,38,'19%','2026-05-18 09:28:01'),(147,7,22,2,37,'12%','2026-05-18 09:29:01'),(148,7,109,45,32,'87%','2026-05-18 09:30:02'),(149,7,103,72,29,'16%','2026-05-18 09:33:01'),(150,7,115,17,1,'28%','2026-05-18 09:34:01'),(151,7,77,8,5,'6%','2026-05-18 09:35:01'),(152,7,68,26,2,'60%','2026-05-18 09:36:01'),(153,7,58,26,1,'46%','2026-05-18 09:37:01'),(154,7,116,49,29,'21%','2026-05-18 09:39:01'),(155,7,42,59,20,'5%','2026-05-18 09:40:01'),(156,7,80,2,19,'62%','2026-05-18 09:41:01'),(157,7,47,7,38,'20%','2026-05-18 09:42:01'),(158,7,84,66,8,'47%','2026-05-18 09:43:01'),(159,7,105,75,11,'23%','2026-05-18 09:44:01'),(160,7,35,18,21,'77%','2026-05-18 09:52:01'),(161,7,4.98,0.26,1,'4%','2026-05-18 09:52:52'),(162,7,5,57,8,'20%','2026-05-18 09:53:01'),(163,7,56,55,8,'66%','2026-05-18 09:54:01'),(164,7,105,80,14,'85%','2026-05-18 09:55:01'),(165,7,115,75,25,'62%','2026-05-18 09:56:01'),(166,7,92,53,3,'20%','2026-05-18 09:57:01'),(167,7,0.03,0.1,1,'2%','2026-05-18 09:57:16'),(168,7,23,66,39,'86%','2026-05-18 09:58:01'),(169,7,0.03,0.11,1,'2%','2026-05-18 09:58:12'),(170,7,107,48,10,'83%','2026-05-18 09:59:01'),(171,7,119,45,27,'51%','2026-05-18 10:00:01'),(172,7,64,65,6,'46%','2026-05-18 10:01:01'),(173,7,38,15,17,'62%','2026-05-18 10:02:01'),(174,7,101,60,12,'77%','2026-05-18 10:03:01'),(175,7,112,44,10,'51%','2026-05-18 10:04:02'),(176,7,45,6,3,'89%','2026-05-18 10:05:01'),(177,1,0.02,0.05,0,'5%','2026-05-18 10:49:49'),(178,1,0.01,0.01,0,'4%','2026-05-18 14:09:56'),(179,1,0.04,0.04,0,'4%','2026-05-18 17:40:29'),(180,1,0.01,0.03,0,'2%','2026-05-18 19:28:46'),(181,1,0.01,0.01,0,'3%','2026-05-18 21:43:30'),(182,1,0.01,0,0,'5%','2026-05-18 21:57:56'),(183,1,0.01,0,0,'6%','2026-05-19 05:11:38'),(184,1,0.01,0,0,'9%','2026-05-19 05:12:00'),(185,1,0,0,0,'17%','2026-05-19 05:18:13'),(186,1,0.01,0,0,'9%','2026-05-19 08:27:56'),(187,1,0,0,0,'12%','2026-05-19 08:54:26'),(188,1,0.01,0,0,'2%','2026-05-19 09:31:21'),(189,1,0.01,0,0,'37%','2026-05-19 09:35:30'),(190,1,0.01,0,0,'5%','2026-05-19 12:03:41'),(191,1,0.01,0,0,'22%','2026-05-19 13:15:19');
/*!40000 ALTER TABLE `router_bandwidth` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `router_heartbeat`
--

DROP TABLE IF EXISTS `router_heartbeat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `router_heartbeat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_id` int DEFAULT NULL,
  `router_name` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `router_ip` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `response_time` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `checked_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `router_heartbeat`
--

LOCK TABLES `router_heartbeat` WRITE;
/*!40000 ALTER TABLE `router_heartbeat` DISABLE KEYS */;
INSERT INTO `router_heartbeat` VALUES (1,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','189 ms','2026-05-18 06:58:59'),(2,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','180 ms','2026-05-18 07:00:02'),(3,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','113 ms','2026-05-18 07:02:02'),(4,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','181 ms','2026-05-18 07:04:01'),(5,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','193 ms','2026-05-18 07:06:01'),(6,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','176 ms','2026-05-18 07:08:01'),(7,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','177 ms','2026-05-18 07:10:02'),(8,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','177 ms','2026-05-18 07:12:02'),(9,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','188 ms','2026-05-18 07:14:02'),(10,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','197 ms','2026-05-18 07:16:01'),(11,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','185 ms','2026-05-18 07:18:01'),(12,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','179 ms','2026-05-18 07:20:01'),(13,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','252 ms','2026-05-18 07:22:02'),(14,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','178 ms','2026-05-18 07:24:02'),(15,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','14 ms','2026-05-18 07:26:01'),(16,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','175 ms','2026-05-18 07:28:01'),(17,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','5015 ms','2026-05-18 07:30:06'),(18,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','177 ms','2026-05-18 07:32:02'),(19,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','236 ms','2026-05-18 07:34:01'),(20,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','181 ms','2026-05-18 07:36:01'),(21,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','1154 ms','2026-05-18 07:38:02'),(22,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','726 ms','2026-05-18 07:40:02'),(23,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','258 ms','2026-05-18 07:42:01'),(24,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','1197 ms','2026-05-18 07:44:03'),(25,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','179 ms','2026-05-18 07:46:02'),(26,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','180 ms','2026-05-18 07:48:01'),(27,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','176 ms','2026-05-18 07:50:02'),(28,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','180 ms','2026-05-18 07:52:01'),(29,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','173 ms','2026-05-18 07:54:02'),(30,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','6232 ms','2026-05-18 07:56:07'),(31,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','209 ms','2026-05-18 07:58:01'),(32,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','176 ms','2026-05-18 08:00:01'),(33,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','181 ms','2026-05-18 08:02:02'),(34,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','179 ms','2026-05-18 08:04:01'),(35,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','187 ms','2026-05-18 08:06:02'),(36,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','1197 ms','2026-05-18 08:08:02'),(37,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','179 ms','2026-05-18 08:10:02'),(38,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','182 ms','2026-05-18 08:12:01'),(39,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','1210 ms','2026-05-18 08:14:03'),(40,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','222 ms','2026-05-18 08:16:01'),(41,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','181 ms','2026-05-18 08:18:02'),(42,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','272 ms','2026-05-18 08:20:02'),(43,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','1211 ms','2026-05-18 08:22:02'),(44,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','212 ms','2026-05-18 08:24:02'),(45,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','5797 ms','2026-05-18 08:26:07'),(46,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','1269 ms','2026-05-18 08:28:02'),(47,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','209 ms','2026-05-18 08:30:02'),(48,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','6076 ms','2026-05-18 08:32:07'),(49,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','181 ms','2026-05-18 08:34:02'),(50,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','209 ms','2026-05-18 08:36:01'),(51,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','293 ms','2026-05-18 08:38:02'),(52,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','183 ms','2026-05-18 08:40:01'),(53,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','247 ms','2026-05-18 08:42:01'),(54,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','230 ms','2026-05-18 08:44:02'),(55,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','295 ms','2026-05-18 08:46:02'),(56,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','1223 ms','2026-05-18 08:48:02'),(57,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','520 ms','2026-05-18 08:50:01'),(58,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','257 ms','2026-05-18 08:52:02'),(59,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','181 ms','2026-05-18 08:54:01'),(60,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','180 ms','2026-05-18 08:56:02'),(61,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','180 ms','2026-05-18 08:58:01'),(62,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','194 ms','2026-05-18 09:00:02'),(63,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','178 ms','2026-05-18 09:02:01'),(64,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','262 ms','2026-05-18 09:04:01'),(65,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','1234 ms','2026-05-18 09:06:03'),(66,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','233 ms','2026-05-18 09:08:01'),(67,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','288 ms','2026-05-18 09:10:01'),(68,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','192 ms','2026-05-18 09:12:01'),(69,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','191 ms','2026-05-18 09:14:01'),(70,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','177 ms','2026-05-18 09:16:02'),(71,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','288 ms','2026-05-18 09:18:02'),(72,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','15 ms','2026-05-18 09:20:01'),(73,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','185 ms','2026-05-18 09:22:02'),(74,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','1223 ms','2026-05-18 09:24:03'),(75,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','215 ms','2026-05-18 09:26:01'),(76,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','210 ms','2026-05-18 09:28:01'),(77,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','201 ms','2026-05-18 09:30:02'),(78,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','14 ms','2026-05-18 09:32:01'),(79,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','183 ms','2026-05-18 09:34:02'),(80,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','1244 ms','2026-05-18 09:36:02'),(81,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','18 ms','2026-05-18 09:38:01'),(82,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','181 ms','2026-05-18 09:40:02'),(83,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','11 ms','2026-05-18 09:42:01'),(84,7,'mhakimapi','hj80a5yj8cc.sn.mynetname.net','online','181 ms','2026-05-18 09:44:02'),(85,7,'mhakimapi','192.168.88.1','online','11 ms','2026-05-18 09:52:01'),(86,7,'mhakimapi','192.168.88.1','online','1 ms','2026-05-18 09:54:01'),(87,7,'mhakimapi','192.168.88.1','online','12 ms','2026-05-18 09:56:01'),(88,7,'mhakimapi','192.168.88.1','online','2 ms','2026-05-18 09:58:01'),(89,7,'mhakimapi','192.168.88.1','online','2 ms','2026-05-18 10:00:02'),(90,7,'mhakimapi','192.168.88.1','online','1 ms','2026-05-18 10:02:01'),(91,7,'mhakimapi','192.168.88.1','online','1077 ms','2026-05-18 10:04:03');
/*!40000 ALTER TABLE `router_heartbeat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `router_history`
--

DROP TABLE IF EXISTS `router_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `router_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `router_ip` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `router_name` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `username` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `api_port` int DEFAULT '8728',
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'unknown',
  `action` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `router_history`
--

LOCK TABLES `router_history` WRITE;
/*!40000 ALTER TABLE `router_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `router_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `router_live_cache`
--

DROP TABLE IF EXISTS `router_live_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `router_live_cache` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_id` int DEFAULT NULL,
  `router_name` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `router_ip` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_general_ci DEFAULT 'offline',
  `rx_mbps` float DEFAULT '0',
  `tx_mbps` float DEFAULT '0',
  `active_users` int DEFAULT '0',
  `simple_queues` int DEFAULT '0',
  `cpu_load` varchar(20) COLLATE utf8mb4_general_ci DEFAULT '0%',
  `uptime` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `free_memory` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `board` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_seen` datetime DEFAULT NULL,
  `last_error` text COLLATE utf8mb4_general_ci,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `company_id` (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `router_live_cache`
--

LOCK TABLES `router_live_cache` WRITE;
/*!40000 ALTER TABLE `router_live_cache` DISABLE KEYS */;
INSERT INTO `router_live_cache` VALUES (8,1,'MikroTik','192.168.88.1','online',0.01,0,0,4,'22%','1d2h35m39s','79073280','RB951Ui-2HnD','2026-05-19 16:15:19',NULL,'2026-05-19 13:15:19');
/*!40000 ALTER TABLE `router_live_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `routers`
--

DROP TABLE IF EXISTS `routers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `routers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `router_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `router_ip` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `router_username` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `router_password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `api_port` int DEFAULT '8728',
  `location` varchar(100) COLLATE utf8mb4_general_ci DEFAULT 'Main Site',
  `status` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `routers`
--

LOCK TABLES `routers` WRITE;
/*!40000 ALTER TABLE `routers` DISABLE KEYS */;
INSERT INTO `routers` VALUES (6,'mhakimapi','192.168.88.1','mhakimapi','12345678',8728,'ruiru','active','2026-05-19 17:27:47');
/*!40000 ALTER TABLE `routers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `security_events`
--

DROP TABLE IF EXISTS `security_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `security_events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_type` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `details` text COLLATE utf8mb4_general_ci,
  `ip_address` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `security_events`
--

LOCK TABLES `security_events` WRITE;
/*!40000 ALTER TABLE `security_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `security_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smart_vouchers`
--

DROP TABLE IF EXISTS `smart_vouchers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `smart_vouchers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `voucher_code` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `package_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `speed_down` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '8M',
  `speed_up` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '2M',
  `duration_hours` int DEFAULT '24',
  `price` decimal(10,2) DEFAULT '0.00',
  `status` enum('unused','used','expired') COLLATE utf8mb4_general_ci DEFAULT 'unused',
  `used_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `used_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `voucher_code` (`voucher_code`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smart_vouchers`
--

LOCK TABLES `smart_vouchers` WRITE;
/*!40000 ALTER TABLE `smart_vouchers` DISABLE KEYS */;
INSERT INTO `smart_vouchers` VALUES (1,'MH-B538636A','24 Hours','8M','2M',24,49.00,'expired','192.168.88.199','2026-05-20 02:04:16','2026-05-21 02:04:16','2026-05-19 22:44:47','2026-05-21 08:09:01'),(2,'MH-A5D8E3C0','24 Hours','8M','2M',24,49.00,'expired','::1','2026-05-20 01:54:25','2026-05-21 01:54:25','2026-05-19 22:44:47','2026-05-21 08:09:01'),(3,'MH-A7CB4361','24 Hours','8M','2M',24,49.00,'expired','::1','2026-05-20 01:52:41','2026-05-21 01:52:41','2026-05-19 22:44:47','2026-05-21 08:09:01'),(11,'MH-51DC5F3A','24 Hours','10M','3M',15,30.00,'unused',NULL,NULL,NULL,'2026-05-19 23:09:01',NULL),(12,'MH-4BFD68BF','24 Hours','10M','3M',15,30.00,'expired','192.168.88.193','2026-05-20 02:30:06','2026-05-20 17:30:06','2026-05-19 23:09:01','2026-05-20 14:31:01'),(13,'MH-D7C202CA','24 Hours','10M','3M',15,30.00,'expired','192.168.88.193','2026-05-20 02:21:15','2026-05-20 17:21:15','2026-05-19 23:09:01','2026-05-20 14:22:02'),(14,'MH-70AE1B8D','24 Hours','8M','2M',24,50.00,'unused',NULL,NULL,NULL,'2026-05-19 23:56:47',NULL),(15,'MH-A65181B2','24 Hours','8M','2M',24,50.00,'unused',NULL,NULL,NULL,'2026-05-19 23:56:47',NULL),(16,'MH-4AB55378','24 Hours','8M','2M',24,50.00,'unused',NULL,NULL,NULL,'2026-05-19 23:56:47',NULL),(17,'MH-DD52D062','24 Hours','8M','2M',24,50.00,'unused',NULL,NULL,NULL,'2026-05-19 23:56:47',NULL),(18,'MH-E4A9DFC0','24 Hours','8M','2M',24,50.00,'unused',NULL,NULL,NULL,'2026-05-19 23:56:47',NULL),(19,'MH-094F907C','24 Hours','8M','2M',24,50.00,'unused',NULL,NULL,NULL,'2026-05-19 23:56:47',NULL),(20,'MH-36C2B9B3','24 Hours','8M','2M',24,50.00,'unused',NULL,NULL,NULL,'2026-05-19 23:56:47',NULL),(21,'MH-EDE38D69','24 Hours','8M','2M',24,50.00,'unused',NULL,NULL,NULL,'2026-05-19 23:56:47',NULL),(22,'MH-E57E4C01','24 Hours','8M','2M',24,50.00,'unused',NULL,NULL,NULL,'2026-05-19 23:56:47',NULL),(23,'MH-8ACDD206','24 Hours','8M','2M',24,50.00,'unused',NULL,NULL,NULL,'2026-05-19 23:56:47',NULL),(24,'MH-4AAC0AD0','24 Hours','8M','2M',24,50.00,'unused',NULL,NULL,NULL,'2026-05-20 00:08:17',NULL),(25,'MH-F7E7B5F0','24 Hours','8M','2M',24,50.00,'unused',NULL,NULL,NULL,'2026-05-20 00:08:17',NULL),(26,'MH-5CC15167','24 Hours','8M','2M',24,50.00,'unused',NULL,NULL,NULL,'2026-05-20 00:08:17',NULL),(27,'MH-4BC57DF2','24 Hours','8M','2M',24,30.00,'unused',NULL,NULL,NULL,'2026-05-20 00:32:09',NULL),(28,'MH-B7492473','24 Hours','8M','2M',24,10.00,'unused',NULL,NULL,NULL,'2026-05-20 00:37:14',NULL),(29,'MH-783448F3','24 Hours','8M','2M',24,50.00,'expired','192.168.88.177','2026-05-20 13:29:55','2026-05-21 13:29:55','2026-05-20 00:48:32','2026-05-21 23:07:42'),(30,'MH-2155BF80','24 Hours','8M','2M',24,50.00,'expired','192.168.88.177','2026-05-20 13:28:38','2026-05-21 13:28:38','2026-05-20 00:48:33','2026-05-21 23:07:42'),(31,'MH-ADAF1DA0','24 Hours','8M','2M',24,50.00,'expired','192.168.88.179','2026-05-20 13:16:08','2026-05-21 13:16:08','2026-05-20 00:48:33','2026-05-21 23:07:42'),(32,'MH-9C944FB8','24 Hours','8M','2M',24,50.00,'expired','192.168.88.179','2026-05-20 13:02:33','2026-05-21 13:02:33','2026-05-20 00:48:33','2026-05-21 23:07:42'),(33,'MH-D0BE5D07','24 Hours','8M','2M',24,50.00,'expired','192.168.88.179','2026-05-20 05:17:09','2026-05-21 05:17:09','2026-05-20 00:48:33','2026-05-21 08:09:02'),(34,'MH-31C88E8A','24 Hours','8M','2M',24,40.00,'expired','192.168.88.180','2026-05-20 05:06:02','2026-05-21 05:06:02','2026-05-20 00:54:16','2026-05-21 08:09:02'),(35,'MH-DCCFA4BA','24 Hours','8M','2M',24,40.00,'expired','192.168.88.183','2026-05-20 04:54:50','2026-05-21 04:54:50','2026-05-20 00:54:17','2026-05-21 08:09:02'),(36,'MH-44C6EAB6','24 Hours','8M','2M',24,40.00,'expired','192.168.88.1','2026-05-20 04:41:51','2026-05-21 04:41:51','2026-05-20 00:54:17','2026-05-21 08:09:02'),(37,'MH-E0DEA99B','24 Hours','8M','2M',24,40.00,'expired','192.168.88.187','2026-05-20 04:32:24','2026-05-21 04:32:24','2026-05-20 00:54:17','2026-05-21 08:09:02'),(38,'MH-E85274DE','24 Hours','8M','2M',24,40.00,'expired','192.168.88.1','2026-05-20 04:24:20','2026-05-21 04:24:20','2026-05-20 00:54:17','2026-05-21 08:09:02'),(39,'MH-D3C83AD9','24 Hours','8M','2M',24,12.00,'expired','192.168.88.193','2026-05-20 04:14:30','2026-05-21 04:14:30','2026-05-20 01:00:22','2026-05-21 08:09:02'),(40,'MH-DC630866','24 Hours','8M','2M',24,50.00,'unused',NULL,NULL,NULL,'2026-05-20 13:40:27',NULL),(41,'MH-6BFA9B60','24 Hours','8M','2M',24,50.00,'unused',NULL,NULL,NULL,'2026-05-20 13:40:27',NULL),(42,'MH-BAF76B4C','24 Hours','8M','2M',24,50.00,'expired','192.168.88.177','2026-05-20 23:40:29','2026-05-21 23:40:27','2026-05-20 13:40:27','2026-05-21 23:07:42'),(43,'MH-6ABCFE0E','24 Hours','8M','2M',24,50.00,'expired','192.168.88.177','2026-05-20 23:39:41','2026-05-21 23:39:40','2026-05-20 13:40:27','2026-05-21 23:07:42'),(44,'MH-ABE748C5','24 Hours','8M','2M',24,50.00,'expired','192.168.88.177','2026-05-20 17:23:46','2026-05-21 17:23:46','2026-05-20 13:40:27','2026-05-21 23:07:42'),(45,'MH-86DF7292','24 Hours','8M','2M',24,50.00,'expired','192.168.88.179','2026-05-21 02:48:33','2026-05-22 02:48:33','2026-05-20 22:13:31','2026-05-21 23:49:01'),(46,'MH-EFAB14C2','24 Hours','8M','2M',24,50.00,'expired','192.168.88.179','2026-05-21 02:39:02','2026-05-22 02:39:02','2026-05-20 22:13:31','2026-05-21 23:40:01'),(48,'MH-7993B6EF','24 Hours','8M','2M',24,50.00,'expired','192.168.88.179','2026-05-21 01:37:35','2026-05-22 01:37:35','2026-05-20 22:13:32','2026-05-21 23:07:42'),(49,'MH-9BB1972C','24 Hours','8M','2M',24,50.00,'expired','192.168.88.179','2026-05-21 01:31:48','2026-05-22 01:31:48','2026-05-20 22:13:32','2026-05-21 23:07:42'),(50,'MH-98A55D6E','M.Hakim','8M','2M',720,50.00,'used','192.168.88.199','2026-05-21 02:51:42','2026-06-20 02:51:42','2026-05-20 23:51:22',NULL),(51,'MH-A4EBA059','24 Hours','3M','8M',24,50.00,'expired','192.168.88.179','2026-05-21 02:57:22','2026-05-22 02:57:22','2026-05-20 23:55:04','2026-05-21 23:58:02'),(52,'MH-90288312','Hakim','3M','8M',720,50.00,'used','192.168.88.199','2026-05-21 02:59:40','2026-06-20 02:59:40','2026-05-20 23:58:38',NULL),(53,'MH-74A6BBE2','TEST','8M','2M',0,50.00,'expired','192.168.88.179','2026-05-21 03:04:55','2026-05-21 03:04:55','2026-05-21 00:04:14','2026-05-21 08:09:03'),(54,'MH-8866C9D9','24 Hours','3M','8M',24,50.00,'expired','192.168.88.199','2026-05-21 11:43:29','2026-05-22 11:43:29','2026-05-21 03:08:34',NULL),(55,'MH-08657E4F','24 Hours','3M','8M',24,50.00,'expired','192.168.88.179','2026-05-21 11:42:34','2026-05-22 11:42:34','2026-05-21 03:08:34',NULL),(56,'MH-C9901CB6','24 Hours','3M','8M',24,50.00,'expired','192.168.88.179','2026-05-21 11:14:01','2026-05-22 11:14:01','2026-05-21 03:08:34',NULL),(57,'MH-FB5E15C6','24 Hours','3M','8M',24,50.00,'expired','192.168.88.199','2026-05-21 10:32:10','2026-05-22 10:32:10','2026-05-21 03:08:34',NULL),(58,'MH-1455B166','24 Hours','3M','8M',24,50.00,'expired','192.168.88.179','2026-05-21 06:09:22','2026-05-22 06:09:22','2026-05-21 03:08:35',NULL),(59,'MH-EF995812','24 Hours','6M','2M',24,50.00,'unused',NULL,NULL,NULL,'2026-05-21 08:47:38',NULL),(60,'MH-450F6188','24 Hours','6M','2M',24,50.00,'unused',NULL,NULL,NULL,'2026-05-21 08:47:38',NULL),(61,'MH-B630323E','24 Hours','6M','2M',24,50.00,'unused',NULL,NULL,NULL,'2026-05-21 08:47:38',NULL),(62,'MH-E0195E7C','24 Hours','6M','2M',24,50.00,'expired','192.168.88.199','2026-05-21 11:59:44','2026-05-22 11:59:44','2026-05-21 08:47:38',NULL),(63,'MH-75AD8D48','24 Hours','6M','2M',24,50.00,'expired','192.168.88.179','2026-05-21 11:48:48','2026-05-22 11:48:48','2026-05-21 08:47:38',NULL),(64,'MH-B5F45B29','24 Hours','8M','2M',24,50.00,'unused',NULL,NULL,NULL,'2026-05-21 09:33:45',NULL),(65,'MH-FFDDDD4E','24 Hours','8M','2M',24,50.00,'used','192.168.88.199','2026-05-22 04:04:47','2026-05-23 04:04:47','2026-05-21 09:33:45',NULL),(66,'MH-DA891EB8','24 Hours','8M','2M',24,50.00,'used','192.168.88.199','2026-05-22 03:17:52','2026-05-23 03:17:52','2026-05-21 09:33:46',NULL),(67,'MH-8C870756','24 Hours','8M','2M',24,50.00,'used','192.168.88.199','2026-05-21 12:37:54','2026-05-22 12:37:54','2026-05-21 09:33:46',NULL),(68,'MH-318E543D','24 Hours','8M','2M',24,50.00,'used','192.168.88.179','2026-05-21 12:34:21','2026-05-22 12:34:21','2026-05-21 09:33:46',NULL),(69,'MH-4A5E088C','24 Hours','8M','3M',5,50.00,'expired','192.168.88.177','2026-05-21 22:15:50','2026-05-22 03:15:50','2026-05-21 19:14:42','2026-05-22 00:16:02'),(70,'MH-BA306623','24 Hours','8M','3M',24,50.00,'unused',NULL,NULL,NULL,'2026-05-22 09:35:08',NULL),(71,'MH-513E7CC4','24 Hours','8M','3M',24,50.00,'used','192.168.88.170','2026-05-22 15:18:57','2026-05-23 15:18:56','2026-05-22 09:35:08',NULL),(72,'MH-445A5713','24 Hours','8M','3M',24,50.00,'used','192.168.88.199','2026-05-22 12:35:51','2026-05-23 12:35:51','2026-05-22 09:35:08',NULL);
/*!40000 ALTER TABLE `smart_vouchers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sms_logs`
--

DROP TABLE IF EXISTS `sms_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sms_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `phone` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_general_ci,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `response` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sms_logs`
--

LOCK TABLES `sms_logs` WRITE;
/*!40000 ALTER TABLE `sms_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `sms_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sms_settings`
--

DROP TABLE IF EXISTS `sms_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sms_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `provider` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'africastalking',
  `username` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `api_key` text COLLATE utf8mb4_general_ci,
  `sender_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sms_settings`
--

LOCK TABLES `sms_settings` WRITE;
/*!40000 ALTER TABLE `sms_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `sms_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL,
  `amount` decimal(10,2) DEFAULT '1000.00',
  `status` enum('pending','paid','expired','failed') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `checkout_id` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mpesa_receipt` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `starts_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `payment_phone` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_reference` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`),
  CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscriptions`
--

LOCK TABLES `subscriptions` WRITE;
/*!40000 ALTER TABLE `subscriptions` DISABLE KEYS */;
INSERT INTO `subscriptions` VALUES (1,1,1000.00,'pending',NULL,NULL,NULL,NULL,'2026-05-16 13:56:40',NULL,NULL),(2,3,1000.00,'pending',NULL,NULL,NULL,NULL,'2026-05-16 16:29:43',NULL,NULL),(3,4,1000.00,'pending',NULL,NULL,NULL,NULL,'2026-05-17 03:16:48',NULL,NULL),(4,4,1000.00,'paid',NULL,NULL,'2026-05-17 06:16:56','2026-06-17 06:16:56','2026-05-17 03:16:56',NULL,NULL),(5,5,1000.00,'pending',NULL,NULL,NULL,NULL,'2026-05-17 03:18:53',NULL,NULL),(6,5,1000.00,'paid',NULL,NULL,'2026-05-17 06:19:09','2026-06-17 06:19:09','2026-05-17 03:19:09',NULL,NULL),(7,6,1000.00,'pending',NULL,NULL,NULL,NULL,'2026-05-17 03:31:40',NULL,NULL),(8,6,1000.00,'paid',NULL,NULL,'2026-05-17 06:31:42','2026-06-17 06:31:42','2026-05-17 03:31:42',NULL,NULL),(9,7,1000.00,'pending',NULL,NULL,NULL,NULL,'2026-05-17 03:44:07',NULL,NULL),(10,7,1000.00,'paid',NULL,NULL,'2026-05-17 06:44:09','2026-06-17 06:44:09','2026-05-17 03:44:09',NULL,NULL),(11,8,1000.00,'pending',NULL,NULL,NULL,NULL,'2026-05-17 14:50:23',NULL,NULL),(12,8,1000.00,'pending',NULL,NULL,NULL,NULL,'2026-05-17 14:51:26','0704467699','efghghhbnvcnv'),(13,8,1000.00,'pending',NULL,NULL,NULL,NULL,'2026-05-17 14:59:40','0704467699','efghghhbnvcnv'),(14,8,1000.00,'paid',NULL,NULL,'2026-05-17 18:06:29','2026-06-17 18:06:29','2026-05-17 15:06:29',NULL,NULL),(15,9,1000.00,'pending',NULL,NULL,NULL,NULL,'2026-05-17 15:08:23',NULL,NULL),(16,9,1000.00,'pending',NULL,NULL,NULL,NULL,'2026-05-17 15:10:53','0704919887','efghghhbnvcnv'),(17,9,1000.00,'pending',NULL,NULL,NULL,NULL,'2026-05-17 15:12:08','0704919888','efghghhbnvcnv'),(18,9,1000.00,'pending',NULL,NULL,NULL,NULL,'2026-05-17 15:12:17','0704919887','efghghhbnvcnv'),(19,9,1000.00,'paid',NULL,NULL,'2026-05-17 18:12:39','2026-06-17 18:12:39','2026-05-17 15:12:39',NULL,NULL),(20,9,1000.00,'paid',NULL,NULL,'2026-05-17 18:45:54','2026-06-17 18:45:54','2026-05-17 15:45:54',NULL,NULL),(21,9,1000.00,'paid',NULL,NULL,'2026-05-17 18:48:38','2026-06-17 18:48:38','2026-05-17 15:48:38',NULL,NULL),(22,9,1000.00,'paid',NULL,NULL,'2026-05-17 18:50:14','2026-06-17 18:50:14','2026-05-17 15:50:14',NULL,NULL),(23,9,1000.00,'paid',NULL,NULL,'2026-05-17 18:50:20','2026-06-17 18:50:20','2026-05-17 15:50:20',NULL,NULL),(24,9,1000.00,'paid',NULL,NULL,'2026-05-17 18:50:38','2026-06-17 18:50:38','2026-05-17 15:50:38',NULL,NULL),(25,3,1000.00,'paid',NULL,NULL,'2026-05-17 19:06:14','2026-06-17 19:06:14','2026-05-17 16:06:14',NULL,NULL),(26,10,1000.00,'pending',NULL,NULL,NULL,NULL,'2026-05-17 16:12:20',NULL,NULL),(27,10,1000.00,'pending',NULL,NULL,NULL,NULL,'2026-05-17 16:12:44','0704919881','efghghhbnvcnv'),(28,10,1000.00,'paid',NULL,NULL,'2026-05-17 19:13:12','2026-06-17 19:13:12','2026-05-17 16:13:12',NULL,NULL),(29,11,1000.00,'paid',NULL,NULL,'2026-05-17 19:27:24','2026-06-17 19:27:24','2026-05-17 16:16:33',NULL,NULL);
/*!40000 ALTER TABLE `subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `super_admin_router`
--

DROP TABLE IF EXISTS `super_admin_router`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `super_admin_router` (
  `id` int NOT NULL AUTO_INCREMENT,
  `router_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT 'MikroTik Main Router',
  `router_ip` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `router_user` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `router_pass` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `router_port` varchar(20) COLLATE utf8mb4_general_ci DEFAULT '8728',
  `router_connected` tinyint DEFAULT '0',
  `online_status` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'offline',
  `last_seen` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `super_admin_router`
--

LOCK TABLES `super_admin_router` WRITE;
/*!40000 ALTER TABLE `super_admin_router` DISABLE KEYS */;
INSERT INTO `super_admin_router` VALUES (1,'MikroTik Main Router','192.168.88.1','mhakimapi','HakimAPI2026','8728',1,'online','2026-05-18 13:07:40','2026-05-18 10:07:40');
/*!40000 ALTER TABLE `super_admin_router` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_tickets`
--

DROP TABLE IF EXISTS `support_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_tickets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int DEFAULT NULL,
  `subject` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `priority` enum('low','medium','high','critical') COLLATE utf8mb4_general_ci DEFAULT 'medium',
  `status` enum('open','assigned','resolved','closed') COLLATE utf8mb4_general_ci DEFAULT 'open',
  `assigned_to` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_tickets`
--

LOCK TABLES `support_tickets` WRITE;
/*!40000 ALTER TABLE `support_tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `support_tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_stats`
--

DROP TABLE IF EXISTS `system_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_stats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `router_status` varchar(30) COLLATE utf8mb4_general_ci DEFAULT 'unknown',
  `online_hotspot` int DEFAULT '0',
  `online_pppoe` int DEFAULT '0',
  `wan_rx` float DEFAULT '0',
  `wan_tx` float DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_stats`
--

LOCK TABLES `system_stats` WRITE;
/*!40000 ALTER TABLE `system_stats` DISABLE KEYS */;
INSERT INTO `system_stats` VALUES (1,'offline',2,0,2.2,0.38,'2026-05-22 12:23:11');
/*!40000 ALTER TABLE `system_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_router_logs`
--

DROP TABLE IF EXISTS `tenant_router_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenant_router_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_id` int DEFAULT NULL,
  `action_type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `router_ip` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_router_logs`
--

LOCK TABLES `tenant_router_logs` WRITE;
/*!40000 ALTER TABLE `tenant_router_logs` DISABLE KEYS */;
INSERT INTO `tenant_router_logs` VALUES (1,8,'admin_disconnect','','Super admin disconnected tenant router','2026-05-17 14:52:19'),(2,8,'admin_disconnect','','Super admin disconnected tenant router','2026-05-17 14:59:01'),(3,7,'disconnect','192.168.88.1','Tenant disconnected MikroTik router','2026-05-17 15:14:07'),(4,NULL,'reconnect','','Tenant reconnected MikroTik router','2026-05-17 15:19:52'),(5,NULL,'reconnect','','Tenant reconnected MikroTik router','2026-05-17 15:19:53'),(6,NULL,'reconnect','','Tenant reconnected MikroTik router','2026-05-17 15:19:53'),(7,NULL,'reconnect','','Tenant reconnected MikroTik router','2026-05-17 15:19:54'),(8,NULL,'reconnect','','Tenant reconnected MikroTik router','2026-05-17 15:20:11'),(9,NULL,'reconnect','','Tenant reconnected MikroTik router','2026-05-17 15:22:36'),(10,NULL,'reconnect','','Tenant reconnected MikroTik router','2026-05-17 15:22:37'),(11,NULL,'reconnect','','Tenant reconnected MikroTik router','2026-05-17 15:23:00'),(12,NULL,'reconnect','','Tenant reconnected MikroTik router','2026-05-17 15:48:49'),(13,NULL,'reconnect','','Tenant reconnected MikroTik router','2026-05-17 15:48:50'),(14,NULL,'reconnect','','Tenant reconnected MikroTik router','2026-05-17 15:49:14'),(15,NULL,'reconnect','','Tenant reconnected MikroTik router','2026-05-17 15:49:28'),(16,NULL,'connect','192.168.88.1','Tenant connected MikroTik router','2026-05-17 15:49:50'),(17,9,'admin_disconnect','','Super admin disconnected tenant router','2026-05-17 15:50:35'),(18,NULL,'reconnect','','Tenant reconnected MikroTik router','2026-05-17 15:56:54'),(19,NULL,'reconnect','192.168.88.1','Tenant reconnected MikroTik router','2026-05-17 15:57:22'),(20,7,'reconnect','192.168.88.1','Tenant reconnected MikroTik router','2026-05-17 15:58:35'),(21,NULL,'disconnect','','Tenant disconnected MikroTik router','2026-05-17 16:09:32'),(22,NULL,'reconnect','','Tenant reconnected MikroTik router','2026-05-17 16:09:34'),(23,NULL,'reconnect','','Tenant reconnected MikroTik router','2026-05-17 16:09:35'),(24,NULL,'reconnect','','Tenant reconnected MikroTik router','2026-05-17 16:09:36'),(25,NULL,'reconnect','','Tenant reconnected MikroTik router','2026-05-17 16:09:36'),(26,NULL,'reconnect','','Tenant reconnected MikroTik router','2026-05-17 16:09:37'),(27,NULL,'reconnect','','Tenant reconnected MikroTik router','2026-05-17 16:09:38'),(28,NULL,'reconnect','','Tenant reconnected MikroTik router','2026-05-17 16:09:38'),(29,NULL,'reconnect','192.168.88.1','Tenant reconnected MikroTik router','2026-05-17 16:09:57'),(30,NULL,'reconnect','192.168.88.1','Tenant reconnected MikroTik router','2026-05-17 16:10:11'),(31,NULL,'connect','192.168.88.1','Tenant connected MikroTik router','2026-05-17 16:10:26'),(32,7,'reconnect','192.168.88.1','Tenant reconnected MikroTik router','2026-05-17 16:10:59'),(33,7,'reconnect','192.168.88.1','Tenant reconnected MikroTik router','2026-05-17 16:11:02'),(34,7,'disconnect','192.168.88.1','Tenant disconnected MikroTik router','2026-05-17 16:11:07'),(35,7,'reconnect','192.168.88.1','Tenant reconnected MikroTik router','2026-05-17 16:11:09'),(36,7,'disconnect','192.168.88.1','Tenant disconnected MikroTik router','2026-05-18 06:24:24'),(37,7,'connect','hj80a5yj8cc.sn.mynetname.net','Tenant connected MikroTik router','2026-05-18 06:28:29'),(38,7,'disconnect','hj80a5yj8cc.sn.mynetname.net','Tenant disconnected MikroTik router','2026-05-18 09:41:30'),(39,7,'admin_disconnect','','Super admin disconnected tenant router','2026-05-18 09:45:00');
/*!40000 ALTER TABLE `tenant_router_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_users`
--

DROP TABLE IF EXISTS `tenant_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenant_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_id` int DEFAULT NULL,
  `full_name` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('super_admin','tenant_admin','operator') COLLATE utf8mb4_general_ci DEFAULT 'tenant_admin',
  `status` enum('active','pending','suspended') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_users`
--

LOCK TABLES `tenant_users` WRITE;
/*!40000 ALTER TABLE `tenant_users` DISABLE KEYS */;
INSERT INTO `tenant_users` VALUES (5,NULL,'Mogan Hakim','moganhakim2@gmail.com','0704919887','$2y$10$cCE69ADs4BqM.UdcqhDtg.hArxOvLxmdcpC6XYCKk5rwS2LRJk.6G','super_admin','active','2026-05-16 17:16:46'),(9,7,'Hakim James','moganngaasi@gmil.com','0700000001','$2y$10$mvPGtIrmlGSUO25WURytKuhdlnXl0/IrOPeuzr/G8/szw8pcsHFKK','tenant_admin','active','2026-05-17 03:44:07'),(10,8,'MORGAN NGAASii','moganhakim@gmail.com','0704919888','$2y$10$Upv4GIH110IIVtnaE65X8uQbYQcyBaqItxvshx/p/vgXP11VfTdQG','tenant_admin','active','2026-05-17 14:50:23'),(11,9,'MORGAN NGAASII','moganhakim3@gmail.com','0704919889','$2y$10$Y3ddnhKXybwK8vvWRfpNgeD.UHwb68XWqv.q9RmpWEd4OHxjuHunW','tenant_admin','active','2026-05-17 15:08:23'),(12,10,'MORGAN NGAAS','moganhakim1@gmail.com','0704919882','$2y$10$V4eEYhf5IEG9aF1vRLnin.IB6lEQ4FgSGCA2JlNQKkPOVCuCCnRIC','tenant_admin','active','2026-05-17 16:12:20'),(13,11,'MORGAN NGAASI','moganhakim4@gmail.com','0704919887','$2y$10$EgM4sr.tjlJN/9PsbBNyYekzoTZwM/bZnJ94s24SKU5VLZJfrI2Sm','tenant_admin','active','2026-05-17 16:16:33');
/*!40000 ALTER TABLE `tenant_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `topology_nodes`
--

DROP TABLE IF EXISTS `topology_nodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `topology_nodes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `node_name` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `node_type` enum('mikrotik','switch','ap','client','router') COLLATE utf8mb4_general_ci DEFAULT 'ap',
  `parent_id` int DEFAULT NULL,
  `ip_address` varchar(60) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mac_address` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('online','offline','unknown') COLLATE utf8mb4_general_ci DEFAULT 'unknown',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `topology_nodes`
--

LOCK TABLES `topology_nodes` WRITE;
/*!40000 ALTER TABLE `topology_nodes` DISABLE KEYS */;
/*!40000 ALTER TABLE `topology_nodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `traffic_analytics`
--

DROP TABLE IF EXISTS `traffic_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `traffic_analytics` (
  `id` int NOT NULL AUTO_INCREMENT,
  `router_id` int DEFAULT NULL,
  `client_ip` varchar(60) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `download_mbps` decimal(10,2) DEFAULT '0.00',
  `upload_mbps` decimal(10,2) DEFAULT '0.00',
  `recorded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `traffic_analytics`
--

LOCK TABLES `traffic_analytics` WRITE;
/*!40000 ALTER TABLE `traffic_analytics` DISABLE KEYS */;
/*!40000 ALTER TABLE `traffic_analytics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('super_admin','admin','operator') COLLATE utf8mb4_general_ci DEFAULT 'admin',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `isp_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (4,'M. Hakim','admin','$2y$10$hENMPwzpYEYKYKO1Glz2xOHsHxCBcZjAo0g3qPDeJTRSQev6rOyqS','admin','2026-05-06 07:47:09',NULL),(5,'M. Hakim','hakim','308163092187715964aa7d8728cac9ca','super_admin','2026-05-12 22:31:47',NULL),(6,'Admin Name','admin2','0192023a7bbd73250516f069df18b500','admin','2026-05-12 23:43:13',NULL),(7,'Operator Name','operator1','2407bd807d6ca01d1bcd766c730cec9a','operator','2026-05-12 23:43:28',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vouchers`
--

DROP TABLE IF EXISTS `vouchers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vouchers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `package_id` int NOT NULL,
  `status` enum('unused','used','expired') COLLATE utf8mb4_general_ci DEFAULT 'unused',
  `used_by_client_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `used_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `company_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_vouchers_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vouchers`
--

LOCK TABLES `vouchers` WRITE;
/*!40000 ALTER TABLE `vouchers` DISABLE KEYS */;
INSERT INTO `vouchers` VALUES (1,'MH-9066659E',2,'unused',NULL,'2026-05-06 13:08:43',NULL,NULL,NULL),(2,'MH-21415622',2,'unused',NULL,'2026-05-06 13:08:43',NULL,NULL,NULL),(3,'MH-B24A6ECF',7,'used',32,'2026-05-06 13:51:50','2026-05-07 13:00:38','2026-05-07 13:00:38',NULL),(4,'MH-5A1124D6',3,'unused',NULL,'2026-05-06 13:58:59',NULL,NULL,NULL),(5,'MH-2418B4B9',1,'unused',NULL,'2026-05-07 08:38:28',NULL,NULL,NULL),(6,'MH-26E565EA',6,'unused',NULL,'2026-05-07 10:01:55',NULL,NULL,NULL),(7,'MH-4056ABC0',1,'used',33,'2026-05-07 10:02:17','2026-05-07 13:02:44','2026-05-07 13:02:44',NULL),(8,'MH-970DF910',7,'used',37,'2026-05-07 12:33:42','2026-05-07 15:34:08','2026-05-07 15:34:08',NULL),(9,'MH-D28C86A0',1,'used',NULL,'2026-05-07 15:31:05',NULL,'2026-05-07 18:31:05',NULL),(10,'MH-3B538619',2,'used',NULL,'2026-05-07 15:43:53',NULL,'2026-05-07 18:43:53',NULL),(11,'MH-7E2BEAE1',1,'used',NULL,'2026-05-07 15:55:18',NULL,'2026-05-07 18:55:18',NULL),(12,'MH-1557410C',1,'used',NULL,'2026-05-07 16:01:07',NULL,'2026-05-07 19:01:07',NULL),(13,'MH-252689AD',1,'used',NULL,'2026-05-07 16:05:20',NULL,'2026-05-07 19:05:20',NULL),(14,'MH-43D268DA',1,'used',NULL,'2026-05-07 16:08:24',NULL,'2026-05-07 19:08:24',NULL),(15,'MH-7BDFC673',1,'used',NULL,'2026-05-07 16:08:28',NULL,'2026-05-07 19:08:28',NULL),(16,'MH-3B11C6D9',1,'used',NULL,'2026-05-07 16:10:54',NULL,'2026-05-07 19:10:54',NULL),(17,'MH-3C2DAB44',1,'used',49,'2026-05-07 17:19:28','2026-05-07 20:19:46','2026-05-07 20:19:46',NULL),(18,'MH-F10F55A5',1,'used',50,'2026-05-07 17:23:45','2026-05-07 20:25:00','2026-05-07 20:25:00',NULL),(19,'MH-4FB87F4B',1,'used',51,'2026-05-07 17:40:34','2026-05-07 20:41:32','2026-05-07 20:41:32',NULL),(20,'MH-F15C2A8E',1,'used',52,'2026-05-07 17:42:56','2026-05-07 20:43:29','2026-05-07 20:43:29',NULL),(21,'MH-24B87317',1,'used',53,'2026-05-07 18:36:04','2026-05-07 21:37:14','2026-05-07 21:37:14',NULL),(22,'MH-98576D2F',1,'used',54,'2026-05-07 19:53:05','2026-05-07 22:53:36','2026-05-07 22:53:36',NULL),(23,'MH-4E656D08',1,'used',55,'2026-05-07 20:41:14','2026-05-07 23:42:08','2026-05-07 23:42:08',NULL),(24,'MH-AC4E68E3',1,'used',56,'2026-05-07 21:45:59','2026-05-08 00:46:11','2026-05-08 00:46:11',NULL),(25,'MH-5487FAEB',5,'used',57,'2026-05-08 00:46:17','2026-05-08 03:49:40','2026-05-08 03:49:40',NULL),(26,'MH-ADF8BB3F',5,'used',65,'2026-05-08 00:53:47','2026-05-10 22:01:18','2026-05-10 22:01:18',NULL),(27,'MH-BE81F04F',2,'used',58,'2026-05-08 02:26:14','2026-05-08 05:27:08','2026-05-08 05:27:08',NULL),(28,'MH-BB8100EF',12,'unused',NULL,'2026-05-09 12:31:04',NULL,NULL,NULL),(29,'MH-65741580',1,'used',64,'2026-05-09 15:17:31','2026-05-10 10:49:21','2026-05-10 10:49:21',NULL),(30,'MH-8C6990BD',12,'used',62,'2026-05-09 15:17:39','2026-05-10 04:07:46','2026-05-10 04:07:46',NULL),(31,'MH-67B045FB',12,'used',63,'2026-05-10 07:40:55','2026-05-10 10:41:57','2026-05-10 10:41:57',NULL),(32,'MH-E5933E98',1,'used',66,'2026-05-10 19:16:11','2026-05-10 22:17:44','2026-05-10 22:17:44',NULL),(33,'MH-16D9FABB',1,'used',67,'2026-05-10 19:22:02','2026-05-10 22:22:46','2026-05-10 22:22:46',NULL),(34,'MH-21C74C4E',1,'unused',NULL,'2026-05-10 19:22:13',NULL,NULL,NULL),(35,'MH-5E643429',5,'used',75,'2026-05-12 16:43:37','2026-05-12 19:44:55','2026-05-12 19:44:55',NULL),(36,'MH-BACCD936',5,'used',76,'2026-05-12 20:57:10','2026-05-13 00:01:11','2026-05-13 00:01:11',NULL),(37,'MH-7A878BCF',5,'used',NULL,'2026-05-14 11:54:39',NULL,'2026-05-14 14:54:39',NULL),(38,'MH-4CC875FE',5,'used',NULL,'2026-05-14 17:01:15',NULL,'2026-05-14 20:01:15',NULL),(39,'MH-5D07F976',4,'used',NULL,'2026-05-15 07:51:36',NULL,'2026-05-15 10:51:36',NULL),(40,'MH-6AFB8D84',6,'used',NULL,'2026-05-15 08:36:24',NULL,'2026-05-15 11:36:24',NULL),(41,'MH-193EBD23',6,'used',NULL,'2026-05-15 09:33:48',NULL,'2026-05-15 12:33:48',NULL),(42,'MH-8EFD49CF',4,'used',NULL,'2026-05-15 10:07:49',NULL,'2026-05-15 13:07:49',NULL),(43,'MH-9AC42E9B',4,'used',NULL,'2026-05-15 10:44:22',NULL,'2026-05-15 13:44:22',NULL),(44,'MH-980C29B7',4,'used',NULL,'2026-05-15 11:45:00',NULL,'2026-05-15 14:45:00',NULL),(45,'MH-77B9B73C',4,'used',NULL,'2026-05-15 13:05:39',NULL,'2026-05-15 16:05:39',NULL),(46,'MH-0A4B439F',2,'used',NULL,'2026-05-15 15:01:03',NULL,'2026-05-15 18:01:03',NULL),(47,'MH-9CAE960D',2,'used',NULL,'2026-05-15 15:21:36',NULL,'2026-05-15 18:21:36',NULL),(48,'MH-C3C3AD0B',2,'used',NULL,'2026-05-15 15:51:39',NULL,'2026-05-15 18:51:39',NULL),(49,'MH-5CA5EE7A',2,'used',NULL,'2026-05-15 16:11:18',NULL,'2026-05-15 19:11:18',NULL),(50,'MH-9699EECB',1,'used',NULL,'2026-05-15 18:40:06',NULL,'2026-05-15 21:40:06',NULL),(51,'MH-91CC2B87',12,'unused',NULL,'2026-05-15 21:36:24',NULL,NULL,NULL),(52,'MH-F1596522',5,'used',NULL,'2026-05-15 21:59:24',NULL,'2026-05-16 00:59:24',NULL),(53,'MH-D1859DC7',6,'used',NULL,'2026-05-16 08:25:00',NULL,'2026-05-16 11:25:00',NULL),(54,'MH-100C13DB',3,'used',NULL,'2026-05-16 21:45:59',NULL,NULL,NULL),(55,'MH-0F8731DF',1,'used',NULL,'2026-05-16 21:51:25',NULL,NULL,NULL),(56,'MH-823BDBC9',1,'used',NULL,'2026-05-16 22:08:31',NULL,NULL,NULL),(57,'MH-14659A66',2,'used',NULL,'2026-05-16 22:49:40',NULL,NULL,NULL),(58,'MH-A8E60B7E',2,'used',NULL,'2026-05-16 23:05:34',NULL,NULL,NULL),(59,'MH-53991D6A',1,'used',NULL,'2026-05-16 23:14:09',NULL,NULL,NULL),(60,'MH-242ED04D',1,'used',NULL,'2026-05-16 23:16:48',NULL,NULL,NULL),(61,'MH-DF7CE60C',5,'used',NULL,'2026-05-17 01:13:13',NULL,NULL,NULL),(62,'MH-1339F6BF',1,'used',NULL,'2026-05-17 14:22:01',NULL,NULL,NULL),(63,'MH-22A0239A',5,'used',NULL,'2026-05-17 18:33:37',NULL,NULL,NULL),(64,'MH-0AC121E8',1,'used',NULL,'2026-05-18 01:44:06',NULL,NULL,NULL),(65,'MH-D17EAED7',5,'used',NULL,'2026-05-18 18:39:54',NULL,NULL,NULL),(66,'MH-F9AE177F',12,'used',NULL,'2026-05-18 20:34:28',NULL,NULL,NULL),(67,'MH-808D5A26',12,'used',NULL,'2026-05-18 20:53:45',NULL,NULL,NULL),(68,'MH-7766D521',12,'used',NULL,'2026-05-18 21:03:30',NULL,NULL,NULL),(69,'MH-C429D58D',12,'used',NULL,'2026-05-18 21:17:15',NULL,NULL,NULL),(70,'MH-46ABC1E8',12,'used',NULL,'2026-05-18 21:44:54',NULL,NULL,NULL),(71,'MH-7449FDD5',12,'unused',NULL,'2026-05-18 22:05:28',NULL,NULL,NULL),(72,'MH-2FA5B558',1,'used',NULL,'2026-05-19 06:46:13',NULL,NULL,NULL),(73,'MH-3ABD8703',1,'used',NULL,'2026-05-19 06:46:44',NULL,NULL,NULL),(74,'MH-8E3145EE',1,'used',NULL,'2026-05-19 08:43:26','2026-05-19 11:43:43',NULL,NULL),(75,'MH-916D43A9',1,'used',NULL,'2026-05-19 08:54:49','2026-05-19 11:55:08',NULL,NULL),(76,'MH-BE838471',1,'used',NULL,'2026-05-19 09:07:08','2026-05-19 12:07:38',NULL,NULL),(77,'MH-EBD1FF7B',12,'used',NULL,'2026-05-19 09:11:55','2026-05-19 12:18:20',NULL,NULL),(78,'MH-36B3DD56',12,'used',NULL,'2026-05-19 09:11:58','2026-05-19 12:12:36',NULL,NULL),(79,'MH-1F7E3E3A',1,'used',NULL,'2026-05-19 09:18:38','2026-05-19 12:32:19',NULL,NULL),(80,'MH-865CE68E',12,'used',NULL,'2026-05-19 09:38:57','2026-05-19 12:39:26',NULL,NULL),(81,'MH-38E26647',1,'used',NULL,'2026-05-19 10:10:35','2026-05-19 13:10:50',NULL,NULL),(82,'MH-ACCB87FE',1,'used',NULL,'2026-05-19 10:19:24','2026-05-19 13:20:07',NULL,NULL),(83,'MH-0D62024E',1,'unused',NULL,'2026-05-19 13:57:07',NULL,NULL,NULL),(84,'MH-133F3A08',1,'unused',NULL,'2026-05-20 00:38:36',NULL,NULL,NULL);
/*!40000 ALTER TABLE `vouchers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `whatsapp_sms_logs`
--

DROP TABLE IF EXISTS `whatsapp_sms_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `whatsapp_sms_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `recipient` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `channel` enum('sms','whatsapp') COLLATE utf8mb4_general_ci DEFAULT 'whatsapp',
  `message` text COLLATE utf8mb4_general_ci,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'queued',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whatsapp_sms_logs`
--

LOCK TABLES `whatsapp_sms_logs` WRITE;
/*!40000 ALTER TABLE `whatsapp_sms_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `whatsapp_sms_logs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-22 15:23:36
