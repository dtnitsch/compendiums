drop table if exists public.list;
create table public.list (
	id serial primary key
	,active boolean default true
	,public boolean default true
	,uses_other_lists boolean default false
	,user_id int default 0
	,key varchar(10) not null default ''
	,title varchar(200) not null default ''
	,alias varchar(200) not null default ''
	,description text default ''
	,created timestamp default '0001-01-01 00:00:00'
	,modified timestamp default '0001-01-01 00:00:00'
);

drop table if exists public.asset;
create table public.asset (
	id serial primary key
	,active boolean default true
	,title varchar(200) not null default ''
	,alias varchar(200) not null default ''
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
	,created timestamp default '0001-01-01 00:00:00'
	,modified timestamp default '0001-01-01 00:00:00'
);

drop table if exists public.list_combination_map;
create table public.list_combination_map (
	id serial primary key
	,active boolean default true
	,static boolean default true
	,list_id int not null default 0
	,secondary_list_id int not null default 0
	,created timestamp default '0001-01-01 00:00:00'
	,modified timestamp default '0001-01-01 00:00:00'
);


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
	,collection_id int not null default 0
	,list_id int not null default 0
	,display_limit int default 0
	,created timestamp default '0001-01-01 00:00:00'
	,modified timestamp default '0001-01-01 00:00:00'
);