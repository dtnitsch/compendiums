/*********************************************************** 
*	Master Query List
***********************************************************/

/******************************************************************
*	Users
******************************************************************/
drop table if exists users cascade;
create table users (
	id int primary key auto_increment
	,active tinyint(1) default 1
	,is_superadmin tinyint(1) default 0
	,email varchar(128) not null default ''
	,first varchar(64) not null default ''
	,last varchar(64) default ''
	,password varchar(64) not null default ''
	,password_salt varbinary(32) not null default ''
	,created int default 0
	,modified int default 0
);
/*
$users = array(
	array(1,'daniel@weareturnstyle.com','Daniel','Tisza-Nitsch','testpass')
	,array(1,'eric@weareturnstyle.com','Eric','Adams','testpass')
	,array(1,'stephen@weareturnstyle.com','Stephen','Streit','testpass')
	,array(1,'shawn@weareturnstyle.com','Shawn','Longfield','testpass')
	,array(1,'todd@weareturnstyle.com','Todd','McHenry','testpass')
	,array(1,'paul@weareturnstyle.com','Paul','Horn','testpass')
	,array(1,'jason@weareturnstyle.com','Jason','Sharp','testpass')

);
$time = time();
foreach($users as $row) {
	list($password,$salt) = user_hash_password($row[4]);
	$q = "
		insert into users (is_superadmin,email,first,last,password,password_salt,created,modified) values
			('". $row[0] ."','". $row[1] ."','". $row[2] ."','". $row[3] ."','". $password ."','". db_prep_sql($salt) ."','". $time ."','". $time ."')
	";
	db_query($q,"Adding User ". $row[1]);
}

 */

/******************************************************************
*	Paths
******************************************************************/
drop table if exists paths cascade;
create table paths (
	id smallint primary key auto_increment
	,active tinyint(1) default 1
	,is_dynamic tinyint(1) default 0
	,dynamic_content_id int default 0
	,path varchar(128) not null default ''
	,module_name varchar(64) not null  default ''
	,template varchar(64) default 'default'
	,title varchar(64) default ''
	,alias varchar(64) default ''
	,folder varchar(128) default ''
	,description varchar(256) default ''
	,dynamic_variables text default ''
	,created int default 0
	,modified int default 0
);

