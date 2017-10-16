/*********************************************************** 
*	Database: Compendiums
***********************************************************/
/*********************************************************** 
*	Master Query List
***********************************************************/
drop schema if exists "audits" cascade;
create schema "audits";

drop schema if exists "backups" cascade;
create schema "backups";

drop schema if exists "public" cascade;
create schema "public";

drop schema if exists "supplements" cascade;
create schema "supplements";

drop schema if exists "security" cascade;
create schema "security";

drop schema if exists "system" cascade;
create schema "system";



/******************************************************************
*	Languages
******************************************************************/
drop table if exists "supplements"."languages" cascade;
create table "supplements"."languages" (
	"id" serial primary key,
	"active" boolean not null default true,
	"2code" varchar(2) default '',
	"3code" varchar(3) default '',
	"title" varchar(64) not null default '',
	"native" varchar(128) default '',
	"description" text default '',
	"created" timestamp default current_timestamp,
	"modified" timestamp default current_timestamp
);
insert into supplements.languages ("2code","3code",title,native) values
	('en','eng','English','English')
;




/******************************************************************
*	Users
******************************************************************/
drop table if exists "system"."users" cascade;
create table "system"."users" (
	"id" serial primary key
	,"active" boolean not null default true
	,"is_superadmin" boolean default false
	,"region_id" smallint default 0
	,"country_id" smallint default 0
	,"firstname" varchar(96) default ''
	,"lastname" varchar(96) default ''
	,"username" varchar(192) default ''
	,"email" varchar(192) default ''
	,"password" char(64) default ''
	,"password_salt" bytea default ''
	,"created" timestamp default current_timestamp
	,"modified" timestamp default current_timestamp
);
insert into system.users (id,active,firstname,lastname,username,region_id,country_id) values 
	(0,false,'Empty User','Empty User','empty',0,0)
	,(1,false,'Public User','Public User','public',0,0)
;
SELECT setval('system.users_id_seq', COALESCE((SELECT MAX(id)+1 FROM system.users), 1), false);




/******************************************************************
*	Paths
******************************************************************/
drop table if exists "system"."paths" cascade;
create table "system"."paths" (
	"id" serial primary key
	,"active" boolean default true
	,"redirect" boolean default false
	,"is_dynamic" boolean default false
	,"dynamic_content_id" int default null
	,"path_redirect" varchar(128) not null default ''
	,"path" varchar(128) not null default ''
	,"module_name" varchar(64) not null  default ''
	,"template" varchar(64) default 'default'
	,"title" varchar(128) default ''
	,"alias" varchar(128) default ''
	,"folder" varchar(128) default ''
	,"description" varchar(256) default ''
	,"dynamic_variables" json default '[]'::json
	,"created" timestamp default current_timestamp
	,"modified" timestamp default current_timestamp
);

INSERT INTO "system"."paths" (path, module_name, template, title, alias, folder, description, dynamic_variables) VALUES 
	('/', 'landing', 'default', 'Landing', 'landing', '', '', '[]')
	,('/forgot-password/', 'forgot_password', 'default', 'Forgot Password', 'forgot_password', '', '', '[]')
	,('/login/', 'login', 'login', 'Login', 'login', '', '', '[]')
	,('/profile/', 'profile_edit', 'default', 'Edit Profile', 'profile_edit', '', '', '[]')
	,('/contact-us/', 'contact_us', 'default', 'Contact Us', 'contact_us', '', '', '[]')
	,('/permission-denied/', 'permission_denied', 'default', 'Permission Denied', 'permission_denied', '', '', '[]')
;


drop table if exists "system"."paths_ajax" cascade;
create table "system"."paths_ajax" (
	"id" serial primary key
	,"uid" varchar(32) not null
	,"folder" varchar(128) not null
	,"file" varchar(128) not null
	,"dynamic_variables" json default '[]'::json
	,"created" timestamp default current_timestamp
);

insert into "system"."paths_ajax" (uid, folder, file, dynamic_variables) values
	('a9068764f45c45d5799d85488d242fac', 'modules/ajax_files/', 'page_hits.ajax.php', '{"db_schema":"public","db_table":"page_hits"}')
	,('621ea1449472caca9ed301610dca5a84', 'modules/ajax_files/', 'site_wide_notes.ajax.php', '{}')
	,('1a3873edb1f3643c2c60ff495780bb9a', 'modules/ajax_files/', 'export_to_csv.ajax.php', '{}')
;



/******************************************************************
*	Forgot Password
******************************************************************/
drop table if exists "system"."forgot_password" cascade;
create table "system"."forgot_password" (
	"id" serial primary key
	,"user_id" int not null
	,"unique_value" varchar(40) default ''
	,"created" timestamp not null default current_timestamp
);

