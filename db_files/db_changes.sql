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
