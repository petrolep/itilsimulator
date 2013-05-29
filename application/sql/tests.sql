/**
 * ITIL Simulator
 * Tests database initialization script
 * 
 * This is test database structure with test data. This file can be used to
 * perform integration and acceptance tests to refresh database.
 * 
 * FOR APPLICATION INSTALLATION USE SCRIPT initialization.sql. NOT THIS ONE.
 */
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

DROP TABLE IF EXISTS `configuration_item_specifications_per_scenario_steps`;
DROP TABLE IF EXISTS `configuration_items_per_services`;
DROP TABLE IF EXISTS `service_specifications_per_scenario_steps`;
DROP TABLE IF EXISTS `workflow_activities_per_workflow_activities`;
DROP TABLE IF EXISTS `workflow_activity_specifications_per_scenario_steps`;
DROP TABLE IF EXISTS `workflow_activity_specifications`;
DROP TABLE IF EXISTS `workflow_activities`;
DROP TABLE IF EXISTS `workflows`;
DROP TABLE IF EXISTS `io_output_per_configuration_item`;
DROP TABLE IF EXISTS `io_input_per_configuration_item`;
DROP TABLE IF EXISTS `service_specifications`;
DROP TABLE IF EXISTS `services_per_scenarios`;
DROP TABLE IF EXISTS `configuration_item_specifications`;
DROP TABLE IF EXISTS `operation_problems`;
DROP TABLE IF EXISTS `operation_incidents`;
DROP TABLE IF EXISTS `operation_events`;
DROP TABLE IF EXISTS `known_issues`;
DROP TABLE IF EXISTS `inputs_outputs`;
DROP TABLE IF EXISTS `configuration_items`;
DROP TABLE IF EXISTS `scenario_design_result`;
DROP TABLE IF EXISTS `operation_categories`;
DROP TABLE IF EXISTS `scenario_steps`;
DROP TABLE IF EXISTS `training_steps`;
DROP TABLE IF EXISTS `scenarios`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `trainings`;
DROP TABLE IF EXISTS `users_per_roles`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `roles`;

--
-- Struktura tabulky `configuration_items`
--

CREATE TABLE IF NOT EXISTS `configuration_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `isGlobal` tinyint(1) NOT NULL,
  `code` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `expectedReliability` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=26 ;

--
-- Vypisuji data pro tabulku `configuration_items`
--

INSERT INTO `configuration_items` (`id`, `name`, `description`, `isGlobal`, `code`, `expectedReliability`) VALUES
(1, 'Router', '', 0, 'ROUTER', NULL),
(2, 'Test global CI', '', 1, '', NULL),
(3, 'Simple CI', 'Not global, local only for service #1', 0, '', NULL),
(4, 'Adassda', 'Abc', 0, '', NULL),
(5, 'Media server', 'asddas', 0, 'MEDIA', NULL),
(14, 'Load balancer', NULL, 1, 'BALANCER', NULL),
(16, 'New dummy service', NULL, 0, '', NULL),
(17, 'Cloud Databáze', NULL, 0, 'CLOUD_DB', NULL),
(18, 'Load balancer', NULL, 0, 'BALANCER', NULL),
(19, 'Datové úložiště', NULL, 0, 'FILESYSTEM', NULL),
(20, 'Server farm', NULL, 0, 'SERVER', NULL),
(21, 'Webserver', NULL, 0, 'WEBSERVER', NULL),
(22, 'ERP', NULL, 0, 'ERP', NULL),
(23, 'Statistiky (server)', NULL, 0, 'STATS', NULL),
(24, 'Microsoft SharePoint', NULL, 0, 'SHAREPOINT', NULL),
(25, 'Tiskárna Canon', NULL, 0, 'PRINT', NULL);

-- --------------------------------------------------------

--
-- Struktura tabulky `configuration_items_per_services`
--

CREATE TABLE IF NOT EXISTS `configuration_items_per_services` (
  `service_id` int(11) NOT NULL,
  `configuration_item_id` int(11) NOT NULL,
  PRIMARY KEY (`service_id`,`configuration_item_id`),
  KEY `IDX_7387007BED5CA9E6` (`service_id`),
  KEY `IDX_7387007B9C279A80` (`configuration_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Vypisuji data pro tabulku `configuration_items_per_services`
--

INSERT INTO `configuration_items_per_services` (`service_id`, `configuration_item_id`) VALUES
(3, 17),
(3, 18),
(3, 19),
(3, 20),
(3, 23),
(4, 21),
(4, 22),
(4, 23),
(5, 22),
(5, 24);

-- --------------------------------------------------------

--
-- Struktura tabulky `configuration_item_specifications`
--

CREATE TABLE IF NOT EXISTS `configuration_item_specifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `priority` int(11) NOT NULL,
  `configurationItem_id` int(11) DEFAULT NULL,
  `isDefault` tinyint(1) NOT NULL,
  `onPing` longtext COLLATE utf8_unicode_ci,
  `purchaseCosts` decimal(10,0) NOT NULL,
  `operationalCosts` decimal(10,0) NOT NULL,
  `data` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `onInputReceived` longtext COLLATE utf8_unicode_ci,
  `onPingRaw` longtext COLLATE utf8_unicode_ci,
  `onInputReceivedRaw` longtext COLLATE utf8_unicode_ci,
  `attributes` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `onRestart` longtext COLLATE utf8_unicode_ci,
  `onRestartRaw` longtext COLLATE utf8_unicode_ci,
  `onReplace` longtext COLLATE utf8_unicode_ci,
  `onReplaceRaw` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `IDX_972A9AF1D2C6ABDC` (`configurationItem_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1392 ;

--
-- Vypisuji data pro tabulku `configuration_item_specifications`
--