INSERT INTO paths (path, module_name, template, title, alias, folder, description, dynamic_variables) VALUES 
	('/', 'landing', 'default', 'Apartments', 'landing', '', '', '[]')
	,('/forgot-password/', 'forgot_password', 'default', 'Forgot Password', 'forgot_password', '', '', '[]')
	,('/login/', 'login', 'default', 'Login', 'login', '', '', '[]')
	,('/profile/', 'profile_edit', 'default', 'Edit Profile', 'profile_edit', '', '', '[]')
	,('/contact-us/', 'contact_us', 'default', 'Contact Us', 'contact_us', '', '', '[]')
	,('/permission-denied/', 'permission_denied', 'default', 'Permission Denied', 'permission_denied', '', '', '[]')

	,('/acu/', 'dashboard', 'admin', 'Administration Dashboard', 'dashboard', '', '', '[]')
	,('/acu/forgot_password/', 'forgot_password', 'admin', 'Forgot Password', 'forgot_password', '', '', '[]')
	,('/acu/login/', 'login', 'admin', 'Login', 'login', '', '', '[]')

	,('/acu/pages/', 'page', 'admin', 'Page', 'page', 'modules/acu/pages/', '', '[]')
	,('/acu/pages/add/', 'page_add', 'admin', 'Add Page', 'page_add', 'modules/acu/pages/', '', '[]')
	,('/acu/pages/edit/', 'page_edit', 'admin', 'Edit Page', 'page_edit', 'modules/acu/pages/', '', '[]')
	,('/acu/pages/audit/', 'page_audit', 'admin', 'Audit Page', 'page_audit', 'modules/acu/pages/', '', '[]')
	,('/acu/pages/delete/', 'page_delete', 'admin', 'Delete Page', 'page_delete', 'modules/acu/pages/', '', '[]')

	,('/acu/security-roles/', 'security_role', 'admin', 'Security Role', 'security_role', 'modules/acu/security_roles/', '', '[]')
	,('/acu/security-roles/add/', 'security_role_add', 'admin', 'Add Security Role', 'security_role_add', 'modules/acu/security_roles/', '', '[]')
	,('/acu/security-roles/edit/', 'security_role_edit', 'admin', 'Edit Security Role', 'security_role_edit', 'modules/acu/security_roles/', '', '[]')
	,('/acu/security-roles/audit/', 'security_role_audit', 'admin', 'Audit Security Role', 'security_role_audit', 'modules/acu/security_roles/', '', '[]')
	,('/acu/security-roles/delete/', 'security_role_delete', 'admin', 'Delete Security Role', 'security_role_delete', 'modules/acu/security_roles/', '', '[]')

	,('/acu/security-groups/', 'security_group', 'admin', 'Security Group', 'security_group', 'modules/acu/security_groups/', '', '[]')
	,('/acu/security-groups/add/', 'security_group_add', 'admin', 'Add Security Group', 'security_group_add', 'modules/acu/security_groups/', '', '[]')
	,('/acu/security-groups/edit/', 'security_group_edit', 'admin', 'Edit Security Group', 'security_group_edit', 'modules/acu/security_groups/', '', '[]')
	,('/acu/security-groups/audit/', 'security_group_audit', 'admin', 'Audit Security Group', 'security_group_audit', 'modules/acu/security_groups/', '', '[]')
	,('/acu/security-groups/delete/', 'security_group_delete', 'admin', 'Delete Security Group', 'security_group_delete', 'modules/acu/security_groups/', '', '[]')

	,('/acu/security-sections/', 'security_section', 'admin', 'Security Section', 'security_section', '', '', '[]')
	,('/acu/security-sections/add/', 'security_section_add', 'admin', 'Add Security Section', 'security_section_add', '', '', '[]')
	,('/acu/security-sections/edit/', 'security_section_edit', 'admin', 'Edit Security Section', 'security_section_edit', '', '', '[]')
	,('/acu/security-sections/audit/', 'security_section_audit', 'admin', 'Audit Security Section', 'security_section_audit', '', '', '[]')
	,('/acu/security-sections/delete/', 'security_section_delete', 'admin', 'Delete Security Section', 'security_section_delete', '', '', '[]')

	,('/acu/security-permissions/', 'security_permission', 'admin', 'Security Permission', 'security_permission', 'modules/acu/security_permissions/', '', '[]')
	,('/acu/security-permissions/add/', 'security_permission_add', 'admin', 'Add Security Permission', 'security_permission_add', 'modules/acu/security_permissions/', '', '[]')
	,('/acu/security-permissions/edit/', 'security_permission_edit', 'admin', 'Edit Security Permission', 'security_permission_edit', 'modules/acu/security_permissions/', '', '[]')
	,('/acu/security-permissions/audit/', 'security_permission_audit', 'admin', 'Audit Security Permission', 'security_permission_audit', 'modules/acu/security_permissions/', '', '[]')
	,('/acu/security-permissions/delete/', 'security_permission_delete', 'admin', 'Delete Security Permission', 'security_permission_delete', 'modules/acu/security_permissions/', '', '[]')

	,('/acu/paths/', 'path', 'admin', 'Path', 'path', 'modules/acu/paths/', '', '[]')
	,('/acu/paths/add/', 'path_add', 'admin', 'Add Path', 'path_add', 'modules/acu/paths/', '', '[]')
	,('/acu/paths/edit/', 'path_edit', 'admin', 'Edit Path', 'path_edit', 'modules/acu/paths/', '', '[]')
	,('/acu/paths/audit/', 'path_audit', 'admin', 'Audit Path', 'path_audit', 'modules/acu/paths/', '', '[]')
	,('/acu/paths/delete/', 'path_delete', 'admin', 'Delete Path', 'path_delete', 'modules/acu/paths/', '', '[]')

	,('/acu/users/', 'user', 'admin', 'User', 'user', 'modules/acu/users/', '', '[]')
	,('/acu/users/add/', 'user_add', 'admin', 'Add User', 'user_add', 'modules/acu/users/', '', '[]')
	,('/acu/users/edit/', 'user_edit', 'admin', 'Edit User', 'user_edit', 'modules/acu/users/', '', '[]')
	,('/acu/users/audit/', 'user_audit', 'admin', 'Audit User', 'user_audit', 'modules/acu/users/', '', '[]')
	,('/acu/users/delete/', 'user_delete', 'admin', 'Delete User', 'user_delete', 'modules/acu/users/', '', '[]')
	,('/acu/users/permissions/', 'user_permission', 'admin', 'User Permissions', 'user_permission', 'modules/acu/users/', '', '[]')
;

/******************************************************************
*	Ajax Paths
******************************************************************/
drop table if exists paths_ajax;
CREATE TABLE paths_ajax (
	id int primary key auto_increment
	,uid varchar(32) NOT NULL
	,folder varchar(128) NOT NULL
	,file varchar(128) NOT NULL
	,dynamic_variables text default ''
	,created int default 0
);
	
