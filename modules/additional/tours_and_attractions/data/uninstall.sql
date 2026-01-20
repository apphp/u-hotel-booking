
DELETE FROM `<DB_PREFIX>modules` WHERE `name` = 'tours_and_attractions';

DELETE FROM `<DB_PREFIX>modules_settings` WHERE `module_name` = 'tours_and_attractions';


DROP TABLE IF EXISTS `<DB_PREFIX>tours`;
DROP TABLE IF EXISTS `<DB_PREFIX>tours_description`;
DROP TABLE IF EXISTS `<DB_PREFIX>tours_agencies`;
DROP TABLE IF EXISTS `<DB_PREFIX>tours_agencies_description`;
DROP TABLE IF EXISTS `<DB_PREFIX>tours_types`;
DROP TABLE IF EXISTS `<DB_PREFIX>tours_types_description`;
DROP TABLE IF EXISTS `<DB_PREFIX>tours_locations`;
DROP TABLE IF EXISTS `<DB_PREFIX>tours_locations_description`;
DROP TABLE IF EXISTS `<DB_PREFIX>tours_reviews`;
DROP TABLE IF EXISTS `<DB_PREFIX>tours_images`;
DROP TABLE IF EXISTS `<DB_PREFIX>tours_reservations`;


DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOURS_AND_ATTRACTIONS_SHORT';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOURS_AND_ATTRACTIONS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOURS_AND_ATTRACTIONS_MANAGEMENT';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOURS_AND_ATTRACTIONS_SETTINGS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MD_TOURS_AND_ATTRACTIONS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MSN_ACTIVATE_TOURS_AND_ATTRACTIONS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MS_TOURS_AND_ATTRACTIONS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOURS_MANAGEMENT';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOUR_TYPES';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOUR_TYPE';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOUR_LOCATIONS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOURS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOUR_INFO';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOUR_TYPES_MANAGEMENT';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOUR_REVIEWS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOUR_REVIEWS_MANAGEMENT';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_AVAILABLE_TOURS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_NO_TOURS_FOUND';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MSN_ENABLE_REVIEWS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MS_ENABLE_REVIEWS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOUR';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_YOU_NOT_REVIEW_THIS_TOUR';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_LEGEND_TOUR_CANCELED';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_LEGEND_TOUR_PENDING';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_LEGEND_TOUR_REFUNDED';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_LEGEND_TOUR_RESERVED';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MSN_SHOW_TAB_ON_HOMEPAGE';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MS_SHOW_TAB_ON_HOMEPAGE';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_FIND_TOUR_OR_ATTRACTION_NOTICE';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_WRONG_EMPTY_FROM_DATE';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_WRONG_EMPTY_TO_DATE';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MY_TOUR_REVIEWS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_FOUND_TOURS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOTAL_TOURS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_PERSON_PER_DAY';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOUR_DETAILS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOUR_AGENCIES';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOUR_AGENCY';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_FOUND_TOUR_AGENCIES';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOUR_NOT_REVIEWED_YET';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOUR_WAS_ADDED';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOUR_WAS_COMPLETED_MSG';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOUR_WAS_REMOVED';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_SELECTED_TOURS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOUR_RESERVATION';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOUR_REMOVE_TOUR_FROM_CART';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOUR_RESERVATIONS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_MY_TOUR_RESERVATIONS';
DELETE FROM `<DB_PREFIX>vocabulary` WHERE `key_value` = '_TOUR_BOOKING_COMPLETED';