/******************************************************************
*	Audit Tables - Logins
******************************************************************/
drop table if exists "audits"."public_logins" cascade;
create table "audits"."public_logins" (
	"id" serial primary key,
	"user_id" int4 not null default '0',
	"ipv4" varchar(15) not null default '',
	"ipv6" varchar(32) not null default '',
	"created" timestamp default current_timestamp
);

drop table if exists "audits"."system_logins" cascade;
create table "audits"."system_logins" (
	"id" serial primary key,
	"user_id" int4 not null default '0',
	"ipv4" varchar(15) not null default '',
	"ipv6" varchar(32) not null default '',
	"created" timestamp default current_timestamp
);


/******************************************************************
*	Audit Tables - Failed Login Attempts
******************************************************************/
drop table if exists "audits"."failed_login_attempts" cascade;
create table "audits"."failed_login_attempts" (
	"id" serial primary key,
	"login_input" varchar(75) not null default '',
	"ipv4" varchar(15) not null default '',
	"ipv6" varchar(32) not null default '',
	"created" timestamp default current_timestamp
);

-- /******************************************************************
-- *	Audit Tables - All Page Hits
-- ******************************************************************/
-- drop table if exists "audits"."page_hits" cascade;
-- create table "audits"."page_hits" (
-- 	"id" serial primary key,
-- 	"user_id" int default 1
-- 	"path" varchar(256) not null default '',
-- 	"session_id" varchar(40) not null default '',
-- 	"params" varchar(256) not null default '',
-- 	"created" timestamp default current_timestamp
-- );
-- create index audits_page_hit_path_idx ON "audits"."page_hits" ("created","path");


/******************************************************************
*	Audit Tables - Security Audits
******************************************************************/
drop table if exists "audits"."security_table_logs" cascade;
create table "audits"."security_table_logs" (
	"id" serial primary key,
	"user_id" int not null default 0,
	"primary_key_id" bigint not null default 0,
	"schema_name" varchar(64) not null default '',
	"table_name" varchar(64) not null default '',
	"created" timestamp default current_timestamp
);

drop table if exists "audits"."security_field_logs" cascade;
create table "audits"."security_field_logs" (
	"id" serial primary key,
	"table_log_id" int not null default 0,
	"column_name" varchar(64) not null default '',
	"old_value" varchar not null default '',
	"new_value" varchar not null default '',
	"created" timestamp default current_timestamp
);


/******************************************************************
*	Audit Tables - Public Audits
******************************************************************/
drop table if exists "audits"."public_table_logs" cascade;
create table "audits"."public_table_logs" (
	"id" serial primary key,
	"user_id" int not null default 0,
	"primary_key_id" bigint not null default 0,
	"schema_name" varchar(64) not null default '',
	"table_name" varchar(64) not null default '',
	"created" timestamp default current_timestamp
);

drop table if exists "audits"."public_field_logs" cascade;
create table "audits"."public_field_logs" (
	"id" serial primary key,
	"table_log_id" int not null default 0,
	"column_name" varchar(64) not null default '',
	"old_value" varchar not null default '',
	"new_value" varchar not null default '',
	"created" timestamp default current_timestamp
);


/******************************************************************
*	Audit Tables - System Audits
******************************************************************/
drop table if exists "audits"."system_table_logs" cascade;
create table "audits"."system_table_logs" (
	"id" serial primary key,
	"user_id" int not null default 0,
	"primary_key_id" bigint not null default 0,
	"schema_name" varchar(64) not null default '',
	"table_name" varchar(64) not null default '',
	"created" timestamp default current_timestamp
);

drop table if exists "audits"."system_field_logs" cascade;
create table "audits"."system_field_logs" (
	"id" serial primary key,
	"table_log_id" int not null default 0,
	"column_name" varchar(64) not null default '',
	"old_value" varchar not null default '',
	"new_value" varchar not null default '',
	"created" timestamp default current_timestamp
);

/******************************************************************
*	Audit Tables - Activities Audits
******************************************************************/
drop table if exists "audits"."activity_table_logs" cascade;
create table "audits"."activity_table_logs" (
	"id" serial primary key,
	"user_id" int not null default 0,
	"primary_key_id" bigint not null default 0,
	"schema_name" varchar(64) not null default '',
	"table_name" varchar(64) not null default '',
	"created" timestamp default current_timestamp
);

drop table if exists "audits"."activity_field_logs" cascade;
create table "audits"."activity_field_logs" (
	"id" serial primary key,
	"table_log_id" int not null default 0,
	"column_name" varchar(64) not null default '',
	"old_value" varchar not null default '',
	"new_value" varchar not null default '',
	"created" timestamp default current_timestamp
);