INSERT INTO paths_ajax (uid, folder, file, dynamic_variables) VALUES
	('18f5cd61c62e1c08b37e817b5bc40369', 'modules/ajax_files/', 'ajax_test.ajax.php', '')
	,('02a54624a9ea0058b4ab1b96265afd84', 'modules/acu/users/ajax_files/', 'user.jqgrid.ajax.php', '')
;

/******************************************************************
*	Forgot Password
******************************************************************/
drop table if exists forgot_password cascade;
create table forgot_password (
	id int primary key auto_increment
	,user_id int not null
	,unique_value varchar(40) default ''
	,created int default 0
);

/******************************************************************
*	Security Levels
******************************************************************/
drop table if exists security_roles cascade;
create table security_roles (
	id int primary key auto_increment
	,active tinyint(1) default 1
	,title varchar(30) not null default ''
	,alias varchar(30) not null default ''
	,description varchar(128) default ''
	,created int default 0
	,modified int default 0
);
insert into security_roles (title,alias) values 
	('Super User','super_user')
	,('Administrator','administrator')
	,('Privileged User','privileged_user')
	,('Authenticated User','authenticated_user')
	,('Public User','public_user')
;

drop table if exists security_sections cascade;
create table security_sections (
	id int primary key auto_increment
	,active tinyint(1) default 1
	,title varchar(30) not null default ''
	,alias varchar(30) not null default ''
	,description varchar(128) default ''
	,created int default 0
	,modified int default 0
);
insert into security_sections (title,alias) values 
	('General','general')
	,('Users','users')
	,('Products','products')
	,('Media','media')
	,('Content','content')
	,('Reports','reports')
	,('Administration','administration')
	,('Settings','settings')
;


drop table if exists security_groups cascade;
create table security_groups (
	id int primary key auto_increment
	,active tinyint(1) default 1
	,title varchar(30) not null default ''
	,alias varchar(30) not null default ''
	,description varchar(128) default ''
	,created int default 0
	,modified int default 0
);
insert into security_groups (title,alias) values 
	-- users
	('Admin Users','admin_users')
	,('Public Users','public_users')
	,('Prospects','prospects')
	-- Products
	-- ,('','')
	--  Media
	,('Images','images')
	,('Videos','videos')
	-- Content
	,('Dynamic Pages','dynamic_pages')
	,('Dynamic Emails','dynamic_emails')
	-- Reports
	,('Reports','reports')
	-- Administration
	,('Security Roles','roles')
	,('Security Section','sections')
	,('Security Groups','groups')
	,('Security Permissions','permissions')
	-- Settings
	,('Configurations','configurations')
	,('Backups','backups')
	-- General
	,('Dashboard','dashboard')
;


drop table if exists security_permissions cascade;
create table security_permissions (
	id int primary key auto_increment
	,active tinyint(1) default 1
	,section_id smallint not null default 0
	,group_id smallint not null default 0
	,title varchar(30) not null default ''
	,alias varchar(30) not null default ''
	,description varchar(128) default ''
	,created int default 0
	,modified int default 0
);


-- Users -> Roles
drop table if exists security_role_user_map cascade;
create table security_role_user_map (
	id int primary key auto_increment
	,role_id smallint not null default 0
	,user_id int not null default 0
	,created int default 0
	,modified int default 0
);
insert into security_role_user_map (role_id,user_id) values (5,1); -- Public User

-- Roles -> Permissions
drop table if exists security_role_permission_map cascade;
create table security_role_permission_map (
	role_id smallint not null default 0
	,permission_id smallint not null default 0
	,created int default 0
	,modified int default 0
	,primary key(role_id,permission_id)
);

-- Users -> Permissions (overriding the Role -> Permissions)
drop table if exists security_permission_override_map cascade;
create table security_permission_override_map (
	user_id int not null default 0
	,permission_id smallint not null default 0
	,permission_type_id smallint not null default 0
	,created int default 0
	,modified int default 0
	,primary key(user_id,permission_id)
);

drop table if exists security_permission_types cascade;
create table security_permission_types (
	id int primary key auto_increment
	,title varchar(30) not null default ''
	,alias varchar(30) not null default ''
	,description varchar(128) default ''
	,created int default 0
	,modified int default 0
);
insert into security_permission_types (title,alias) values 
	('Allow','allow'),('Deny','deny'),('Ignore','ignore')
;



