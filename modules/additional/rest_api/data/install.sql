
INSERT INTO `<DB_PREFIX>modules` (`id`, `name`, `name_const`, `description_const`, `icon_file`, `module_tables`, `dependent_modules`, `settings_page`, `settings_const`, `settings_access_by`, `management_page`, `management_const`, `management_access_by`, `is_installed`, `module_type`, `show_on_dashboard`, `priority_order`) VALUES
(NULL, 'rest_api', '_REST_API', '_MD_REST_API', 'rest_api.png', '', '', '', '', 'owner,mainadmin,hotelowner', '', '', 'owner,mainadmin,hotelowner', 1, 2, 0, 0);


INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_REST_API', 'REST API');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_MD_REST_API', 'This module provides a customizable, extensible RESTful API of data managed by uHotelBooking.');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_REST_API_SETTINGS', 'REST API Settings');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_REST_API_MANAGEMENT', 'REST API Management');


