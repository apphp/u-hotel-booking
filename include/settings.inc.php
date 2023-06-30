<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site https://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

// Don't change it!
define('PROJECT_NAME', 'HotelBooking');
define('CURRENT_VERSION', '2.8.9');
define('MOBILE_APP_NAME', 'uHotelBooking Mobile App');
define('MOBILE_APP_NAME_SHORT', 'uHotelBooking');
define('MOBILE_APP_VERSION', '1.0.0');

// Default parameters
define('IMAGE_DIRECTORY', 'images/');     
define('CACHE_DIRECTORY', 'tmp/cache/');
define('DEFAULT_TEMPLATE', 'default'); 		// default
define('DEFAULT_DIRECTION', 'ltr');    		// ltr|rtl

// Script mode
define('SITE_MODE', 'production');     	// demo|development|production

// (list of supported Timezones - http://us3.php.net/manual/en/timezones.php)    
define('TIME_ZONE', 'America/Los_Angeles');

define('DB_TYPE', 'MySQLi'); 				/* possible values: PDO, MySQLi */


/////////////////////////////////////////////////////////////////
// Return types for database_query function
/////////////////////////////////////////////////////////////////
define('ALL_ROWS', 0);
define('FIRST_ROW_ONLY', 1);
define('DATA_ONLY', 0);
define('ROWS_ONLY', 1);
define('DATA_AND_ROWS', 2);
define('FIELDS_ONLY', 3);
define('FETCH_ASSOC', 'mysqli_fetch_assoc');
define('FETCH_ARRAY', 'mysqli_fetch_array');

// Optimization
// --------------------------------------------------------------
define('OPTIMIZE_SQL', true);
$SQL_CASHE = array(
	'room_default_price' 			=> array(),
	'room_week_default_price'		=> array(),
	'room_extra_bed_price'			=> array(),
	'room_lowest_price'				=> array(),
	'rooms_availability_for_month' 	=> array(),
);