insert into security_permissions (section_id,group_id,title,alias) values
	(1,15,'View Dashboard','view_dashboard') -- 7 = Administration, 9 = Security Roles
	
	,(7,9,'List','list') -- 7 = Administration, 9 = Security Roles
	,(7,9,'Add','add') -- 7 = Administration, 9 = Security Roles
	,(7,9,'Edit','edit') -- 7 = Administration, 9 = Security Roles
	,(7,9,'Delete','delete') -- 7 = Administration, 9 = Security Roles
	,(7,9,'Audit','audit') -- 7 = Administration, 9 = Security Roles

	,(7,10,'List','list') -- 7 = Administration, 10 = Security Sections
	,(7,10,'Add','add') -- 7 = Administration, 10 = Security Sections
	,(7,10,'Edit','edit') -- 7 = Administration, 10 = Security Sections
	,(7,10,'Delete','delete') -- 7 = Administration, 10 = Security Sections
	,(7,10,'Audit','audit') -- 7 = Administration, 10 = Security Sections

	,(7,11,'List','list') -- 7 = Administration, 11 = Security Groups
	,(7,11,'Add','add') -- 7 = Administration, 11 = Security Groups
	,(7,11,'Edit','edit') -- 7 = Administration, 11 = Security Groups
	,(7,11,'Delete','delete') -- 7 = Administration, 11 = Security Groups
	,(7,11,'Audit','audit') -- 7 = Administration, 11 = Security Groups

	,(7,12,'List','list') -- 7 = Administration, 12 = Security Permissions
	,(7,12,'Add','add') -- 7 = Administration, 12 = Security Permissions
	,(7,12,'Edit','edit') -- 7 = Administration, 12 = Security Permissions
	,(7,12,'Delete','delete') -- 7 = Administration, 12 = Security Permissions
	,(7,12,'Audit','audit') -- 7 = Administration, 12 = Security Permissions

	,(6,8,'Report 1','report1') -- 6 = Reports, 8 = Reports
	,(6,8,'Report 2','report2') -- 6 = Reports, 8 = Reports

	,(5,6,'List','list') -- 5 = Content, 6 = Dynamic Pages
	,(5,6,'Add','add') -- 5 = Content, 6 = Dynamic Pages
	,(5,6,'Edit','edit') -- 5 = Content, 6 = Dynamic Pages
	,(5,6,'Delete','delete') -- 5 = Content, 6 = Dynamic Pages
	,(5,6,'Audit','audit') -- 5 = Content, 6 = Dynamic Pages

	,(5,7,'List','list') -- 5 = Content, 7 = Dynamic Emails
	,(5,7,'Add','add') -- 5 = Content, 7 = Dynamic Emails
	,(5,7,'Edit','edit') -- 5 = Content, 7 = Dynamic Emails
	,(5,7,'Delete','delete') -- 5 = Content, 7 = Dynamic Emails
	,(5,7,'Audit','audit') -- 5 = Content, 7 = Dynamic Emails
;

/******************************************************************
*	Country List
******************************************************************/
drop table if exists countries cascade;
create table countries (
	id int primary key auto_increment
	,active tinyint(1) default 1
	,2code varchar(3) not null default ''
	,3code varchar(4) not null default ''
	,title varchar(100) not null default ''
	,sort_order int2 not null default '100'
	,created int default 0
	,modified int default 0
);