INSERT INTO `configuration_item_specifications` (`id`, `priority`, `configurationItem_id`, `isDefault`, `onPing`, `purchaseCosts`, `operationalCosts`, `data`, `onInputReceived`, `onPingRaw`, `onInputReceivedRaw`, `attributes`, `onRestart`, `onRestartRaw`, `onReplace`, `onReplaceRaw`) VALUES
(1, 4, 1, 1, '$random=$_helpers->math->random();if($random%2){$_context->createEvent(''ROUTER PING''.$_helpers->date->internalTime,''Ping generated'');}else{$_context->createEvent(''ROUTER SKIP''.$_helpers->date->internalTime,''Ping skipped'');}if($_context->healthLevel>80){$_context->healthLevel=70;}elseif($_context->healthLevel>50){$_context->healthLevel=40;$_context->setAttribute(''CI1'',33);}else{$_context->healthLevel=0;}', '0', '0', 'a:0:{}', NULL, 'random = Math.random();\r\nif (random % 2) {\r\n  this.createEvent(\r\n    ''ROUTER PING'' + Date.internalTime, \r\n    ''Ping generated'');\r\n} else {\r\n  this.createEvent(\r\n    ''ROUTER SKIP'' + Date.internalTime, \r\n    ''Ping skipped'');\r\n}\r\n\r\nif(this.healthLevel > 80) {\r\n  this.healthLevel = 70;\r\n\r\n} else if(this.healthLevel > 50) {\r\n  this.healthLevel = 40;\r\n  //this.priority = 1;\r\n  this.setAttribute(''CI1'', 33);\r\n\r\n} else {\r\n  this.healthLevel = 0;\r\n}\r\n\r\n/*\r\ncounter = this.getData(''counter'');\r\n\r\nif(counter >= 0 && counter < 2) {\r\n  this.setData(''counter'', counter+1);\r\n\r\n} else if (counter >= 2) {\r\n  this.createEvent(''OUTPUT PING'', \r\n  ''Ping generated'');\r\n  this.generateOutput(''ping'');\r\n  this.setData(''counter'', -1);\r\n}\r\n*/\r\n\r\n/*key = ''startTime'';\r\nstartTime = this.getData(key)\r\nif (!startTime) {\r\n  startTime = Date.internalTime;\r\n  this.setData(key, startTime);\r\n}\r\n\r\nif (Date.internalTime - 20 > startTime) {\r\n  this.generateOutput(''ping'');\r\n  this.setData(key, Date.internalTime);\r\n}*/', '', 'a:2:{s:3:"CI1";O:53:"ITILSimulator\\Runtime\\Training\\CustomServiceAttribute":7:{s:7:"\0*\0name";s:9:"HDD space";s:7:"\0*\0code";s:3:"CI1";s:15:"\0*\0currentValue";s:3:"120";s:15:"\0*\0defaultValue";s:3:"120";s:15:"\0*\0minimumValue";s:0:"";s:15:"\0*\0maximumValue";s:3:"150";s:7:"\0*\0unit";s:2:"GB";}s:3:"CI2";O:53:"ITILSimulator\\Runtime\\Training\\CustomServiceAttribute":7:{s:7:"\0*\0name";s:8:"requests";s:7:"\0*\0code";s:3:"CI2";s:15:"\0*\0currentValue";s:2:"50";s:15:"\0*\0defaultValue";s:2:"50";s:15:"\0*\0minimumValue";s:2:"40";s:15:"\0*\0maximumValue";s:0:"";s:7:"\0*\0unit";s:5:"req/s";}}', '$_context->setAttribute(''CI1'',100);', 'this.setAttribute(''CI1'', 100);', NULL, ''),
(2, 1, 2, 1, '', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 1, 3, 1, '', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 1, 4, 1, '', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 1, 5, 1, NULL, '0', '10', 'a:0:{}', '$_context->createEvent(''MEDIA'',''My media event'');$_context->generateOutput(''snmp'');', '', 'this.createEvent(''MEDIA'', ''My media event'')\r\nthis.generateOutput(''snmp'')', NULL, NULL, NULL, NULL, NULL),
(30, 4, 14, 1, '', '123', '30', 'a:0:{}', '$_context->createEvent(''BALANCER RCV'',''Email received'');$_context->setData(''counter'',5);', '/*\r\ncounter = this.getData(''counter'');\r\nif(counter) {\r\n  this.setData(''counter'', counter - 1);\r\n  if (counter == 1) {\r\n    this.createEvent(''BALANCER SNMP'', ''Data sent'');\r\n    this.generateOutput(''snmp'');\r\n  }\r\n}\r\n*/\r\n;', 'this.createEvent(''BALANCER RCV'', ''Email received'')\r\nthis.setData(''counter'', 5)', NULL, NULL, '', NULL, ''),
(910, 4, 16, 1, NULL, '23400', '23400', 'a:0:{}', NULL, '', '', 'a:0:{}', NULL, '', NULL, ''),
(911, 1, 17, 1, NULL, '40000', '10000', 'a:0:{}', NULL, '', '', 'a:3:{s:12:"TRANSACTIONS";O:53:"ITILSimulator\\Runtime\\Training\\CustomServiceAttribute":7:{s:7:"\0*\0name";s:17:"Počet transakcí";s:7:"\0*\0code";s:12:"TRANSACTIONS";s:15:"\0*\0currentValue";s:4:"1000";s:15:"\0*\0defaultValue";s:4:"1000";s:15:"\0*\0minimumValue";s:3:"700";s:15:"\0*\0maximumValue";s:0:"";s:7:"\0*\0unit";s:5:"txn/s";}s:3:"HDD";O:53:"ITILSimulator\\Runtime\\Training\\CustomServiceAttribute":7:{s:7:"\0*\0name";s:17:"Dostupný prostor";s:7:"\0*\0code";s:3:"HDD";s:15:"\0*\0currentValue";s:2:"10";s:15:"\0*\0defaultValue";s:2:"10";s:15:"\0*\0minimumValue";s:1:"5";s:15:"\0*\0maximumValue";s:0:"";s:7:"\0*\0unit";s:2:"GB";}s:8:"SEEKTIME";O:53:"ITILSimulator\\Runtime\\Training\\CustomServiceAttribute":7:{s:7:"\0*\0name";s:14:"Doba vybavení";s:7:"\0*\0code";s:8:"SEEKTIME";s:15:"\0*\0currentValue";s:1:"5";s:15:"\0*\0defaultValue";s:1:"5";s:15:"\0*\0minimumValue";s:0:"";s:15:"\0*\0maximumValue";s:2:"15";s:7:"\0*\0unit";s:2:"ms";}}', '$_context->setAttribute(''TRANSACTIONS'',1000);', '// restore transactions per second\r\nthis.setAttribute(''TRANSACTIONS'', 1000)', NULL, ''),
(912, 3, 18, 1, NULL, '140000', '13000', 'a:0:{}', 'if($inputCode==''ECHO''){$_context->createEvent(''ECHO_ACK'',''ECHO accepted'');}', '', 'if (inputCode == ''ECHO'') {\r\n  this.createEvent(''ECHO_ACK'', ''ECHO accepted'');\r\n}', 'a:2:{s:4:"TIME";O:53:"ITILSimulator\\Runtime\\Training\\CustomServiceAttribute":7:{s:7:"\0*\0name";s:17:"Čas vyhodnocení";s:7:"\0*\0code";s:4:"TIME";s:15:"\0*\0currentValue";s:2:"10";s:15:"\0*\0defaultValue";s:2:"10";s:15:"\0*\0minimumValue";s:0:"";s:15:"\0*\0maximumValue";s:2:"20";s:7:"\0*\0unit";s:2:"ms";}s:8:"REQUESTS";O:53:"ITILSimulator\\Runtime\\Training\\CustomServiceAttribute":7:{s:7:"\0*\0name";s:33:"Počet obsloužených požadavků";s:7:"\0*\0code";s:8:"REQUESTS";s:15:"\0*\0currentValue";s:3:"300";s:15:"\0*\0defaultValue";s:3:"300";s:15:"\0*\0minimumValue";s:3:"250";s:15:"\0*\0maximumValue";s:0:"";s:7:"\0*\0unit";s:0:"";}}', NULL, '', NULL, ''),
(913, 2, 19, 1, NULL, '140000', '30000', 'a:0:{}', NULL, '', '', 'a:2:{s:5:"SPACE";O:53:"ITILSimulator\\Runtime\\Training\\CustomServiceAttribute":7:{s:7:"\0*\0name";s:18:"Dostupná kapacita";s:7:"\0*\0code";s:5:"SPACE";s:15:"\0*\0currentValue";s:4:"3000";s:15:"\0*\0defaultValue";s:4:"3000";s:15:"\0*\0minimumValue";s:4:"2000";s:15:"\0*\0maximumValue";s:0:"";s:7:"\0*\0unit";s:2:"GB";}s:10:"REDUNDANCY";O:53:"ITILSimulator\\Runtime\\Training\\CustomServiceAttribute":7:{s:7:"\0*\0name";s:10:"Redundance";s:7:"\0*\0code";s:10:"REDUNDANCY";s:15:"\0*\0currentValue";s:1:"2";s:15:"\0*\0defaultValue";s:1:"2";s:15:"\0*\0minimumValue";s:1:"1";s:15:"\0*\0maximumValue";s:0:"";s:7:"\0*\0unit";s:5:"krát";}}', NULL, '', NULL, ''),
(914, 1, 20, 1, 'if(!($_helpers->date->seconds%2)){$_context->generateOutput(''ECHO'');$_context->createEvent(''SERVER_ECHO'',''Regular ECHO msg sent'');}', '300000', '23000', 'a:0:{}', NULL, 'if(!(Date.seconds % 2)) {\r\n  // every odd second send ECHO\r\n  this.generateOutput(''ECHO'')\r\n  this.createEvent(''SERVER_ECHO'', ''Regular ECHO msg sent'');\r\n}', '', 'a:2:{s:5:"TOTAL";O:53:"ITILSimulator\\Runtime\\Training\\CustomServiceAttribute":7:{s:7:"\0*\0name";s:24:"Celkový počet serverů";s:7:"\0*\0code";s:5:"TOTAL";s:15:"\0*\0currentValue";s:2:"14";s:15:"\0*\0defaultValue";s:2:"14";s:15:"\0*\0minimumValue";s:2:"10";s:15:"\0*\0maximumValue";s:0:"";s:7:"\0*\0unit";s:2:"ks";}s:6:"FAILED";O:53:"ITILSimulator\\Runtime\\Training\\CustomServiceAttribute":7:{s:7:"\0*\0name";s:22:"Nedostupných serverů";s:7:"\0*\0code";s:6:"FAILED";s:15:"\0*\0currentValue";s:1:"0";s:15:"\0*\0defaultValue";s:1:"0";s:15:"\0*\0minimumValue";s:0:"";s:15:"\0*\0maximumValue";s:1:"2";s:7:"\0*\0unit";s:2:"ks";}}', NULL, '', NULL, ''),
(915, 2, 21, 1, NULL, '30000', '2500', 'a:0:{}', NULL, '', '', 'a:1:{s:8:"REQUESTS";O:53:"ITILSimulator\\Runtime\\Training\\CustomServiceAttribute":7:{s:7:"\0*\0name";s:18:"Počet požadavků";s:7:"\0*\0code";s:8:"REQUESTS";s:15:"\0*\0currentValue";s:2:"50";s:15:"\0*\0defaultValue";s:2:"50";s:15:"\0*\0minimumValue";s:2:"30";s:15:"\0*\0maximumValue";s:0:"";s:7:"\0*\0unit";s:5:"req/s";}}', NULL, '', NULL, ''),
(916, 2, 22, 1, NULL, '200000', '2000', 'a:0:{}', NULL, '', '', 'a:1:{s:13:"RESPONSE_TIME";O:53:"ITILSimulator\\Runtime\\Training\\CustomServiceAttribute":7:{s:7:"\0*\0name";s:13:"Response time";s:7:"\0*\0code";s:13:"RESPONSE_TIME";s:15:"\0*\0currentValue";s:3:"100";s:15:"\0*\0defaultValue";s:3:"100";s:15:"\0*\0minimumValue";s:0:"";s:15:"\0*\0maximumValue";s:4:"1000";s:7:"\0*\0unit";s:2:"ms";}}', NULL, '', NULL, ''),
(917, 4, 23, 1, NULL, '15000', '2000', 'a:0:{}', NULL, '', '', 'a:1:{s:5:"DELAY";O:53:"ITILSimulator\\Runtime\\Training\\CustomServiceAttribute":7:{s:7:"\0*\0name";s:20:"Zpoždění záznamu";s:7:"\0*\0code";s:5:"DELAY";s:15:"\0*\0currentValue";s:2:"24";s:15:"\0*\0defaultValue";s:2:"24";s:15:"\0*\0minimumValue";s:0:"";s:15:"\0*\0maximumValue";s:2:"48";s:7:"\0*\0unit";s:1:"h";}}', NULL, '', NULL, ''),
(918, 4, 24, 1, 'if(!($_helpers->date->internalTime%10))$_context->createEvent(''BACKUP'',''SharePoint backup finished.'');', '20000', '500', 'a:0:{}', NULL, 'if (!(Date.internalTime % 10))\r\n  this.createEvent(''BACKUP'', ''SharePoint backup finished.'')', '', 'a:0:{}', NULL, '', NULL, ''),
(1285, 3, 25, 1, 'if($_context->waitTil<$_helpers->date->internalTime){$_context->generateOutput(''RANDOM EVENT'');$_context->waitTil=$_helpers->date->internalTime+$_helpers->math->random()%20;}', '10000', '1000', 'a:0:{}', 'if($inputCode==''PRINT_DOCUMENT''){$papers=$_helpers->math->random()%30+1;$availablePapers=$_context->getAttribute(''PAPERS'');if($availablePapers-$papers<0){$_context->createEvent(''NO PAPER'',''Nedostatek volných listů, tisk přerušen.'');}else{$_context->setAttribute(''PAPERS'',$availablePapers-$papers);$_context->createEvent(''PRINT'',''Tisk dokončen, vytisknuto ''.$papers.'' listů. Nový stav ''.$_context->getAttribute(''PAPERS'').'' listů'');}}', '// náhodné naplánované čekání a generování události\r\n// proměnná this.waitTil obsahuje čas příštího spuštění\r\nif (this.waitTil < Date.internalTime) {\r\n  // čas uplynul, vygenerujeme náhodnou událost\r\n  // a naplánujeme další spuštění\r\n  this.generateOutput(''RANDOM EVENT'');\r\n  this.waitTil = Date.internalTime + Math.random() % 20;\r\n}', 'if (inputCode == ''PRINT_DOCUMENT'') {\r\n  // přijali jsme požadavek na tisk dokumentu\r\n  papers = Math.random() % 30 + 1; // náhodný počet listů k tisku\r\n  availablePapers = this.getAttribute(''PAPERS''); // počet dostupných listů v zásobníku\r\n  if (availablePapers - papers < 0) {\r\n    // nedostatek listů\r\n    this.createEvent(''NO PAPER'', ''Nedostatek volných listů, tisk přerušen.'');\r\n  \r\n  } else {\r\n    // tisk, snížení počtu dostupných listů\r\n    this.setAttribute(''PAPERS'', availablePapers - papers);\r\n    this.createEvent(''PRINT'', ''Tisk dokončen, vytisknuto '' + papers + '' listů. Nový stav '' + this.getAttribute(''PAPERS'') + '' listů'');\r\n  }\r\n}', 'a:2:{s:6:"PAPERS";O:53:"ITILSimulator\\Runtime\\Training\\CustomServiceAttribute":7:{s:7:"\0*\0name";s:27:"Počet listů v zásobníku";s:7:"\0*\0code";s:6:"PAPERS";s:15:"\0*\0currentValue";s:3:"100";s:15:"\0*\0defaultValue";s:3:"100";s:15:"\0*\0minimumValue";s:2:"10";s:15:"\0*\0maximumValue";s:0:"";s:7:"\0*\0unit";s:2:"ks";}s:5:"TONER";O:53:"ITILSimulator\\Runtime\\Training\\CustomServiceAttribute":7:{s:7:"\0*\0name";s:5:"Toner";s:7:"\0*\0code";s:5:"TONER";s:15:"\0*\0currentValue";s:3:"100";s:15:"\0*\0defaultValue";s:3:"100";s:15:"\0*\0minimumValue";s:2:"20";s:15:"\0*\0maximumValue";s:0:"";s:7:"\0*\0unit";s:1:"%";}}', '$_context->setAttribute(''TONER'',$_context->getAttribute(''TONER'')-$_helpers->math->random()%10);if($_helpers->math->random()%3==0){$papers=$_helpers->math->random()%30+1;$availablePapers=$_context->getAttribute(''PAPERS'');if($availablePapers-$papers<0){$_context->createEvent(''NO PAPER'',''Nedostatek volných listů, tisk přerušen.'');}else{$_context->setAttribute(''PAPERS'',$availablePapers-$papers);$_context->createEvent(''PRINT'',''Tisk dokončen, vytisknuto ''.$papers.'' listů. Nový stav ''.$_context->getAttribute(''PAPERS'').'' listů'');}}', 'this.setAttribute(''TONER'', this.getAttribute(''TONER'') - Math.random() % 10);\r\nif(Math.random() % 3 == 0) {\r\n  papers = Math.random() % 30 + 1;\r\n  availablePapers = this.getAttribute(''PAPERS'');\r\n  if (availablePapers - papers < 0) {\r\n    this.createEvent(''NO PAPER'', ''Nedostatek volných listů, tisk přerušen.'');\r\n\r\n  } else {\r\n    this.setAttribute(''PAPERS'', availablePapers - papers);\r\n    this.createEvent(''PRINT'', ''Tisk dokončen, vytisknuto '' + papers + '' listů. Nový stav '' + this.getAttribute(''PAPERS'') + '' listů'');\r\n  }\r\n}', NULL, '');