/////////////////////////////////////////////////////////////////
// Definition of Tables Constants
/////////////////////////////////////////////////////////////////
define('TABLE_ACCOUNTS', DB_PREFIX.'accounts');
define('TABLE_ACCOUNT_LOCATIONS', DB_PREFIX.'account_locations');
define('TABLE_AFFILIATES', DB_PREFIX.'affiliates');
define('TABLE_BANLIST', DB_PREFIX.'banlist');      
define('TABLE_BANNERS', DB_PREFIX.'banners');      
define('TABLE_BANNERS_DESCRIPTION', DB_PREFIX.'banners_description');      
define('TABLE_BOOKINGS', DB_PREFIX.'bookings');      
define('TABLE_BOOKINGS_ROOMS', DB_PREFIX.'bookings_rooms');      
define('TABLE_CAMPAIGNS', DB_PREFIX.'campaigns');      
define('TABLE_CAR_AGENCIES', DB_PREFIX.'car_agencies');
define('TABLE_CAR_AGENCIES_DESCRIPTION', DB_PREFIX.'car_agencies_description');
define('TABLE_CAR_AGENCIES_LOCATIONS', DB_PREFIX.'car_agencies_locations');
define('TABLE_CAR_AGENCIES_LOCATIONS_DESCRIPTION', DB_PREFIX.'car_agencies_locations_description');
define('TABLE_CAR_AGENCY_LOCATIONS', DB_PREFIX.'car_agency_locations');
define('TABLE_CAR_AGENCY_LOCATIONS_DESCRIPTION', DB_PREFIX.'car_agency_locations_description');
define('TABLE_CAR_AGENCY_MAKES', DB_PREFIX.'car_agency_makes');
define('TABLE_CAR_AGENCY_MAKES_DESCRIPTION', DB_PREFIX.'car_agency_makes_description');
define('TABLE_CAR_AGENCY_PAYMENT_GATEWAYS', DB_PREFIX.'car_agency_payment_gateways');
define('TABLE_CAR_AGENCY_RESERVATIONS', DB_PREFIX.'car_reservations');
define('TABLE_CAR_AGENCY_VEHICLES', DB_PREFIX.'car_agency_vehicles');
define('TABLE_CAR_AGENCY_VEHICLES_DESCRIPTION', DB_PREFIX.'car_agency_vehicles_description');
define('TABLE_CAR_AGENCY_VEHICLE_CATEGORIES', DB_PREFIX.'car_agency_vehicle_categories');
define('TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION', DB_PREFIX.'car_agency_vehicle_categories_description');
define('TABLE_CAR_AGENCY_VEHICLE_TYPES', DB_PREFIX.'car_agency_vehicle_types');
define('TABLE_COMMENTS', DB_PREFIX.'comments');      
define('TABLE_COUNTRIES', DB_PREFIX.'countries');
define('TABLE_COUNTRIES_DESCRIPTION', DB_PREFIX.'countries_description');
define('TABLE_COUPONS', DB_PREFIX.'coupons');
define('TABLE_CURRENCIES', DB_PREFIX.'currencies');
define('TABLE_CUSTOMERS', DB_PREFIX.'customers');
define('TABLE_CUSTOMER_FUNDS', DB_PREFIX.'customer_funds');
define('TABLE_CUSTOMER_AWARD_POINTS', DB_PREFIX.'customer_award_points');
define('TABLE_CUSTOMER_GROUPS', DB_PREFIX.'customer_groups');      
define('TABLE_EMAIL_TEMPLATES', DB_PREFIX.'email_templates');      
define('TABLE_EVENTS_REGISTERED', DB_PREFIX.'events_registered');
define('TABLE_EXTRAS', DB_PREFIX.'extras');
define('TABLE_EXTRAS_DESCRIPTION', DB_PREFIX.'extras_description');
define('TABLE_FAQ_CATEGORIES', DB_PREFIX.'faq_categories');
define('TABLE_FAQ_CATEGORIES_DESCRIPTION', DB_PREFIX.'faq_categories_description');
define('TABLE_FAQ_CATEGORY_ITEMS', DB_PREFIX.'faq_category_items');
define('TABLE_FAQ_CATEGORY_ITEMS_DESCRIPTION', DB_PREFIX.'faq_category_items_description');      
define('TABLE_GALLERY_ALBUMS', DB_PREFIX.'gallery_albums');      
define('TABLE_GALLERY_ALBUMS_DESCRIPTION', DB_PREFIX.'gallery_albums_description');      
define('TABLE_GALLERY_ALBUM_ITEMS', DB_PREFIX.'gallery_album_items');      
define('TABLE_GALLERY_ALBUM_ITEMS_DESCRIPTION', DB_PREFIX.'gallery_album_items_description');      
define('TABLE_HOTELS', DB_PREFIX.'hotels');      
define('TABLE_HOTELS_DESCRIPTION', DB_PREFIX.'hotels_description');      
define('TABLE_HOTELS_LOCATIONS', DB_PREFIX.'hotels_locations');
define('TABLE_HOTELS_LOCATIONS_DESCRIPTION', DB_PREFIX.'hotels_locations_description');
define('TABLE_HOTELS_PROPERTY_TYPES', DB_PREFIX.'hotels_property_types');
define('TABLE_HOTELS_PROPERTY_TYPES_DESCRIPTION', DB_PREFIX.'hotels_property_types_description');
define('TABLE_HOTEL_IMAGES', DB_PREFIX.'hotel_images');
define('TABLE_HOTEL_PAYMENT_GATEWAYS', DB_PREFIX.'hotel_payment_gateways');
define('TABLE_HOTEL_PERIODS', DB_PREFIX.'hotel_periods');
define('TABLE_LANGUAGES', DB_PREFIX.'languages');
define('TABLE_MAIL_LOGS', DB_PREFIX.'mail_log');
define('TABLE_MEAL_PLANS', DB_PREFIX.'meal_plans');      
define('TABLE_MEAL_PLANS_DESCRIPTION', DB_PREFIX.'meal_plans_description');      
define('TABLE_MENUS', DB_PREFIX.'menus');      
define('TABLE_MODULES', DB_PREFIX.'modules');      
define('TABLE_MODULES_SETTINGS', DB_PREFIX.'modules_settings');      
define('TABLE_NEWS', DB_PREFIX.'news');
define('TABLE_NEWS_SUBSCRIBED', DB_PREFIX.'news_subscribed');
define('TABLE_PACKAGES', DB_PREFIX.'packages');
define('TABLE_PAGES', DB_PREFIX.'pages');
define('TABLE_PRIVILEGES', DB_PREFIX.'privileges');
define('TABLE_RATINGS_ITEMS', DB_PREFIX.'ratings_items');
define('TABLE_RATINGS_USERS', DB_PREFIX.'ratings_users');
define('TABLE_REVIEWS', DB_PREFIX.'reviews');
define('TABLE_ROLES', DB_PREFIX.'roles');
define('TABLE_ROLE_PRIVILEGES', DB_PREFIX.'role_privileges');		   
define('TABLE_ROOMS', DB_PREFIX.'rooms');      
define('TABLE_ROOMS_AVAILABILITIES', DB_PREFIX.'rooms_availabilities');      
define('TABLE_ROOMS_DESCRIPTION', DB_PREFIX.'rooms_description');      
define('TABLE_ROOMS_PRICES', DB_PREFIX.'rooms_prices');      
define('TABLE_ROOM_FACILITIES', DB_PREFIX.'room_facilities');
define('TABLE_ROOM_FACILITIES_DESCRIPTION', DB_PREFIX.'room_facilities_description');      
define('TABLE_SEARCH_WORDLIST', DB_PREFIX.'search_wordlist');      
define('TABLE_SETTINGS', DB_PREFIX.'settings');      
define('TABLE_SITE_DESCRIPTION', DB_PREFIX.'site_description');
define('TABLE_STATES', DB_PREFIX.'states');      
define('TABLE_TESTIMONIALS', DB_PREFIX.'testimonials');      
define('TABLE_VOCABULARY', DB_PREFIX.'vocabulary');
define('TABLE_WISHLIST', DB_PREFIX.'wishlist');      