insert into countries (id,2code,3code,title,active) values
(1,'AX','ALA','Åland Islands',0),
(2,'AF','AFG','Afghanistan',1),
(3,'AL','ALB','Albania',1),
(4,'DZ','DZA','Algeria',1),
(5,'AS','ASM','American Samoa',1),
(6,'AD','AND','Andorra',1),
(7,'AO','AGO','Angola',1),
(8,'AI','AIA','Anguilla',1),
(9,'AQ','ATA','Antarctica',1),
(10,'AG','ATG','Antigua And Barbuda',1),
(11,'AR','ARG','Argentina',1),
(12,'AM','ARM','Armenia',1),
(13,'AW','ABW','Aruba',1),
(14,'AU','AUS','Australia',1),
(15,'AT','AUT','Austria',1),
(16,'AZ','AZE','Azerbaijan',1),
(17,'BS','BHS','Bahamas',1),
(18,'BH','BHR','Bahrain',1),
(19,'BD','BGD','Bangladesh',1),
(20,'BB','BRB','Barbados',1),
(21,'BY','BLR','Belarus',1),
(22,'BE','BEL','Belgium',1),
(23,'BZ','BLZ','Belize',1),
(24,'BJ','BEN','Benin',1),
(25,'BM','BMU','Bermuda',1),
(26,'BT','BTN','Bhutan',1),
(27,'BO','BOL','Bolivia',1),
(28,'BA','BIH','Bosnia And Herzegovina',1),
(29,'BW','BWA','Botswana',1),
(30,'BV','BVT','Bouvet Island',1),
(31,'BR','BRA','Brazil',1),
(32,'IO','IOT','British Indian Ocean Territory',0),
(33,'BN','BRN','Brunei Darussalam',1),
(34,'BG','BGR','Bulgaria',1),
(35,'BF','BFA','Burkina Faso',1),
(36,'BI','BDI','Burundi',1),
(37,'CI','CIV','Côte d\'Ivoire (Ivory Coast)',0),
(38,'KH','KHM','Cambodia',1),
(39,'CM','CMR','Cameroon',1),
(40,'CA','CAN','Canada',1),
(41,'','','Canary Islands',0),
(42,'CV','CPV','Cape Verde',1),
(43,'KY','CYM','Cayman Islands',1),
(44,'CF','CAF','Central African Republic',1),
(45,'TD','TCD','Chad',1),
(46,'CL','CHL','Chile',1),
(47,'CN','CHN','China',1),
(48,'CX','CXR','Christmas Island',1),
(49,'CC','CCK','Cocos (Keeling) Islands',1),
(50,'CO','COL','Colombia',1),
(51,'KM','COM','Comorian',1),
(52,'CG','COG','Congo, Republic Of',1),
(53,'CD','COD','Congo, The Democratic Republic Of The (Formerly Zaire)',0),
(54,'CK','COK','Cook Islands',1),
(55,'CR','CRI','Costa Rica',1),
(56,'HR','HRV','Croatia',1),
(57,'CU','CUB','Cuba',0),
(58,'CY','CYP','Cyprus',1),
(59,'CZ','CZE','Czech Republic',1),
(60,'DK','DNK','Denmark',1),
(61,'DJ','DJI','Djibouti',1),
(62,'DM','DMA','Dominica',1),
(63,'DO','DOM','Dominican Republic',1),
(64,'','','East Timor',0),
(65,'EC','ECU','Ecuador',1),
(66,'EG','EGY','Egypt',1),
(67,'SV','SLV','El Salvador',1),
(68,'GQ','GNQ','Equatorial Guinea',1),
(69,'ER','ERI','Eritrea',1),
(70,'EE','EST','Estonia',1),
(71,'ET','ETH','Ethiopia',1),
(72,'FO','FRO','Faroe Islands',1),
(73,'FK','FLK','Falkland Islands (Malvinas)',1),
(74,'FJ','FJI','Fiji',1),
(75,'FI','FIN','Finland',1),
(76,'FR','FRA','France',1),
(77,'GF','GUF','French Guiana',1),
(78,'PF','PYF','French Polynesia',1),
(79,'TF','ATF','French Southern Territories',0),
(80,'GA','GAB','Gabon',1),
(81,'GM','GMB','Gambia',1),
(82,'GE','GEO','Georgia, Republic Of',1),
(83,'DE','DEU','Germany',1),
(84,'GH','GHA','Ghana',1),
(85,'GI','GIB','Gibraltar',1),
(86,'GB','GBR','Great Britain',1),
(87,'GR','GRC','Greece',1),
(88,'GL','GRL','Greenland',1),
(89,'GD','GRD','Grenada',1),
(90,'GP','GLP','Guadeloupe',1),
(91,'GU','GUM','Guam',1),
(92,'GT','GTM','Guatemala',1),
(93,'','','Guernsey',0),
(94,'GN','GIN','Guinea',1),
(95,'GW','GNB','Guinea-Bissau',1),
(96,'GY','GUY','Guyana',1),
(97,'HT','HTI','Haiti',1),
(98,'HM','HMD','Heard And Mc Donald Islands',0),
(99,'HN','HND','Honduras',1),
(100,'HK','HKG','Hong Kong',1),
(101,'HU','HUN','Hungary',1),
(102,'IS','ISL','Iceland',1),
(103,'IN','IND','India',1),
(104,'ID','IDN','Indonesia',1),
(105,'IR','IRN','Iran (Islamic Republic Of Iran)',0),
(106,'IQ','IRQ','Iraq',1),
(107,'IE','IRL','Ireland',1),
(108,'','','Isle Of Man',0),
(109,'IL','ISR','Israel',1),
(110,'IT','ITA','Italy',1),
(111,'JM','JAM','Jamaica',1),
(112,'JP','JPN','Japan',1),
(113,'','','Jersey',0),
(114,'JO','JOR','Jordan',1),
(115,'KZ','KAZ','Kazakhstan',1),
(116,'KE','KEN','Kenya',1),
(117,'KI','KIR','Kiribati',1),
(118,'KP','PRK','Korea, Democratic People\'s Rep',0),
(119,'KR','KOR','Korea, Republic Of',1),
(120,'KW','KWT','Kuwait',1),
(121,'KG','KGZ','Kyrgyzstan',1),
(122,'LA','LAO','Lao People\'s Democratic R. Of',1),
(123,'LV','LVA','Latvia',1),
(124,'LB','LBN','Lebanon',1),
(125,'LS','LSO','Lesotho',1),
(126,'LR','LBR','Liberia',1),
(127,'LY','LBY','Libya (Libyan Arab Jamahirya)',1),
(128,'LI','LIE','Liechtenstein',1),
(129,'LT','LTU','Lithuania',1),
(130,'LU','LUX','Luxembourg',1),
(131,'MO','MAC','Macau',1),
(132,'MK','MKD','Macedonia',1),
(133,'MG','MDG','Madagascar',1),
(134,'MW','MWI','Malawi',1),
(135,'MY','MYS','Malaysia',1),
(136,'MV','MDV','Maldives',1),
(137,'ML','MLI','Mali',1),
(138,'MT','MLT','Malta',1),
(139,'MH','MHL','Marshall Islands',1),
(140,'MQ','MTQ','Martinique',1),
(141,'MR','MRT','Mauritania',1),
(142,'MU','MUS','Mauritius',1),
(143,'YT','MYT','Mayotte',1),
(144,'MX','MEX','Mexico',1),
(145,'FM','FSM','Micronesia',1),
(146,'MD','MDA','Moldova, Republic Of',1),
(147,'MC','MCO','Monaco',1),
(148,'MN','MNG','Mongolia',1),
(149,'MS','MSR','Montserrat',1),
(150,'MA','MAR','Morocco',1),
(151,'MZ','MOZ','Mozambique',1),
(152,'MM','MMR','Myanmar',0),
(153,'NA','NAM','Namibia',1),
(154,'NR','NRU','Nauru',1),
(155,'NP','NPL','Nepal',1),
(156,'NL','NLD','Netherlands',1),
(157,'AN','ANT','Netherlands Antilles',1),
(158,'NC','NCL','New Caledonia',1),
(159,'NZ','NZL','New Zealand',1),
(160,'NI','NIC','Nicaragua',1),
(161,'NE','NER','Niger',1),
(162,'NG','NGA','Nigeria',1),
(163,'NU','NIU','Niue',1),
(164,'NF','NFK','Norfolk Island',1),
(165,'MP','MNP','Northern Mariana Islands',1),
(166,'NO','NOR','Norway',1),
(167,'OM','OMN','Oman',1),
(168,'PK','PAK','Pakistan',1),
(169,'PW','PLW','Palau',1),
(170,'PS','PSE','Palestinian Territory',0),
(171,'PA','PAN','Panama',1),
(172,'PG','PNG','Papua New Guinea',1),
(173,'PY','PRY','Paraguay',1),
(174,'PE','PER','Peru',1),
(175,'PH','PHL','Philippines',1),
(176,'PN','PCN','Pitcairn',1),
(177,'PL','POL','Poland',1),
(178,'PT','PRT','Portugal',1),
(179,'PR','PRI','Puerto Rico',1),
(180,'QA','QAT','Qatar',1),
(181,'RE','REU','Reunion',0),
(182,'RO','ROU','Romania',1),
(183,'RU','RUS','Russia',1),
(184,'RW','RWA','Rwanda',1),
(185,'SH','SHN','Saint Helena',1),
(186,'','','S Georgia & The S Sandwich Islands',0),
(187,'KN','KNA','Saint Kitts And Nevis',1),
(188,'LC','LCA','Saint Lucia',1),
(189,'PM','SPM','Saint Pierre And Miquelon',1),
(190,'VC','VCT','Saint Vincent And The Grenadines',1),
(191,'WS','WSM','Samoa (Formerly Western Samoa)',1),
(192,'SM','SMR','San Marino',1),
(193,'ST','STP','Sao Tome And Principe',1),
(194,'SA','SAU','Saudi Arabia',1),
(195,'','','Scotland',0),
(196,'SN','SEN','Senegal',1),
(197,'CS','SCG','Serbia And Montenegro (Yugoslavia)',1),
(198,'SC','SYC','Seychelles',1),
(199,'SL','SLE','Sierra Leone',1),
(200,'SG','SGP','Singapore',1),
(201,'SK','SVK','Slovakia',1),
(202,'SI','SVN','Slovenia',1),
(203,'SB','SLB','Solomon Islands',1),
(204,'SO','SOM','Somalia',1),
(205,'ZA','ZAF','South Africa',1),
(206,'GS','SGS','South Georgia And The South Sandwich Islands',0),
(207,'ES','ESP','Spain',1),
(208,'LK','LKA','Sri Lanka',1),
(209,'','','St. Helena',0),
(210,'SD','SDN','Sudan',0),
(211,'','','St. Pierre And Miquelon',0),
(212,'SR','SUR','Suriname',1),
(213,'SJ','SJM','Svalbard And Jan Mayen Islands',1),
(214,'SZ','SWZ','Swaziland',1),
(215,'SE','SWE','Sweden',1),
(216,'CH','CHE','Switzerland',1),
(217,'SY','SYR','Syrian Arab Republic',0),
(218,'TW','TWN','Taiwan',1),
(219,'TJ','TJK','Tajikistan',1),
(220,'TZ','TZA','Tanzania, United Republic Of',1),
(221,'TH','THA','Thailand',1),
(222,'TL','TLS','Timor-Leste (Formerly East Timor)',1),
(223,'TG','TGO','Togo',1),
(224,'TK','TKL','Tokelau',1),
(225,'TO','TON','Tonga',1),
(226,'TT','TTO','Trinidad And Tobago',1),
(227,'TN','TUN','Tunisia',1),
(228,'TR','TUR','Turkey',1),
(229,'TM','TKM','Turkmenistan',1),
(230,'TC','TCA','Turks And Caicos Islands',1),
(231,'TV','TUV','Tuvalu',1),
(232,'UG','UGA','Uganda',1),
(233,'UA','UKR','Ukraine',1),
(234,'AE','ARE','United Arab Emirates',1),
(235,'GB','GBR','United Kingdom',1),
(236,'US','USA','United States',1),
(237,'UM','UMI','United States Minor Outlying',0),
(238,'UY','URY','Uruguay',1),
(239,'UZ','UZB','Uzbekistan',1),
(240,'VU','VUT','Vanuatu',1),
(241,'VA','VAT','Vatican City State (Holy See)',1),
(242,'VE','VEN','Venezuela',1),
(243,'VN','VNM','Viet Nam',1),
(244,'VG','VGB','Virgin Islands (British)',1),
(245,'VI','VIR','Virgin Islands (U.S.)',1),
(246,'WF','WLF','Wallis And Futuna Islands',1),
(247,'EH','ESH','Western Sahara',1),
(248,'YE','YEM','Yemen',1),
(249,'YU','','Yugoslavia',0),
(250,'ZM','ZMB','Zambia',1),
(251,'ZW','ZWE','Zimbabwe',1),
(252,'','','Curacao',0),
(253,'','','Dutch Antilles',0),
(254,'','','San Salvador',0);


