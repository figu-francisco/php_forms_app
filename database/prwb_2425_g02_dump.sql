-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: prwb_2425_g02
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `answers`
--

LOCK TABLES `answers` WRITE;
/*!40000 ALTER TABLE `answers` DISABLE KEYS */;
INSERT INTO `answers` VALUES (3,39,'Vodka',-1),(4,39,'Vodka',-1),(5,39,'Rum',-1),(6,39,'Wine',-1),(7,39,'Wine',-1),(8,39,'Beer',-1),(9,39,'Beer',-1),(11,40,'Peter Pirelli',-1),(11,41,'ppirelli@demo.eu',-1),(11,42,'1968-01-01',-1),(11,43,'Help with my account access please',-1),(12,40,'Not your business',-1),(12,41,'not.your@business.com',-1),(12,42,'0001-01-01',-1),(12,43,'Nope',-1),(13,40,'Bob',-1),(13,41,'bob@sponge.com',-1),(13,42,'1980-12-12',-1),(13,43,'',-1),(14,45,'true',1),(14,45,'false',2),(14,45,'false',3),(14,46,'true',1),(14,46,'false',2),(14,47,'true',1),(14,47,'false',2),(14,47,'true',3),(14,48,'false',1),(14,48,'true',2),(14,48,'true',3),(14,48,'false',4),(15,45,'true',1),(15,45,'false',2),(15,45,'false',3),(15,46,'false',1),(15,46,'true',2),(15,47,'false',1),(15,47,'false',2),(15,47,'true',3),(15,48,'true',1),(15,48,'false',2),(15,48,'false',3),(15,48,'true',4),(16,45,'true',1),(16,45,'false',2),(16,45,'false',3),(16,46,'true',1),(16,46,'false',2),(16,47,'false',1),(16,47,'false',2),(16,47,'false',3),(16,48,'false',1),(16,48,'true',2),(16,48,'false',3),(16,48,'false',4),(17,45,'true',1),(17,45,'false',2),(17,45,'false',3),(17,46,'true',1),(17,46,'false',2),(17,47,'false',1),(17,47,'false',2),(17,47,'false',3),(17,48,'false',1),(17,48,'true',2),(17,48,'false',3),(17,48,'false',4),(18,45,'false',1),(18,45,'false',2),(18,45,'false',3),(18,46,'false',1),(18,46,'true',2),(18,47,'true',1),(18,47,'false',2),(18,47,'false',3),(18,48,'false',1),(18,48,'true',2),(18,48,'false',3),(18,48,'false',4);
/*!40000 ALTER TABLE `answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `form_colors`
--

LOCK TABLES `form_colors` WRITE;
/*!40000 ALTER TABLE `form_colors` DISABLE KEYS */;
INSERT INTO `form_colors` VALUES (1,'blue'),(15,'blue'),(16,'blue'),(16,'red'),(17,'blue'),(17,'red'),(17,'yellow');
/*!40000 ALTER TABLE `form_colors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `forms`
--

LOCK TABLES `forms` WRITE;
/*!40000 ALTER TABLE `forms` DISABLE KEYS */;
INSERT INTO `forms` VALUES (1,'Registration Form','This form is intended to collect your administrative information.',1,1),(2,'WIP Empty form',NULL,1,0),(3,'Empty public form','Test form 2',2,1),(14,'End-of-year big event',NULL,1,0),(15,'Important information request for the party!','Short test form',1,1),(16,'Contact information form',NULL,1,1),(17,'Were do we go for vacations ?',NULL,1,1);
/*!40000 ALTER TABLE `forms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `instances`
--

LOCK TABLES `instances` WRITE;
/*!40000 ALTER TABLE `instances` DISABLE KEYS */;
INSERT INTO `instances` VALUES (3,15,1,'2024-11-13 07:43:47','2024-11-13 07:43:52'),(4,15,1,'2024-11-13 07:43:57','2024-11-13 07:44:01'),(5,15,4,'2024-11-13 07:44:11','2024-11-13 07:44:17'),(6,15,2,'2024-11-13 07:44:49','2024-11-13 07:44:53'),(7,15,2,'2024-11-13 07:45:01','2024-11-13 07:45:04'),(8,15,3,'2024-11-13 07:45:16','2024-11-13 07:45:20'),(9,15,6,'2024-11-13 07:45:38','2024-11-13 07:45:43'),(10,15,6,'2024-11-13 07:45:50',NULL),(11,16,1,'2024-11-15 16:50:31','2024-11-15 16:52:06'),(12,16,6,'2024-11-15 16:52:30','2024-11-15 16:53:57'),(13,16,6,'2024-11-15 16:54:05','2024-11-15 16:55:00'),(14,17,1,'2025-04-08 07:31:50','2025-04-08 10:05:08'),(15,17,6,'2025-04-08 10:16:40','2025-04-08 10:16:54'),(16,17,6,'2025-04-08 10:16:56','2025-04-08 10:17:12'),(17,17,6,'2025-04-08 10:17:15','2025-04-08 10:17:23'),(18,17,1,'2025-04-08 10:18:19','2025-04-08 10:18:30');
/*!40000 ALTER TABLE `instances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `option_values`
--

LOCK TABLES `option_values` WRITE;
/*!40000 ALTER TABLE `option_values` DISABLE KEYS */;
INSERT INTO `option_values` VALUES ('Female',44,2),('Male',44,1),('Other',44,3),('Europe',45,1),('America',45,2),('Asia',45,3),('By plane',46,1),('By train',46,2),('Mountain',47,1),('Sea',47,2),('Lake',47,3),('Camping',48,1),('Hotel',48,3),('Airbnb',48,4),('Resort',48,2);
/*!40000 ALTER TABLE `option_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `questions`
--

LOCK TABLES `questions` WRITE;
/*!40000 ALTER TABLE `questions` DISABLE KEYS */;
INSERT INTO `questions` VALUES (1,1,1,'Your last name?','Your last name','short',1),(2,1,2,'Your first name?','Your first name','short',1),(3,1,4,'Your date of birth?','Your date of birth','date',1),(4,1,5,'Your place of birth?','Your place of birth','short',0),(6,1,6,'Your address?','Your address','short',0),(7,1,7,'Your postal code?','Your postal code','short',0),(8,1,8,'Your city?','Your city of residence','short',0),(10,1,9,'Your phone number?','Your phone number','short',0),(11,1,10,'Your email address?','Your email address','email',1),(15,1,11,'Your profession?','Your profession','short',0),(16,1,12,'Do you have any comments for us?',NULL,'long',0),(19,14,1,'Your name?',NULL,'short',1),(20,14,2,'Your department?',NULL,'long',1),(24,14,3,'Your position?',NULL,'short',1),(26,14,4,'Your email?',NULL,'email',1),(28,14,5,'Your favorites sports?',NULL,'long',0),(29,14,6,'Your favorites hobbies?',NULL,'long',0),(33,14,7,'Any suggestions for the activities we could do at the event?',NULL,'long',0),(35,14,8,'Any suggestions on the location?',NULL,'long',0),(39,15,1,'Favorite drink?','including non-alcoholic!','short',0),(40,16,1,'Your name ?',NULL,'short',1),(41,16,2,'Your email address ?',NULL,'email',1),(42,16,3,'Your birth date ?','Not required, but helpful','date',0),(43,16,4,'How can we assist you ?','We will come back to you as soon as possible','long',0),(44,1,3,'Your gender ?','Your gender','mcq_single',0),(45,17,1,'Any preferences for the continent?',NULL,'mcq_single',0),(46,17,2,'Transportation preference',NULL,'mcq_single',1),(47,17,3,'Location preference',NULL,'mcq_multiple',0),(48,17,4,'Accomodation preference',NULL,'mcq_multiple',1);
/*!40000 ALTER TABLE `questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `user_form_accesses`
--

LOCK TABLES `user_form_accesses` WRITE;
/*!40000 ALTER TABLE `user_form_accesses` DISABLE KEYS */;
INSERT INTO `user_form_accesses` VALUES (1,3,'editor'),(2,2,'editor'),(2,14,'editor'),(4,1,'editor'),(4,2,'editor'),(4,3,'editor'),(4,14,'user');
/*!40000 ALTER TABLE `user_form_accesses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Joe Doe','joe.doe@demo.com','$2y$10$s63N0AZ.LA5/3O05jNANZ.AAyYPKJH7IityYwOzehXKOqXWAW84xi','user'),
                            (2,'Jane Smith','jane.smith@demo.com','$2y$10$s63N0AZ.LA5/3O05jNANZ.AAyYPKJH7IityYwOzehXKOqXWAW84xi','user'),
                            (3,'John Roe','john.roe@demo.com','$2y$10$s63N0AZ.LA5/3O05jNANZ.AAyYPKJH7IityYwOzehXKOqXWAW84xi','user'),
                            (4,'Mary Major','mary.major@demo.com','$2y$10$s63N0AZ.LA5/3O05jNANZ.AAyYPKJH7IityYwOzehXKOqXWAW84xi','user'),
                            (5,'Administrator','admin@demo.com','$2y$10$s63N0AZ.LA5/3O05jNANZ.AAyYPKJH7IityYwOzehXKOqXWAW84xi','admin'),
                            (6,'Guest','guest@demo.com','$2y$10$s63N0AZ.LA5/3O05jNANZ.AAyYPKJH7IityYwOzehXKOqXWAW84xi','guest');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-11 12:35:16