-- --------------------------------------------------------

--
-- Struktura tabulky `configuration_item_specifications_per_scenario_steps`
--

CREATE TABLE IF NOT EXISTS `configuration_item_specifications_per_scenario_steps` (
  `scenario_step_id` int(11) NOT NULL,
  `configuration_item_specification_id` int(11) NOT NULL,
  PRIMARY KEY (`scenario_step_id`,`configuration_item_specification_id`),
  KEY `IDX_3D6394EC6C74DE9C` (`scenario_step_id`),
  KEY `IDX_3D6394EC9313E558` (`configuration_item_specification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Vypisuji data pro tabulku `configuration_item_specifications_per_scenario_steps`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `inputs_outputs`
--

CREATE TABLE IF NOT EXISTS `inputs_outputs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `training_id` int(11) DEFAULT NULL,
  `code` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_8789EBBFBEFD98D1` (`training_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=18 ;

--
-- Vypisuji data pro tabulku `inputs_outputs`
--

INSERT INTO `inputs_outputs` (`id`, `name`, `training_id`, `code`) VALUES
(4, 'Zákazník', 5, 'CUSTOMER'),
(5, 'Statistika služby', 5, 'SERVICE_STATS'),
(6, 'Statistika zákazníka (report)', 5, 'CUSTOMER_STATS_REPORT'),
(7, 'Dokument', 5, 'DOCUMENT'),
(8, 'ICMP echo', 5, 'ECHO'),
(9, 'Soubor (request)', 5, 'FILE_REQUEST'),
(10, 'Zákazník (request)', 5, 'CUSTOMER_REQUEST'),
(11, 'DB záznam (request)', 5, 'DB_RECORD_REQUEST'),
(12, 'DB záznam', 5, 'DB_RECORD'),
(13, 'Soubor', 5, 'FILE'),
(14, 'Dokument (request)', 5, 'DOCUMENT_REQUEST');

-- --------------------------------------------------------

--
-- Struktura tabulky `io_input_per_configuration_item`
--

CREATE TABLE IF NOT EXISTS `io_input_per_configuration_item` (
  `configuration_item_id` int(11) NOT NULL,
  `input_output_id` int(11) NOT NULL,
  PRIMARY KEY (`configuration_item_id`,`input_output_id`),
  KEY `IDX_A786065C9C279A80` (`configuration_item_id`),
  KEY `IDX_A786065C9C209138` (`input_output_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Vypisuji data pro tabulku `io_input_per_configuration_item`
--

INSERT INTO `io_input_per_configuration_item` (`configuration_item_id`, `input_output_id`) VALUES
(17, 11),
(18, 8),
(19, 9),
(20, 12),
(20, 13),
(21, 4),
(21, 6),
(22, 6),
(22, 10),
(23, 5),
(24, 14);

-- --------------------------------------------------------

--
-- Struktura tabulky `io_output_per_configuration_item`
--

CREATE TABLE IF NOT EXISTS `io_output_per_configuration_item` (
  `configuration_item_id` int(11) NOT NULL,
  `input_output_id` int(11) NOT NULL,
  PRIMARY KEY (`configuration_item_id`,`input_output_id`),
  KEY `IDX_3C1989769C279A80` (`configuration_item_id`),
  KEY `IDX_3C1989769C209138` (`input_output_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Vypisuji data pro tabulku `io_output_per_configuration_item`
--

INSERT INTO `io_output_per_configuration_item` (`configuration_item_id`, `input_output_id`) VALUES
(17, 5),
(17, 12),
(19, 5),
(19, 13),
(20, 8),
(20, 9),
(20, 11),
(22, 4),
(23, 6),
(24, 7);

-- --------------------------------------------------------

--
-- Struktura tabulky `known_issues`
--

CREATE TABLE IF NOT EXISTS `known_issues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `training_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `keywords` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `workaround` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `workaroundCost` double NOT NULL,
  `fix` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fixCost` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_EFDAB741BEFD98D1` (`training_id`),
  KEY `IDX_EFDAB74112469DE2` (`category_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

--
-- Vypisuji data pro tabulku `known_issues`
--

INSERT INTO `known_issues` (`id`, `training_id`, `category_id`, `name`, `code`, `keywords`, `description`, `workaround`, `workaroundCost`, `fix`, `fixCost`) VALUES
(2, 5, 7, 'Cloud DB synchronization fail', 'DB_SOCKET_ERR', 'cloud db, databáze, SOCKET_SYNC_ERR', 'Cloud DB občas (výjimečně) selže synchronizace.\r\nChyba se projevuje odmítáním připojení a návratovým kódem SOCKET_SYNC_ERR. DB je pak třeba ji restartovat.', '', 0, 'Stačí tedy restartovat Cloud DB ve službě Cloud!', 0);

-- --------------------------------------------------------

--
-- Struktura tabulky `operation_categories`
--

CREATE TABLE IF NOT EXISTS `operation_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `training_id` int(11) DEFAULT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_746429B5BEFD98D1` (`training_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=17 ;

--
-- Vypisuji data pro tabulku `operation_categories`
--

INSERT INTO `operation_categories` (`id`, `training_id`, `name`) VALUES
(6, 5, 'Požadavky klientů'),
(7, 5, 'Správa HW a SW'),
(8, 5, 'Servisní zásahy');

-- --------------------------------------------------------

--
-- Struktura tabulky `operation_events`
--

CREATE TABLE IF NOT EXISTS `operation_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` int(11) NOT NULL,
  `isUndid` tinyint(1) NOT NULL,
  `scenarioStepFrom_id` int(11) DEFAULT NULL,
  `scenarioStepTo_id` int(11) DEFAULT NULL,
  `date` datetime NOT NULL,
  `code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `originalId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_2E07EE283F740515` (`scenarioStepFrom_id`),
  KEY `IDX_2E07EE284E698928` (`scenarioStepTo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Vypisuji data pro tabulku `operation_events`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `operation_incidents`
--

CREATE TABLE IF NOT EXISTS `operation_incidents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `referenceNumber` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(11) NOT NULL,
  `isUndid` tinyint(1) NOT NULL,
  `scenarioStepFrom_id` int(11) DEFAULT NULL,
  `scenarioStepTo_id` int(11) DEFAULT NULL,
  `date` datetime NOT NULL,
  `level` int(11) NOT NULL,
  `canBeEscalated` tinyint(1) NOT NULL,
  `history` longtext COLLATE utf8_unicode_ci,
  `priority` int(11) DEFAULT NULL,
  `urgency` int(11) DEFAULT NULL,
  `impact` int(11) DEFAULT NULL,
  `symptoms` longtext COLLATE utf8_unicode_ci,
  `timeToResponse` int(11) DEFAULT NULL,
  `timeToResolve` int(11) DEFAULT NULL,
  `isMajor` tinyint(1) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `originalId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_75BDBC323F740515` (`scenarioStepFrom_id`),
  KEY `IDX_75BDBC324E698928` (`scenarioStepTo_id`),
  KEY `IDX_75BDBC3212469DE2` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Vypisuji data pro tabulku `operation_incidents`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `operation_problems`
--

CREATE TABLE IF NOT EXISTS `operation_problems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `referenceNumber` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `history` longtext COLLATE utf8_unicode_ci,
  `priority` int(11) DEFAULT NULL,
  `symptoms` longtext COLLATE utf8_unicode_ci,
  `problemOwner` tinytext COLLATE utf8_unicode_ci,
  `date` datetime NOT NULL,
  `status` int(11) NOT NULL,
  `isUndid` tinyint(1) NOT NULL,
  `scenarioStepFrom_id` int(11) DEFAULT NULL,
  `scenarioStepTo_id` int(11) DEFAULT NULL,
  `originalId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6B101DC812469DE2` (`category_id`),
  KEY `IDX_6B101DC83F740515` (`scenarioStepFrom_id`),
  KEY `IDX_6B101DC84E698928` (`scenarioStepTo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Vypisuji data pro tabulku `operation_problems`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B63E2EC777153098` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

--
-- Vypisuji data pro tabulku `roles`
--

INSERT INTO `roles` (`id`, `name`, `code`) VALUES
(1, 'Administrator', 'admin'),
(2, 'Creator', 'creator'),
(3, 'Student', 'student');

-- --------------------------------------------------------

--
-- Struktura tabulky `scenarios`
--

CREATE TABLE IF NOT EXISTS `scenarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `training_id` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `initialBudget` double NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `designService_id` int(11) DEFAULT NULL,
  `detailDescription` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_9338D025BEFD98D1` (`training_id`),
  KEY `IDX_9338D02570F9CDEC` (`designService_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=10 ;

--
-- Vypisuji data pro tabulku `scenarios`
--

INSERT INTO `scenarios` (`id`, `training_id`, `name`, `description`, `initialBudget`, `type`, `designService_id`, `detailDescription`) VALUES
(6, 5, 'Návrh cloud service', 'Softwarová společnost chce nabídnout svůj systém pro správu dokumentů do prostřední vlastního cloudu. Navrhněte službu, která jí umožní tento produkt uvést na trh.', 0, 'design', 3, 'Cloudového řešení sestává z několika serverů zapojených do farmy (server farm), distribuované databáze (Cloud databáze) a datového úložiště.\r\n\r\nVedení společnosti očekává velký úspěch služby, proto vyžaduje vhodnou distribuci zátěže na jednotlivé stroje (load balancing). Současně chce mít k dispozici souhrnné přehledy o zatížení jednotlivých strojů a o operacích, které zákazníci provádějí. Zákazníky má firma ve interní ERP systému, jeho začlenění ale není součástí této fáze realizace.\r\n\r\nZ dostupných konfiguračních položek sestavte návrh služby, který bude zajišťovat odpovídající výstupy a bude finančně nejvýhodnější.'),
(7, 5, 'Provoz cloud service', 'Seznámení se systémem provozu navrženého cloudového řešení. Jedná se o krátký scénář, který slouží k předvedení funkcí rozhraní Service Desku.', 10000, 'operation', NULL, '');

-- --------------------------------------------------------

--
-- Struktura tabulky `scenario_design_result`
--

CREATE TABLE IF NOT EXISTS `scenario_design_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment` longtext COLLATE utf8_unicode_ci,
  `metadata` longtext COLLATE utf8_unicode_ci,
  `trainingStep_id` int(11) DEFAULT NULL,
  `purchaseCost` double NOT NULL,
  `operationCost` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_1CE2EC5C76D007EC` (`trainingStep_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=10 ;

--
-- Vypisuji data pro tabulku `scenario_design_result`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `scenario_steps`
--

CREATE TABLE IF NOT EXISTS `scenario_steps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `isUndid` tinyint(1) NOT NULL,
  `trainingStep_id` int(11) DEFAULT NULL,
  `evaluationPoints` int(11) NOT NULL,
  `budget` double NOT NULL,
  `undoDate` datetime DEFAULT NULL,
  `internalTime` int(11) NOT NULL,
  `servicesSettlementTime` int(11) NOT NULL,
  `lastActivityDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_1B2DBB2376D007EC` (`trainingStep_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Vypisuji data pro tabulku `scenario_steps`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `services`
--

CREATE TABLE IF NOT EXISTS `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `training_id` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `serviceOwner` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `graphicDesignData` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7332E169BEFD98D1` (`training_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=8 ;

--
-- Vypisuji data pro tabulku `services`
--

INSERT INTO `services` (`id`, `training_id`, `name`, `description`, `serviceOwner`, `code`, `graphicDesignData`) VALUES
(3, 5, 'Cloud platform', 'Poskytování cloudového řešení pro správu dokumentů. Jedná se o hlavní produkt společnosti využívaném největším počtem zákazníků. Dostupnost služby je klíčová.', 'Jan Novák', 'CLOUD', ''),
(4, 5, 'Client portal', 'Zákazní­ci využívající Cloud mají přístup do klientského portálu, kde mohou spravovat nastavení svého systému, evidovat uživatele a své licence.', '', 'PORTAL', ''),
(5, 5, 'Intranet portal', 'Intranet pro zaměstnance, který umožňuje spravovat zákazníky a komunikovat v rámci společnosti.', '', 'INTRANET', ''),
(6, 5, 'Pomocná služba pro design', '', '', 'DUMMY', '');

-- --------------------------------------------------------

--
-- Struktura tabulky `services_per_scenarios`
--

CREATE TABLE IF NOT EXISTS `services_per_scenarios` (
  `scenario_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  PRIMARY KEY (`scenario_id`,`service_id`),
  KEY `IDX_4EAE69E0E04E49DF` (`scenario_id`),
  KEY `IDX_4EAE69E0ED5CA9E6` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Vypisuji data pro tabulku `services_per_scenarios`
--

INSERT INTO `services_per_scenarios` (`scenario_id`, `service_id`) VALUES
(6, 3),
(6, 4),
(6, 5),
(7, 3),
(7, 5);

-- --------------------------------------------------------

--
-- Struktura tabulky `service_specifications`
--

CREATE TABLE IF NOT EXISTS `service_specifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) DEFAULT NULL,
  `priority` int(11) DEFAULT NULL,
  `isDefault` tinyint(1) NOT NULL,
  `earnings` decimal(10,0) NOT NULL,
  `attributes` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `IDX_8BCD7FDED5CA9E6` (`service_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=10 ;

--
-- Vypisuji data pro tabulku `service_specifications`
--

INSERT INTO `service_specifications` (`id`, `service_id`, `priority`, `isDefault`, `earnings`, `attributes`) VALUES
(5, 3, 1, 1, '100000', 'a:0:{}'),
(6, 4, 3, 1, '20000', 'a:0:{}'),
(7, 5, 3, 1, '0', 'a:0:{}'),
(8, 6, 4, 1, '0', 'a:0:{}');

-- --------------------------------------------------------

--
-- Struktura tabulky `service_specifications_per_scenario_steps`
--

CREATE TABLE IF NOT EXISTS `service_specifications_per_scenario_steps` (
  `scenario_step_id` int(11) NOT NULL,
  `service_specification_id` int(11) NOT NULL,
  PRIMARY KEY (`scenario_step_id`,`service_specification_id`),
  KEY `IDX_AA556ACD6C74DE9C` (`scenario_step_id`),
  KEY `IDX_AA556ACD37CB08EF` (`service_specification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Vypisuji data pro tabulku `service_specifications_per_scenario_steps`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `training_id` int(11) DEFAULT NULL,
  `is_finished` tinyint(1) NOT NULL,
  `date_start` datetime NOT NULL,
  `date_end` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_9A609D13A76ED395` (`user_id`),
  KEY `IDX_9A609D13BEFD98D1` (`training_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Vypisuji data pro tabulku `sessions`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `trainings`
--

CREATE TABLE IF NOT EXISTS `trainings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `isPublic` tinyint(1) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `shortDescription` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `isPublished` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_66DC4330A76ED395` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

--
-- Vypisuji data pro tabulku `trainings`
--

INSERT INTO `trainings` (`id`, `name`, `isPublic`, `user_id`, `shortDescription`, `description`, `isPublished`) VALUES
(5, 'Cloudové řešení ukládání dokumentů', 1, 1, 'Ukázkový výcvik zahrnující scénář návrhu a provozu služeb. Výcvik představuje rozhraní aplikace a provádí uživatele návrhem i provozem fiktivní služby zaměřené na ukládání dokumentů v cloudu.', 'Obsahuje dva scénáře -- návrh služby a její provoz, přičemž v provozu jsou prezentovány možnosti archivace událostí a řešení incidentu. Ideální příležitost na otestování aplikace.', 1);

-- --------------------------------------------------------

--
-- Struktura tabulky `training_steps`
--

CREATE TABLE IF NOT EXISTS `training_steps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) DEFAULT NULL,
  `scenario_id` int(11) DEFAULT NULL,
  `dateStart` datetime NOT NULL,
  `dateEnd` datetime NOT NULL,
  `isFinished` tinyint(1) NOT NULL,
  `evaluationPoints` int(11) DEFAULT NULL,
  `budget` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_DBF8801F613FECDF` (`session_id`),
  KEY `IDX_DBF8801FE04E49DF` (`scenario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Vypisuji data pro tabulku `training_steps`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dateRegistration` datetime NOT NULL,
  `dateLastLogin` datetime DEFAULT NULL,
  `passwordSalt` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `isAnonymous` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_1483A5E9E7927C74` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=11 ;

--
-- Vypisuji data pro tabulku `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `dateRegistration`, `dateLastLogin`, `passwordSalt`, `isAnonymous`) VALUES
(1, 'System Administrator', 'admin@example.com', 'db08a594ccebf226356221a34b7a09555bb1d9be', '0000-00-00 00:00:00', '2013-05-02 23:45:17', '7de1uuu54wer1cy8oausaz', 0);

-- --------------------------------------------------------

--
-- Struktura tabulky `users_per_roles`
--

CREATE TABLE IF NOT EXISTS `users_per_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `IDX_1EEFA1BFA76ED395` (`user_id`),
  KEY `IDX_1EEFA1BFD60322AC` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Vypisuji data pro tabulku `users_per_roles`
--

INSERT INTO `users_per_roles` (`user_id`, `role_id`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Struktura tabulky `workflows`
--

CREATE TABLE IF NOT EXISTS `workflows` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scenario_id` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_EFBFBFC2E04E49DF` (`scenario_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=14 ;

--
-- Vypisuji data pro tabulku `workflows`
--

INSERT INTO `workflows` (`id`, `scenario_id`, `name`) VALUES
(11, 7, 'Seznámení se systémem');

-- --------------------------------------------------------

--
-- Struktura tabulky `workflow_activities`
--

CREATE TABLE IF NOT EXISTS `workflow_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workflow_id` int(11) DEFAULT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `activityType` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `onEvent` longtext COLLATE utf8_unicode_ci,
  `metadata` longtext COLLATE utf8_unicode_ci,
  `type` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `points` int(11) DEFAULT NULL,
  `money` double DEFAULT NULL,
  `title` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `priority` int(11) DEFAULT NULL,
  `urgency` int(11) DEFAULT NULL,
  `impact` int(11) DEFAULT NULL,
  `symptoms` longtext COLLATE utf8_unicode_ci,
  `timeToResponse` int(11) DEFAULT NULL,
  `timeToResolve` int(11) DEFAULT NULL,
  `isMajor` tinyint(1) DEFAULT NULL,
  `serviceDeskLevel` int(11) DEFAULT NULL,
  `onStart` longtext COLLATE utf8_unicode_ci,
  `referenceNumber` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `canBeEscalated` tinyint(1) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `problemOwner` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `onEventRaw` longtext COLLATE utf8_unicode_ci,
  `onStartRaw` longtext COLLATE utf8_unicode_ci,
  `status` int(11) DEFAULT NULL,
  `onCancel` longtext COLLATE utf8_unicode_ci,
  `onCancelRaw` longtext COLLATE utf8_unicode_ci,
  `onFinish` longtext COLLATE utf8_unicode_ci,
  `onFinishRaw` longtext COLLATE utf8_unicode_ci,
  `onFlow` longtext COLLATE utf8_unicode_ci,
  `onFlowRaw` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `FK_28DE71D02C7C2CBA` (`workflow_id`),
  KEY `IDX_28DE71D012469DE2` (`category_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=196 ;

--
-- Vypisuji data pro tabulku `workflow_activities`
--

INSERT INTO `workflow_activities` (`id`, `workflow_id`, `description`, `activityType`, `onEvent`, `metadata`, `type`, `points`, `money`, `title`, `priority`, `urgency`, `impact`, `symptoms`, `timeToResponse`, `timeToResolve`, `isMajor`, `serviceDeskLevel`, `onStart`, `referenceNumber`, `canBeEscalated`, `category_id`, `problemOwner`, `onEventRaw`, `onStartRaw`, `status`, `onCancel`, `onCancelRaw`, `onFinish`, `onFinishRaw`, `onFlow`, `onFlowRaw`) VALUES
(120, 11, NULL, 'start', NULL, 'O:48:"ITILSimulator\\Entities\\Workflow\\ActivityMetadata":1:{s:11:"\0*\0position";O:27:"ITILSimulator\\Base\\Position":2:{s:4:"\0*\0x";i:17;s:4:"\0*\0y";i:32;}}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(121, 11, NULL, 'finish', NULL, 'O:48:"ITILSimulator\\Entities\\Workflow\\ActivityMetadata":1:{s:11:"\0*\0position";O:27:"ITILSimulator\\Base\\Position":2:{s:4:"\0*\0x";i:1141;s:4:"\0*\0y";i:301;}}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(123, 11, 'Toto rozhraní se zabývá provozem služeb. V horní části obrazovky jsou zobrazeny sledované služby a jejich aktuální stav, včetně jejich konfiguračních položek. \r\n\r\nV dolní části je panel Service Desku, ve kterém lze sledovat události (eventy), incidenty a problémy. Dále pak databáze známých chyb a základní monitorovací nástroje. \r\n\r\nV neposlední řadě se tam také nachází průběžné hodnocení.\r\n\r\nTak směle do toho!', 'message', NULL, 'O:48:"ITILSimulator\\Entities\\Workflow\\ActivityMetadata":1:{s:11:"\0*\0position";O:27:"ITILSimulator\\Base\\Position":2:{s:4:"\0*\0x";i:-12;s:4:"\0*\0y";i:169;}}', 'info', NULL, NULL, 'Vítej v provozu služeb', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(124, 11, 'V Service Desku v Event managementu se objevují události generované konfiguračními položkami. Mohou to být pouze pravidelné informační zprávy, ale i varovná či dokonce chybová hlášení. Proto je třeba občas tyto události zkontrolovat. \r\n\r\nPokud je událost v pořádku, lze ji archivovat. V praxi existují pravidla, která události odpovídající určitému typu archivují automaticky.\r\n\r\nÚkol: najdi v seznamu událost signalizující úspěšné dokončení zálohy a archivuj ji.\r\nNápověda: událost generuje "Microsoft SharePoint" a objeví se pouze jednou za čas.', 'message', NULL, 'O:48:"ITILSimulator\\Entities\\Workflow\\ActivityMetadata":1:{s:11:"\0*\0position";O:27:"ITILSimulator\\Base\\Position":2:{s:4:"\0*\0x";i:0;s:4:"\0*\0y";i:287;}}', 'info', NULL, NULL, 'Event management', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(125, 11, NULL, 'flow', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(126, 11, 'wait', 'flow', 'if($_context->startTime+20<$_helpers->date->internalTime)$_context->finish();', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$_context->startTime=$_helpers->date->internalTime;', NULL, NULL, NULL, NULL, 'if (this.startTime + 20 < Date.internalTime)\r\n  this.finish()', 'this.startTime = Date.internalTime', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(127, 11, 'Úkolem bylo provést archivaci informace o dokončení zálohování, nikoliv archivovat něco jiného! Nevadí, zkus to znovu.', 'message', NULL, 'O:48:"ITILSimulator\\Entities\\Workflow\\ActivityMetadata":1:{s:11:"\0*\0position";O:27:"ITILSimulator\\Base\\Position":2:{s:4:"\0*\0x";i:-6;s:4:"\0*\0y";i:461;}}', 'error', NULL, NULL, 'Špatně.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(128, 11, 'Skvěle, archivaci událostí zvládáme. Pokročíme tedy k dalšímu úkolu.', 'message', NULL, 'O:48:"ITILSimulator\\Entities\\Workflow\\ActivityMetadata":1:{s:11:"\0*\0position";O:27:"ITILSimulator\\Base\\Position":2:{s:4:"\0*\0x";i:324;s:4:"\0*\0y";i:282;}}', 'success', NULL, NULL, 'Skvěle!', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(129, 11, NULL, 'evaluation', NULL, 'O:48:"ITILSimulator\\Entities\\Workflow\\ActivityMetadata":1:{s:11:"\0*\0position";O:27:"ITILSimulator\\Base\\Position":2:{s:4:"\0*\0x";i:232;s:4:"\0*\0y";i:465;}}', NULL, -5, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(130, 11, NULL, 'evaluation', NULL, 'O:48:"ITILSimulator\\Entities\\Workflow\\ActivityMetadata":1:{s:11:"\0*\0position";O:27:"ITILSimulator\\Base\\Position":2:{s:4:"\0*\0x";i:317;s:4:"\0*\0y";i:151;}}', NULL, 15, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(131, 11, 'správná archivace', 'flow', 'if($event->type==\\ITILSimulator\\Runtime\\Events\\EventTypeEnum::RUNTIME_EVENT_ARCHIVED&&$event->code==''BACKUP'')$_context->finish();', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'if (event.type == eventTypeEnum.EVENT_ARCHIVED && event.code == ''BACKUP'')\r\n  this.finish()', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(132, 11, 'špatná archivace', 'flow', 'if($event->type==\\ITILSimulator\\Runtime\\Events\\EventTypeEnum::RUNTIME_EVENT_ARCHIVED&&$event->code!=''BACKUP'')$_context->finish();', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'if (event.type == eventTypeEnum.EVENT_ARCHIVED && event.code != ''BACKUP'')\r\n  this.finish()', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(133, 11, NULL, 'flow', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(134, 11, NULL, 'flow', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(137, 11, 'A to je vše, přátelé.', 'message', NULL, 'O:48:"ITILSimulator\\Entities\\Workflow\\ActivityMetadata":1:{s:11:"\0*\0position";O:27:"ITILSimulator\\Base\\Position":2:{s:4:"\0*\0x";i:914;s:4:"\0*\0y";i:489;}}', 'success', NULL, NULL, 'Finíto!', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(140, 11, NULL, 'flow', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(141, 11, NULL, 'flow', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(142, 11, 'Ouha, vypadá to, že klíčová služba Cloudu má nějaké problémy. Zákazníci si stěžují, že nemohou nahrávat data. Podívej se na incident, který byl do systému založen a vyřeš jej.\r\n\r\nMožná se jedná o známou situaci, tak se zkus podívat do Známých chyb, třeba tam řešení najdeš.', 'message', NULL, 'O:48:"ITILSimulator\\Entities\\Workflow\\ActivityMetadata":1:{s:11:"\0*\0position";O:27:"ITILSimulator\\Base\\Position":2:{s:4:"\0*\0x";i:670;s:4:"\0*\0y";i:26;}}', 'warning', NULL, NULL, 'Řešení incidentu', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(143, 11, NULL, 'incident', NULL, 'O:48:"ITILSimulator\\Entities\\Workflow\\ActivityMetadata":1:{s:11:"\0*\0position";O:27:"ITILSimulator\\Base\\Position":2:{s:4:"\0*\0x";i:361;s:4:"\0*\0y";i:26;}}', NULL, NULL, NULL, NULL, 1, 1, 1, 'Zákazníci nemají přístup k datům v cloudu, vypadá to na výpadek databáze. Systém jim vrací chybu "SOCKET_SYNC_ERR".\r\n\r\nUrgentní!!!', 0, 0, 1, 1, NULL, 'CLOUD-001', 1, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(144, 11, 'Pravda, předat problém na někoho dalšího také lze, nicméně takové řešení trvá delší dobu a stojí více peněz.\r\n\r\nŘešení incidentu bylo možné nalézt ve Známých chybách, stačilo konfigurační položku databáze restartovat.', 'message', NULL, 'O:48:"ITILSimulator\\Entities\\Workflow\\ActivityMetadata":1:{s:11:"\0*\0position";O:27:"ITILSimulator\\Base\\Position":2:{s:4:"\0*\0x";i:839;s:4:"\0*\0y";i:149;}}', 'success', NULL, NULL, '2nd level to vyřeší', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(145, 11, 'Řešení dle očekávání, skvěle!', 'message', NULL, 'O:48:"ITILSimulator\\Entities\\Workflow\\ActivityMetadata":1:{s:11:"\0*\0position";O:27:"ITILSimulator\\Base\\Position":2:{s:4:"\0*\0x";i:562;s:4:"\0*\0y";i:254;}}', 'success', NULL, NULL, 'Výborně', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(146, 11, NULL, 'evaluation', NULL, 'O:48:"ITILSimulator\\Entities\\Workflow\\ActivityMetadata":1:{s:11:"\0*\0position";O:27:"ITILSimulator\\Base\\Position":2:{s:4:"\0*\0x";i:867;s:4:"\0*\0y";i:310;}}', NULL, 10, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(147, 11, NULL, 'evaluation', NULL, 'O:48:"ITILSimulator\\Entities\\Workflow\\ActivityMetadata":1:{s:11:"\0*\0position";O:27:"ITILSimulator\\Base\\Position":2:{s:4:"\0*\0x";i:620;s:4:"\0*\0y";i:396;}}', NULL, 30, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(148, 11, 'damage Cloud DB', 'flow', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$_context->setCIValue(''CLOUD'',''CLOUD_DB'',''TRANSACTIONS'',20);', NULL, NULL, NULL, NULL, '', '// damage the Cloud DB\r\ncontext.setCIValue(''CLOUD'', ''CLOUD_DB'', ''TRANSACTIONS'', 20)', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(149, 11, NULL, 'flow', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(150, 11, 'escalace', 'flow', 'if($event->type==\\ITILSimulator\\Runtime\\Events\\EventTypeEnum::RUNTIME_INCIDENT_ESCALATED&&$event->source==''CLOUD-001'')$_context->finish();', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'if(event.type == eventTypeEnum.INCIDENT_ESCALATED && event.source == ''CLOUD-001'')\r\n  this.finish()', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(151, 11, 'restart DB', 'flow', 'if($event->type==\\ITILSimulator\\Runtime\\Events\\EventTypeEnum::RUNTIME_CONFIGURATION_ITEM_RESTARTED&&$event->source==''CLOUD_DB'')$_context->finish();', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'if(event.type == eventTypeEnum.CONFIGURATION_ITEM_RESTARTED && event.source == ''CLOUD_DB'')\r\n  this.finish()', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(152, 11, NULL, 'flow', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(153, 11, NULL, 'flow', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(154, 11, 'wait', 'flow', 'if($_context->startTime+20<$_helpers->date->internalTime)$_context->finish();', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$_context->startTime=$_helpers->date->internalTime;', NULL, NULL, NULL, NULL, 'if (this.startTime + 20 < Date.internalTime)\r\n  this.finish()', 'this.startTime = Date.internalTime', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(155, 11, 'wait', 'flow', 'if($_context->startTime+20<$_helpers->date->internalTime)$_context->finish();', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$_context->startTime=$_helpers->date->internalTime;', NULL, NULL, NULL, NULL, 'if (this.startTime + 20 < Date.internalTime)\r\n  this.finish()', 'this.startTime = Date.internalTime', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(156, 11, 'Vyhodit problémovou konfigurační položku a nahradit ji novou přináší velké finanční náklady a není zrovna ekonomické.', 'message', NULL, 'O:48:"ITILSimulator\\Entities\\Workflow\\ActivityMetadata":1:{s:11:"\0*\0position";O:27:"ITILSimulator\\Base\\Position":2:{s:4:"\0*\0x";i:1065;s:4:"\0*\0y";i:114;}}', 'warning', NULL, NULL, 'Tak snadné to není', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(157, 11, 'replace CI', 'flow', 'if($event->type==\\ITILSimulator\\Runtime\\Events\\EventTypeEnum::RUNTIME_CONFIGURATION_ITEM_REPLACED&&$event->source==''CLOUD_DB'')$_context->finish();', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'if (event.type == eventTypeEnum.CONFIGURATION_ITEM_REPLACED && event.source == ''CLOUD_DB'')\r\n  this.finish();', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(158, 11, NULL, 'flow', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktura tabulky `workflow_activities_per_workflow_activities`
--

CREATE TABLE IF NOT EXISTS `workflow_activities_per_workflow_activities` (
  `source_activity_id` int(11) NOT NULL,
  `target_activity_id` int(11) NOT NULL,
  PRIMARY KEY (`source_activity_id`,`target_activity_id`),
  KEY `IDX_5DE458E1BDBF3036` (`source_activity_id`),
  KEY `IDX_5DE458E16FA2F9C` (`target_activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Vypisuji data pro tabulku `workflow_activities_per_workflow_activities`
--

INSERT INTO `workflow_activities_per_workflow_activities` (`source_activity_id`, `target_activity_id`) VALUES
(120, 125),
(123, 126),
(124, 131),
(124, 132),
(125, 123),
(126, 124),
(127, 133),
(128, 134),
(129, 141),
(130, 148),
(131, 128),
(132, 127),
(133, 129),
(134, 130),
(137, 140),
(140, 121),
(141, 124),
(142, 150),
(142, 151),
(142, 157),
(143, 149),
(144, 153),
(145, 152),
(146, 155),
(147, 154),
(148, 143),
(149, 142),
(150, 144),
(151, 145),
(152, 147),
(153, 146),
(154, 137),
(155, 137),
(156, 158),
(157, 156),
(158, 146);

-- --------------------------------------------------------

--
-- Struktura tabulky `workflow_activity_specifications`
--

CREATE TABLE IF NOT EXISTS `workflow_activity_specifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` longtext COLLATE utf8_unicode_ci,
  `workflowActivity_id` int(11) DEFAULT NULL,
  `state` int(11) NOT NULL,
  `isDefault` tinyint(1) NOT NULL,
  `data` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  PRIMARY KEY (`id`),
  KEY `IDX_19389EADF938C10C` (`workflowActivity_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2342 ;

--
-- Vypisuji data pro tabulku `workflow_activity_specifications`
--

INSERT INTO `workflow_activity_specifications` (`id`, `description`, `workflowActivity_id`, `state`, `isDefault`, `data`) VALUES
(1195, NULL, 120, 2, 1, 'a:0:{}'),
(1196, NULL, 121, 1, 1, 'a:0:{}'),
(1198, NULL, 123, 1, 1, 'a:0:{}'),
(1199, NULL, 124, 1, 1, 'a:0:{}'),
(1200, NULL, 125, 1, 1, 'a:0:{}'),
(1201, NULL, 126, 1, 1, 'a:0:{}'),
(1202, NULL, 127, 1, 1, 'a:0:{}'),
(1203, NULL, 128, 1, 1, 'a:0:{}'),
(1204, NULL, 129, 1, 1, 'a:0:{}'),
(1205, NULL, 130, 1, 1, 'a:0:{}'),
(1206, NULL, 131, 1, 1, 'a:0:{}'),
(1207, NULL, 132, 1, 1, 'a:0:{}'),
(1208, NULL, 133, 1, 1, 'a:0:{}'),
(1209, NULL, 134, 1, 1, 'a:0:{}'),
(1212, NULL, 137, 1, 1, 'a:0:{}'),
(1215, NULL, 140, 1, 1, 'a:0:{}'),
(1247, NULL, 141, 1, 1, 'a:0:{}'),
(1360, NULL, 142, 1, 1, 'a:0:{}'),
(1361, NULL, 143, 1, 1, 'a:0:{}'),
(1362, NULL, 144, 1, 1, 'a:0:{}'),
(1363, NULL, 145, 1, 1, 'a:0:{}'),
(1364, NULL, 146, 1, 1, 'a:0:{}'),
(1365, NULL, 147, 1, 1, 'a:0:{}'),
(1366, NULL, 148, 1, 1, 'a:0:{}'),
(1367, NULL, 149, 1, 1, 'a:0:{}'),
(1368, NULL, 150, 1, 1, 'a:0:{}'),
(1369, NULL, 151, 1, 1, 'a:0:{}'),
(1370, NULL, 152, 1, 1, 'a:0:{}'),
(1371, NULL, 153, 1, 1, 'a:0:{}'),
(1372, NULL, 154, 1, 1, 'a:0:{}'),
(1373, NULL, 155, 1, 1, 'a:0:{}'),
(1405, NULL, 156, 1, 1, 'a:0:{}'),
(1406, NULL, 157, 1, 1, 'a:0:{}'),
(1407, NULL, 158, 1, 1, 'a:0:{}');

-- --------------------------------------------------------

--
-- Struktura tabulky `workflow_activity_specifications_per_scenario_steps`
--

CREATE TABLE IF NOT EXISTS `workflow_activity_specifications_per_scenario_steps` (
  `scenario_step_id` int(11) NOT NULL,
  `workflow_activity_specification_id` int(11) NOT NULL,
  PRIMARY KEY (`scenario_step_id`,`workflow_activity_specification_id`),
  KEY `IDX_375A964A6C74DE9C` (`scenario_step_id`),
  KEY `IDX_375A964A8270BD0B` (`workflow_activity_specification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Vypisuji data pro tabulku `workflow_activity_specifications_per_scenario_steps`
--


--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `configuration_items_per_services`
--
ALTER TABLE `configuration_items_per_services`
  ADD CONSTRAINT `FK_7387007B9C279A80` FOREIGN KEY (`configuration_item_id`) REFERENCES `configuration_items` (`id`),
  ADD CONSTRAINT `FK_7387007BED5CA9E6` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Omezení pro tabulku `configuration_item_specifications`
--
ALTER TABLE `configuration_item_specifications`
  ADD CONSTRAINT `FK_972A9AF1D2C6ABDC` FOREIGN KEY (`configurationItem_id`) REFERENCES `configuration_items` (`id`);

--
-- Omezení pro tabulku `configuration_item_specifications_per_scenario_steps`
--
ALTER TABLE `configuration_item_specifications_per_scenario_steps`
  ADD CONSTRAINT `FK_3D6394EC6C74DE9C` FOREIGN KEY (`scenario_step_id`) REFERENCES `scenario_steps` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_3D6394EC9313E558` FOREIGN KEY (`configuration_item_specification_id`) REFERENCES `configuration_item_specifications` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `inputs_outputs`
--
ALTER TABLE `inputs_outputs`
  ADD CONSTRAINT `FK_8789EBBFBEFD98D1` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`);

--
-- Omezení pro tabulku `io_input_per_configuration_item`
--
ALTER TABLE `io_input_per_configuration_item`
  ADD CONSTRAINT `FK_A786065C9C209138` FOREIGN KEY (`input_output_id`) REFERENCES `inputs_outputs` (`id`),
  ADD CONSTRAINT `FK_A786065C9C279A80` FOREIGN KEY (`configuration_item_id`) REFERENCES `configuration_items` (`id`);

--
-- Omezení pro tabulku `io_output_per_configuration_item`
--
ALTER TABLE `io_output_per_configuration_item`
  ADD CONSTRAINT `FK_3C1989769C209138` FOREIGN KEY (`input_output_id`) REFERENCES `inputs_outputs` (`id`),
  ADD CONSTRAINT `FK_3C1989769C279A80` FOREIGN KEY (`configuration_item_id`) REFERENCES `configuration_items` (`id`);

--
-- Omezení pro tabulku `known_issues`
--
ALTER TABLE `known_issues`
  ADD CONSTRAINT `FK_EFDAB74112469DE2` FOREIGN KEY (`category_id`) REFERENCES `operation_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_EFDAB741BEFD98D1` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`);

--
-- Omezení pro tabulku `operation_categories`
--
ALTER TABLE `operation_categories`
  ADD CONSTRAINT `FK_746429B5BEFD98D1` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`);

--
-- Omezení pro tabulku `operation_events`
--
ALTER TABLE `operation_events`
  ADD CONSTRAINT `FK_2E07EE283F740515` FOREIGN KEY (`scenarioStepFrom_id`) REFERENCES `scenario_steps` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_2E07EE284E698928` FOREIGN KEY (`scenarioStepTo_id`) REFERENCES `scenario_steps` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `operation_incidents`
--
ALTER TABLE `operation_incidents`
  ADD CONSTRAINT `FK_75BDBC3212469DE2` FOREIGN KEY (`category_id`) REFERENCES `operation_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_75BDBC323F740515` FOREIGN KEY (`scenarioStepFrom_id`) REFERENCES `scenario_steps` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_75BDBC324E698928` FOREIGN KEY (`scenarioStepTo_id`) REFERENCES `scenario_steps` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `operation_problems`
--
ALTER TABLE `operation_problems`
  ADD CONSTRAINT `FK_6B101DC812469DE2` FOREIGN KEY (`category_id`) REFERENCES `operation_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_6B101DC83F740515` FOREIGN KEY (`scenarioStepFrom_id`) REFERENCES `scenario_steps` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_6B101DC84E698928` FOREIGN KEY (`scenarioStepTo_id`) REFERENCES `scenario_steps` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `scenarios`
--
ALTER TABLE `scenarios`
  ADD CONSTRAINT `FK_9338D02570F9CDEC` FOREIGN KEY (`designService_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `FK_9338D025BEFD98D1` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`);

--
-- Omezení pro tabulku `scenario_design_result`
--
ALTER TABLE `scenario_design_result`
  ADD CONSTRAINT `FK_1CE2EC5C76D007EC` FOREIGN KEY (`trainingStep_id`) REFERENCES `training_steps` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `scenario_steps`
--
ALTER TABLE `scenario_steps`
  ADD CONSTRAINT `FK_1B2DBB2376D007EC` FOREIGN KEY (`trainingStep_id`) REFERENCES `training_steps` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `FK_7332E169BEFD98D1` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`);

--
-- Omezení pro tabulku `services_per_scenarios`
--
ALTER TABLE `services_per_scenarios`
  ADD CONSTRAINT `FK_4EAE69E0E04E49DF` FOREIGN KEY (`scenario_id`) REFERENCES `scenarios` (`id`),
  ADD CONSTRAINT `FK_4EAE69E0ED5CA9E6` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Omezení pro tabulku `service_specifications`
--
ALTER TABLE `service_specifications`
  ADD CONSTRAINT `FK_8BCD7FDED5CA9E6` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Omezení pro tabulku `service_specifications_per_scenario_steps`
--
ALTER TABLE `service_specifications_per_scenario_steps`
  ADD CONSTRAINT `FK_AA556ACD37CB08EF` FOREIGN KEY (`service_specification_id`) REFERENCES `service_specifications` (`id`),
  ADD CONSTRAINT `FK_AA556ACD6C74DE9C` FOREIGN KEY (`scenario_step_id`) REFERENCES `scenario_steps` (`id`);

--
-- Omezení pro tabulku `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `FK_9A609D13BEFD98D1` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_9A609D13A76ED395` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Omezení pro tabulku `trainings`
--
ALTER TABLE `trainings`
  ADD CONSTRAINT `FK_66DC4330A76ED395` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `training_steps`
--
ALTER TABLE `training_steps`
  ADD CONSTRAINT `FK_DBF8801F613FECDF` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`),
  ADD CONSTRAINT `FK_DBF8801FE04E49DF` FOREIGN KEY (`scenario_id`) REFERENCES `scenarios` (`id`);

--
-- Omezení pro tabulku `users_per_roles`
--
ALTER TABLE `users_per_roles`
  ADD CONSTRAINT `FK_1EEFA1BFA76ED395` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `FK_1EEFA1BFD60322AC` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Omezení pro tabulku `workflows`
--
ALTER TABLE `workflows`
  ADD CONSTRAINT `FK_EFBFBFC2E04E49DF` FOREIGN KEY (`scenario_id`) REFERENCES `scenarios` (`id`);

--
-- Omezení pro tabulku `workflow_activities`
--
ALTER TABLE `workflow_activities`
  ADD CONSTRAINT `FK_28DE71D012469DE2` FOREIGN KEY (`category_id`) REFERENCES `operation_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_28DE71D02C7C2CBA` FOREIGN KEY (`workflow_id`) REFERENCES `workflows` (`id`);

--
-- Omezení pro tabulku `workflow_activities_per_workflow_activities`
--
ALTER TABLE `workflow_activities_per_workflow_activities`
  ADD CONSTRAINT `FK_5DE458E16FA2F9C` FOREIGN KEY (`target_activity_id`) REFERENCES `workflow_activities` (`id`),
  ADD CONSTRAINT `FK_5DE458E1BDBF3036` FOREIGN KEY (`source_activity_id`) REFERENCES `workflow_activities` (`id`);

--
-- Omezení pro tabulku `workflow_activity_specifications`
--
ALTER TABLE `workflow_activity_specifications`
  ADD CONSTRAINT `FK_19389EADF938C10C` FOREIGN KEY (`workflowActivity_id`) REFERENCES `workflow_activities` (`id`);

--
-- Omezení pro tabulku `workflow_activity_specifications_per_scenario_steps`
--
ALTER TABLE `workflow_activity_specifications_per_scenario_steps`
  ADD CONSTRAINT `FK_375A964A6C74DE9C` FOREIGN KEY (`scenario_step_id`) REFERENCES `scenario_steps` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_375A964A8270BD0B` FOREIGN KEY (`workflow_activity_specification_id`) REFERENCES `workflow_activity_specifications` (`id`) ON DELETE CASCADE;

--
-- Test data
--
INSERT INTO `sessions` (`id`, `user_id`, `training_id`, `is_finished`, `date_start`, `date_end`) VALUES
(1, 1, 5, 0, '2013-05-21 15:16:53', '2013-05-21 15:16:53');

INSERT INTO `training_steps` (`id`, `session_id`, `scenario_id`, `dateStart`, `dateEnd`, `isFinished`, `evaluationPoints`, `budget`) VALUES
(1, 1, 7, '2013-05-21 15:16:58', '2013-05-21 15:16:58', 0, NULL, NULL);

INSERT INTO `scenario_steps` (`id`, `date`, `isUndid`, `trainingStep_id`, `evaluationPoints`, `budget`, `undoDate`, `internalTime`, `servicesSettlementTime`, `lastActivityDate`) VALUES
(1, '2013-05-21 15:16:58', 0, 1, 0, 10000, NULL, 3, 0, '2013-05-21 15:16:58'),
(2, '2013-05-21 15:17:02', 0, 1, 0, 10000, NULL, 17, 0, '2013-05-21 15:17:11'),
(3, '2013-05-21 15:17:13', 0, 1, 0, 29500, NULL, 72, 72, '2013-05-21 15:17:26'),
(4, '2013-05-21 15:19:07', 0, 1, 0, 29500, NULL, 118, 72, '2013-05-21 15:19:07'),
(5, '2013-05-21 15:19:12', 0, 1, 0, 68500, NULL, 210, 204, '2013-05-21 15:32:46');

INSERT INTO `operation_events` (`id`, `source`, `status`, `isUndid`, `scenarioStepFrom_id`, `scenarioStepTo_id`, `date`, `code`, `description`, `originalId`) VALUES
(1, 'Load balancer', 1, 0, 5, NULL, '2013-05-21 15:19:14', 'ECHO_ACK', 'ECHO accepted', NULL),
(2, 'Server farm', 1, 0, 5, NULL, '2013-05-21 15:19:14', 'SERVER_ECHO', 'Regular ECHO msg sent', NULL),
(3, 'Load balancer', 1, 0, 5, NULL, '2013-05-21 15:19:30', 'ECHO_ACK', 'ECHO accepted', NULL),
(4, 'Server farm', 1, 0, 5, NULL, '2013-05-21 15:19:30', 'SERVER_ECHO', 'Regular ECHO msg sent', NULL),
(5, 'Load balancer', 1, 0, 5, NULL, '2013-05-21 15:19:54', 'ECHO_ACK', 'ECHO accepted', NULL),
(6, 'Server farm', 1, 0, 5, NULL, '2013-05-21 15:19:54', 'SERVER_ECHO', 'Regular ECHO msg sent', NULL),
(7, 'Microsoft SharePoint', 1, 0, 5, NULL, '2013-05-21 15:19:54', 'BACKUP', 'SharePoint backup finished.', NULL),
(8, 'Load balancer', 1, 0, 5, NULL, '2013-05-21 15:32:46', 'ECHO_ACK', 'ECHO accepted', NULL),
(9, 'Server farm', 1, 0, 5, NULL, '2013-05-21 15:32:46', 'SERVER_ECHO', 'Regular ECHO msg sent', NULL),
(10, 'Microsoft SharePoint', 1, 0, 5, NULL, '2013-05-21 15:32:46', 'BACKUP', 'SharePoint backup finished.', NULL);