/******************************************************************
*	Localities
******************************************************************/
drop table if exists localities cascade;
create table localities (
	id int primary key auto_increment
	,active tinyint(1) not null default 1
	,country_id int2 not null default '0'
	,2code varchar(3) not null default ''
	,title varchar(150) not null default ''
	,created int default 0
	,modified int default 0
);

insert into localities (country_id,title,2code) VALUES 
(236,'Alabama','AL'),
(236,'Alaska','AK'),
(236,'Arizona','AZ'),
(236,'Arkansas','AR'),
(236,'California','CA'),
(236,'Colorado','CO'),
(236,'Connecticut','CT'),
(236,'Delaware','DE'),
(236,'District Of Columbia','DC'),
(236,'Florida','FL'),
(236,'Georgia','GA'),
(236,'Hawaii','HI'),
(236,'Idaho','ID'),
(236,'Illinois','IL'),
(236,'Indiana','IN'),
(236,'Iowa','IA'),
(236,'Kansas','KS'),
(236,'Kentucky','KY'),
(236,'Louisiana','LA'),
(236,'Maine','ME'),
(236,'Maryland','MD'),
(236,'Massachusetts','MA'),
(236,'Michigan','MI'),
(236,'Minnesota','MN'),
(236,'Mississippi','MS'),
(236,'Missouri','MO'),
(236,'Montana','MT'),
(236,'Nebraska','NE'),
(236,'Nevada','NV'),
(236,'New Hampshire','NH'),
(236,'New Jersey','NJ'),
(236,'New Mexico','NM'),
(236,'New York','NY'),
(236,'North Carolina','NC'),
(236,'North Dakota','ND'),
(236,'Ohio','OH'),
(236,'Oklahoma','OK'),
(236,'Oregon','OR'),
(236,'Pennsylvania','PA'),
(236,'Rhode Island','RI'),
(236,'South Carolina','SC'),
(236,'South Dakota','SD'),
(236,'Tennessee','TN'),
(236,'Texas','TX'),
(236,'Utah','UT'),
(236,'Vermont','VT'),
(236,'Virginia','VA'),
(236,'Washington','WA'),
(236,'West Virginia','WV'),
(236,'Wisconsin','WI'),
(236,'Wyoming','WY'),
(40,'Alberta','AB'),
(40,'British Columbia','BC'),
(40,'Manitoba','MB'),
(40,'New Brunswick','NB'),
(40,'Newfoundland and Labrador','NL'),
(40,'Northwest Territories','NT'),
(40,'Nova Scotia','NS'),
(40,'Nunavut','NU'),
(40,'Quebec','QC'),
(40,'Ontario','ON'),
(40,'Prince Edward Island','PE'),
(40,'Saskatchewan','SK'),
(40,'Yukon','YT');


