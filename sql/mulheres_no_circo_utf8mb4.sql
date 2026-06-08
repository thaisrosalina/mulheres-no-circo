-- MariaDB dump 10.19  Distrib 10.4.24-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: mulheres_no_circo
-- ------------------------------------------------------
-- Server version	10.4.24-MariaDB

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
-- Table structure for table `curadoria_salva`
--

DROP TABLE IF EXISTS `curadoria_salva`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `curadoria_salva` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `curador_id` int(11) NOT NULL,
  `artista_id` int(11) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cur_art` (`curador_id`,`artista_id`),
  KEY `artista_id` (`artista_id`),
  CONSTRAINT `cs_artista` FOREIGN KEY (`artista_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cs_curador` FOREIGN KEY (`curador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logs_acesso`
--

DROP TABLE IF EXISTS `logs_acesso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs_acesso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `evento` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `logs_acesso_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mensagens_contato`
--

DROP TABLE IF EXISTS `mensagens_contato`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mensagens_contato` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `assunto` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mensagem` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `lida` tinyint(1) NOT NULL DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `titulo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conteudo` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `imagem` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `arquivo_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_youtube` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tentativas_login`
--

DROP TABLE IF EXISTS `tentativas_login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tentativas_login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sucesso` tinyint(1) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome_artistico` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_nascimento` date DEFAULT NULL,
  `cidade_origem` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade_atual` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `genero` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `orientacao_sexual` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `biografia` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_perfil` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `area_atuacao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `especialidades` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `youtube` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site_oficial` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mapa_cultura` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disponivel_contratacao` tinyint(1) DEFAULT 0,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `remember_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_recuperacao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_expira_em` datetime DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `aceite_termos` tinyint(1) NOT NULL DEFAULT 0,
  `aceite_termos_em` datetime DEFAULT NULL,
  `necessidades_tecnicas` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fotos_divulgacao` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `organizacao` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quer_novidades` tinyint(1) NOT NULL DEFAULT 0,
  `canal_novidades` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-04 12:28:24
-- MariaDB dump 10.19  Distrib 10.4.24-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: mulheres_no_circo
-- ------------------------------------------------------
-- Server version	10.4.24-MariaDB

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
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Thais Oliveira','admin@exemplo.com','$2y$12$cv.S6jylqkaJWm6j46BioOpnrsE/APCR7No3RatRBkgqAHh2/l.WK','1992-02-15','Confins','Belo Horizonte','cisgenero','homossexual','','avatar_1_1780409569.png','','Lira aérea, Faixas aéreas, Rede aérea, Malabares com fogo, Ilusionismo','','','','','',0,'admin',NULL,NULL,NULL,'2026-06-02 14:06:20','2026-06-03 19:33:29',0,NULL,NULL,NULL,NULL,NULL,0,NULL);
INSERT INTO `usuarios` VALUES (2,'Luna Araújo','luna@teste.com','$2y$12$gU6SN4kbQiZfPQdZ2X190um301yx.CVlzJEw5hRDtxc5O7iB6jS8e',NULL,'','','','','',NULL,'Aéreos','Tecido acrobático, Lira aérea, Corda lisa','','','','','',1,'user',NULL,NULL,NULL,'2026-06-02 14:37:40','2026-06-03 19:15:17',0,NULL,NULL,NULL,NULL,NULL,0,NULL);
INSERT INTO `usuarios` VALUES (3,'Maya Valentim','maya@teste.com','$2y$12$gU6SN4kbQiZfPQdZ2X190um301yx.CVlzJEw5hRDtxc5O7iB6jS8e','1989-07-25','Salvador - BA','','cisgenero','bissexual','',NULL,'','','','','','','',0,'user',NULL,NULL,NULL,'2026-06-02 14:37:40','2026-06-03 12:35:17',0,NULL,NULL,NULL,NULL,NULL,0,NULL);
INSERT INTO `usuarios` VALUES (4,'Nina Torres','nina@teste.com','$2y$12$gU6SN4kbQiZfPQdZ2X190um301yx.CVlzJEw5hRDtxc5O7iB6jS8e','1997-11-04','Curitiba - PR','Curitiba - PR','nao_binaria','queer','Performer de malabares, equilíbrio e intervenção urbana. Pesquisa corpo, cidade e arte circense contemporânea.',NULL,'Manipulação de Objetos','Malabares, equilibrismo, intervenção urbana, performance','https://instagram.com/ninatorres','https://youtube.com/@ninatorres','','https://ninatorres.art','https://mapacultural.com/ninatorres',0,'user',NULL,NULL,NULL,'2026-06-02 14:37:40','2026-06-03 19:52:33',0,NULL,NULL,NULL,NULL,NULL,0,NULL);
INSERT INTO `usuarios` VALUES (5,'Helena Duarte','helena@teste.com','$2y$12$gU6SN4kbQiZfPQdZ2X190um301yx.CVlzJEw5hRDtxc5O7iB6jS8e','1991-09-18','Fortaleza - CE','Rio de Janeiro - RJ','transgenero','pansexual','Artista trans circense com atuação em acrobacia de solo, dança e dramaturgia corporal. Trabalha com espetáculos autorais e formação artística.','avatar_5_1780489068.jpg','Acrobacias','Acrobacia de solo, dança, dramaturgia corporal, espetáculos','https://instagram.com/helenaduartecirco','','https://linkedin.com/in/helenaduarte','','https://mapacultural.com/helenaduarte',1,'user',NULL,'46cb154b89adc30700748898531fe5884314b5fb27f0bdf3e48c6480d29b07dd','2026-06-03 20:16:29','2026-06-02 14:37:40','2026-06-03 19:52:33',0,NULL,NULL,NULL,NULL,NULL,0,NULL);
INSERT INTO `usuarios` VALUES (6,'Clara Estrela','clara@teste.com','$2y$12$gU6SN4kbQiZfPQdZ2X190um301yx.CVlzJEw5hRDtxc5O7iB6jS8e','1986-01-30','Porto Alegre - RS','Florianópolis - SC','cisgenero','heterossexual','Diretora circense, trapezista e pesquisadora das artes do corpo. Atua na criação de espetáculos, direção artística e oficinas de trapézio.',NULL,'Aéreos','Trapézio fixo, direção circense, oficinas, criação artística','https://instagram.com/claraestrela','https://youtube.com/@claraestrela','https://linkedin.com/in/claraestrela','https://claraestrela.com.br','https://mapacultural.com/claraestrela',1,'user',NULL,NULL,NULL,'2026-06-02 14:37:40','2026-06-03 19:52:33',0,NULL,NULL,NULL,NULL,NULL,0,NULL);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (1,1,'Perna de Pau',NULL,'Equilibrio na Perna de Pau',NULL,NULL,NULL,'2026-06-03 14:09:23','2026-06-03 14:09:23');
INSERT INTO `posts` VALUES (2,1,'malabares com claves',NULL,'Malabares com claves teste',NULL,NULL,NULL,'2026-06-03 14:10:05','2026-06-03 14:10:05');
INSERT INTO `posts` VALUES (3,1,'Perna de Pau','Performance','Teste de Descrição','trabalho_1_1780497336.jpg',NULL,'','2026-06-03 14:35:36','2026-06-03 14:35:36');
INSERT INTO `posts` VALUES (4,1,'malabares com claves','Performance','teste','trabalho_1_1780497419.jpg',NULL,'https://www.youtube.com/watch?v=Ps3VhxIxRhg&list=RDPs3VhxIxRhg&start_radio=1','2026-06-03 14:36:59','2026-06-03 14:36:59');
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-04 12:28:24