/******************************************************************
*	Sessions
******************************************************************/
drop table if exists "system"."sessions" cascade;
CREATE TABLE "system"."sessions" (
	"id" varchar(128) NOT NULL primary key,
	"data" varchar NOT NULL,
	"modified" timestamp NOT NULL
);




/******************************************************************
*
*	Optional Tables
*
******************************************************************/

/*  Site wide notes  */
-- drop table if exists "public"."site_wide_notes" cascade;
-- CREATE TABLE "public"."site_wide_notes" (
-- 	"id" serial primary key
-- 	,"active" boolean not null default true
-- 	,"user_id" int not null default 0
-- 	,"path_id" int not null default 0
-- 	,"identifier" varchar(128) default ''
-- 	,"content" varchar not null default ''
-- 	,"created" timestamp not null default current_timestamp
-- 	,"modified" timestamp not null default current_timestamp
-- );


/******************************************************************
*
*	Custom Site Tables
*
******************************************************************/

/*
	Can insert AND update
	- If "assets" change, then INSERT a new one altogether.
	- If "assets" no not change, then update existing.
	- future:
		- Percentages for lists
		- Mixing multiple lists together - aka: other lists
		- Tagging of lists
*/
drop table if exists public.list;
create table public.list (
	id serial primary key
	,active boolean default true
	,public boolean default true
	,tables boolean default false
	,percentages boolean default false
	,uses_other_lists boolean default false
	,user_id int default 0
	,version int default 1
	,parent_id int default 0
	,key varchar(10) not null default ''
	,title varchar(200) not null default ''
	,alias varchar(200) not null default ''
	,other_lists varchar(120) not null default ''
	,tags json default '{}'::json
	,filter_labels json default '{}'::json
	,filter_orders json default '{}'::json
	,description text default ''
	,created timestamp default '0001-01-01 00:00:00'
	,modified timestamp default '0001-01-01 00:00:00'
);

/*
	Unique list of assets
	- Space saving
	- Interesting reporting
	- if base64(asset) length is > 40, then sha1 for alias
*/

drop table if exists public.asset;
create table public.asset (
	id serial primary key
	,active boolean default true
	,title text not null default ''
	,alias text not null default ''
	,created timestamp default '0001-01-01 00:00:00'
	,modified timestamp default '0001-01-01 00:00:00'
	,unique(alias)
);

drop table if exists public.list_asset_map;
create table public.list_asset_map (
	id serial primary key
	,list_id int not null default 0
	,asset_id int not null default 0
	,percentage int not null default 0
	,filters json default '{}'::json
	,created timestamp default '0001-01-01 00:00:00'
);



drop table if exists public.collection;
create table public.collection (
	id serial primary key
	,active boolean default true
	,public boolean default true
	,user_id int default 0
	,version int default 1
	,key varchar(10) not null default ''
	,title varchar(200) not null default ''
	,alias varchar(200) not null default ''
	,tags json default '{}'::json
	,description text default ''
	,created timestamp default '0001-01-01 00:00:00'
	,modified timestamp default '0001-01-01 00:00:00'
);


drop table if exists public.collection_list_map;
create table public.collection_list_map (
	id serial primary key
	,collection_id int not null default 0
	,list_id int not null default 0
	,randomize boolean default true
	,is_multi boolean default false
	,connected smallint default 0
	,list_order smallint default 0
	,display_limit int default 0
	,label varchar(200) not null default ''
	,created timestamp default '0001-01-01 00:00:00'
);



drop table if exists public.compendium;
create table public.compendium (
	id serial primary key
	,active boolean default true
	,public boolean default true
	,user_id int default 0
	,key varchar(10) not null default ''
	,title varchar(200) not null default ''
	,alias varchar(200) not null default ''
	,sections json default '{}'::json
	,section_navigation json default '{}'::json
	,description text default ''
	,created timestamp default '0001-01-01 00:00:00'
	,modified timestamp default '0001-01-01 00:00:00'
);


drop table if exists public.compendium_section_map;
create table public.compendium_section_map (
	id serial primary key
	,compendium_id int not null default 0
	,list_id int default 0
	,collection_id int default 0
	,compendium_note_id int default 0
	,compendium_section_type_id smallint default 0
	,section varchar(50) not null default ''
	,label varchar(100) not null default ''
	,created timestamp default '0001-01-01 00:00:00'
);

drop table if exists public.compendium_section_type;
create table public.compendium_section_type (
	id smallint primary key
	,active boolean default true
	,title varchar(200) not null default ''
	,alias varchar(200) not null default ''
	,description text default ''
	,created timestamp default '0001-01-01 00:00:00'
	,modified timestamp default '0001-01-01 00:00:00'
);
insert into public.compendium_section_type (id,title,alias,created,modified) values 
	(1,'List','list',now(),now())
	,(2,'Collection','collection',now(),now())
	,(3,'Note','note',now(),now())