/******************************************************************
*	Audit Tables - Logins
******************************************************************/
drop table if exists logins cascade;
create table logins (
	id int primary key auto_increment
	,user_id int4 not null default '0'
	,created int default 0
);


/******************************************************************
*	Audit Tables - Failed Login Attempts
******************************************************************/
drop table if exists failed_login_attempts cascade;
create table failed_login_attempts (
	id int primary key auto_increment
	,login_input varchar(75) not null default ''
	,ipv4 varchar(15) not null default ''
	,ipv6 varchar(32) not null default ''
	,created int default 0
);


/******************************************************************
*	Audit Tables - All Page Hits
******************************************************************/
drop table if exists page_hits cascade;
create table page_hits (
	id int primary key auto_increment
	,path varchar(255) not null default ''
	,session_id varchar(40) default ''
	,params varchar(255) default ''
	,created int default 0
);


/******************************************************************
*	Audit Tables - Public Audits
******************************************************************/
drop table if exists audit_table_logs cascade;
create table audit_table_logs (
	id int primary key auto_increment
	,user_id int not null default 0
	,primary_key_id bigint not null default 0
	,schema_name varchar(64) not null default ''
	,table_name varchar(64) not null default ''
	,created int default 0
);

drop table if exists audit_field_logs cascade;
create table audit_field_logs (
	id int primary key auto_increment
	,table_log_id int not null default 0
	,column_name varchar(64) not null default ''
	,old_value varchar(255) not null default ''
	,new_value varchar(255) not null default ''
	,created int default 0
);

