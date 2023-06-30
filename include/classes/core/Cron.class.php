<?php

/***
 *	Class Cron (has differences)
 *  -------------- 
 *  Description : encapsulates cron job properties
 *	Written by  : ApPHP
 *	Version     : 1.0.3
 *  Updated	    : 09.06.2016
 *	Usage       : Core Class (ALL)
 *
 *	PUBLIC:					STATIC:					PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct             Run
 *	__destruct
 *	
 *  1.0.3
 *      - added CarRental::RemoveExpired();
 *      -
 *      -
 *      -
 *      -
 *  1.0.2
 *      - added Appointments::SendReminders()
 *      - added Appointments::RemoveExpired()
 *      - added Inquiries::RemoveOld();
 *      - added Polls::UpdateStatus();				
 *      - added MembershipPlans::RemoveExpired();            
 *  1.0.1
 *      - fixed error on first time running
 *      - added Orders::RemoveExpired()
 *      - added Coupons::UpdateStatus();
 *      - added Core functionality
 *      - added functionality for BusinnessDirectory      
 **/

class Cron {

	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{

	}

	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	/**
	 * Run - called by outside cron
	 */
	public static function Run()
	{
		// add here your code...
        // Class::Method();
		$perform_actions = false;

        // update last time running
		$sql = 'SELECT
					cron_type,
					cron_run_last_time,
					cron_run_period,
					cron_run_period_value,
					CASE
						WHEN cron_run_last_time IS NULL THEN \'999\'
						WHEN cron_run_period = \'minute\' THEN TIMESTAMPDIFF(MINUTE, cron_run_last_time, \''.date('Y-m-d H:i:s').'\')
						ELSE TIMESTAMPDIFF(HOUR, cron_run_last_time, \''.date('Y-m-d H:i:s').'\')
					END as time_diff										
				FROM '.TABLE_SETTINGS;
		$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);

        if($result['cron_type'] == 'batch'){
			$perform_actions = true;    
        }elseif($result['cron_type'] == 'non-batch' && $result['time_diff'] > $result['cron_run_period_value']){
			$perform_actions = true;
		}else{
			$perform_actions = false;
		}
        
		if($perform_actions)
		{
			// Update Feeds
			RSSFeed::UpdateFeeds();
			
			// Close expired discount campaigns
			Campaigns::UpdateStatus();
			// Close expired coupons
			Coupons::UpdateStatus();
			// Remove expired 'Prebooking' bookings
			Bookings::RemoveExpired();
			// Remove expired 'Prebooking' cars
			CarRental::RemoveExpired();
			// Notification of customer after they stayed at the hotel
			Bookings::NotifyCustomerAfterStayed();

			// update last time running
			$sql = "UPDATE ".TABLE_SETTINGS." SET cron_run_last_time = '".date('Y-m-d H:i:s')."'";
			database_void_query($sql);
		}
	}
}
