drop table if exists public.list;
create table public.list (
	id serial primary key
	,active boolean default true
	,public boolean default true
	,tables boolean default false
	,percentages boolean default true
	,uses_other_lists boolean default false
	,user_id int default 0
	,key varchar(10) not null default ''
	,title varchar(200) not null default ''
	,alias varchar(200) not null default ''
	,tags json default '{}'::json
	,filter_labels json default '{}'::json
	,filter_orders json default '{}'::json
	,description text default ''
	,created timestamp default '0001-01-01 00:00:00'
	,modified timestamp default '0001-01-01 00:00:00'
);

drop table if exists public.asset;
create table public.asset (
	id serial primary key
	,active boolean default true
	,title text not null default ''
	,alias text not null default ''
	,description text default ''
	,created timestamp default '0001-01-01 00:00:00'
	,modified timestamp default '0001-01-01 00:00:00'
	,unique(alias)
);

drop table if exists public.list_asset_map;
create table public.list_asset_map (
	id serial primary key
	,active boolean default true
	,list_id int not null default 0
	,asset_id int not null default 0
	,percentage int not null default 0
	,tags json default '{}'::json
	,filters json default '{}'::json
	,created timestamp default '0001-01-01 00:00:00'
	,modified timestamp default '0001-01-01 00:00:00'
);

-- drop table if exists public.list_combination_map;
-- create table public.list_combination_map (
-- 	id serial primary key
-- 	,active boolean default true
-- 	,static boolean default true
-- 	,list_id int not null default 0
-- 	,secondary_list_id int not null default 0
-- 	,created timestamp default '0001-01-01 00:00:00'
-- 	,modified timestamp default '0001-01-01 00:00:00'
-- );


-- insert into "system"."paths_ajax" (uid, folder, file, dynamic_variables) values
-- 	('18f5cd61c62e1c08b37e817b5bc40369', 'modules/ajax_files/', 'ajax_test3.ajax.php', '{}')
-- 	,('f08e25bbed8a398a8f500dab97f4ed9d', 'modules/acu/worlds/ajax_files/', 'world.ajax.php', '{"db_schema":"public","db_table":"worlds"}')
-- 	,('96201b318f6e27aa6579dbe0779d9770', '', 'audits.ajax.php', '{"db_schema":"public","db_table":"worlds"}')


drop table if exists public.collection;
create table public.collection (
	id serial primary key
	,active boolean default true
	,public boolean default true
	,user_id int default 0
	,key varchar(10) not null default ''
	,title varchar(200) not null default ''
	,alias varchar(200) not null default ''
	,description text default ''
	,created timestamp default '0001-01-01 00:00:00'
	,modified timestamp default '0001-01-01 00:00:00'
);


drop table if exists public.collection_list_map;
create table public.collection_list_map (
	id serial primary key
	,active boolean default true
	,randomize boolean default true
	,is_multi boolean default false
	,connected smallint default 0
	,list_order smallint default 0
	,collection_id int not null default 0
	,list_id int not null default 0
	,display_limit int default 0
	,label varchar(200) not null default ''
	,created timestamp default '0001-01-01 00:00:00'
	,modified timestamp default '0001-01-01 00:00:00'
);

-- insert into "system"."paths_ajax" (uid, folder, file, dynamic_variables) values
-- 	('bca4b7dad46a1d984ec7975274671955', 'modules/ajax_files/', 'modal_list.ajax.php', '{}')
-- ;
insert into "system"."paths_ajax" (uid, folder, file, dynamic_variables) values
	('ff15890b1815ec8d9eaf91ad22a5286e', 'modules/ajax_files/', 'list.ajax.php', '{}')
;

INSERT INTO "system"."paths" (path, module_name, template, title, alias, folder, description, dynamic_variables) VALUES 
	('/compendium', 'compendium', 'default', 'Compendiums', 'compendiums', '', '', '[]')
;

alter table public.collection_list_map add column is_multi boolean default false;
alter table public.collection_list_map add column connected smallint default 0;