/******************************************************************
*	Sessions
******************************************************************/
-- drop table if exists session;
-- create table session (
-- 	id varchar(128) not null primary key
-- 	,data text not null
-- 	,modified timestamp not null
-- );







/******************************************************************
*
*	NEEDED Project Tables
*
******************************************************************/



drop table if exists dynamic_content;
CREATE TABLE dynamic_content (
	id int primary key auto_increment
	,active tinyint(1) default 1
	,dynamic_content_type_id smallint not null default 0
	,alias varchar(150)  default ''
	,title varchar(150) not null default ''
	,content text not null default ''
	,created int default 0
	,modified int default 0
);

drop table if exists dynamic_content_types;
CREATE TABLE dynamic_content_types (
	id int primary key auto_increment
	,active tinyint(1) default 1
	,title varchar(30) not null default ''
	,alias varchar(30) not null default ''
	,description varchar(128) default ''
	,created int default 0
	,modified int default 0
);

insert into dynamic_content_types (alias,title,description) values
	('web_pages','Web Pages','Web Pages')
	,('emails','Emails','Emails')
	,('newsletters','Newsletters','Newsletters')
	,('blog','Blogs','Blogs')
	,('product_descriptions','Product Descriptions','Product Descriptions')
;
/*  Versioning of Editors  */


/*  Site wide notes  */
drop table if exists site_wide_notes;
CREATE TABLE site_wide_notes (
	id int primary key auto_increment
	,active tinyint(1) default 1
	,user_id smallint not null default 0
	,path_id int not null default 0
	,identifier varchar(128) default ''
	,content text not null default ''
	,created int default 0
	,modified int default 0
);


/******************************************************************
*
*	Current Project Tables
*
******************************************************************/