;

drop table if exists public.compendium_notes;
create table public.compendium_list_map (
	id serial primary key
	,active boolean default true
	,compendium_id int not null default 0
	,list_id int default 0
	,collection_id int default 0
	,key varchar(10) not null default ''
	,section varchar(50) not null default ''
	,label varchar(100) not null default ''
	,created timestamp default '0001-01-01 00:00:00'
	,modified timestamp default '0001-01-01 00:00:00'
);


drop table if exists public.list_likes;
create table public.list_likes (
	id serial primary key
	,list_id int not null default 0
	,user_id int default 0
	,ip_address cidr default '0'::cidr
	,created timestamp default '0001-01-01 00:00:00'
);

drop table if exists public.collection_likes;
create table public.collection_likes (
	id serial primary key
	,collection_id int not null default 0
	,user_id int default 0
	,ip_address cidr default '0'::cidr
	,created timestamp default '0001-01-01 00:00:00'
);

drop table if exists public.compendium_likes;
create table public.compendium_likes (
	id serial primary key
	,compendium_id int not null default 0
	,user_id int default 0
	,ip_address cidr default '0'::cidr
	,created timestamp default '0001-01-01 00:00:00'
);


INSERT INTO "system"."paths" ("path", module_name, "template", title, "alias", folder, description) VALUES 
	('/lists/', 'list', 'default', 'Lists', 'lists', 'modules/lists/', '')
	,('/lists/add/', 'list_add', 'default', 'Add List', 'list_add', 'modules/lists/', '')
	,('/lists/edit/', 'list_edit', 'default', 'Edit List', 'list_edit', 'modules/lists/', '')
	,('/lists/delete/', 'list_delete', 'default', 'Delete List', 'list_delete', 'modules/lists/', '')
	
	,('/collections/', 'collection', 'default', 'Collections', 'collections', 'modules/collections/', '')
	,('/collections/add/', 'collection_add', 'default', 'Add Collection', 'collection_add', 'modules/collections/', '')
	,('/collections/edit/', 'collection_edit', 'default', 'Edit Collection', 'collection_edit', 'modules/collections/', '')
	,('/collections/delete/', 'collection_delete', 'default', 'Delete Collection', 'collection_delete', 'modules/collections/', '')


	,('/collections/', 'collection', 'default', 'Collections', 'collections', 'modules/collections/', '')

	,('/compendiums/', 'compendium', 'default', 'compendiums', 'Compendiums', 'modules/compendiums/', '')
	,('/compendiums/add/', 'compendium_add', 'default', 'Add Compendium', 'compendium_add', 'modules/compendiums/', '')
	,('/compendiums/edit/', 'compendium_edit', 'default', 'Edit Compendium', 'compendium_edit', 'modules/compendiums/', '')
	,('/compendiums/audit/', 'compendium_audit', 'default', 'Audit Compendium', 'compendium_audit', 'modules/compendiums/', '')
	,('/compendiums/delete/', 'compendium_delete', 'default', 'Delete Compendium', 'compendium_delete', 'modules/compendiums/', '')

	,('/search/', 'search', 'default', 'Search', 'search', '', '')

	,('/register/', 'register', 'default', 'Register', 'register', '', '')

	,('/u/(\w+)/lists/', 'user_lists', 'default', 'User List', 'user_list', '', '')
	,('/u/(\w+)/collections/', 'user_collections', 'default', 'User Collection', 'user_collection', '', '')
	,('/u/(\w+)/compendiums/', 'user_compendiums', 'default', 'User Compendium', 'user_compendium', '', '')
;

insert into "system"."paths_ajax" (uid, folder, file, dynamic_variables) values
	('a9068764f45c45d5799d85488d242fac', 'modules/ajax_files/', 'page_hits.ajax.php', '{"db_schema":"public","db_table":"page_hits"}')
	,('621ea1449472caca9ed301610dca5a84', 'modules/ajax_files/', 'site_wide_notes.ajax.php', '{}')
	,('1a3873edb1f3643c2c60ff495780bb9a', 'modules/ajax_files/', 'export_to_csv.ajax.php', '{}')

	,('06a0fc087756944595785e90f79ecad4', 'modules/lists/ajax_files/', 'list.ajax.php', '{"db_schema":"public","db_table":"lists"}')
	,('bc31fc693c24f4aa0bf13dcf0fbfb1e8', 'modules/compendiums/ajax_files/', 'compendium.ajax.php', '{"db_schema":"public","db_table":"compendiums"}')
	,('bca4b7dad46a1d984ec7975274671955', 'modules/ajax_files/', 'module_list.ajax.php', '{}')
;
