
DELETE FROM `<DB_PREFIX>modules` WHERE `name` = 'car_rental';

DELETE FROM `<DB_PREFIX>modules_settings` WHERE `module_name` = 'car_rental';


DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'owner') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'edit_car_reservations');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'owner') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'cancel_car_reservations');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'owner') AND `privilege_id` = (SELECT id FROM `<DB_PREFIX>privileges` WHERE `code` = 'delete_car_reservations');
DELETE FROM `<DB_PREFIX>role_privileges` WHERE `role_id` = (SELECT id FROM `<DB_PREFIX>roles` WHERE `code` = 'agencyowner');
DELETE FROM `<DB_PREFIX>privileges` WHERE `code` = 'edit_car_agency_info';
DELETE FROM `<DB_PREFIX>privileges` WHERE `code` = 'edit_car_agency_vehicles';
DELETE FROM `<DB_PREFIX>privileges` WHERE `code` = 'edit_car_reservations';
DELETE FROM `<DB_PREFIX>privileges` WHERE `code` = 'cancel_car_reservations';
DELETE FROM `<DB_PREFIX>privileges` WHERE `code` = 'delete_car_reservations';
DELETE FROM `<DB_PREFIX>roles` WHERE `code` = 'agencyowner';


DROP TABLE IF EXISTS `<DB_PREFIX>car_agencies`;
DROP TABLE IF EXISTS `<DB_PREFIX>car_agencies_description`;
DROP TABLE IF EXISTS `<DB_PREFIX>car_agencies_locations`;
DROP TABLE IF EXISTS `<DB_PREFIX>car_agencies_locations_description`;
DROP TABLE IF EXISTS `<DB_PREFIX>car_agency_locations`;
DROP TABLE IF EXISTS `<DB_PREFIX>car_agency_vehicle_categories`;
DROP TABLE IF EXISTS `<DB_PREFIX>car_agency_vehicle_categories_description`;
DROP TABLE IF EXISTS `<DB_PREFIX>car_agency_vehicle_types`;
DROP TABLE IF EXISTS `<DB_PREFIX>car_agency_vehicles`;
DROP TABLE IF EXISTS `<DB_PREFIX>car_agency_vehicles_description`;
DROP TABLE IF EXISTS `<DB_PREFIX>car_agency_makes`;
DROP TABLE IF EXISTS `<DB_PREFIX>car_agency_makes_description`;
DROP TABLE IF EXISTS `<DB_PREFIX>car_agency_payment_gateways`;
DROP TABLE IF EXISTS `<DB_PREFIX>car_reservations`;


DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CARS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CARS_IN_RENTAL';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CARS_RESERVATION';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CART_WAS_UPDATED';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CAR_AGENCIES';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CAR_AGENCY';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CAR_AGENCY_OWNER';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CAR_AGENCY_OWNERS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CAR_AGENCY_PAYMENT_GATEWAYS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CAR_BOOKING_COMPLETED';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CAR_HALF_HOUR_ALERT';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CAR_INVENTORY';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CAR_NOT_FOUND';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CAR_OWNER_NOT_ASSIGNED';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CAR_PICKING_UP';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CAR_RENTAL';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CAR_RENTAL_SETTINGS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CAR_RESERVATIONS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CAR_RETURNING';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CAR_TYPE';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CAR_WAS_ADDED';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CAR_WAS_COMPLETED_MSG';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_CAR_WAS_REMOVED';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_LEGEND_CAR_CANCELED';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_LEGEND_CAR_PENDING';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_LEGEND_CAR_REFUNDED';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_LEGEND_CAR_RESERVED';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MD_CAR_RENTAL';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MSN_ACTIVATE_CAR_RENTAL';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MS_ACTIVATE_CAR_RENTAL';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MY_CAR_RENTALS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_NO_CARS_FOUND';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_REMOVE_CAR_FROM_CART';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_RENT_A_CAR';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_SELECTED_CARS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOTAL_CARS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_WIDGET_INTEGRATION_MESSAGE_CARS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_WIDGET_INTEGRATION_MESSAGE_HINT_CARS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_AGENCY_CAR_TYPES';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_AVAILABLE_CARS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_VEHICLE_CATEGORIES';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_VEHICLE_CATEGORY';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_VEHICLE_CATEGORY_DELETE_ALERT';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_VEHICLE_DETAILS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_VEHICLE_TYPE';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_VEHICLE_TYPES';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TRANSMISSION';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TRANSPORTATION_AGENCIES';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TRANSPORTATION_AGENCIES_MANAGEMENT';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MS_SEND_ORDER_COPY_TO_AGENCY';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MSN_SEND_ORDER_COPY_TO_AGENCY';