INSERT INTO "system"."paths" (path, module_name, template, title, alias, folder, description, dynamic_variables) VALUES 
	('/compendiums/', 'compendium', 'default', 'compendiums', 'Compendiums', 'modules/compendiums/', '', '[]')
	,('/compendiums/add/', 'compendium_add', 'default', 'Add Compendium', 'compendium_add', 'modules/compendiums/', '', '[]')
	,('/compendiums/edit/', 'compendium_edit', 'default', 'Edit Compendium', 'compendium_edit', 'modules/compendiums/', '', '[]')
	,('/compendiums/audit/', 'compendium_audit', 'default', 'Audit Compendium', 'compendium_audit', 'modules/compendiums/', '', '[]')
	,('/compendiums/delete/', 'compendium_delete', 'default', 'Delete Compendium', 'compendium_delete', 'modules/compendiums/', '', '[]')
;

INSERT INTO "system"."paths" (path, module_name, template, title, alias, folder, description, dynamic_variables) VALUES 
	('/search/', 'search', 'default', 'Search', 'search', '', '', '[]')
;

insert into "system"."paths_ajax" (uid, folder, file, dynamic_variables) values
	('bc31fc693c24f4aa0bf13dcf0fbfb1e8', 'modules/compendiums/ajax_files/', 'compendium.ajax.php', '{"db_schema":"public","db_table":"compendiums"}')
;



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
	,description text default ''
	,created timestamp default '0001-01-01 00:00:00'
	,modified timestamp default '0001-01-01 00:00:00'
);


drop table if exists public.compendium_list_map;
create table public.compendium_list_map (
	id serial primary key
	,active boolean default true
	,compendium_id int not null default 0
	,list_id int default 0
	,collection_id int default 0
	,section varchar(50) not null default ''
	,label varchar(100) not null default ''
	,created timestamp default '0001-01-01 00:00:00'
	,modified timestamp default '0001-01-01 00:00:00'
);

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



INSERT INTO "system"."paths" (path, module_name, template, title, alias, folder, description, dynamic_variables) VALUES 
	('/register/', 'register', 'default', 'Register', 'register', '', '', '[]')
;

INSERT INTO "system"."paths" (path, module_name, template, title, alias, folder, description, dynamic_variables) VALUES 
	('/u/(\w+)/lists/', 'user_lists', 'default', 'User List', 'user_list', '', '', '[]')
	,('/u/(\w+)/collections/', 'user_collections', 'default', 'User Collection', 'user_collection', '', '', '[]')
	,('/u/(\w+)/compendiums/', 'user_compendiums', 'default', 'User Compendium', 'user_compendium', '', '', '[]')
;

INSERT INTO "system"."paths" (path, module_name, template, title, alias, folder, description, dynamic_variables) VALUES 
	('/u/(\w+)/', 'user_page', 'default', 'User Page', 'user_page', '', '', '[]')
;

alter table public.list add column filter_labels json default '{}'::json;
alter table public.list add column filter_orders json default '{}'::json;

alter table public.collection_list_map add column list_order smallint default 0;

alter table public.list_asset_map add column filters json default '{}'::json;


alter table public.asset alter column title type text;
alter table public.asset alter column alias type text;


drop table if exists public.list_markdown;
create table public.list_markdown (
	id serial primary key
	,active boolean default true
	,list_id int not null default 0
	,markdown text default ''
	,created timestamp default '0001-01-01 00:00:00'
	,modified timestamp default '0001-01-01 00:00:00'
);

drop table if exists public.collection_markdown;
create table public.collection_markdown (
	id serial primary key
	,active boolean default true
	,collection_id int not null default 0
	,markdown text default ''
	,created timestamp default '0001-01-01 00:00:00'
	,modified timestamp default '0001-01-01 00:00:00'
);

drop table if exists public.compendium_markdown;
create table public.compendium_markdown (
	id serial primary key
	,active boolean default true
	,compendium_id int not null default 0
	,section varchar(100) default ''
	,markdown text default ''
	,created timestamp default '0001-01-01 00:00:00'
	,modified timestamp default '0001-01-01 00:00:00'
);

