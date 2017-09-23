

-- insert into "system"."paths_ajax" (uid, folder, file, dynamic_variables) values
-- 	('18f5cd61c62e1c08b37e817b5bc40369', 'modules/ajax_files/', 'ajax_test3.ajax.php', '{}')
-- 	,('f08e25bbed8a398a8f500dab97f4ed9d', 'modules/acu/worlds/ajax_files/', 'world.ajax.php', '{"db_schema":"public","db_table":"worlds"}')
-- 	,('96201b318f6e27aa6579dbe0779d9770', '', 'audits.ajax.php', '{"db_schema":"public","db_table":"worlds"}')


-- insert into "system"."paths_ajax" (uid, folder, file, dynamic_variables) values
-- 	('bca4b7dad46a1d984ec7975274671955', 'modules/ajax_files/', 'modal_list.ajax.php', '{}')
-- ;
insert into "system"."paths_ajax" (uid, folder, file, dynamic_variables) values
	('ff15890b1815ec8d9eaf91ad22a5286e', 'modules/ajax_files/', 'list.ajax.php', '{}')
;

INSERT INTO "system"."paths" (path, module_name, template, title, alias, folder, description, dynamic_variables) VALUES 
	('/compendium', 'compendium', 'default', 'Compendiums', 'compendiums', '', '', '[]')
;


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

INSERT INTO "system"."paths" (path, module_name, template, title, alias, folder, description, dynamic_variables) VALUES 
	('/list/simple/', 'list_simple', 'default', 'Simple List', 'list_simple', '', '', '[]')
;