/////////////////////////////////////////////////////////////////
// Set Errors Handling
/////////////////////////////////////////////////////////////////
// --------------------------------------------------------------
if(SITE_MODE == 'development'){
	error_reporting(E_ALL);
	ini_set('display_errors', 'On');    
}else{
	error_reporting(E_ALL);
	ini_set('display_errors', 'Off');
    ini_set('log_errors', 'On');
}

// Profiler
// --------------------------------------------------------------
$PROFILER = array(
	'start_time'		=> 0,
	'end_time'			=> 0,
	'sql_total_time'	=> 0,
	'warnings'			=> array(),
	'errors'			=> array(),
	'queries'			=> array()
);

/////////////////////////////////////////////////////////////////
// Non-Documented Settings
/////////////////////////////////////////////////////////////////

// Copyright Settings
// --------------------------------------------------------------
define('SHOW_COPYRIGHT', true); 

// Calendar Settings
// --------------------------------------------------------------
define('CALENDAR_HOTEL', 'new'); // old|new
define('SHOW_QUANTITY_MONTHS_CALENDAR', 1); // 1|2|3...

// Turn the script into the mode of working with apartments, not hotels
// --------------------------------------------------------------
define('FLATS_INSTEAD_OF_HOTELS', false);

// Hotel Owner Settings
// --------------------------------------------------------------


// Search Settings
// --------------------------------------------------------------
// Hotel search block settings
define('HOTEL_SELECT_LOCATION', 'autocomplete'); // dropdownlist|autocomplete
// Perform destination validation
define('DESTINATION_VALIDATION', true); // true|false
// Automatically search for available rooms if is empty result
define('AUTOMATIC_RE_SEARCH_ROOM', true);
// Show tab "All" for search all types of properties on homepage
define('ALLOW_TAB_SEARCH_ALL', false);


// Check availability settings & search results
// --------------------------------------------------------------
define('HOTEL_BUTTON_RESERVE_AJAX', true); // true|false
// Max price for room prices and filters (in default currency)
define('MAX_PRICE_FILTER', 10000);
// Show filter distance to center point
define('SHOW_FILTER_DISTANCE_TO_CENTER_POINT', false);
// Filter by the number of rooms
define('TYPE_FILTER_TO_NUMBER_ROOMS', 'hotel'); // rooms|hotel
// Residue rooms is availability
define('RESIDUE_ROOMS_IS_AVAILABILITY', false);
// Enable Best Price for number beds
define('SHOW_BEST_PRICE_ROOMS', true);
// Min beds use for adults (you must use the 'allow_minimum_beds' setting)
define('MIN_BEDS_USE_FOR_ADULTS', false);
// Calculate children in calculation of meal plans price
define('COUNT_MEAL_FOR_CHILDREN', false);
// Enables adults selecting while reservation process
define('ALLOW_ADULTS_SELECTING', true);
// Enables children selecting while reservation process
define('ALLOW_CHILDREN_SELECTING', true);
// Allow admin to see inactive properties when logged in 
define('ADMIN_CAN_SEE_INACTIVE_PROPERTIES', true);


// Global campaigns enabled or disabled
// --------------------------------------------------------------
define('GLOBAL_CAMPAIGNS', 'disabled'); 	// enabled|disabled


// Enable gallery carousel
// --------------------------------------------------------------
define('GALLERY_TYPE', 'carousel'); // carousel or slider


// Login links
// --------------------------------------------------------------
// Link for default = login
define('DEFAULT_LOGIN_LINK', 'login');
// Link for admin login, default = login
define('SHOW_ADMIN_LOGIN', true);
define('ADMIN_LOGIN', 'login');
define('HOTEL_OWNER_LOGIN', 'login');
// Link for travel agency login, default = login
define('SHOW_TRAVEL_AGENCY_LOGIN', true);
define('TRAVEL_AGENCY_LOGIN', 'agency_login');
// Link for customer login
define('DEFAULT_CUSTOMER_LINK', 'login');

// Type calculation "discount guest"
// --------------------------------------------------------------
define('TYPE_DISCOUNT_GUEST', 'rooms'); // by guests|rooms recipient


// Payment settings
// --------------------------------------------------------------
// Show checkbox
define('SHOW_PAYMENT_FOR_CHECKBOX', false);
// Restrict adding additional payment
define('RESTRICT_ADD_PAYMENT', true);


// Mail Settings
// --------------------------------------------------------------
define('SAVE_MAIL_LOG', 'all'); // all|mass_only or empty

// Booking (filter) in BO?
define('SHOW_CREATION_DATE_FROM_TO', false);

