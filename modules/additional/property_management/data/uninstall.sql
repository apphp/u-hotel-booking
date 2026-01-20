
DROP TABLE IF EXISTS `<DB_PREFIX>property_inventory`;

DROP TABLE IF EXISTS `<DB_PREFIX>property_expenses`;

DELETE FROM `<DB_PREFIX>modules` WHERE `name` = 'property_management';
DELETE FROM `<DB_PREFIX>modules_settings` WHERE `module_name` = 'property_management';

DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'owner') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'add_property_inventory');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'mainadmin') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'add_property_inventory');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'admin') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'add_property_inventory');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelowner') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'add_property_inventory');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelmanager') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'add_property_inventory');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'owner') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'edit_property_inventory');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'mainadmin') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'edit_property_inventory');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'admin') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'edit_property_inventory');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelowner') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'edit_property_inventory');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelmanager') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'edit_property_inventory');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'owner') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'delete_property_inventory');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'mainadmin') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'delete_property_inventory');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'admin') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'delete_property_inventory');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelowner') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'delete_property_inventory');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelmanager') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'delete_property_inventory');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'owner') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'add_property_expenses');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'mainadmin') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'add_property_expenses');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'admin') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'add_property_expenses');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelowner') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'add_property_expenses');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelmanager') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'add_property_expenses');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'owner') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'edit_property_expenses');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'mainadmin') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'edit_property_expenses');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'admin') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'edit_property_expenses');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelowner') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'edit_property_expenses');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelmanager') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'edit_property_expenses');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'owner') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'delete_property_expenses');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'mainadmin') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'delete_property_expenses');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'admin') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'delete_property_expenses');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelowner') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'delete_property_expenses');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelmanager') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'delete_property_expenses');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'owner') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'add_property_managers');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'mainadmin') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'add_property_managers');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'admin') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'add_property_managers');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelowner') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'add_property_managers');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelmanager') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'add_property_managers');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'owner') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'edit_property_managers');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'mainadmin') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'edit_property_managers');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'admin') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'edit_property_managers');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelowner') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'edit_property_managers');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelmanager') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'edit_property_managers');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'owner') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'delete_property_managers');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'mainadmin') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'delete_property_managers');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'admin') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'delete_property_managers');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelowner') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'delete_property_managers');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelmanager') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'delete_property_managers');

DELETE FROM `<DB_PREFIX>privileges` WHERE `code` = 'add_property_inventory';
DELETE FROM `<DB_PREFIX>privileges` WHERE `code` = 'edit_property_inventory';
DELETE FROM `<DB_PREFIX>privileges` WHERE `code` = 'delete_property_inventory';
DELETE FROM `<DB_PREFIX>privileges` WHERE `code` = 'add_property_expenses';
DELETE FROM `<DB_PREFIX>privileges` WHERE `code` = 'edit_property_expenses';
DELETE FROM `<DB_PREFIX>privileges` WHERE `code` = 'delete_property_expenses';
DELETE FROM `<DB_PREFIX>privileges` WHERE `code` = 'add_property_managers';
DELETE FROM `<DB_PREFIX>privileges` WHERE `code` = 'edit_property_managers';
DELETE FROM `<DB_PREFIX>privileges` WHERE `code` = 'delete_property_managers';

DELETE FROM `<DB_PREFIX>roles` WHERE `code` = 'hotelmanager';

DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_PROPERTY_MANAGEMENT';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_PROPERTY_MANAGEMENT_SHORT';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_PROPERTY_MANAGEMENT_SETTINGS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MD_PROPERTY_MANAGEMENT';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_PROPERTY_INVENTORY';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_PROPERTY_EXPENSES';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_PROPERTY_MANAGERS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_PROPERTY_INVENTORY_NAME';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_PROPERTY_EXPENSE_NAME';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_PROPERTY_MANAGER_NAME';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MSN_PROPERTY_MANAGEMENT_IS_ACTIVE';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MS_PROPERTY_MANAGEMENT_IS_ACTIVE';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MSN_ADMIN_CHANGE_MANAGER_PASSWORD';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MS_ADMIN_CHANGE_MANAGER_PASSWORD';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_FLAT_MANAGERS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_HOTEL_MANAGERS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_HOTEL_MANAGER_LOGIN';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_HOTEL_MANAGER';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_FLAT_MANAGER';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MSN_MULTI_HOTELS_FOR_MANAGERS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MS_MULTI_HOTELS_FOR_MANAGERS';

