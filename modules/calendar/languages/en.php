<?php
//------------------------------------------------------------------------------             
//*** English (en)
//------------------------------------------------------------------------------
function setLanguage(){ 
    
	$lang['all_available'] = "All Available";
	$lang['partially_booked'] = "Partially Booked";
	$lang['not_avaliable'] = "Not Available";
	$lang['fully_booked'] = "Fully Booked";
	$lang['legend'] = "Legend";
	$lang['rooms'] = "rooms";
	$lang['with_reserved'] = "With Reserved";
	$lang['without_reserved'] = "Without Reserved";
	$lang['bookings'] = "Bookings";
	$lang['all_rooms'] = "All Rooms";
	$lang['reserved_and_completed'] = "Reserved & Completed";
	$lang['completed_only'] = "Completed Only";
	
	$lang['actions'] = "Actions";
	$lang['add_category'] = "Add Category";
	$lang['add_event'] = "Add Event";
	$lang['add_new_category'] = "Add New Category";
    $lang['add_new_event'] = "Add New Event";
	$lang['back'] = "Back";
	$lang['cancel'] = "Cancel";
	$lang['category_color'] = "Category Color";
	$lang['category_description'] = "Category Description";
	$lang['category_details'] = "Category Details";
	$lang['category_name'] = "Category Name";
	$lang['categories'] = "Categories";
	$lang['categories_events'] = "Categories Events";	
	$lang['click_to_delete'] = "Click to delete";
	$lang['chart_bar'] = "Column Chart";
	$lang['chart_column'] = "Bar Chart";
	$lang['chart_pie'] = "Pie Chart";
	$lang['click_view_week'] = "Click to view this week";
	$lang['click_to_print'] = "Click to print";
	$lang['close'] = "Close";
	$lang['close_lc'] = "close";
	$lang['collapse'] = "collapse";
	$lang['debug_info'] = "Debug Info";
	$lang['default'] = "default";
	$lang['details'] = "Details";
	$lang['delete'] = "Delete";
	$lang['delete_events'] = "Delete Events";
	$lang['delete_by_range'] = "Delete By Range";
	$lang['duration'] = "Duration";	
	$lang['edit'] = "Edit";
	$lang['edit_category'] = "Edit Category";
	$lang['edit_event'] = "Edit Event";
	$lang['order_lc'] = "order";
	$lang['orders_lc'] = "orders";
	$lang['events_categories'] = "Events Categories";
	$lang['event_name'] = "Event Name";
	$lang['event_date'] = "Event Date";
	$lang['event_time'] = "Event Time";
	$lang['event_description'] = "Event Description";
	$lang['event_details'] = "Event Details";
	$lang['events'] = "Events";
	$lang['events_management'] = "Events Management";
	$lang['events_statistics'] = "Events Statistics";
	$lang['expand'] = "Expand";
	$lang['from'] = "From";
	$lang['go'] = "Go";
	$lang['hours'] = "Hours";
	$lang['manage_events'] = "Manage Events";
	$lang['not_defined'] = "not defined";
	$lang['occurrences'] = "Occurrences";
	$lang['one_time'] = "One Time Only";
	$lang['or'] = "or";
	$lang['pages'] = "Pages";
	$lang['print'] = "Print";
	$lang['repeat_every'] = "Repeat Every";
	$lang['repeatedly'] = "Repeatedly";
	$lang['select'] = "select";
	$lang['select_event'] = "select event";
	$lang['show_all'] = "show all";	
	$lang['select_category'] = "select category";
	$lang['select_chart_type'] = "Select Chart Type";
	$lang['start_time'] = "Start Time";
	$lang['statistics'] = "Statistics";
	$lang['th'] = "th"; // suffix for dates, like: 25th
	$lang['to'] = "To";
	$lang['today'] = "Today";
	$lang['top_10_events'] = "Top 10 events";
	$lang['total_events'] = "Total Events";
	$lang['total_categories'] = "Total Categories";
	$lang['total_running_time'] = "Total running time";
	$lang['undefined'] = "Undefined";
	$lang['update'] = "Update";
	$lang['update_category'] = "Update Category";
	$lang['update_event'] = "Update Event";
	$lang['view'] = "View";
	$lang['view_events'] = "View Events";
	$lang['select_hotel'] = "Select Hotel"; 
	
	$lang['lbl_add_event_to_list'] = "Just add to the list of events";
	$lang['lbl_add_event_occurrences'] = "Add occurrences for this event";

	$lang['msg_editing_event_in_past'] = "Event cannot be added in past time! Please re-enter.";
	$lang['msg_this_operation_blocked'] = "This operation is blocked!";
	$lang['msg_this_operation_blocked_demo'] = "This operation is blocked in DEMO version!";
	$lang['msg_timezone_invalid'] = "Timezone ID '_TIME_ZONE_' is invalid.";
	$lang['msg_view_type_invalid'] = "Default View '_DEFAULT_VIEW_' was not allowed! Please select another.";

    $lang['error_inserting_new_events'] = "An error occurred while inserting new events! Please try again later.";
	$lang['error_inserting_new_category'] = "An error occurred while inserting new category! Please try again later.";
    $lang['error_deleting_event'] = "An error occurred while deleting event! Please try again later.";
	$lang['error_duplicate_event_inserting'] = "Event with such name was already added to selected period! Please choose another.";
	$lang['error_duplicate_events_inserting'] = "Selected time period is already occupied! Please choose another.";
    $lang['error_updating_event'] = "An error occurred while updating event! Please try again later.";
	$lang['error_event_exists'] = "Event with such name already exists! Please choose another name.";
	$lang['error_category_exists'] = "Category with such name already exists! Please choose another name.";
	$lang['error_from_to_hour'] = "'From' hours cannot be greater than 'To' hours! Please re-enter.";
    $lang['error_updating_category'] = "An error occurred while updating category! Please try again later.";
	$lang['error_deleting_category'] = "An error occurred while deleting category! Please try again later.";
	$lang['error_deleting_event_hours'] = "Cannot delete event! Less than _HOURS_ hours remained.";	
	$lang['error_deleting_event_past'] = "Cannot delete event in the past!";
	$lang['error_no_event_found'] = "No events found!";
	$lang['error_no_dates_found'] = "No suitable dates were found to insert event! Please re-enter.";

    $lang['success_new_event_was_added'] = "New event was successfully added!";
    $lang['success_event_was_deleted'] = "Event '_EVENT_NAME_' was successfully deleted!";
	$lang['success_events_were_deleted'] = "Events for selected period of time were successfully deleted!";
    $lang['success_event_was_updated'] = "Event was successfully updated!";
	$lang['success_new_category_added'] = "New category was successfully added!";
	$lang['success_category_was_updated'] = "Category was successfully updated!";
    $lang['success_category_was_deleted'] = "Category was successfully deleted!";

    
    // date-time
    $lang['day']    = "day";
    $lang['month']  = "month";
    $lang['year']   = "year";
    $lang['hour']   = "hour";
    $lang['min']    = "min";
    $lang['sec']    = "sec";
    
    $lang['daily']     = "Daily";
    $lang['weekly']    = "Weekly";
    $lang['monthly']   = "Monthly";
    $lang['yearly']    = "Yearly";
	$lang['list_view'] = "List View";

    $lang['sun'] = "Sun";
	$lang['mon'] = "Mon";
	$lang['tue'] = "Tue";
	$lang['wed'] = "Wed";
	$lang['thu'] = "Thu";
	$lang['fri'] = "Fri";
	$lang['sat'] = "Sat";    

    $lang['sunday'] = "Sunday";
	$lang['monday'] = "Monday";
	$lang['tuesday'] = "Tuesday";
	$lang['wednesday'] = "Wednesday";
	$lang['thursday'] = "Thursday";
	$lang['friday'] = "Friday";
	$lang['saturday'] = "Saturday";    
    
    $lang['months'][1] = "January";
    $lang['months'][2] = "February";
    $lang['months'][3] = "March";
    $lang['months'][4] = "April";
    $lang['months'][5] = "May";
    $lang['months'][6] = "June";
    $lang['months'][7] = "July";
    $lang['months'][8] = "August";
    $lang['months'][9] = "September";
    $lang['months'][10] = "October";
    $lang['months'][11] = "November";
    $lang['months'][12] = "December";
    
    return $lang;
}
