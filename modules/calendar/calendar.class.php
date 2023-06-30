<?php
################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 #
## --------------------------------------------------------------------------- #
##  ApPHP Calendar version 3.0.5                                               #
##  Developed by:  ApPhp <info@apphp.com>                                      #
##  Last modified: 12.09.2016                                                  #
##  License:       GNU LGPL v.3                                                #
##  Site:          http://www.apphp.com/php-calendar/                          #
##  Copyright:     ApPHP Calendar (c) 2009-2010. All rights reserved.          #
##                                                                             #
##  Additional modules (embedded):                                             #
##  -- (Javascript - Draggable Elements)                                       #
##     Paranoid Ferret Productions              http://www.switchonthecode.com #
##  -- Google AJAX Libraries API                  http://code.google.com/apis/ #
##  -- Crystal Project Icons (icons set)               http://www.everaldo.com #
##                                                                             #
################################################################################
// Non-documented:
// ---------------
// disableEarlierHours - disable hours in dropdown box before selected hour
// hideWeekEmptySlots - hides empty slots in Weekly View
// separator 		  - used to separate events for GROUP_CONCAT() function in SELECT SQL 
//
// Modified for Hotel Site
// ---------------
// [05.02.2021] - replaced google.com/jsapi
// [05.12.2016] - added a new variable "calUrl"
// [12.09.2016] - fixed issue with all rooms for single hotel
// [02.04.2016] - added color colorDarkGray for not available rooms
// [12.03.2016] - added monthly tripple number of months
// [31.10.2015] - changed booked rooms to left rooms
// [12.09.2015] - removed All Rooms for multi-hotels
// [15.05.2014] - fixed bug with drawing DrawMonthlyTriple()
// [28.01.2014] - fixed bug with drawing MonthlyView(), WeeklyView() and YearlyView()
// [16.01.2014] - added DrawMonthlyTriple()
// [09.10.2013] - fixed bug in drawing negative number of rooms
// [20.10.2012] - commented to prevent scrolling for big ammount of rooms
// [26.02.2012] - fixed issue with wrong displaying occupated rooms when booked on 31.m/01.m+1
// [25.11.2011] - fixed displaying wrong occupated das for 'over year' periods
// [03.08.2011] - fixed displaying available days on yearly view

class Calendar{


    // PUBLIC						PRIVATE
    // --------                     ---------
    // __construct()				SetDefaultParameters
    // __destruct()                 GetCurrentParameters
    // Show()                       DrawCssStyle
    // SetCalendarDimensions        DrawJsFunctions
    // SetCaption                   IncludeModules
    // SetWeekStartedDay			InitializeJsFunctions
    // SetWeekDayNameLength         DrawMessages
    // ShowWeekNumberOfYear         DrawListView
    // SetTimeZone                  DrawYear
    // GetCurrentTimeZone           DrawMonth
    // SetDefaultView               DrawMonthSmall
    // SetEventsDisplayType         DrawWeek
    // SetSundayColor               DrawDay
    // SetTimeFormat                DrawTypesChanger
    // SetEventsOperations          DrawDateJumper
    // SetCategoriesOperations      DrawTodayJumper
    // SetCssStyle                  DrawEventsAddForm
    // SetAddEventFormType          DrawEventsEditForm
    // SetCachingParameters         DrawEventsDetailsForm
    // SetInterfaceLang             DrawEventsStatistics
    // SetUserID                    DrawAddEventForm
    // SetCalendarViews    			DrawMonthlyDayCell
    // SetAllowedHours              ParseHour
    // Debug                        ParseMinutes
    // EditingEventsInPast          HandleEventsOperations
    // BlockEventsDeletingBefore    DrawCategoriesAddForm
    // SetDisabledDay               DrawCategoriesDetailsForm
    // SetPostBackMethod            DrawCategoriesEditForm
    // SetEventsMultipleOccurrences DrawColors
    // SaveHttpRequestVars          DrawCategoriesDDL
    // SetTimeSlot                  DrawEventsDDL
    // ShowTimes                    GetMessage
    // SetMonthlySmallLinks         DrawDateTime
    //                              DrawDate
    //                              DrawTime
    //                              CheckBadChars
    //                              RemoveBadChars
    //                              DrawDeleteEventsByRange
    //                              DrawEventsManagement
    //                              DrawAvailabilityLegend
    //
    // STATIC                       Auxilary (PRIVATE)
    // ----------                   --------
    // Version						SetLanguage
    // GetDefaultTimeZone			StartCaching
    // GetColorsByName				FinishCaching
    // GetColorNameByValue			GetEventsListForHour
    // 								GetEventsListForWeekDay
    // 								GetEventCountForDay
    // 								GetEventsCountForMonth
    // 								GetEventsListForMonth
    // 								GetMonthlyDayEvents
    // 								IsYear
    // 								IsMonth
    // 								IsDay
    // 								ConvertToDecimal
    // 								GetFormattedMicrotime
    //                              PrepareOption
    //                              PrepareText
    //                              PrepareFormatedText
    //                              PrepareMinutesHoures
    //                              GetDayForMonth
    //                              IsActionType
    //                              GetParameter
    //                              RemoveDuplications
    //                              PrepareWhereClauseFromToTime
    //                              PrepareWhereClauseEventTime
    //                              PrepareWhereClauseCategory


    //--- PUBLIC DATA MEMBERS --------------------------------------------------
    public $error;

    //--- PROTECTED DATA MEMBERS -----------------------------------------------
    protected $weekDayNameLength;

    //--- PRIVATE DATA MEMBERS -------------------------------------------------
    private $arrWeekDays;
    private $arrMonths;
    private $arrViewTypes;
    private $defaultView;
    private $eventsDisplayType;
    private $defaultAction;
    private $showControlPanel;

    private $arrParameters;
    private $arrToday;
    private $prevYear;
    private $nextYear;
    private $prevMonth;
    private $nextMonth;
    private $currWeek;
    private $prevWeek;
    private $nextWeek;
    private $prevDay;
    private $nextDay;

    private $isDrawNavigation;
    private $isWeekNumberOfYear;

    private $crLt;
    private $caption;
    private $calWidth;
    private $calHeight;
    private $celHeight;
    private $weekColWidth;

    private $timezone;
    private $timeFormat;
    private $timeFormatSQL;
    private $isShowTimes;

    private $langName;
    private $lang;

    private $arrEventsOperations;
    private $arrCatOperations;
    private $arrCalendarOperations;
    private $postBackMethod;
    private $actionLink;

    private $isDemo;
    private $isShowcase;
    private $isDebug;
    private $allowEditingEventsInPast;
    private $allowEventsMultipleOccurrences;
    private $canNotDeleteBefore;
    private $arrMessages;
    private $arrErrors;
    private $startTime;
    private $endTime;

    private $addEventFormType;

    private $isCachingAllowed;
    private $cacheLifetime;
    private $cacheDir;
    private $maxCacheFiles;

    private $userID;
    private $calDir;
    private $calUrl;

    private $fromHour;
    private $toHour;

    private $httpRequestVars;

    private $arrInitJsFunction;

    private $separator;
    private $arrDailyMaxRooms;
    private $arrRooms;
    private $dailyMaxRooms;
    private $colorGreen;
    private $colorRed;
    private $colorYellow;
    private $colorGray;
    private $colorDarkGray;

    static private $version = "3.0.5";

    // -- non-documented ----------
    private $disableEarlierHours = "false";
    private $hideWeekEmptySlots = false;

    private $showAs;
    private $roomTypes;
    private $monthNumber;
    private $hotelId;
    private $arrRoomTypes;
    private $arrRoomTypes_DDL;
    private $arrRoomAvailability = array();
    public $shownRoomsType = 'booked'; /* booked | left */

    private $defined_left = "left";
    private $defined_right = "right";
    private $usedOn = "global"; /* global or room */

    private $select_calss = 'mgrid_select';


    //--------------------------------------------------------------------------
    // CLASS CONSTRUCTOR
    // @param $params
    //--------------------------------------------------------------------------
    function __construct($params = array())
    {
        if(defined("CALENDAR_DIR")) $this->calDir = CALENDAR_DIR;
        else $this->calDir = "";
        if(defined("CALENDAR_URL")) $this->calUrl = CALENDAR_URL;
        else $this->calUrl = $this->calDir;

        $this->defaultView   = "monthly";
        $this->defaultAction = "view";
        $this->showControlPanel = true;
        $this->eventsDisplayType = array("weekly"=>"inline", "monthly"=>"inline", "yearly"=>"tooltip");

        // possible values 1,2,....7
        $this->weekStartedDay = 1;
        $this->weekDisabledDay = "";

        $this->weekDayNameLength = "short"; // short|long

        $this->langName = "en";

        $this->timezone = self::GetDefaultTimeZone();
        @date_default_timezone_set($this->timezone);
        $this->timeFormat = "24";
        $this->timeFormatSQL = "%H:%i";
        $this->timeSlot = "60";
        $this->isShowTimes = true;

        $this->arrEventsOperations = array();
        $this->arrEventsOperations['add']     = true;
        $this->arrEventsOperations['edit']    = true;
        $this->arrEventsOperations['details'] = true;
        $this->arrEventsOperations['delete']  = true;
        $this->arrEventsOperations['delete_by_range']  = true;
        $this->arrEventsOperations['manage'] = true;

        $this->arrCalendarOperations = array();
        $this->arrCalendarOperations['statistics'] = true;
        $this->arrCalendarOperations['printing'] = true;

        $this->arrCatOperations = array();

        $this->arrWeekDays  = array();
        $this->arrMonths    = array();

        $this->arrViewTypes = array();
        $this->arrViewTypes["daily"]     = array("name"=>"Daily", "enabled"=>false);
        $this->arrViewTypes["weekly"]    = array("name"=>"Weekly", "enabled"=>false);
        $this->arrViewTypes["monthly"]   = array("name"=>"Monthly", "enabled"=>false);
        $this->arrViewTypes["monthly_triple"] = array("name"=>"Monthly Triple", "enabled"=>false);
        $this->arrViewTypes["monthly_small"] = array("name"=>"Monthly Small", "enabled"=>false);
        $this->arrViewTypes["yearly"]  	 = array("name"=>"Yearly", "enabled"=>false);
        $this->arrViewTypes["list_view"] = array("name"=>"List View", "enabled"=>false);

        $this->arrParameters = array();
        $this->SetDefaultParameters();

        $this->arrToday  = array();
        $this->prevYear  = array();
        $this->nextYear  = array();
        $this->prevMonth = array();
        $this->nextMonth = array();
        $this->currWeek  = array();
        $this->prevWeek  = array();
        $this->nextWeek  = array();
        $this->prevDay   = array();
        $this->nextDay   = array();

        $this->isDrawNavigation = true;
        $this->isWeekNumberOfYear = true;

        $this->crLt = "\n";
        $this->caption = "";
        $this->calWidth = "800px";
        $this->calHeight = "470px";
        $this->celHeight = number_format(((int)$this->calHeight)/6, "0")."px";
        $this->weekColWidth = number_format(((int)$this->calWidth)/8, "0")."px";

        $this->postBackMethod = "post";
        $this->actionLink = "";

        $this->cssStyle = "blue";

        $this->addEventFormType = "floating";

        $this->userID = "0";

        $this->isCachingAllowed = false;
        $this->cacheLifetime = 5; // in minutes
        $this->cacheDir = $this->calDir."cache/";
        $this->maxCacheFiles = 100;

        $this->isDemo = false;
        $this->isShowcase = false;
        $this->isDebug = false;
        $this->allowEditingEventsInPast = true;
        $this->canNotDeleteBefore = "";
        $this->allowEventsMultipleOccurrences = true;
        $this->arrMessages = array();
        $this->arrErrors = array();
        $this->arrSQLs = array();

        $this->fromHour = 0;
        $this->toHour = 24;

        $this->separator = "$$";

        $this->httpRequestVars = array();

        $this->arrInitJsFunction = array();

        $this->usedOn = (isset($params['used_on']) && $params['used_on'] == 'room') ? 'room' : 'global';
        $this->roomTypes = (isset($params['room_types']) && $params['room_types'] != '') ? (int)$params['room_types'] : $this->GetParameter("sel_room_types", "");
        $this->showAs = (isset($params['show_as']) && in_array($params['show_as'], array('reserved_and_completed', 'completed_only'))) ? $params['show_as'] : $this->GetParameter("show_as", "reserved_and_completed");
        $this->monthNumber = isset($params['month_number']) ? (int)$params['month_number'] : 3;

        if($this->usedOn == 'room'){
            $this->colorGreen = "#ccffcc";
            $this->colorYellow = "#ffcc00";
            $this->colorRed = "#db4c0d";
        }else{
            $this->colorGreen = "#00a300";
            $this->colorYellow = "#ffcc00";
            $this->colorRed = "#a30000";
        }
        $this->colorGray = "#cccccc";
        $this->colorDarkGray = "#777777";
        $this->hotelId = '';
        $total_hotels = Hotels::HotelsCount();

        if(preg_match('/h:/i', $this->roomTypes)){
            $this->hotelId = str_replace('h:', '', $this->roomTypes);
            $this->roomTypes = '';
        }elseif(empty($this->roomTypes) && $total_hotels > 1){
            // Don't show rooms for multi-hotels
            $this->hotelId = -1;
        }

        $this->arrRoomTypes = Rooms::GetRoomTypes($this->hotelId);
        $this->arrRoomTypes_DDL = Rooms::GetRoomTypes();

        // Validate Room Type / Hotel ID
        $valid_data = false;
        foreach($this->arrRoomTypes as $k => $v){
            if($v['id'] == $this->roomTypes || $v['hotel_id'] == $this->hotelId){
                $valid_data = true;
                break;
            }
        }
        if(!$valid_data){
            $this->roomTypes = -1;
            $this->hotelId = -1;
        }

        $current_hotel = '';
        $this->hotelsCount = 0;
        // get hotels count
        foreach($this->arrRoomTypes_DDL as $room){
            if($current_hotel != $room['hotel_id']) $this->hotelsCount++;
            $current_hotel = $room['hotel_id'];
        }

        // Assign hotel ID
        if($this->hotelsCount == 1){
            $this->hotelId = $current_hotel;
            if($this->roomTypes == '-1'){
                $this->roomTypes = '';
            }
        }

        $this->arrDailyMaxRooms = array();
        $this->arrRooms = array();
        $this->dailyMaxRooms = 0;

        $sql = "SELECT id, room_type, room_count as cnt
                FROM ".TABLE_ROOMS."
                WHERE
                    ".(($this->hotelId != "") ? "hotel_id = ".(int)$this->hotelId." AND " : "")."
                    ".(($this->roomTypes != "") ? "id = ".(int)$this->roomTypes." AND " : "")."
                    is_active = 1
                ORDER BY id ";

        $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
        for($i=0; $i < $result[1]; $i++){
            $this->arrDailyMaxRooms[$result[0][$i]['id']] = $result[0][$i]['cnt'];
            $this->arrRooms[$result[0][$i]['id']] = $result[0][$i]['room_type'];
            $this->dailyMaxRooms += $result[0][$i]['cnt'];
        }

        $this->defined_left = Application::Get("defined_left");
        $this->defined_right = Application::Get("defined_right");
    }

    //--------------------------------------------------------------------------
    // CLASS DESTRUCTOR
    //--------------------------------------------------------------------------
    function __destruct()
    {
        // echo 'this object has been destroyed';
    }


    //==========================================================================
    // PUBLIC DATA FUNCTIONS
    //==========================================================================
    /**
     * Show Calendar
     *
     */
    function Show()
    {
        // start calculating running time of the script
        $this->startTime = 0;
        $this->endTime = 0;
        if($this->isDebug){
            error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
            $this->startTime = $this->GetFormattedMicrotime();
        }

        $this->SetLanguage();

        //ob_start();
        $event_action = $this->GetParameter("hid_event_action");
        $event_id     = $this->GetParameter("hid_event_id");
        $category_id  = $this->GetParameter("hid_category_id");
        $jump_year 	  = $this->GetParameter("jump_year");
        $jump_month	  = $this->GetParameter("jump_month");
        $jump_day 	  = $this->GetParameter("jump_day");
        $view_type 	  = $this->GetParameter("view_type");

        if($this->isDemo && !$this->isShowcase){
            $lang_id 	= $this->GetParameter("lang_id", "en");
            $css_style 	= $this->GetParameter("css_style", "blue");
        }

        $this->HandleEventsOperations();
        $this->GetCurrentParameters();
        $this->DrawCssStyle();
        $this->DrawJsFunctions();
        $this->IncludeModules();

        // prepare stored http request variables
        $http_query_string = "";
        foreach($this->httpRequestVars as $key){
            if(isset($_REQUEST[$key])){
                $http_query_string .= ((!$http_query_string) ? "?" : "&").$key."=".$this->RemoveBadChars($_REQUEST[$key]);
            }
        }
        echo "<form name='frmCalendar' id='frmCalendar' action='".(($this->actionLink != "") ? $this->actionLink : $this->arrParameters["current_file"]).$http_query_string."' method='".$this->postBackMethod."'>".$this->crLt;
        echo "<input type='hidden' id='hid_event_action' name='hid_event_action' value='' />".$this->crLt;
        echo "<input type='hidden' id='hid_event_id' name='hid_event_id' value='' />".$this->crLt;
        echo "<input type='hidden' id='hid_action' name='hid_action' value='' />".$this->crLt;
        echo "<input type='hidden' id='hid_view_type' name='hid_view_type' value='' />".$this->crLt;
        echo "<input type='hidden' id='hid_year' name='hid_year' value='' />".$this->crLt;
        echo "<input type='hidden' id='hid_month' name='hid_month' value='' />".$this->crLt;
        echo "<input type='hidden' id='hid_day' name='hid_day' value='' />".$this->crLt;
        echo "<input type='hidden' id='hid_previous_action' name='hid_previous_action' value='".$event_action."' />".$this->crLt;
        echo "<input type='hidden' id='hid_page' name='hid_page' value='' />".$this->crLt;
        echo "<input type='hidden' id='hid_chart_type' name='hid_chart_type' value='' />".$this->crLt;
        echo "<input type='hidden' id='hid_category_id' name='hid_category_id' value='' />".$this->crLt;
        echo "<input type='hidden' id='hid_sort_by' name='hid_sort_by' value='' />".$this->crLt;
        echo "<input type='hidden' id='hid_sort_direction' name='hid_sort_direction' value='' />".$this->crLt;
        echo "<input type='hidden' id='hid_selected_category' name='hid_selected_category' value='".$this->arrParameters["selected_category"]."' />".$this->crLt;

        if($event_action == "events_add" || $event_action == "events_edit" || $event_action == "events_details" || $event_action == "events_delete" || $event_action == "events_update" || $event_action == "events_management" || $event_action == "events_delete_by_range" ||
            $event_action == "categories_add" || $event_action == "categories_update" || $event_action == "categories_details" || $event_action == "categories_delete" || $event_action == "categories_edit" || $event_action == "categories_management")
        {
            echo "<input type='hidden' id='view_type' name='view_type' value='".$view_type."' />".$this->crLt;
            echo "<input type='hidden' id='jump_year' name='jump_year' value='".$jump_year."' />".$this->crLt;
            echo "<input type='hidden' id='jump_month' name='jump_month' value='".$jump_month."' />".$this->crLt;
            echo "<input type='hidden' id='jump_day' name='jump_day' value='".$jump_day."' />".$this->crLt;
        }

        echo "<div id='mod_calendar' style='width:".$this->calWidth.";'>".$this->crLt;
        // draw calendar header
        if($this->usedOn == 'global' && $this->defaultView != "monthly_small"){
            echo "<table border='0' align='center' cellpadding='0' id='calendar_header'>".$this->crLt;
            echo "<tr>";
            echo "<th width='33%' class='caption_".$this->defined_left."'>".$this->DrawTodayJumper(false)."</th>";
            //echo "<th width='34%' class='caption' nowrap='nowrap'>".$this->caption."</th>";
            echo "<th width='67%' class='caption_".$this->defined_right."'>";
            echo "<select class='".$this->select_calss." chosen_select' name='sel_room_types' onchange=\"javascript:__doPostBack('view', '".$this->arrParameters["view_type"]."');\">";
            if($this->hotelsCount > 1){
                echo "<option value=''>-- ".$this->lang['select_hotel']." --</option>";
            }else{
                echo '<option value="h:'.$this->hotelId.'">'.$this->lang['all_rooms'].'</option>';
            }

            $current_hotel = '';
            foreach($this->arrRoomTypes_DDL as $room){
                if($this->hotelsCount > 1 && $current_hotel != $room['hotel_id']){
                    echo '<optgroup label="'.$room['hotel_name'].'">';
                    echo '<option '.(($this->hotelId == $room['hotel_id']) ? 'selected="selected"' : '').' value="h:'.$room['hotel_id'].'">'.(($this->hotelId == $room['hotel_id']) ? $room['hotel_name'] : '').' [ '.$this->lang['all_rooms'].' ] </option>';
                }
                echo "<option ".(($this->roomTypes == $room['id']) ? "selected='selected'" : "")." value='".$room['id']."'>".$room['room_type']."</option>";
                if($this->hotelsCount > 1) $current_hotel = $room['hotel_id'];
            }
            if($this->hotelsCount > 1) echo '</optgroup>';
            echo "</select>";

            echo "<select class='".$this->select_calss."' name='show_as' onchange=\"javascript:__doPostBack('view', '".$this->arrParameters["view_type"]."');\">
						<option ".(($this->showAs == "reserved_and_completed") ? "selected='selected'" : "")." value='reserved_and_completed'>".$this->lang['reserved_and_completed']."</option>
						<option ".(($this->showAs == "completed_only") ? "selected='selected'" : "")." value='completed_only'>".$this->lang['completed_only']."</option>
					 </select>";

            if($this->arrParameters["view_type"] == "daily" || $this->arrParameters["view_type"] == "weekly"){
                $selected_value = "";
            }else{
                $selected_value = $this->arrParameters["selected_category"];
                //echo $this->DrawCategoriesDDL($selected_value, "onchange='javascript:__CategoryChange(this.value, \"".$this->arrParameters["view_type"]."\");'", "width:145px;margin-right:5px;", "sel_category_name_change");
            }
            echo $this->DrawTypesChanger(false)."</th>";
            echo "</tr>".$this->crLt;
            echo "<tr><td colspan='3' nowrap height='5px'></td></tr>".$this->crLt;

            if($this->showControlPanel){
                echo "<tr>";
                echo "<th class='caption_".$this->defined_left."' colspan='3' valign='bottom'>
						<table border='0' width='100%' cellpadding='0' cellspacing='1'><tr>";
                $this->DrawAvailabilityLegend();
                if($this->arrCalendarOperations['printing']){
                    echo "<td width='20%' align='".$this->defined_right."'><a class='print' href='javascript:window.print()' title='".$this->lang['click_to_print']."'> <img src='".$this->calUrl."style/".$this->cssStyle."/images/print.png' border='0' /> ".$this->lang['print']."</a></td>";
                }
                $tabs_count = 0;
                if($this->arrEventsOperations['manage']){
                    echo "<td><img src='".$this->calUrl."style/".$this->cssStyle."/images/manage_events.png' border='0' /></td>";
                    echo "<td>&nbsp;<a href=\"javascript:__doPostBack('view', '".$this->arrParameters["view_type"]."', '".$this->arrParameters['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."', 'events_management')\" ".(($this->IsActionType($event_action) == "event") ? "class='tab_active'" : "").">".$this->lang['events']."</a></td>";
                    $tabs_count++;
                }
                if($this->arrCatOperations['manage']){
                    if($tabs_count++ > 0) echo "<td>&nbsp;|&nbsp;</td>";
                    echo "<td><img src='".$this->calUrl."style/".$this->cssStyle."/images/manage_categories.png' border='0' /></td>";
                    echo "<td>&nbsp;<a href=\"javascript:__doPostBack('view', '".$this->arrParameters["view_type"]."', '".$this->arrParameters['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."', 'categories_management')\" ".(($this->IsActionType($event_action) == "category") ? "class='tab_active'" : "").">".$this->lang['categories']."</a></td>";
                }
                if($this->arrCalendarOperations['statistics']){
                    if($tabs_count++ > 0) echo "<td>&nbsp;|&nbsp;</td>";
                    echo "<td><img src='".$this->calUrl."style/".$this->cssStyle."/images/events_statistics.png' border='0' /></td>";
                    echo "<td>&nbsp;<a href=\"javascript:__doPostBack('view', '".$this->arrParameters["view_type"]."', '".$this->arrParameters['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."', 'events_statistics')\" ".(($this->IsActionType($event_action) == "statistics") ? "class='tab_active'" : "").">".$this->lang['statistics']."</a></td>";
                }
                echo "  </tr></table></th>";
                echo "<th class='caption_".$this->defined_right."' valign='bottom'>";
                echo "</th>";
                echo "</tr>".$this->crLt;
            }
            echo $this->DrawMessages(false);
            echo "</table>";
        }

        switch($event_action)
        {
            // Events actions
            case "events_add":
                if($this->arrEventsOperations['add']) $this->DrawEventsAddForm();
                else echo "<span class='msg_error'>".$this->lang['msg_this_operation_blocked']."</span>";
                break;
            case "events_edit":
                if($this->arrEventsOperations['edit']) $this->DrawEventsEditForm($event_id);
                else echo "<span class='msg_error'>".$this->lang['msg_this_operation_blocked']."</span>";
                break;
            case "events_details":
                if($this->arrEventsOperations['details']) $this->DrawEventsDetailsForm($event_id);
                else echo "<span class='msg_error'>".$this->lang['msg_this_operation_blocked']."</span>";
                break;
            case "events_management":
            case "events_delete":
            case "events_update":
            case "events_insert":
            case "events_delete_by_range":
                if($this->arrEventsOperations['manage']) $this->DrawEventsManagement();
                break;
            case "events_by_range":
                if($this->arrEventsOperations['manage']) $this->DrawDeleteEventsByRange();
                break;

            // Categories actions
            case "categories_add":
                if($this->arrCatOperations['add']) $this->DrawCategoriesAddForm();
                break;
            case "categories_edit":
                if($this->arrCatOperations['manage']) $this->DrawCategoriesEditForm($category_id);
                break;
            case "categories_details":
                if($this->arrCatOperations['manage']) $this->DrawCategoriesDetailsForm($category_id);
                break;
            case "categories_management":
            case "categories_delete":
            case "categories_update":
            case "categories_insert":
                if($this->arrCatOperations['manage']) $this->DrawEventsCategoriesManagement();
                break;

            // Events statistics
            case "events_statistics":
                if($this->arrEventsOperations['details']) $this->DrawEventsStatistics();
                else echo "<span class='msg_error'>".$this->lang['msg_this_operation_blocked']."</span>";
                break;

            default:
                if($this->defaultView == 'monthly_triple') $this->arrParameters["view_type"] = 'monthly_triple';

                switch($this->arrParameters["view_type"])
                {
                    case "daily":
                        $this->DrawDay();
                        break;
                    case "weekly":
                        $this->DrawWeek();
                        break;
                    case "yearly":
                        $this->DrawYear();
                        break;
                    case "list_view":
                        $this->DrawListView();
                        break;
                    case "monthly_small":
                        $this->DrawMonthSmall();
                        break;
                    case "monthly_triple":
                        $this->DrawMonthlyTriple();
                        break;
                    default:
                    case "monthly":
                        $this->DrawMonth();
                        break;
                }
                break;
        }

        echo "</div>".$this->crLt;
        echo "</form>".$this->crLt;
        $this->InitializeJsFunctions();

        if($this->isDebug){
            $this->endTime = $this->GetFormattedMicrotime();

            echo "<div class='debug_info' style='width:".$this->calWidth.";'>";
            echo "<b>".$this->lang['debug_info']."</b>: (".$this->lang['total_running_time'].": ".round((float)$this->endTime - (float)$this->startTime, 6)." sec.) <br />=========<br /><br />";
            echo "<b>ERRORS: (".count($this->arrErrors).")</b> <br />--------<br />";
            echo "<p class='debug_error'>".$this->DrawErrors(false)."</p>";
            echo "<b>SQL:</b> <br />--------<br />";
            echo "<p class='debug_sql'>".$this->DrawSQLs(false)."</p>";
            echo "<b>GET:</b> <br />--------<br />";
            echo "<pre class='debug_get'>";
            print_r($_GET);
            echo "</pre><br />";
            echo "<b>POST:</b> <br />--------<br />";
            echo "<pre class='debug_post'>";
            print_r($_POST);
            echo "</pre><br />";
            echo "</div>";
        }


        //ob_end_flush();
    }

    /**
     * Set calendar dimensions
     * @param $width
     * @param $height
     */
    function SetCalendarDimensions($width = "", $height = "")
    {
        $this->calWidth = ($width != "") ? $width : "800px";
        $this->calHeight = ($height != "") ? $height : "470px";
        $this->celHeight = number_format(((int)$this->calHeight)/6, "0")."px";
    }

    /**
     * Check if parameters is 4-digit year
     * @param $year - string to be checked if it's 4-digit year
     */
    function SetCaption($caption_text = "")
    {
        $this->caption = $caption_text;
    }

    /**
     * Set week started day
     * @param $started_day - started day of week 1...7
     */
    function SetWeekStartedDay($started_day = "1")
    {
        if(is_numeric($started_day) && (int)$started_day >= 1 && (int)$started_day <= 7){
            $this->weekStartedDay = (int)$started_day;
        }
    }

    /**
     * Set week disabled day
     * @param $disabled_day - disabledday of week 1...7
     */
    function SetDisabledDay($disabled_day = "1")
    {
        if(is_numeric($disabled_day) && (int)$disabled_day >= 1 && (int)$disabled_day <= 7){
            $this->weekDisabledDay = (int)$disabled_day;
        }
    }

    /**
     * Set week day name length
     * @param $length_name - "short"|"long"
     */
    function SetWeekDayNameLength($length_name = "short")
    {
        if(strtolower($length_name) == "long"){
            $this->weekDayNameLength = "long";
        }
    }

    /**
     * Show week number of year
     * @param $show - true|false
     */
    function ShowWeekNumberOfYear($show = true)
    {
        if($show === true || strtolower($show) == "true"){
            $this->isWeekNumberOfYear = true;
        }else{
            $this->isWeekNumberOfYear = false;
        }
    }

    /**
     * Show times in Daily and Weekly views
     * @param $show - true|false
     */
    function ShowTimes($show = true)
    {
        if($show === true || strtolower($show) == "true"){
            $this->isShowTimes = true;
        }else{
            $this->isShowTimes = false;
        }
    }

    /**
     * Set timezone
     * @param $timezone
     */
    function SetTimeZone($timezone = "")
    {
        if($timezone != ""){
            if($this->isDebug){
                if(function_exists("timezone_identifiers_list") && !in_array($timezone, timezone_identifiers_list())){
                    $this->arrErrors[] = str_replace("_TIME_ZONE_", $timezone, $this->lang['msg_timezone_invalid'])."<br />";
                }
            }
            $this->timezone = $timezone;
            @date_default_timezone_set($this->timezone);
        }
    }

    /**
     * Get current timezone
     */
    function GetCurrentTimeZone()
    {
        return $this->timezone;
    }

    /**
     * Set displaying type of events
     * @param $events_display_type - array
     */
    function SetEventsDisplayType($events_display_type = array())
    {
        $this->eventsDisplayType['weekly'] = isset($events_display_type['weekly']) ? $events_display_type['weekly'] : "inline";
        $this->eventsDisplayType['monthly'] = isset($events_display_type['monthly']) ? $events_display_type['monthly'] : "inline";
    }

    /**
     * Set calendar Views
     * @param $views - array
     */
    function SetCalendarViews($views = array())
    {
        $this->arrViewTypes["daily"]["enabled"]     = (isset($views["daily"]) && ($views["daily"] === true || strtolower($views["daily"]) == "true")) ? true : false;
        $this->arrViewTypes["weekly"]["enabled"]    = (isset($views["weekly"]) && ($views["weekly"] === true || strtolower($views["weekly"]) == "true")) ? true : false;
        $this->arrViewTypes["monthly"]["enabled"]   = (isset($views["monthly"]) && ($views["monthly"] === true || strtolower($views["monthly"]) == "true")) ? true : false;
        $this->arrViewTypes["monthly_triple"]["enabled"] = (isset($views["monthly_triple"]) && ($views["monthly_triple"] === true || strtolower($views["monthly_triple"]) == "true")) ? true : false;
        $this->arrViewTypes["monthly_small"]["enabled"] = (isset($views["monthly_small"]) && ($views["monthly_small"] === true || strtolower($views["monthly_small"]) == "true")) ? true : false;
        $this->arrViewTypes["yearly"]["enabled"]    = (isset($views["yearly"]) && ($views["yearly"] === true || strtolower($views["yearly"]) == "true")) ? true : false;
        $this->arrViewTypes["list_view"]["enabled"] = (isset($views["list_view"]) && ($views["list_view"] === true || strtolower($views["list_view"]) == "true")) ? true : false;

        if(!$this->arrViewTypes[$this->defaultView]['enabled']){
            foreach($this->arrViewTypes as $key => $val){
                if($val['enabled']){
                    $this->defaultView = $key;
                    $this->arrErrors[] = str_replace("_DEFAULT_VIEW_", $this->defaultView, $this->lang['msg_view_type_invalid'])."<br />";
                    break;
                }
            }
        }
    }

    /**
     * Set allowed hours
     * @param $hour_from - started hour
     * @param $hour_to - ended hour
     */
    function SetAllowedHours($from_hour, $to_hour)
    {
        $this->fromHour = (!is_numeric($from_hour) || $from_hour < 0) ? 0 : (int)$from_hour;
        $this->toHour = (!is_numeric($to_hour) || $to_hour > 24) ? 24 : (int)$to_hour;
        if(($this->fromHour > $this->toHour) && $this->isDebug){
            $this->arrErrors[] = $this->lang['error_from_to_hour']."<br />";
        }
    }

    /**
     * Set default calendar view
     * @param $default_view
     */
    function SetDefaultView($default_view = "monthly")
    {
        if(isset($this->arrViewTypes[$default_view]["enabled"]) && $this->arrViewTypes[$default_view]["enabled"] == true){
            $this->defaultView = $default_view;
        }
    }

    /**
     * Set action link for monthly small view type
     * @param $link
     */
    function SetMonthlySmallLinks($link){
        $this->actionLink = $link;
    }

    /**
     * Set Sunday color
     * @param $default_view
     */
    function SetSundayColor($color = false)
    {
        $this->sundayColor = ($color == true || $color === "true") ? true : false;
    }

    /**
     * Set time format
     * @param $time_format
     */
    function SetTimeFormat($time_format = "24")
    {
        $this->timeFormat = (strtoupper($time_format) == "AM/PM") ? "AP/PM" : "24";
        if($this->timeFormat == "24"){
            $this->timeFormatSQL = "%H:%i";
        }else{
            $this->timeFormatSQL = "%h:%i %p";
        }
    }

    /**
     * Set time slot
     * @param $time_format
     */
    function SetTimeSlot($time_slot = "60")
    {
        $time_slot = (int)$time_slot;
        if($time_slot == "15") $this->timeSlot = "15";
        else if($time_slot == "30") $this->timeSlot = "30";
        else if($time_slot == "45") $this->timeSlot = "45";
        else $this->timeSlot = "60";
    }

    /**
     * Set (allow) calendar categories operations
     * @param $operations
     */
    function SetCategoriesOperations($operations = array())
    {
        $this->arrCatOperations['add']     = (isset($operations['add']) && ($operations['add'] === true || strtolower($operations['add']) == "true")) ? true : false;
        $this->arrCatOperations['edit']    = (isset($operations['edit']) && ($operations['edit'] === true || strtolower($operations['edit']) == "true")) ? true : false;
        $this->arrCatOperations['details'] = (isset($operations['details']) && ($operations['details'] === true || strtolower($operations['details']) == "true")) ? true : false;
        $this->arrCatOperations['delete']  = (isset($operations['delete']) && ($operations['delete'] === true || strtolower($operations['delete']) == "true")) ? true : false;
        $this->arrCatOperations['manage']  = (isset($operations['manage']) && ($operations['manage'] === true || strtolower($operations['manage']) == "true")) ? true : false;
        $this->arrCatOperations['allow_colors'] = (isset($operations['allow_colors']) && ($operations['allow_colors'] === true || strtolower($operations['allow_colors']) == "true")) ? true : false;
    }

    /**
     * Set (allow) calendar events operations
     * @param $operations
     */
    function SetEventsOperations($operations = array())
    {
        $this->arrEventsOperations['add']      = (isset($operations['add']) && ($operations['add'] === true || strtolower($operations['add']) == "true")) ? true : false;
        $this->arrEventsOperations['edit']     = (isset($operations['edit']) && ($operations['edit'] === true || strtolower($operations['edit']) == "true")) ? true : false;
        $this->arrEventsOperations['details']  = (isset($operations['details']) && ($operations['details'] === true || strtolower($operations['details']) == "true")) ? true : false;
        $this->arrEventsOperations['delete']   = (isset($operations['delete']) && ($operations['delete'] === true || strtolower($operations['delete']) == "true")) ? true : false;
        $this->arrEventsOperations['delete_by_range'] = (isset($operations['delete_by_range']) && ($operations['delete_by_range'] === true || strtolower($operations['delete_by_range']) == "true")) ? true : false;
        $this->arrEventsOperations['manage']   = (isset($operations['manage']) && ($operations['manage'] === true || strtolower($operations['manage']) == "true")) ? true : false;
    }

    /**
     * Set (allow) calendar operations
     * @param $operations
     */
    function SetCalendarActions($operations = array())
    {
        $this->arrCalendarOperations['statistics'] = (isset($operations['statistics']) && ($operations['statistics'] === true || strtolower($operations['statistics']) == "true")) ? true : false;
        $this->arrCalendarOperations['printing'] = (isset($operations['printing']) && ($operations['printing'] === true || strtolower($operations['printing']) == "true")) ? true : false;
    }

    /**
     * Set form PostBack method
     * @param $postback_method
     */
    function SetPostBackMethod($postback_method = "post")
    {
        if(strtolower($postback_method) == "get") $this->postBackMethod = "get";
        else $this->postBackMethod = "post";
    }

    /**
     * Set CSS style
     * @param $style
     */
    function SetCssStyle($style = "blue")
    {
        if(strtolower($style) == "green") $this->cssStyle = "green";
        else if(strtolower($style) == "brown") $this->cssStyle = "brown";
        else $this->cssStyle = "blue";
    }

    /**
     * Set Add Event form type
     * @param $type
     */
    function SetAddEventFormType($type = "blue")
    {
        if(strtolower($type) == "floating") $this->addEventFormType = "floating";
        else $this->addEventFormType = "popup";
    }

    /**
     * Set Caching Parameters
     * @param $allowed
     * @param $lifetime
     */
    function SetCachingParameters($allowed, $lifetime = "5")
    {
        if(strtolower($allowed) === "true" || $allowed == true) $this->isCachingAllowed = true;
        else $this->isCachingAllowed = false;
        // timeout in minutes
        if(is_numeric($lifetime) && $lifetime < 24*60){
            $this->cacheLifetime = $lifetime;
        }else{
            $this->cacheLifetime = 5;
        }
    }

    /**
     * Set interface language
     * @param $lang
     */
    function SetInterfaceLang($lang = "en")
    {
        if($lang != "") $this->langName = $lang;
        $this->SetLanguage();
    }

    /**
     * Set user ID
     * @param $user_id
     */
    function SetUserID($user_id = "0")
    {
        if($user_id != "" and is_numeric($user_id)) $this->userID = (int)$user_id;
    }


    /**
     * Set debug mode
     * @param $mode
     */
    function Debug($mode = false)
    {
        if($mode == true || strtolower($mode) === "true") $this->isDebug = true;
    }

    /**
     * Set allow editing events in past
     * @param $mode
     */
    function EditingEventsInPast($mode = false)
    {
        if($mode == false || strtolower($mode) === "false") $this->allowEditingEventsInPast = false;
    }

    /**
     * Block deleting events before defined period of time
     * @param $hours
     */
    function BlockEventsDeletingBefore($hours = "")
    {
        if($hours != "" || is_numeric($hours)) $this->canNotDeleteBefore = intval($hours);
    }

    /**
     * Set allow multiple occurrences for events
     * @param $mode
     */
    function SetEventsMultipleOccurrences($mode = true)
    {
        if($mode == false || strtolower($mode) === "false") $this->allowEventsMultipleOccurrences = false;
    }


    //==========================================================================
    // STATIC
    //==========================================================================
    /**
     * Return current version
     */
    static function Version()
    {
        return self::$version;
    }

    /**
     * Return default time zone
     */
    static function GetDefaultTimeZone()
    {
        return @date_default_timezone_get();
    }


    //==========================================================================
    // PRIVATE DATA FUNCTIONS
    //==========================================================================
    /**
     * Set default parameters
     *
     */
    function SetDefaultParameters()
    {
        $this->arrParameters["year"]  = date("Y");
        $this->arrParameters["month"] = date("m");
        $this->arrParameters["month_full_name"] = date("F");
        $this->arrParameters["day"]   = date("d");
        $this->arrParameters["view_type"] = $this->defaultView;
        $this->arrParameters["action"] = "display";
        $this->arrParameters["page"] = "1";
        $this->arrParameters["chart_type"] = "columnchart";
        $this->arrParameters["event_action"] = "";
        $this->arrToday = getdate();

        // get current file
        $this->arrParameters["current_file"] = $_SERVER["SCRIPT_NAME"];
        $parts = explode('/', $this->arrParameters["current_file"]);
        $this->arrParameters["current_file"] = $parts[count($parts) - 1];
    }

    /**
     * Get current parameters - read them from URL
     *
     */
    function GetCurrentParameters()
    {
        $year 		= (isset($_REQUEST['hid_year']) && $this->IsYear($_REQUEST['hid_year'])) ? $this->RemoveBadChars($_REQUEST['hid_year']) : date("Y");
        $month 		= (isset($_REQUEST['hid_month']) && $this->IsMonth($_REQUEST['hid_month'])) ? $this->RemoveBadChars($_REQUEST['hid_month']) : date("m");
        $day 		= (isset($_REQUEST['hid_day']) && $this->IsDay($_REQUEST['hid_day'])) ? $this->RemoveBadChars($_REQUEST['hid_day']) : date("d");
        $view_type 	= (isset($_REQUEST['hid_view_type']) && array_key_exists($_REQUEST['hid_view_type'], $this->arrViewTypes)) ? $this->RemoveBadChars($_REQUEST['hid_view_type']) : $this->defaultView;
        $previous_action = (isset($_REQUEST['hid_previous_action']) ? $this->RemoveBadChars($_REQUEST['hid_previous_action']) : "");
        $page 	    	 = $this->GetParameter("hid_page", 1);
        $chart_type 	 = $this->GetParameter("hid_chart_type", "columnchart");
        $event_action 	 = $this->GetParameter("hid_event_action");
        if($view_type == "daily" || $view_type == "weekly") $selected_category = "";
        else $selected_category = $this->GetParameter("hid_selected_category");

        $cur_date = getdate(mktime(0,0,0,$month,$day,$year));

        $this->arrParameters["year"]  = $cur_date['year'];
        $this->arrParameters["month"] = $this->ConvertToDecimal($cur_date['mon']);
        $this->arrParameters["month_full_name"] = $this->lang['months'][$cur_date['mon']]; //$cur_date['month'];
        $this->arrParameters["day"]   	  = $day;
        $this->arrParameters["wday"]      = $cur_date['wday'];
        $this->arrParameters["weekday"]   = $this->lang[strtolower($cur_date['weekday'])];
        $this->arrParameters["view_type"] = $view_type;
        $this->arrParameters["action"] = "view";
        $this->arrParameters["previous_action"] = $previous_action;
        $this->arrParameters["page"] = $page;
        $this->arrParameters["chart_type"] = $chart_type;
        $this->arrParameters["event_action"] = $event_action;
        $this->arrParameters["selected_category"] = $selected_category;

        // find starting day for current day
        if($view_type == "weekly"){
            $sign = 1;
            for($i=0; $i<7; $i++){
                $week_day = date("w", mktime(0,0,0,$this->arrParameters["month"],$this->arrParameters["day"]+($sign*$i)-($this->weekStartedDay-1),$this->arrParameters["year"]));
                if(($i == 0) && ($week_day != "0")) $sign = -1;
                if($week_day == "0"){
                    $parts = explode("-", date("d-n-Y", mktime(0,0,0,$this->arrParameters["month"],$this->arrParameters["day"]+($sign*$i),$this->arrParameters["year"])));
                    $this->currWeek["year"]  = $parts[2];
                    $this->currWeek["month"] = $this->ConvertToDecimal($parts[1]);
                    $this->currWeek["month_full_name"] = $this->lang['months'][$parts[1]];
                    $this->currWeek["day"]   = $parts[0];
                    break;
                }
            }
        }else{
            $this->currWeek["year"]  = $this->arrParameters["year"];
            $this->currWeek["month"] = $this->arrParameters["month"];
            $this->currWeek["month_full_name"] = $this->arrParameters["month_full_name"];
            $this->currWeek["day"]   = $this->arrParameters["day"];
        }

        $this->arrToday = getdate();

        $this->prevYear = getdate(mktime(0,0,0,$this->arrParameters['month'],$this->arrParameters["day"],$this->arrParameters['year']-1));
        $this->prevYear['month'] = $this->lang['months'][$this->prevYear['mon']];
        $this->prevYear['weekday'] = $this->lang[strtolower($this->prevYear['weekday'])];
        $this->nextYear = getdate(mktime(0,0,0,$this->arrParameters['month'],$this->arrParameters["day"],$this->arrParameters['year']+1));
        $this->nextYear['month'] = $this->lang['months'][$this->nextYear['mon']];
        $this->nextYear['weekday'] = $this->lang[strtolower($this->nextYear['weekday'])];

        $this->prevMonth = getdate(mktime(0,0,0,$this->arrParameters['month']-1,$this->GetDayForMonth($this->arrParameters['month']-1,$this->arrParameters["day"]),$this->arrParameters['year']));
        $this->prevMonth['month'] = $this->lang['months'][$this->prevMonth['mon']];
        $this->prevMonth['weekday'] = $this->lang[strtolower($this->prevMonth['weekday'])];
        $this->nextMonth = getdate(mktime(0,0,0,$this->arrParameters['month']+1,$this->GetDayForMonth($this->arrParameters['month']+1,$this->arrParameters["day"]),$this->arrParameters['year']));
        $this->nextMonth['month'] = $this->lang['months'][$this->nextMonth['mon']];
        $this->nextMonth['weekday'] = $this->lang[strtolower($this->nextMonth['weekday'])];

        $this->prevWeek = getdate(mktime(0,0,0,$this->currWeek['month'],$this->currWeek["day"]-7,$this->currWeek['year']));
        $this->prevWeek['month'] = $this->lang['months'][$this->prevWeek['mon']];
        $this->prevWeek['weekday'] = $this->lang[strtolower($this->prevWeek['weekday'])];
        $this->nextWeek = getdate(mktime(0,0,0,$this->currWeek['month'],$this->currWeek["day"]+7,$this->currWeek['year']));
        $this->nextWeek['month'] = $this->lang['months'][$this->nextWeek['mon']];
        $this->nextWeek['weekday'] = $this->lang[strtolower($this->nextWeek['weekday'])];

        $this->prevDay = getdate(mktime(0,0,0,$this->arrParameters['month'],$this->arrParameters["day"]-1,$this->arrParameters['year']));
        $this->prevDay['month'] = $this->lang['months'][$this->prevDay['mon']];
        $this->prevDay['weekday'] = $this->lang[strtolower($this->prevDay['weekday'])];
        $this->nextDay = getdate(mktime(0,0,0,$this->arrParameters['month'],$this->arrParameters["day"]+1,$this->arrParameters['year']));
        $this->nextDay['month'] = $this->lang['months'][$this->nextDay['mon']];
        $this->nextDay['weekday'] = $this->lang[strtolower($this->nextDay['weekday'])];
    }

    /**
     * Handle events - proccess events: insert, edit or delete
     *
     */
    private function HandleEventsOperations()
    {
        $event_action 		= $this->GetParameter("hid_event_action");
        $event_id 		    = $this->GetParameter("hid_event_id");

        $sel_event 			= $this->GetParameter("sel_event");
        $event_name 		= $this->GetParameter("event_name");
        $sel_event_name		= $this->GetParameter("sel_event_name");
        $sel_event_name_array = explode("#", $sel_event_name);
        if(isset($sel_event_name_array[0])) $sel_event_name = $sel_event_name_array[0];

        $event_description 	= $this->GetParameter("event_description");
        $event_insertion_type = $this->GetParameter("event_insertion_type");
        $sel_category_id	= $this->GetParameter("sel_category_name");
        $sel_category_id_array = explode("#", $sel_category_id);
        if(isset($sel_category_id_array[0])) $sel_category_id = $sel_category_id_array[0];

        $event_insertion_subtype = $this->GetParameter("event_insertion_subtype");
        $event_from_time_hour  	= $this->GetParameter("event_from_time_hour");
        $event_from_date_day   	= $this->GetParameter("event_from_date_day");
        $event_from_date_month 	= $this->GetParameter("event_from_date_month");
        $event_from_date_year  	= $this->GetParameter("event_from_date_year");
        $event_from_date  	   	= $event_from_date_year."-".$event_from_date_month."-".$event_from_date_day;
        $event_to_time_hour  	= $this->GetParameter("event_to_time_hour");
        $event_to_date_day   	= $this->GetParameter("event_to_date_day");
        $event_to_date_month 	= $this->GetParameter("event_to_date_month");
        $event_to_date_year  	= $this->GetParameter("event_to_date_year");
        $event_to_date  	   	= $event_to_date_year."-".$event_to_date_month."-".$event_to_date_day;
        $event_week_days        = array("0"=>$this->GetParameter("repeat_sun"),
                                        "1"=>$this->GetParameter("repeat_mon"),
                                        "2"=>$this->GetParameter("repeat_tue"),
                                        "3"=>$this->GetParameter("repeat_wed"),
                                        "4"=>$this->GetParameter("repeat_thu"),
                                        "5"=>$this->GetParameter("repeat_fri"),
                                        "6"=>$this->GetParameter("repeat_sat"));

        $event_from_hour 	= $this->GetParameter("event_from_hour");
        $event_from_day 	= $this->GetParameter("event_from_day");
        $event_from_month 	= $this->GetParameter("event_from_month");
        $event_from_year 	= $this->GetParameter("event_from_year");
        $start_date         = $event_from_year."-".$event_from_month."-".$event_from_day." ".$event_from_hour;

        $event_to_hour 		= $this->GetParameter("event_to_hour");
        $event_to_day 		= $this->GetParameter("event_to_day");
        $event_to_month 	= $this->GetParameter("event_to_month");
        $event_to_year 		= $this->GetParameter("event_to_year");
        $finish_date_trim   = $event_to_year.$event_to_month.$event_to_day.$event_to_hour;
        $finish_date        = $event_to_year."-".$event_to_month."-".$event_to_day." ".$event_to_hour;

        $category_id        = $this->GetParameter("hid_category_id");
        $category_name 		= $this->GetParameter("category_name");
        $category_description = $this->GetParameter("category_description");
        $category_color     = $this->GetParameter("category_color");
        $category_duration  = $this->GetParameter("category_duration");

        $sql			    = "";

        if($this->isDemo && $event_action != "" &&
            (
                $event_action != "events_statistics" &&
                $event_action != "events_management" &&
                $event_action != "events_add" &&
                $event_action != "events_edit" &&
                $event_action != "categories_management" &&
                $event_action != "categories_add" &&
                $event_action != "categories_edit"
            )
        ){
            $this->arrMessages[] = $this->GetMessage("error", $this->lang['msg_this_operation_blocked_demo']);
            return false;
        }

        if($event_action == "add"){
            // insert single event
            $insert_id = false;
            if($sel_event == "new"){
                $sql = "SELECT COUNT(*) as cnt FROM ".DB_PREFIX."events WHERE name = '".$this->PrepareText($event_name)."'";
                $event_exists = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
                if($event_exists['cnt'] > 0){
                    $this->arrMessages[] = $this->GetMessage("error", $this->lang['error_event_exists']);
                    return false;
                }else{
                    $sql = "INSERT INTO ".DB_PREFIX."events (id, name, description, category_id) VALUES (NULL, '".$this->PrepareText($event_name)."', '".$this->PrepareText($event_description)."', '".(int)$sel_category_id."')";
                    $insert_id = database_void_query($sql);
                }
            }elseif($sel_event == "current"){
                $insert_id = $sel_event_name;
            }
            if($insert_id != false){
                $result = $this->InsertEventsOccurrences($insert_id, $start_date, $finish_date, $event_from_hour, $event_from_day, $event_from_month, $event_from_year);
                if($result) $this->arrMessages[] = $this->GetMessage("success", $this->lang['success_new_event_was_added']);
            }else{
                $this->arrMessages[] = $this->GetMessage("error", $this->lang['error_inserting_new_events']);
            }
        }elseif($event_action == "delete"){
            $can_delete = true;
            // check if event can be deleted
            $sql = "SELECT
						".DB_PREFIX."events.name,
						TIME_FORMAT(TIMEDIFF(CONCAT(".DB_PREFIX."calendar.event_date, ' ', ".DB_PREFIX."calendar.event_time), '".date("Y-m-d H:i:s")."'), '%H') as time_diff
					FROM ".DB_PREFIX."calendar
						INNER JOIN ".DB_PREFIX."events ON ".DB_PREFIX."calendar.event_id = ".DB_PREFIX."events.id
					WHERE ".DB_PREFIX."calendar.id = ".(int)$event_id."
					ORDER BY time_diff ASC
					LIMIT 0, 1";
            $result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
            if($this->canNotDeleteBefore != "" && isset($result['time_diff']) && $result['time_diff'] < $this->canNotDeleteBefore){
                $can_delete = false;
                if($result['time_diff'] < 0){
                    $this->arrMessages[] = $this->GetMessage("error", str_replace("_HOURS_", $this->canNotDeleteBefore, $this->lang['error_deleting_event_past']));
                }else{
                    $this->arrMessages[] = $this->GetMessage("error", str_replace("_HOURS_", $this->canNotDeleteBefore, $this->lang['error_deleting_event_hours']));
                }
            }else{
                $can_delete = true;
            }
            if($this->isDebug) $this->arrSQLs[] = $sql."<br />".database_error()."<br />";
            // delete single event
            if($can_delete){
                $sql = "DELETE FROM ".DB_PREFIX."calendar WHERE id = ".(int)$event_id;
                if(!database_void_query($sql)){
                    $this->arrMessages[] = $this->GetMessage("error", $this->lang['error_deleting_event']);
                }else{
                    $this->arrMessages[] = $this->GetMessage("success", str_replace("_EVENT_NAME_", $result['name'], $this->lang['success_event_was_deleted']));
                    $this->DeleteCache();
                }
            }
        }elseif($event_action == "events_insert"){
            // insert new event
            $check_passed = true;
            if($event_insertion_type == 2 && $event_insertion_subtype == "repeat"){
                // add occurrences repeatedly  - force insertion
                // do nothing
            }else{
                $sql = "SELECT COUNT(*) as cnt FROM ".DB_PREFIX."events WHERE name = '".$this->PrepareText($event_name)."'";
                $event_exists = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
                if($event_exists['cnt'] > 0){
                    $this->arrMessages[] = $this->GetMessage("error", $this->lang['error_event_exists']);
                    $check_passed = false;
                }
            }
            if($check_passed){
                $sql = "INSERT INTO ".DB_PREFIX."events (id, name, description, category_id) VALUES (NULL, '".$this->PrepareText($event_name)."', '".$this->PrepareText($event_description)."', '".(int)$sel_category_id."')";
                $insert_id = database_void_query($sql);
                if($insert_id != false){
                    // if add event occurrences selected
                    $result = true;
                    if($event_insertion_type == 2){
                        if($event_insertion_subtype == "one_time") $result = $this->InsertEventsOccurrences($insert_id, $start_date, $finish_date, $event_from_hour, $event_from_day, $event_from_month, $event_from_year, false);
                        else if($event_insertion_subtype == "repeat") $result = $this->InsertEventsOccurrencesRepeatedly($insert_id, $event_from_date, $event_to_date, $event_from_time_hour, $event_to_time_hour, $event_week_days);
                    }
                    if($result) $this->arrMessages[] = $this->GetMessage("success", $this->lang['success_new_event_was_added']);
                }else{
                    $this->arrMessages[] = $this->GetMessage("error", $this->lang['error_inserting_new_events']);
                }
            }
        }elseif($event_action == "events_update"){
            // update events
            $sql = "UPDATE ".DB_PREFIX."events SET name='".$this->PrepareText($event_name)."', description='".$this->PrepareText($event_description)."', category_id = '".(int)$sel_category_id."' WHERE id = ".(int)$event_id;
            if(database_void_query($sql)){
                $this->arrMessages[] = $this->GetMessage("success", $this->lang['success_event_was_updated']);
            }else{
                $this->arrMessages[] = $this->GetMessage("error", $this->lang['error_updating_event']);
            }
        }elseif($event_action == "events_delete_by_range"){
            $sql  = "DELETE FROM ".DB_PREFIX."calendar WHERE ";
            if($sel_category_id != "") $sql .= DB_PREFIX."calendar.event_id IN (SELECT id FROM ".DB_PREFIX."events WHERE ".DB_PREFIX."events.category_id = ".(int)$sel_category_id.") AND ";
            $sql .= "(event_date >= '".$event_from_year."-".$event_from_month."-".$event_from_day."') AND
					 (event_date <= '".$event_to_year."-".$event_to_month."-".$event_to_day."') ";

            if(database_void_query($sql)){
                $this->arrMessages[] = $this->GetMessage("success", $this->lang['success_events_were_deleted']);
            }else{
                $this->arrMessages[] = $this->GetMessage("error", $this->lang['error_no_event_found']);
            }
        }elseif($event_action == "events_delete"){
            // delete event from Events table
            $sql = "SELECT name FROM ".DB_PREFIX."events WHERE id = ".(int)$event_id;
            $event_name = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
            if(isset($event_name['name'])){
                $can_delete = true;
                if(!$this->allowEventsMultipleOccurrences){
                    $sql_1 = "SELECT
								".DB_PREFIX."calendar.event_date, 
								".DB_PREFIX."calendar.event_time,								
								TIME_FORMAT(TIMEDIFF(CONCAT(".DB_PREFIX."calendar.event_date, ' ', ".DB_PREFIX."calendar.event_time), '".date("Y-m-d H:i:s")."'), '%H') as time_diff
							FROM ".DB_PREFIX."events
								LEFT OUTER JOIN ".DB_PREFIX."calendar ON ".DB_PREFIX."events.id = ".DB_PREFIX."calendar.event_id 
							WHERE							    
								".DB_PREFIX."events.id = ".(int)$event_id."
							ORDER BY time_diff ASC
							LIMIT 0, 1";
                    $result = database_query($sql_1, DATA_ONLY, FIRST_ROW_ONLY);
                    if($this->canNotDeleteBefore != "" && isset($result['time_diff']) && $result['time_diff'] < $this->canNotDeleteBefore){
                        $can_delete = false;
                        if($result['time_diff'] < 0){
                            $this->arrMessages[] = $this->GetMessage("error", str_replace("_HOURS_", $this->canNotDeleteBefore, $this->lang['error_deleting_event_past']));
                        }else{
                            $this->arrMessages[] = $this->GetMessage("error", str_replace("_HOURS_", $this->canNotDeleteBefore, $this->lang['error_deleting_event_hours']));
                        }
                    }else{
                        $can_delete = true;
                    }
                    if($this->isDebug) $this->arrSQLs[] = $sql."<br />".database_error()."<br />";
                }
                if($can_delete){
                    $sql_1 = "DELETE FROM ".DB_PREFIX."events WHERE id = ".(int)$event_id;
                    $sql_2 = "DELETE FROM ".DB_PREFIX."calendar WHERE event_id = ".(int)$event_id;
                    if(!database_void_query($sql_1) && (database_void_query($sql_2) >= 0)){
                        if($this->isDebug) $this->arrSQLs[] = $sql."<br />".database_error()."<br />";
                        $this->arrMessages[] = $this->GetMessage("error", $this->lang['error_deleting_event']);
                    }else{
                        $this->arrMessages[] = $this->GetMessage("success", str_replace("_EVENT_NAME_", $event_name['name'], $this->lang['success_event_was_deleted']));
                        $this->DeleteCache();
                    }
                    if($this->isDebug) $this->arrSQLs[] = $sql_1."<br />".database_error()."<br />";
                    if($this->isDebug) $this->arrSQLs[] = $sql_2."<br />".database_error()."<br />";
                }
            }else{
                $this->arrMessages[] = $this->GetMessage("error", $this->lang['error_deleting_event']);
            }
        }elseif($event_action == "categories_insert"){
            // insert new category
            $sql = "SELECT COUNT(*) as cnt FROM ".DB_PREFIX."events_categories WHERE name = '".$this->PrepareText($category_name)."'";
            $category_exists = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
            if($category_exists['cnt'] > 0){
                $this->arrMessages[] = $this->GetMessage("error", $this->lang['error_category_exists']);
            }else{
                $sql = "INSERT INTO ".DB_PREFIX."events_categories (id, name, description, color, duration) VALUES (NULL, '".$this->PrepareText($category_name)."', '".$this->PrepareText($category_description)."', '".$category_color."', '".$category_duration."')";
                $insert_id = database_void_query($sql);
                if($insert_id != false){
                    $this->arrMessages[] = $this->GetMessage("success", $this->lang['success_new_category_added']);
                }else{
                    $this->arrMessages[] = $this->GetMessage("error", $this->lang['error_inserting_new_category']);
                }
            }
        }elseif($event_action == "categories_update"){
            // update categories
            $sql = "UPDATE ".DB_PREFIX."events_categories SET name='".$this->PrepareText($category_name)."', description='".$this->PrepareText($category_description)."', color='".$category_color."', duration='".$category_duration."' WHERE id = ".(int)$category_id;
            if(database_void_query($sql)){
                $this->arrMessages[] = $this->GetMessage("success", $this->lang['success_category_was_updated']);
            }else{
                $this->arrMessages[] = $this->GetMessage("error", $this->lang['error_updating_category']);
            }
        }elseif($event_action == "categories_delete"){
            // delete category
            $sql = "DELETE FROM ".DB_PREFIX."events_categories WHERE id = ".(int)$category_id;
            if(!database_void_query($sql)){
                $this->arrMessages[] = $this->GetMessage("error", $this->lang['error_deleting_category']);
            }else{
                $this->arrMessages[] = $this->GetMessage("success", $this->lang['success_category_was_deleted']);
                $this->DeleteCache();
            }
        }
        if($this->isDebug && $sql != "") $this->arrSQLs[] = $sql."<br />".database_error()."<br />";
    }

    /**
     * Draw CSS style
     *
     */
    private function DrawCssStyle()
    {
        if($this->isDemo && !$this->isShowcase){
            $css_style = $this->GetParameter("css_style", "blue");
        }else{
            $css_style = $this->cssStyle;
        }
        echo "<link href='".$this->calUrl."style/".$css_style."/style.css' rel='stylesheet' type='text/css' />".$this->crLt;
    }

    /**
     * Draw javascript functions
     *
     */
    private function DrawJsFunctions()
    {
        echo "<script type='text/javascript'>".$this->crLt;
        echo "<!--\n
			GL_jump_day   = '".$this->arrToday["mday"]."';
			GL_jump_month = '".$this->ConvertToDecimal($this->arrToday["mon"])."';
			GL_jump_year  = '".$this->arrToday["year"]."';
			GL_view_type  = '".$this->defaultView."';
			GL_today_year = '".$this->arrToday["year"]."';
			GL_today_mon  = '".$this->ConvertToDecimal($this->arrToday["mon"])."';
			GL_today_mday = '".$this->arrToday["mday"]."';
		\n//-->".$this->crLt;
        echo "</script>".$this->crLt;
        echo "<script type='text/javascript' src='".$this->calUrl."js/lang/".$this->langName.".js'></script>".$this->crLt;
        echo "<script type='text/javascript' src='".$this->calUrl."js/calendar.js'></script>".$this->crLt;
        if($this->addEventFormType == "floating"){
            echo "<script type='text/javascript' src='".$this->calUrl."js/draggable.js'></script>".$this->crLt;
            $this->arrInitJsFunction[] = "function initialize(){ addEventDragObject = new dragObject('divAddEvent', 'divAddEvent_Header'); } initialize();";
        }
        if($this->arrCalendarOperations['statistics'] && $this->arrParameters["event_action"] == "events_statistics"){
            echo '<script type="text/javascript" src="//www.google.com/jsapi"></script>'.$this->crLt;
            echo "<script type='text/javascript'>function drawVisualization(){
					// Create and populate the data table.
					var data = new google.visualization.DataTable();
					data.addColumn('string', '".$this->lang['event_name']."');
					data.addColumn('number', '".$this->lang['occurrences']."');";

            $sql = "SELECT
								".DB_PREFIX."events.id,
								".DB_PREFIX."events.name,
								".DB_PREFIX."events.description,
								COUNT(".DB_PREFIX."calendar.id) as cnt
							FROM ".DB_PREFIX."events
								LEFT OUTER JOIN ".DB_PREFIX."calendar ON ".DB_PREFIX."calendar.event_id = ".DB_PREFIX."events.id
							WHERE 1=1 ".(($this->userID) ? " AND ".DB_PREFIX."calendar.user_id = ".(int)$this->userID : "")."
							GROUP BY ".DB_PREFIX."events.id							
							ORDER BY cnt DESC
							LIMIT 0, 10";

            $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
            $content = file_get_contents($this->calUrl."template/events_management_row.tpl");

            echo "data.addRows(".$result[1].");".$this->crLt;
            $events_count = "0";
            foreach($result[0] as $key => $val){
                echo "data.setCell(".$events_count.", 0, '".$this->PrepareFormatedText($val['name'])." (".$val['cnt'].")');".$this->crLt;
                echo "data.setCell(".$events_count.", 1, ".$val['cnt'].");".$this->crLt;
                $events_count++;
            }

            // Create and draw the visualization
            if($this->arrParameters["chart_type"] == "barchart"){
                echo "new google.visualization.BarChart(document.getElementById('div_visualization')).draw(data, {is3D: true, min:0, title:'".$this->lang['top_10_events']."'});".$this->crLt;
            }elseif($this->arrParameters["chart_type"] == "piechart"){
                echo "new google.visualization.PieChart(document.getElementById('div_visualization')).draw(data, {is3D: true, min:0, title:'".$this->lang['top_10_events']."'});".$this->crLt;
            }else{ // columnchart
                echo "new google.visualization.ColumnChart(document.getElementById('div_visualization')).draw(data, {is3D: true, min:0, title:'".$this->lang['top_10_events']."'});".$this->crLt;
            }

            echo " }	   
			  </script>".$this->crLt;

            $this->arrInitJsFunction[] = "google.load('visualization', '1', {packages: ['".$this->arrParameters["chart_type"]."']});";
            $this->arrInitJsFunction[] = "google.setOnLoadCallback(drawVisualization);";
        }
    }

    /**
     * Include Modules
     *
     */
    private function IncludeModules()
    {

    }

    /**
     * Initialize javascript functions
     *
     */
    private function InitializeJsFunctions()
    {
        if(count($this->arrInitJsFunction) > 0){
            echo "<script type='text/javascript'>\n";
            echo "<!--\n";
            foreach($this->arrInitJsFunction as $key => $val){
                echo $val."\n";
            }
            echo "//-->\n";
            echo "</script>\n";
        }
    }

    /**
     * Draw system messages
     *
     */
    private function DrawMessages($draw = true)
    {
        $output = "";
        if(count($this->arrMessages) > 0){
            echo "<tr>";
            foreach($this->arrMessages as $key){
                $output .= "<th colspan='3'><center>".$key."</center></th>".$this->crLt;
            }
            echo "</tr>";
        }
        if($draw) echo $output;
        else return $output;
    }

    /**
     * Draw system errors
     *
     */
    private function DrawErrors($draw = true)
    {
        $output = "";
        if(count($this->arrErrors) > 0){
            foreach($this->arrErrors as $key){
                $output .= "<span>".$key."</span><br />".$this->crLt;
            }
        }
        if($draw) echo $output;
        else return $output;
    }

    /**
     * Draw system errors
     */
    private function DrawSQLs($draw = true)
    {
        $output = "";
        if(count($this->arrSQLs) > 0){
            foreach($this->arrSQLs as $key){
                $output .= "<span>".$key."</span><br />".$this->crLt;
            }
        }
        if($draw) echo $output;
        else return $output;
    }

    /**
     * Draw yearly calendar
     */
    private function DrawYear()
    {
        // start caching
        $cachefile = $this->cacheDir."yearly-".$this->arrParameters["selected_category"]."-".$this->arrParameters['year'].".cch";
        if($this->isCachingAllowed){
            if($this->StartCaching($cachefile)) return true;
        }

        $this->celHeight = "20px";
        echo "<table class='year_container'>".$this->crLt;
        echo "<tr>".$this->crLt;
        echo "<th colspan='3'>";
        echo "<table class='table_navbar'>".$this->crLt;
        echo "<tr>";
        echo "<th class='tr_navbar_".$this->defined_left."' valign='middle'>
						".$this->DrawDateJumper(false, false, false)."
					  </th>".$this->crLt;
        echo "<th class='tr_navbar'>".$this->arrParameters['year']."</th>".$this->crLt;
        echo "<th class='tr_navbar_".$this->defined_right."'>				
					  <a href=\"javascript:__doPostBack('view', 'yearly', '".$this->prevYear['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."')\">".$this->prevYear['year']."</a> |
					  <a href=\"javascript:__doPostBack('view', 'yearly', '".$this->nextYear['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."')\">".$this->nextYear['year']."</a>
					  </th>".$this->crLt;
        echo "</tr>".$this->crLt;
        echo "</table>".$this->crLt;
        echo "</td>".$this->crLt;
        echo "</tr>".$this->crLt;

        echo "<tr>";
        for($i = 1; $i <= 12; $i++){
            echo "<td align='center' valign='top'>";
            if($this->arrViewTypes["monthly"]["enabled"]){
                echo "<a href=\"javascript:__doPostBack('view', 'monthly', '".$this->arrParameters['year']."', '".$this->ConvertToDecimal($i)."', '".$this->arrParameters['day']."')\"><b>".$this->arrMonths["$i"]."</b></a>";
            }else{
                echo "<b>".$this->arrMonths["$i"]."</b>";
            }
            $this->DrawMonthSmall($this->arrParameters['year'], $this->ConvertToDecimal($i));
            echo "</td>";
            if(($i != 1) && ($i % 3 == 0)) echo "</tr><tr>";
        }
        echo "</tr>";
        echo "<tr><td nowrap='nowrap' colspan='3' height='10px'></td></tr>";
        echo "<tr>
				<td nowrap='nowrap' colspan='3'>
					<div class='legend_text'> ".$this->lang['bookings'].": </div>
					<div class='legend_f_block'>1</div>
					<div class='legend_block e0'></div>
					<div class='legend_block e1'></div>
					<div class='legend_block e2'></div>
					<div class='legend_block e3'></div>
					<div class='legend_block e4'></div>
					<div class='legend_block e5'></div>
					<div class='legend_block e6'></div>
					<div class='legend_block e7'></div>
					<div class='legend_block e8'></div>
					<div class='legend_block e9'></div>
					<div class='legend_block e10'></div>
					<div class='legend_l_block'>10+</div>
				</td>
			  </tr>";
        echo "<tr><td nowrap='nowrap' colspan='3' height='5px'></td></tr>";
        echo "</table>";

        // finish caching
        if($this->isCachingAllowed) $this->FinishCaching($cachefile);
    }

    /**
     * Draw list view calendar
     *
     */
    private function DrawListView()
    {
        // start caching
        $cachefile = $this->cacheDir."listview-".$this->arrParameters["selected_category"]."-".$this->arrParameters['year']."-".$this->arrParameters['month'].".cch";
        if($this->isCachingAllowed){
            if($this->StartCaching($cachefile)) return true;
        }

        // today, first day and last day in month
        $firstDay = getdate(mktime(0,0,0,$this->arrParameters['month'],1,$this->arrParameters['year']));
        $lastDay  = getdate(mktime(0,0,0,$this->arrParameters['month']+1,0,$this->arrParameters['year']));
        $actday   = 0;

        $arrEventsList = $this->GetEventsListForMonth($this->arrParameters['year'], $this->arrParameters['month'], true);

        $this->arrRoomTypes = Rooms::GetRoomAvalibilityForMonth($this->arrRoomTypes, $this->arrParameters['year'], $this->arrParameters['month']);

        #echo "<pre>";
        #print_r($this->arrRoomTypes);
        #echo "</pre>";

        #echo "<pre>";
        #print_r($arrEventsList);
        #echo "</pre>";

        // Create a table with the necessary header informations
        echo "<table class='month'>".$this->crLt;
        echo "<tr>".$this->crLt;
        if($this->isWeekNumberOfYear) echo "<th colspan='2'>".$this->crLt;
        else echo "<th colspan='2'>".$this->crLt;
        echo "<table class='table_navbar'>".$this->crLt;
        echo "<tr>";
        echo "<th class='tr_navbar_".$this->defined_left."'>
					  ".$this->DrawDateJumper(false)."	
					  </th>".$this->crLt;
        echo "<th class='tr_navbar'>";
        echo " <a href=\"javascript:__doPostBack('view', 'list_view', '".$this->prevMonth['year']."', '".$this->ConvertToDecimal($this->prevMonth['mon'])."', '".$this->ConvertToDecimal($this->prevMonth['mday'])."')\">&laquo;&laquo;</a> ";
        echo $this->arrParameters['month_full_name']." - ".$this->arrParameters['year'];
        echo " <a href=\"javascript:__doPostBack('view', 'list_view', '".$this->nextMonth['year']."', '".$this->ConvertToDecimal($this->nextMonth['mon'])."', '".$this->ConvertToDecimal($this->nextMonth['mday'])."')\">&raquo;&raquo;</a> ";
        echo "</th>".$this->crLt;
        echo "<th class='tr_navbar_".$this->defined_right."'>				
					  <a href=\"javascript:__doPostBack('view', 'list_view', '".$this->prevYear['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."')\">".$this->prevYear['year']."</a> |
					  <a href=\"javascript:__doPostBack('view', 'list_view', '".$this->nextYear['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."')\">".$this->nextYear['year']."</a>
					  </th>".$this->crLt;
        echo "</tr>".$this->crLt;
        echo "</table>".$this->crLt;
        echo "</th>".$this->crLt;
        echo "</tr>".$this->crLt;

        $displayed_count = 0;
        for($i = 0; $i <= $this->GetMonthLastDay($this->arrParameters['month'], $this->arrParameters['year']); $i++){
            $monthly_day_events = $this->GetMonthlyDayEvents($arrEventsList, $this->ConvertToDecimal($i));
            if($monthly_day_events != ""){
                if($displayed_count > 0) echo "<tr><td></td><td align='left'><div class='lv_separator'></div></td></tr>";
                echo "<tr>".$this->crLt;
                echo "<td width='15%' class='lv_lcolumn'>";
                echo $this->ConvertToDecimal($i)." ".$this->arrParameters["month_full_name"]." (".date("D", mktime(0, 0, 0, $this->arrParameters["month"], $this->ConvertToDecimal($i), $this->arrParameters["year"])).")";
                echo "</td>";
                echo "<td width='85%' class='lv_rcolumn'>";
                echo $monthly_day_events;
                echo "</td>";
                echo "</tr>".$this->crLt;
                $displayed_count++;
            }
        }
        echo "<tr><td colspan='2' height='10px' nowrap='nowrap'></td></tr>";

        echo "</table>".$this->crLt;

        // finish caching
        if($this->isCachingAllowed) $this->FinishCaching($cachefile);
    }

    /**
     * Draw monthly calendar
     *
     */
    private function DrawMonth()
    {
        // start caching
        $cachefile = $this->cacheDir."monthly-".$this->arrParameters["selected_category"]."-".$this->arrParameters['year']."-".$this->arrParameters['month'].".cch";
        if($this->isCachingAllowed){
            if($this->StartCaching($cachefile)) return true;
        }

        // today, first day and last day in month
        $firstDay = getdate(mktime(0,0,0,$this->arrParameters['month'],1,$this->arrParameters['year']));
        $lastDay  = getdate(mktime(0,0,0,$this->arrParameters['month']+1,0,$this->arrParameters['year']));
        $actday   = 0;

        $arrEventsList = $this->GetEventsListForMonth($this->arrParameters['year'], $this->arrParameters['month']);

        $this->arrRoomTypes = Rooms::GetRoomAvalibilityForMonth($this->arrRoomTypes, $this->arrParameters['year'], $this->arrParameters['month']);

        //echo "<pre>";
        //print_r($this->arrRoomTypes);
        //echo "</pre>";

        //echo "<pre>";
        //print_r($arrEventsList);
        //echo "</pre>";

        // Create a table with the necessary header informations
        echo "<table class='month'>".$this->crLt;
        echo "<tr>";
        if($this->isWeekNumberOfYear) echo "<th colspan='8'>";
        else echo "<th colspan='7'>";
        echo "<table border='0' class='table_navbar'>".$this->crLt;
        echo "<tr>";
        echo "<th class='tr_navbar_".$this->defined_left."'>
					  ".$this->DrawDateJumper(false)."	
					  </th>".$this->crLt;
        echo "<th class='tr_navbar' nowrap='nowrap'>";
        echo " <a href=\"javascript:__doPostBack('view', 'monthly', '".$this->prevMonth['year']."', '".$this->ConvertToDecimal($this->prevMonth['mon'])."', '".$this->ConvertToDecimal($this->prevMonth['mday'])."')\">&laquo;&laquo;</a> ";
        echo $this->arrParameters['month_full_name']." - ".$this->arrParameters['year'];
        echo " <a href=\"javascript:__doPostBack('view', 'monthly', '".$this->nextMonth['year']."', '".$this->ConvertToDecimal($this->nextMonth['mon'])."', '".$this->ConvertToDecimal($this->nextMonth['mday'])."')\">&raquo;&raquo;</a> ";
        echo "</th>".$this->crLt;
        echo "<th class='tr_navbar_".$this->defined_right."'>				
					  <a href=\"javascript:__doPostBack('view', 'monthly', '".$this->prevYear['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."')\">".$this->prevYear['year']."</a> |
					  <a href=\"javascript:__doPostBack('view', 'monthly', '".$this->nextYear['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."')\">".$this->nextYear['year']."</a>
					  </th>".$this->crLt;
        echo "</tr>".$this->crLt;
        echo "</table>".$this->crLt;
        echo "</th>".$this->crLt;
        echo "</tr>".$this->crLt;
        echo "<tr class='tr_days'>".$this->crLt;
        if($this->isWeekNumberOfYear) echo "<td class='th_wn'></td>".$this->crLt;
        for($i = $this->weekStartedDay-1; $i < $this->weekStartedDay+6; $i++){
            echo "<td class='th'>".$this->arrWeekDays[($i % 7)][$this->weekDayNameLength]."</td>".$this->crLt;
        }
        echo "</tr>".$this->crLt;

        // Display the first calendar row with correct positioning
        if($firstDay['wday'] == 0) $firstDay['wday'] = 7;
        $max_empty_days = $firstDay['wday']-($this->weekStartedDay-1);
        if($max_empty_days < 7){
            echo "<tr class='tr' style='height:".$this->celHeight.";'>".$this->crLt;
            if($this->isWeekNumberOfYear){
                $parts = explode("-", (date("Y-m-d-W", mktime(0,0,0,$this->arrParameters["month"],1-$max_empty_days,$this->arrParameters["year"]))));
                echo "<td class='td_wn'>";
                if($this->arrViewTypes["weekly"]["enabled"]){
                    echo "<a href=\"javascript:__doPostBack('view', 'weekly', '".$parts[0]."', '".$parts[1]."', '".$parts[2]."')\" title='".$this->lang['click_view_week']."'>".$parts[3]."</a>";
                }else{
                    echo $parts[3];
                }
                echo "</td>".$this->crLt;
            }
            for($i = 1; $i <= $max_empty_days; $i++){
                echo "<td class='td_empty'>&nbsp;</td>".$this->crLt;
            }
            for($i = $max_empty_days+1; $i <= 7; $i++){
                $actday++;
                if (($actday == $this->arrToday['mday']) && ($this->arrToday['mon'] == $this->arrParameters["month"])) {
                    $class = " class='td_actday'";
                }elseif ($actday == $this->arrParameters['day']){
                    $class = " class='td_selday'";
                }elseif ($this->arrWeekDays[(($i+($this->weekStartedDay - 2)) % 7)]["short"] == "Sun"){
                    $class = ($this->sundayColor) ? " class='td_sunday'" : " class='td'";
                }else{
                    $class = " class='td'";
                }
                echo "<td$class>";
                $events_count = (is_array($arrEventsList[$this->ConvertToDecimal($actday)])) ? count($arrEventsList[$this->ConvertToDecimal($actday)]) : 0;
                $bookings_count = isset($arrEventsList[$this->ConvertToDecimal($actday)][0]) ? $arrEventsList[$this->ConvertToDecimal($actday)][0] : "0";
                $bookings_count_text = ($bookings_count > 0) ? " (".(($bookings_count > 1) ? $bookings_count." ".$this->lang['rooms'] : $bookings_count." ".$this->lang['rooms']).")" : "";
                if($this->arrViewTypes["daily"]["enabled"]){
                    echo "<a href=\"javascript:__doPostBack('view', 'daily', '".$this->arrParameters["year"]."', '".$this->arrParameters["month"]."', '".$this->ConvertToDecimal($actday)."')\">".$actday." ".$bookings_count_text."</a>";
                }else{
                    echo $actday." ".$bookings_count_text;
                }
                echo "<br />";
                // draw events for this day
                $this->DrawMonthlyDayCell($events_count, $arrEventsList, $actday);
                echo "</td>".$this->crLt;
            }
            echo "</tr>".$this->crLt;
        }

        //Get how many complete weeks are in the actual month
        $fullWeeks = floor(($lastDay['mday']-$actday)/7);

        for ($i=0;$i<$fullWeeks;$i++){
            echo "<tr class='tr' style='height:".$this->celHeight.";'>".$this->crLt;
            if($this->isWeekNumberOfYear){
                $parts = explode("-", (date("Y-m-d-W", mktime(0,0,0,$this->arrParameters["month"],$actday+1,$this->arrParameters["year"]))));
                echo "<td class='td_wn'>";
                if($this->arrViewTypes["weekly"]["enabled"]){
                    echo "<a href=\"javascript:__doPostBack('view', 'weekly', '".$parts[0]."', '".$parts[1]."', '".$parts[2]."')\" title='".$this->lang['click_view_week']."'>".$parts[3]."</a>";
                }else{
                    echo $parts[3];
                }
                echo "</td>".$this->crLt;
            }
            for($j=0;$j<7;$j++){
                $actday++;
                if(($actday == $this->arrToday['mday']) && ($this->arrToday['mon'] == $this->arrParameters["month"])){
                    $class = " class='td_actday'";
                }elseif($actday == $this->arrParameters['day']){
                    $class = " class='td_selday'";
                }elseif($this->arrWeekDays[(($j+($this->weekStartedDay - 1)) % 7)]["short"] == "Sun"){
                    $class = ($this->sundayColor) ? " class='td_sunday'" : " class='td'";
                }else{
                    $class = " class='td'";
                }
                echo "<td$class>";
                $events_count = (is_array($arrEventsList[$this->ConvertToDecimal($actday)])) ? count($arrEventsList[$this->ConvertToDecimal($actday)]) : 0;
                $bookings_count = isset($arrEventsList[$this->ConvertToDecimal($actday)][0]) ? $arrEventsList[$this->ConvertToDecimal($actday)][0] : "0";
                $bookings_count_text = ($bookings_count > 0) ? " (".(($bookings_count > 1) ? $bookings_count." ".$this->lang['rooms'] : $bookings_count." ".$this->lang['rooms']).")" : "";
                if($this->arrViewTypes["daily"]["enabled"]){
                    echo "<a href=\"javascript:__doPostBack('view', 'daily', '".$this->arrParameters["year"]."', '".$this->arrParameters["month"]."', '".$this->ConvertToDecimal($actday)."')\">".$actday." ".$bookings_count_text."</a>";
                }else{
                    echo $actday." ".$bookings_count_text;
                }
                echo "<br />";

                //print_r($arrEventsList[$this->ConvertToDecimal($actday)]);

                // draw events for this day
                $this->DrawMonthlyDayCell($events_count, $arrEventsList, $actday);
                echo "</td>".$this->crLt;
            }
            echo "</tr>".$this->crLt;
        }

        //Now display the rest of the month
        if ($actday < $lastDay['mday']){
            echo "<tr class='tr' style='height:".$this->celHeight.";'>".$this->crLt;
            if($this->isWeekNumberOfYear){
                $parts = explode("-", (date("Y-m-d-W", mktime(0,0,0,$this->arrParameters["month"],$actday+1,$this->arrParameters["year"]))));
                echo "<td class='td_wn'>";
                if($this->arrViewTypes["weekly"]["enabled"]){
                    echo "<a href=\"javascript:__doPostBack('view', 'weekly', '".$parts[0]."', '".$parts[1]."', '".$parts[2]."')\" title='".$this->lang['click_view_week']."'>".$parts[3]."</a>";
                }else{
                    echo $parts[3];
                }
                echo "</td>".$this->crLt;
            }

            for($i=0; $i<7;$i++){
                $actday++;
                if(($actday == $this->arrToday['mday']) && ($this->arrToday['mon'] == $this->arrParameters["month"])){
                    $class = " class='td_actday'";
                }elseif($this->arrWeekDays[(($i+($this->weekStartedDay - 1)) % 7)]["short"] == "Sun"){
                    $class = ($this->sundayColor) ? " class='td_sunday'" : " class='td'";
                }else{
                    $class = " class='td'";
                }
                if($actday <= $lastDay['mday']){
                    echo "<td$class>";
                    $events_count = (is_array($arrEventsList[$this->ConvertToDecimal($actday)])) ? count($arrEventsList[$this->ConvertToDecimal($actday)]) : 0;
                    $bookings_count = isset($arrEventsList[$this->ConvertToDecimal($actday)][0]) ? $arrEventsList[$this->ConvertToDecimal($actday)][0] : "0";
                    $bookings_count_text = ($bookings_count > 0) ? " (".(($bookings_count > 1) ? $bookings_count." ".$this->lang['rooms'] : $bookings_count." ".$this->lang['rooms']).")" : "";
                    if($this->arrViewTypes["daily"]["enabled"]){
                        echo "<a href=\"javascript:__doPostBack('view', 'daily', '".$this->arrParameters["year"]."', '".$this->arrParameters["month"]."', '".$this->ConvertToDecimal($actday)."')\">".$actday." ".$bookings_count_text."</a>";
                    }else{
                        echo $actday." ".$bookings_count_text;
                    }
                    echo "<br />";
                    // draw events for this day
                    $this->DrawMonthlyDayCell($events_count, $arrEventsList, $actday);
                }else{
                    echo "<td class='td_empty'>&nbsp;</td>".$this->crLt;
                }
            }
            echo "</tr>".$this->crLt;
        }
        echo "</table>".$this->crLt;

        // finish caching
        if($this->isCachingAllowed) $this->FinishCaching($cachefile);
    }

    /**
     * Draw monthly triple calendar
     */
    private function DrawMonthlyTriple()
    {
        $total_months = $this->monthNumber;

        // start caching
        $cachefile = $this->cacheDir."yearly-".$this->arrParameters["selected_category"]."-".$this->arrParameters['year'].".cch";
        if($this->isCachingAllowed){
            if($this->StartCaching($cachefile)) return true;
        }

        $this->celHeight = "20px";
        echo "<table class='triple_months_container' width='100%'>".$this->crLt;
        echo "<tr><td nowrap='nowrap' colspan='3' height='5px'></td></tr>";
        echo "<tr>";
        $current_month = (int)$this->arrParameters['month'];
        for($i = $current_month, $ind = 1; $i < $current_month + $total_months; $i++, $ind++){
            echo '<td align="center" valign="top">';
            $year = $this->arrParameters['year'] + (($i > 12) ? 1 : 0);
            $month = (($i > 12) ? $this->arrMonths[$i - 12] : $this->arrMonths[$i]);
            echo '<b>'.$month.' '.$year.'</b>';
            $month_ind = ($i <= 12) ? $i : ($i - 12);
            $this->DrawMonthSmall($year, $this->ConvertToDecimal($month_ind));
            echo "</td>";
            if(($ind != 1) && ($ind % 3 == 0)) echo '</tr><tr>';
        }
        echo "</tr>";
        echo "<tr><td nowrap='nowrap' colspan='3' height='10px'></td></tr>";
        echo "<tr><td colspan='3'>";
        echo "<table border='0' width='100%' cellpadding='0' cellspacing='4'><tr>".$this->DrawAvailabilityLegend(false)."</tr></table>";
        echo "</td></tr>";
        echo "</table>";

        // finish caching
        if($this->isCachingAllowed) $this->FinishCaching($cachefile);
    }

    /**
     * Draw small monthly calendar
     *
     */
    private function DrawMonthSmall($year = "", $month = "")
    {
        if($month == "") $month = $this->arrParameters['month'];
        if($year == "") $year = $this->arrParameters['year'];
        $week_rows = 0;
        $actday = 0;

        // today, first day and last day in month
        $firstDay = getdate(mktime(0,0,0,$month,1,$year));
        $lastDay  = getdate(mktime(0,0,0,$month+1,0,$year));

        //echo "<pre>";
        //print_r($this->arrRoomTypes);
        //echo "</pre>";
        // Get Rooms availability for current month
        $this->arrRoomAvailability = Rooms::GetRoomAvalibilityForMonth($this->arrRoomTypes, $year, $month);

        // get array of events for month
        $arrEventsList = $this->GetEventsListForMonth($year, $month);
        //echo "<pre>";
        //print_r($arrEventsList);
        //echo "</pre>";
        if($this->arrParameters["view_type"] == "monthly_small"){
            echo "<table class='table_navbar'>".$this->crLt;
            echo "<tr>";
            echo "<th class='tr_navbar'>";
            echo " <a href=\"javascript:__doPostBack('view', 'monthly_small', '".$this->prevMonth['year']."', '".$this->ConvertToDecimal($this->prevMonth['mon'])."', '".$this->ConvertToDecimal($this->prevMonth['mday'])."')\">&laquo;&laquo;</a> ";
            echo $this->arrParameters['month_full_name']." - ".$this->arrParameters['year'];
            echo " <a href=\"javascript:__doPostBack('view', 'monthly_small', '".$this->nextMonth['year']."', '".$this->ConvertToDecimal($this->nextMonth['mon'])."', '".$this->ConvertToDecimal($this->nextMonth['mday'])."')\">&raquo;&raquo;</a> ";
            echo "</th>".$this->crLt;
            echo "</tr>".$this->crLt;
            echo "</table>".$this->crLt;
        }

        // create a table with the necessary header informations
        echo "<table class='month_small'>".$this->crLt;
        echo "<tr class='tr_small_days'>".$this->crLt;
        if($this->isWeekNumberOfYear) echo "<td class='th_small_wn'></td>".$this->crLt;
        for($i = $this->weekStartedDay-1; $i < $this->weekStartedDay+6; $i++){
            echo "<td class='th_small'>".$this->arrWeekDays[($i % 7)]["short"]."</td>".$this->crLt;
        }
        echo "</tr>".$this->crLt;

        // display the first calendar row with correct positioning
        if ($firstDay['wday'] == 0) $firstDay['wday'] = 7;
        $max_empty_days = $firstDay['wday']-($this->weekStartedDay-1);
        if($max_empty_days < 7){
            echo "<tr class='tr_small' style='height:".$this->celHeight.";'>".$this->crLt;
            if($this->isWeekNumberOfYear){
                $parts = explode("-", (date("Y-m-d-W", mktime(0,0,0,$month,1-$max_empty_days,$year))));
                echo "<td class='td_small_wn'>";
                if($this->arrViewTypes["weekly"]["enabled"]){
                    echo "<a href=\"javascript:__doPostBack('view', 'weekly', '".$parts[0]."', '".$parts[1]."', '".$parts[2]."')\" title='".$this->lang['click_view_week']."'>".$parts[3]."</a>";
                }else{
                    echo $parts[3];
                }
                echo "</td>".$this->crLt;
            }
            for($i = 1; $i <= $max_empty_days; $i++){
                echo "<td class='td_small_empty'>&nbsp;</td>".$this->crLt;
            }
            for($i = $max_empty_days+1; $i <= 7; $i++){
                $actday++;
                $this->DrawMonthSmallCell($arrEventsList, $year, $month, $actday);
            }
            echo "</tr>".$this->crLt;
            $week_rows++;
        }

        // get how many complete weeks are in the actual month
        $fullWeeks = floor(($lastDay['mday']-$actday)/7);

        for($i=0;$i<$fullWeeks;$i++){
            echo "<tr class='tr_small' style='height:".$this->celHeight.";'>".$this->crLt;
            if($this->isWeekNumberOfYear){
                $parts = explode("-", (date("Y-m-d-W", mktime(0,0,0,$month,$actday+1,$year))));
                echo "<td class='td_small_wn'>";
                if($this->arrViewTypes["weekly"]["enabled"]){
                    echo "<a href=\"javascript:__doPostBack('view', 'weekly', '".$parts[0]."', '".$parts[1]."', '".$parts[2]."')\" title='".$this->lang['click_view_week']."'>".$parts[3]."</a>";
                }else{
                    echo $parts[3];
                }
                echo "</td>".$this->crLt;
            }
            for ($j=0;$j<7;$j++){
                $actday++;
                $this->DrawMonthSmallCell($arrEventsList, $year, $month, $actday);
            }
            echo "</tr>".$this->crLt;
            $week_rows++;
        }

        // now display the rest of the month
        if ($actday < $lastDay['mday']){
            echo "<tr class='tr_small' style='height:".$this->celHeight.";'>".$this->crLt;
            if($this->isWeekNumberOfYear){
                $parts = explode("-", (date("Y-m-d-W", mktime(0,0,0,$month,$actday+1,$year))));
                echo "<td class='td_small_wn'>";
                if($this->arrViewTypes["weekly"]["enabled"]){
                    echo "<a href=\"javascript:__doPostBack('view', 'weekly', '".$parts[0]."', '".$parts[1]."', '".$parts[2]."')\" title='".$this->lang['click_view_week']."'>".$parts[3]."</a>";
                }else{
                    echo $parts[3];
                }
                echo "</td>".$this->crLt;
            }
            for ($i=0; $i<7;$i++){
                $actday++;
                if($actday <= $lastDay['mday']){
                    $this->DrawMonthSmallCell($arrEventsList, $year, $month, $actday);
                }else{
                    echo "<td class='td_small_empty'>&nbsp;</td>".$this->crLt;
                }
            }
            echo "</tr>".$this->crLt;
            $week_rows++;
        }

        // complete last line
        if($week_rows < 5){
            echo "<tr class='tr_small' style='height:".$this->celHeight.";'>".$this->crLt;
            if($this->isWeekNumberOfYear) echo "<td class='td_small_wn'></td>".$this->crLt;
            for ($i=0; $i<7;$i++){
                echo "<td class='td_small_empty'>&nbsp;</td>".$this->crLt;
            }
            echo "</tr>".$this->crLt;
            $week_rows++;
        }

        echo "</table>".$this->crLt;
    }

    /**
     * Draw small month cell
     *
     */
    private function DrawMonthSmallCell(&$arrEventsList, $year, $month, $actday)
    {
        $arrEvents = $this->RemoveDuplications($arrEventsList[$this->ConvertToDecimal($actday)]);
        $events_count = (is_array($arrEventsList[$this->ConvertToDecimal($actday)])) ? count($arrEvents) : $arrEventsList[$this->ConvertToDecimal($actday)];
        $events_count_text = "";
        $daily_max_rooms = 0;
        $is_past = false;

        //echo '<pre>';
        //print_r($this->arrRoomTypes);
        //echo '</pre>';
        //zzz

        $curr_date = $year.$month.$this->ConvertToDecimal($actday);
        $today = date("Ymd");
        $two_years = (nights_diff($today, $curr_date) > 720) ? true : false;

        if($this->usedOn == 'room'){
            $is_past = ($curr_date < $today) ? true : false;
        }

        if(is_array($arrEventsList[$this->ConvertToDecimal($actday)])){
            $events_count_text = $actday." ".get_month_local($month)." ".$year."<br/> --------<br />";
            foreach($arrEventsList[$this->ConvertToDecimal($actday)] as $key => $val){
                if(isset($this->arrRooms[$key])){
                    if($this->shownRoomsType == 'left'){
                        $actday_decimal = $this->ConvertToDecimal($actday);

                        $max_allowed = isset($this->arrRoomAvailability[0]['availability'][$actday_decimal]) ? $this->arrRoomAvailability[0]['availability'][$actday_decimal] : 0;
                        if($max_allowed > 0){
                            $left_rooms = ($max_allowed - $val);
                            $events_count_text .= $this->arrRooms[$key]." - ".$left_rooms.' '.($left_rooms > 1 ? _ROOMS_LEFT : _ROOM_LEFT)."<br />";
                        }
                        $daily_max_rooms += $val;
                    }else{
                        $events_count_text .= $this->arrRooms[$key]." - ".$val."<br />";
                        $daily_max_rooms += $val;
                    }
                }
            }
        }

        if($events_count > 0){
            //$events_count_text = ($events_count > 0) ? " (".(($events_count > 1) ? $events_count." ".$this->lang['orders_lc'] : $events_count." ".$this->lang['order_lc']).")" : "";
            $onmouseover = '';
            if($is_past){
                $class = ' class="td_small_grey"';
            }else{
                if($this->usedOn == 'room'){
                    if($this->dailyMaxRooms <= $daily_max_rooms) $class = " class='td_small_fully'";
                    else if($daily_max_rooms > 0) $class = " class='td_small_partially'";
                }else{
                    if($this->dailyMaxRooms <= $daily_max_rooms) $class = " class='td_small_red'";
                    else $class = " class='td_small_full ".(($events_count < 10) ? "e".$events_count : "e10")."'";
                }
                $onmouseover = ' title="'.$events_count_text.'" style="cursor:help;" ';
            }
            echo "<td".$class.$onmouseover.">";
            if($this->arrViewTypes["daily"]["enabled"]){
                echo "<a href=\"javascript:__doPostBack('view', 'daily', '".$year."', '".$month."', '".$this->ConvertToDecimal($actday)."');\">$actday</a>";
            }else{
                echo $actday;
            }
            echo "</td>".$this->crLt;
        }elseif($events_count == 0){
            if($is_past){
                $class = ' class="td_small_grey"';
            }else{
                if(($actday == $this->arrToday['mday']) && ($this->arrToday['mon'] == $month) && ($this->arrToday['year'] == $year)) $class = " class='td_small_actday'";
                else $class = ($this->roomTypes != "") ? " class='td_small_green'" : " class='td_small_empty'";
            }
            echo "<td$class>$actday</td>".$this->crLt;
        }else{
            $class = (!$two_years) ? (empty($daily_max_rooms) ? " class='td_small_darkgray'" : " class='td_small_red'") : " class='td_small_green'";
            echo "<td$class>$actday</td>".$this->crLt;
        }
    }

    /**
     * Draw weekly calendar
     *
     */
    private function DrawWeek()
    {
        // start caching
        $cachefile = $this->cacheDir."weekly-".$this->arrParameters["selected_category"]."-".$this->arrParameters['year']."-".$this->arrParameters["month"]."-".$this->arrParameters["day"].".cch";
        if($this->isCachingAllowed){
            if($this->StartCaching($cachefile)) return true;
        }

        // today, first day and last day in month
        $firstDay = getdate(mktime(0,0,0,$this->currWeek['month'],1,$this->currWeek['year']));
        $lastDay  = getdate(mktime(0,0,0,$this->currWeek['month']+1,0,$this->currWeek['year']));

        $arrEventsList = $this->GetEventsListForMonth($this->arrParameters['year'], $this->arrParameters['month'], true);

        $this->arrRoomTypes = Rooms::GetRoomAvalibilityForWeek($this->arrRoomTypes, $this->currWeek['year'], $this->currWeek['month'], $this->currWeek["day"]);

        // Create a table with the necessary header informations
        echo "<table class='month' border='0'>".$this->crLt;
        echo "<tr>".$this->crLt;
        echo "<th colspan='7'>".$this->crLt;
        echo "<table border=0 width='100%'>".$this->crLt;
        echo "<tr>";
        echo "<th class='tr_navbar_".$this->defined_left."'>".$this->DrawDateJumper(false)."</th>";
        echo "<th class='tr_navbar' nowrap='nowrap'>";
        // draw Month Year - Month Year line
        if($this->currWeek['year'] != $this->nextWeek['year']){
            echo $this->prevWeek['month']." ".$this->currWeek['year']." - ".$this->nextWeek['month']." ".$this->nextWeek['year'];
        }else{
            $month = (int)$this->currWeek['month'];
            echo $this->arrMonths["$month"].(($month != $this->nextWeek['mon']) ? " - ".$this->nextWeek['month'] : "")." ".$this->currWeek['year'];
        }
        echo "</th>".$this->crLt;
        echo "<th class='tr_navbar_".$this->defined_right."'>				
				  <a href=\"javascript:__doPostBack('view', 'weekly', '".$this->prevWeek['year']."', '".$this->ConvertToDecimal($this->prevWeek['mon'])."', '".$this->ConvertToDecimal($this->prevWeek['mday'])."')\">".$this->ConvertToDecimal($this->prevWeek['mday']).$this->lang['th']." ".$this->prevWeek['month']."</a> |
				  <a href=\"javascript:__doPostBack('view', 'weekly', '".$this->nextWeek['year']."', '".$this->ConvertToDecimal($this->nextWeek['mon'])."', '".$this->ConvertToDecimal($this->nextWeek['mday'])."')\">".$this->ConvertToDecimal($this->nextWeek['mday']).$this->lang['th']." ".$this->nextWeek['month']."</a>
				  </th>".$this->crLt;
        echo "</tr>".$this->crLt;
        echo "</table>".$this->crLt;
        echo "</th>".$this->crLt;
        echo "</tr>".$this->crLt;
        echo "<tr class='tr_days'>".$this->crLt;
        for($i = $this->weekStartedDay-1; $i < $this->weekStartedDay+6; $i++){
            $week_day = date("w", mktime(0,0,0,$this->currWeek["month"],$this->currWeek["day"]+$i-($this->weekStartedDay-1),$this->currWeek["year"]));
            echo "<td class='th'>".$this->arrWeekDays[($week_day % 7)][$this->weekDayNameLength]."</td>";
        }
        echo "</tr>".$this->crLt;

        // Display the first calendar row with correct positioning
        echo "<tr>".$this->crLt;
        if($firstDay['wday'] == 0) $firstDay['wday'] = 7;
        $actday = 0;
        for($i = 0; $i <= 6; $i++){
            $parts = explode("-", date("d-n-Y", mktime(0,0,0,$this->currWeek["month"],$this->currWeek["day"]+$i,$this->currWeek["year"])));
            $actday = $parts[0];
            $actmon = $parts[1];
            $actyear = $parts[2];
            $week_day = date("w", mktime(0,0,0,$this->currWeek["month"],$this->currWeek["day"]+$i-($this->weekStartedDay-1),$this->currWeek["year"]));

            if(($actday == $this->arrToday['mday']) && ($this->arrToday['mon'] == $this->currWeek["month"])){
                $class = " class='td_actday_w'";
            }elseif($this->arrWeekDays[(($week_day+($this->weekStartedDay - 1)) % 7)]["short"] == "Sun"){
                $class = ($this->sundayColor) ? " class='td_sunday_w'" : " class='td_w'";
            }else{
                $class = " class='td_w'";
            }
            echo "<td$class>".$this->crLt;

            echo "<table width='100%' border='0' cellpadding='0' celspacing='0'>".$this->crLt;
            echo "<tr><td class='td_header' colspan='2'><b>";
            if($this->arrViewTypes["daily"]["enabled"]){
                echo "<a href=\"javascript:__doPostBack('view', 'daily', '".$this->currWeek["year"]."', '".$this->ConvertToDecimal($actmon)."', '".$actday."')\">".$this->arrMonths[$actmon]." $actday</a>";
            }else{
                echo $this->arrMonths[$actmon]." ".$actday;
            }
            echo "</b></td></tr>".$this->crLt;
            //$today = date("YmdHi");

            if($this->weekDisabledDay != "" && $this->weekDisabledDay == ($week_day+1)){
                // do nothing - this day is disabled
            }else{
                echo "<tr>";
                echo "<td class='td_w_d' align='left' valign='top'>";

                $curr_week_date = date("Ymd", mktime(0,0,0,$this->currWeek["month"],$this->currWeek["day"]+$i-($this->weekStartedDay-1),$this->currWeek["year"]));
                $today = date("Ymd");
                $is_past = ($today > $curr_week_date) ? true : false;
                $two_years = (nights_diff($today, $curr_week_date) > 720) ? true : false;

                $room_list = "";//"<div class='events_list_inline'>";
                foreach($this->arrRoomTypes as $room)
                {
                    $room_count = isset($room['availability'][$actday]) ? $room['availability'][$actday] : $room['room_count'];
                    $rooms_total = $room_count;

                    if($this->roomTypes != "" && $this->roomTypes != $room['id']) continue;
                    if(is_array($arrEventsList[$this->ConvertToDecimal($actday)]))
                    {
                        if(is_array($arrEventsList[$actday]) && array_key_exists($room['id'], $arrEventsList[$actday])){
                            if(isset($arrEventsList[$actday][$room['id']])){
                                $rooms_total = $rooms_total - $arrEventsList[$actday][$room['id']];
                            }
                        }
                        if(!isset($room['availability'][$actday]) || !$room['availability'][$actday]){
                            $room_color = ($is_past) ? $this->colorGray : $this->colorDarkGray;
                            $room_list .= "&#8226; <span style=color:".$room_color."><strike>".$room['room_type']."</strike></span><br />";
                        }else{
                            if($rooms_total == 0){
                                $room_color = ($is_past) ? $this->colorGray : $this->colorRed;
                                $room_list .= "&#8226; <span style=color:".$room_color."><strike>".$room['room_type']." ".$rooms_total."</strike></span><br />";
                            }elseif($rooms_total < $room['room_count']){
                                $room_color = ($is_past) ? $this->colorGray : $this->colorYellow;
                                $room_list .= "&#8226; <span style=color:".$room_color.">".$room['room_type']." ".$rooms_total."</span><br />";
                            }else{
                                $room_color = ($is_past) ? $this->colorGray : $this->colorGreen;
                                $room_list .= "&#8226; <span style=color:".$room_color.">".$room['room_type']." ".$rooms_total."</span><br />";
                            }
                        }
                    }else{
                        if(!$two_years && (!isset($room['availability'][$actday]) || !$room['availability'][$actday])){
                            $room_color = ($is_past) ? $this->colorGray : $this->colorDarkGray;
                            $room_list .= "&#8226; <span style=color:".$room_color."><strike>".$room['room_type']."</strike></span><br />";
                        }else{
                            $room_color = ($is_past) ? $this->colorGray : $this->colorGreen;
                            $room_list .= "&#8226; <span style=color:".$room_color.">".$room['room_type']." ".$rooms_total."</span><br />";
                        }
                    }
                }
                echo "<br />";
                echo $room_list;
                echo "<br />";

                echo "</td>";
                echo "</tr>".$this->crLt;

            }
            echo "</table>".$this->crLt;
            echo "</td>".$this->crLt;
        }
        echo "</tr>".$this->crLt;
        echo "</table>".$this->crLt;

        //$this->DrawAddEventForm();

        // finish caching
        if($this->isCachingAllowed) $this->FinishCaching($cachefile);
    }

    /**
     * Draw daily calendar
     *
     */
    private function DrawDay()
    {
        // start caching
        $cachefile = $this->cacheDir."daily-".$this->arrParameters["selected_category"]."-".$this->arrParameters['year']."-".$this->arrParameters["month"]."-".$this->arrParameters["day"].".cch";
        if($this->isCachingAllowed){
            if($this->StartCaching($cachefile)) return true;
        }

        $sql = "SELECT
					".DB_PREFIX."calendar.id,
					".DB_PREFIX."calendar.event_date,
					".DB_PREFIX."calendar.event_time,
					DATE_FORMAT(".DB_PREFIX."calendar.event_time, '%H:%i') as event_time_formatted,					
					".DB_PREFIX."events.name,
					".DB_PREFIX."events.description
				FROM ".DB_PREFIX."calendar
					INNER JOIN ".DB_PREFIX."events ON ".DB_PREFIX."calendar.event_id = ".DB_PREFIX."events.id
				WHERE
					".DB_PREFIX."calendar.event_date = '".$this->arrParameters['year']."-".$this->arrParameters['month']."-".$this->arrParameters['day']."'
					".$this->PrepareWhereClauseEventTime()."
					".(($this->userID) ? " AND ".DB_PREFIX."calendar.user_id = ".(int)$this->userID : "")."
				ORDER BY ".DB_PREFIX."calendar.id ASC";

        $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
        if($this->isDebug) $this->arrSQLs[] =  $sql."<br />".database_error()."<br />";

        // prepare time blocks array
        $arrEvents = array();

        if($this->timeSlot == "15"){
            for($i_hour=$this->fromHour; $i_hour<$this->toHour; $i_hour++){
                for($i_block=0; $i_block<4; $i_block++){
                    $hours_part = $i_hour;
                    $minutes_part = ($i_block)*$this->timeSlot;
                    $this->PrepareMinutesHoures($minutes_part, $hours_part);
                    $ind = $this->ConvertToDecimal($hours_part).":".$minutes_part;
                    $arrEvents[$ind] = array();
                }
            }
        }elseif($this->timeSlot == "30"){
            for($i_hour=$this->fromHour; $i_hour<$this->toHour; $i_hour++){
                for($i_block=0; $i_block<2; $i_block++){
                    $hours_part = $i_hour;
                    $minutes_part = ($i_block)*$this->timeSlot;
                    $this->PrepareMinutesHoures($minutes_part, $hours_part);
                    $ind = $this->ConvertToDecimal($hours_part).":".$minutes_part;
                    $arrEvents[$ind] = array();
                }
            }
        }elseif($this->timeSlot == "45"){
            $blocks_count = 0;
            for($i_hour=$this->fromHour; $i_hour<$this->toHour; $i_hour++){
                for($i_block=0; $i_block<4; $i_block++, $blocks_count++){
                    if($blocks_count % 3 == 0){
                        $hours_part = $i_hour;
                        $minutes_part = ($i_block)*15;
                        $this->PrepareMinutesHoures($minutes_part, $hours_part);
                        $ind = $this->ConvertToDecimal($hours_part).":".$minutes_part;
                        $arrEvents[$ind] = array();
                    }
                }
            }
        }else{
            for($i_hour=$this->fromHour; $i_hour<$this->toHour; $i_hour++){
                $ind = $this->ConvertToDecimal($i_hour).":00";
                $arrEvents[$ind] = array();
            }
        }

        foreach($result[0] as $key => $val){
            $arrEvents[$val['event_time_formatted']][] = array("id"=>$val['id'], "name"=>$val['name'], "description"=>$val['description'], "color"=>$val['color']);
        }

        ///echo "<pre>";
        ///print_r($arrEvents);
        ///echo "</pre>";

        // Create a table with the necessary header informations
        echo "<table class='day_navigation' width='100%' border='0' cellpadding='0' celspacing='0'>".$this->crLt;
        echo "<tr>";
        echo "<th class='tr_navbar_".$this->defined_left."'>
			  ".$this->DrawDateJumper(false)."	
			  </th>".$this->crLt;
        echo "<th class='tr_navbar'>";
        echo " <a href=\"javascript:__doPostBack('view', 'daily', '".$this->prevDay['year']."', '".$this->ConvertToDecimal($this->prevDay['mon'])."', '".$this->ConvertToDecimal($this->prevDay['mday'])."')\">&laquo;&laquo;</a> ";
        echo $this->arrParameters["weekday"]." - ".$this->arrParameters['month_full_name']." ".$this->arrParameters['day'].", ".$this->arrParameters['year'];
        echo " <a href=\"javascript:__doPostBack('view', 'daily', '".$this->nextDay['year']."', '".$this->ConvertToDecimal($this->nextDay['mon'])."', '".$this->ConvertToDecimal($this->nextDay['mday'])."')\">&raquo;&raquo;</a> ";
        echo "</th>".$this->crLt;
        echo "<th class='tr_navbar_".$this->defined_right."' colspan='2'>				
			  <a href=\"javascript:__doPostBack('view', 'daily', '".$this->prevWeek['year']."', '".$this->ConvertToDecimal($this->prevWeek['mon'])."', '".$this->ConvertToDecimal($this->prevWeek['mday'])."')\">".$this->ConvertToDecimal($this->prevWeek['mday'])."th ".$this->prevWeek['month']."</a> |
			  <a href=\"javascript:__doPostBack('view', 'daily', '".$this->nextWeek['year']."', '".$this->ConvertToDecimal($this->nextWeek['mon'])."', '".$this->ConvertToDecimal($this->nextWeek['mday'])."')\">".$this->ConvertToDecimal($this->nextWeek['mday'])."th ".$this->nextWeek['month']."</a>
			  </th>".$this->crLt;
        echo "</tr>".$this->crLt;
        echo "</table>".$this->crLt;

        echo "<table class='day' cellpadding='0' celspacing='0'>".$this->crLt;
        $today = date("YmdHi");
        foreach($arrEvents as $key_hour => $val_arr){
            $key_hour_parts = explode(":", $key_hour);
            $hour_part = isset($key_hour_parts[0]) ? $key_hour_parts[0] : "00";
            $minute_part = isset($key_hour_parts[1]) ? $key_hour_parts[1] : "00";
            if(($hour_part >= $this->fromHour) && ($hour_part < $this->toHour)){
                if($this->ConvertToDecimal($key_hour) == $this->arrToday['hours']) {
                    $td_acthour_d = " class='td_acthour_d_h'";
                    $td_d = " class='td_acthour_d'";
                } else {
                    $td_acthour_d = " class='td_d_h'";
                    $td_d = " class='td_d'";
                }
                echo "<tr>";
                echo (($this->isShowTimes) ? "<td".$td_acthour_d."><b>".$this->ConvertToHour($hour_part, $minute_part, true)."</b></td>" : "");
                echo "<td".$td_d.">";
                if($this->weekDisabledDay != "" && $this->weekDisabledDay == ($this->arrParameters["wday"]+1)){
                    // do nothing - this day is disabled
                }else{
                    $current_date = $this->arrParameters['year'].$this->arrParameters['month'].$this->arrParameters['day'].str_replace(":", "", $key_hour);
                    $events_list = $this->GetEventsListForHour($arrEvents[$key_hour]);
                    $allow_add_event = false;
                    if($this->arrEventsOperations['add']){
                        if(!$this->allowEventsMultipleOccurrences){
                            if(!$events_list) $allow_add_event = true;
                        }else{
                            if($this->allowEditingEventsInPast || (!$this->allowEditingEventsInPast && ($current_date > $today))){
                                $allow_add_event = true;
                            }
                        }
                    }
                    if($allow_add_event) echo " <a href=\"javascript:__CallAddEventForm('divAddEvent', '".$this->arrParameters["year"]."', '".$this->arrParameters["month"]."', '".$this->arrParameters["day"]."', '".$key_hour."', ".$this->disableEarlierHours.");\" title='".$this->lang['add_new_event']."'>+</a> ";
                    echo $events_list;
                }
                echo "</td>";
                echo "</tr>".$this->crLt;
            }
        }
        echo "</table>".$this->crLt;

        $this->DrawAddEventForm();

        // finish caching
        if($this->isCachingAllowed) $this->FinishCaching($cachefile);
    }


    ////////////////////////////////////////////////////////////////////////////
    // EVENTS
    /**
     * Draw single event adding form
     *
     */
    private function DrawAddEventForm(){
        // draw Add Event Form from template
        $sql = "SELECT id, name, description
				FROM ".DB_PREFIX."events
				ORDER BY id ASC
				LIMIT 0, 1000";

        $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);

        $content = file_get_contents($this->calUrl."template/add_event_form.tpl");

        if($content !== false){
            $content = str_replace("{h:on_mousedown_header}", (($this->addEventFormType == "floating") ? " onmousedown='grab(this, event)'" : ""), $content);
            $content = str_replace("{h:class_move}", (($this->addEventFormType == "floating") ? " move" : ""), $content);
            $content = str_replace("{h:on_mousedown_body}", (($this->addEventFormType == "floating") ? " onmousedown='grab(false, event);'" : ""), $content);
            $content = str_replace("{h:ddl_from}", $this->DrawDateTime("from", $this->arrParameters["year"], $this->arrParameters["month"], $this->arrParameters["day"], $this->arrToday['hours'], false), $content);
            $content = str_replace("{h:ddl_to}", $this->DrawDateTime("to", $this->arrParameters["year"], $this->arrParameters["month"], $this->arrParameters["day"], $this->arrToday['hours'], false), $content);
            $content = str_replace("{h:lan_event_name}", $this->lang['event_name'], $content);
            $content = str_replace("{h:lan_event_description}", $this->lang['event_description'], $content);
            $content = str_replace("{h:lan_from}", $this->lang['from'], $content);
            $content = str_replace("{h:lan_to}", $this->lang['to'], $content);
            $content = str_replace("{h:lan_add_event}", $this->lang['add_event'], $content);
            $content = str_replace("{h:lan_close}", $this->lang['close'], $content);
            $content = str_replace("{h:lan_add_new_event}", $this->lang['add_new_event'], $content);

            $content = str_replace("{h:ddl_event_name}", $this->DrawEventsDDL("", "onchange='javascript:__EventSelectedDDL(2, ".$this->timeSlot.");'", "display:none;"), $content);
            //$content = str_replace("{h:ddl_category_name}", $this->DrawCategoriesDDL("", "onchange='javascript:__EventSelectedDDL(1, ".$this->timeSlot.");'"), $content);
            echo $content;
        }
    }

    /**
     * Draw events additing form
     *
     */
    private function DrawEventsAddForm()
    {
        // draw Add Event Form from template
        $content = file_get_contents($this->calUrl."template/events_add_form.tpl");

        $separators = 0;
        $legend = "<legend class='cal_legend'>";
        if($this->arrEventsOperations['manage']){ $separators++; $legend .= "<a href=\"javascript:__doPostBack('view', '".$this->arrParameters["view_type"]."', '".$this->arrParameters['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."', 'events_management')\">".$this->lang['events_management']."</a>"; }
        if($this->arrEventsOperations['add']){ if($separators++ > 0) $legend .= " :: "; $legend .= "<a href=\"javascript:__doPostBack('view', '".$this->arrParameters["view_type"]."', '".$this->arrParameters['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."', 'events_add')\"><strong><u>".$this->lang['add_event']."</u></strong></a>"; }
        if($this->arrEventsOperations['delete_by_range']){ if($separators++ > 0) $legend .= " :: "; $legend .= " :: <a href=\"javascript:__doPostBack('view', '".$this->arrParameters["view_type"]."', '".$this->arrParameters['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."', 'events_by_range')\">".$this->lang['delete_by_range']."</a>"; }
        $legend .= "</legend>";

        $content = str_replace("{h:legend}", $legend, $content);
        $content = str_replace("{h:lan_event_name}", $this->lang['event_name'], $content);
        $content = str_replace("{h:lan_event_description}", $this->lang['event_description'], $content);
        $content = str_replace("{h:lan_or}", $this->lang['or'], $content);
        $content = str_replace("{h:lan_add_event}", "+ ".$this->lang['add_new_event'], $content);
        $content = str_replace("{h:lan_cancel}", $this->lang['cancel'], $content);
        $content = str_replace("{h:lan_from}", $this->lang['from'], $content);
        $content = str_replace("{h:lan_to}", $this->lang['to'], $content);
        $content = str_replace("{h:lan_or}", $this->lang['or'], $content);
        $content = str_replace("{h:lan_add_event_to_list}", $this->lang['lbl_add_event_to_list'], $content);
        $content = str_replace("{h:lan_add_event_occurrences}", $this->lang['lbl_add_event_occurrences'], $content);
        $content = str_replace("{h:ddl_from}", $this->DrawDateTime("from", $this->arrToday["year"], $this->ConvertToDecimal($this->arrToday["mon"]), $this->ConvertToDecimal($this->arrToday["mday"]), $this->arrToday['hours'], false, true), $content);
        $content = str_replace("{h:ddl_to}", $this->DrawDateTime("to", $this->arrToday["year"], $this->ConvertToDecimal($this->arrToday["mon"]), $this->ConvertToDecimal($this->arrToday["mday"]), $this->arrToday['hours'], false, true), $content);

        $content = str_replace("{h:lan_repeat_every}", $this->lang['repeat_every'], $content);
        $content = str_replace("{h:lan_hours}", $this->lang['hours'], $content);
        $content = str_replace("{h:lan_repeatedly}", $this->lang['repeatedly'], $content);
        $content = str_replace("{h:lan_one_time}", $this->lang['one_time'], $content);
        $content = str_replace("{h:lan_sun}", $this->lang['sun'], $content);
        $content = str_replace("{h:lan_mon}", $this->lang['mon'], $content);
        $content = str_replace("{h:lan_tue}", $this->lang['tue'], $content);
        $content = str_replace("{h:lan_wed}", $this->lang['wed'], $content);
        $content = str_replace("{h:lan_thu}", $this->lang['thu'], $content);
        $content = str_replace("{h:lan_fri}", $this->lang['fri'], $content);
        $content = str_replace("{h:lan_sat}", $this->lang['sat'], $content);
        $content = str_replace("{h:ddl_from_date}", $this->DrawDate("from_date", $this->arrToday["year"], $this->ConvertToDecimal($this->arrToday["mon"]), $this->ConvertToDecimal($this->arrToday["mday"]), false), $content);
        $content = str_replace("{h:ddl_to_date}", $this->DrawDate("to_date", $this->arrToday["year"], $this->ConvertToDecimal($this->arrToday["mon"]), $this->ConvertToDecimal($this->arrToday["mday"]), false), $content);
        $content = str_replace("{h:ddl_from_time}", $this->DrawTime("from_time", $this->arrToday['hours'], false), $content);
        $content = str_replace("{h:ddl_to_time}", $this->DrawTime("to_time", $this->arrToday['hours'], false), $content);


        #// display categories if allowed
        #if($this->arrCatOperations['manage']){
        #	$content = str_replace("{h:lan_category_name}", $this->lang['category_name'].":<br />", $content);
        #	$content = str_replace("{h:ddl_categories}", $this->DrawCategoriesDDL("", "onchange='javascript:__CategoryOnChange(this.value, ".$this->timeSlot.")'"), $content);
        #}else{
        #	$content = str_replace("{h:lan_category_name}", "", $content);
        #	$content = str_replace("{h:ddl_categories}", "", $content);
        #}

        if($content !== false){
            echo $content;
        }
    }

    /**
     * Draw events editing form
     *
     */
    private function DrawEventsEditForm($event_id)
    {
        // draw Edit Event Form from template
        $content = file_get_contents($this->calUrl."template/events_edit_form.tpl");

        $content = str_replace("{h:lan_event_name}", $this->lang['event_name'], $content);
        $content = str_replace("{h:lan_event_description}", $this->lang['event_description'], $content);
        $content = str_replace("{h:lan_or}", $this->lang['or'], $content);
        $content = str_replace("{h:lan_cancel}", $this->lang['cancel'], $content);
        $content = str_replace("{h:lan_edit_event}", $this->lang['edit_event'], $content);
        $content = str_replace("{h:lan_update_event}", $this->lang['update_event'], $content);

        if($content !== false){
            if(!$this->allowEventsMultipleOccurrences){
                $sql  = "SELECT
							".DB_PREFIX."events.id,
							".DB_PREFIX."events.name,
							".DB_PREFIX."events.description,
							".DB_PREFIX."events.category_id, 
							".DB_PREFIX."calendar.event_date, 
							".DB_PREFIX."calendar.event_time 
						FROM ".DB_PREFIX."events
							LEFT OUTER JOIN ".DB_PREFIX."calendar ON ".DB_PREFIX."events.id = ".DB_PREFIX."calendar.event_id ";
            }else{
                $sql  = "SELECT
							".DB_PREFIX."events.id,
							".DB_PREFIX."events.name,
							".DB_PREFIX."events.description,
							".DB_PREFIX."events.category_id 
						FROM ".DB_PREFIX."events";
            }
            $sql .= "WHERE ".DB_PREFIX."events.id = ".(int)$event_id;

            $result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
            if(!empty($result)){
                $content = str_replace("{h:event_id}", $result["id"], $content);
                $content = str_replace("{h:event_name}", $this->PrepareFormatedText($result["name"]), $content);
                $content = str_replace("{h:event_description}", $result["description"], $content);

                // display event datetime info, if allowed
                if(!$this->allowEventsMultipleOccurrences){
                    $content = str_replace("{h:lan_event_date}", $this->lang['event_date'].":", $content);
                    $content = str_replace("{h:lan_start_time}", $this->lang['start_time'].":", $content);
                    $content = str_replace("{h:event_date}", $result['event_date'], $content);
                    $content = str_replace("{h:event_time}", $result['event_time'], $content);
                }else{
                    $arr_clean = array("{h:lan_event_date}","{h:lan_start_time}","{h:event_date}","{h:event_time}");
                    $content = str_replace($arr_clean, "", $content);
                }

                #// display categories, if allowed
                #if($this->arrCatOperations['manage']){
                #	$content = str_replace("{h:lan_category_name}", $this->lang['category_name'].":<br />", $content);
                #	$content = str_replace("{h:ddl_categories}", $this->DrawCategoriesDDL($result["category_id"], ""), $content);
                #}else{
                #	$content = str_replace("{h:lan_category_name}", "", $content);
                #	$content = str_replace("{h:ddl_categories}", "", $content);
                #}

                echo $content;
            }
        }
    }

    /**
     * Draw events details form
     *
     */
    private function DrawEventsDetailsForm($event_id)
    {
        // draw Details Event Form from template
        $content = file_get_contents($this->calUrl."template/events_details_form.tpl");

        $content = str_replace("{h:lan_event_name}", $this->lang['event_name'], $content);
        $content = str_replace("{h:lan_event_description}", $this->lang['event_description'], $content);
        $content = str_replace("{h:lan_event_details}", $this->lang['event_details'], $content);
        $content = str_replace("{h:lan_back}", $this->lang['back'], $content);

        if($content !== false){
            if(!$this->allowEventsMultipleOccurrences){
                $sql  = "SELECT
							".DB_PREFIX."events.id,
							".DB_PREFIX."events.name,
							".DB_PREFIX."events.description,
							".DB_PREFIX."events.category_id,
							".DB_PREFIX."calendar.event_date, 
							".DB_PREFIX."calendar.event_time 
						FROM ".DB_PREFIX."events
							LEFT OUTER JOIN ".DB_PREFIX."calendar ON ".DB_PREFIX."events.id = ".DB_PREFIX."calendar.event_id ";
            }else{
                $sql  = "SELECT
							".DB_PREFIX."events.id,
							".DB_PREFIX."events.name,
							".DB_PREFIX."events.description,
							".DB_PREFIX."events.category_id
						FROM ".DB_PREFIX."events ";
            }
            $sql .= "WHERE ".DB_PREFIX."events.id = ".(int)$event_id;

            $result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
            if(!empty($result)){
                /// $content = str_replace("{h:event_id}", $result["id"], $content);
                $content = str_replace("{h:event_name}", $result["name"], $content);
                $content = str_replace("{h:event_description}", $result["description"], $content);
                ///if($this->arrParameters["previous_action"] == "events_management"){
                $content = str_replace("{h:js_back_function}", '__EventsBack("events_management")', $content);
                ///}else{
                ///	$content = str_replace("{h:js_back_function}", '__EventsBack("list_view")', $content);
                ///}

                // display event datetime info, if allowed
                if(!$this->allowEventsMultipleOccurrences){
                    $content = str_replace("{h:lan_event_date}", $this->lang['event_date'].":", $content);
                    $content = str_replace("{h:lan_start_time}", $this->lang['start_time'].":", $content);
                    $content = str_replace("{h:event_date}", $result['event_date'], $content);
                    $content = str_replace("{h:event_time}", $result['event_time'], $content);
                }else{
                    $arr_clean = array("{h:lan_event_date}","{h:lan_start_time}","{h:event_date}","{h:event_time}");
                    $content = str_replace($arr_clean, "", $content);
                }

                #// display categories, if allowed
                #if($this->arrCatOperations['manage']){
                #	$content = str_replace("{h:lan_category_name}", $this->lang['category_name'].":", $content);
                #	$content = str_replace("{h:category_name}", $result['category_name'], $content);
                #}else{
                #	$content = str_replace("{h:lan_category_name}", "", $content);
                #	$content = str_replace("{h:category_name}", "", $content);
                #}
            }
        }
        echo $content;
    }

    /**
     * Draw events by range
     *
     */
    private function DrawDeleteEventsByRange()
    {
        $content = file_get_contents($this->calUrl."template/events_delete_by_range.tpl");

        $separators = 0;
        $legend = "<legend class='cal_legend'>";
        if($this->arrEventsOperations['manage']){ $separators++; $legend .= "<a href=\"javascript:__doPostBack('view', '".$this->arrParameters["view_type"]."', '".$this->arrParameters['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."', 'events_management')\">".$this->lang['events_management']."</a>"; }
        if($this->arrEventsOperations['add']){ if($separators++ > 0) $legend .= " :: "; $legend .= "<a href=\"javascript:__doPostBack('view', '".$this->arrParameters["view_type"]."', '".$this->arrParameters['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."', 'events_add')\">".$this->lang['add_event']."</a>"; }
        if($this->arrEventsOperations['delete_by_range']){ if($separators++ > 0) $legend .= " :: "; $legend .= " :: <a href=\"javascript:__doPostBack('view', '".$this->arrParameters["view_type"]."', '".$this->arrParameters['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."', 'events_by_range')\"><strong><u>".$this->lang['delete_by_range']."</u></strong></a>"; }
        $legend .= "</legend>";

        $content = str_replace("{h:legend}", $legend, $content);
        $content = str_replace("{h:lan_category_name}", $this->lang['category_name'].":<br />", $content);
        ///$content = str_replace("{h:ddl_categories}", $this->DrawCategoriesDDL("", "onchange='javascript:__CategoryOnChange(this.value, ".$this->timeSlot.")'"), $content);

        $content = str_replace("{h:lan_from}", $this->lang['from'], $content);
        $content = str_replace("{h:lan_to}", $this->lang['to'], $content);
        $content = str_replace("{h:ddl_from}", $this->DrawDate("from", $this->arrToday["year"], $this->ConvertToDecimal($this->arrToday["mon"]), $this->ConvertToDecimal($this->arrToday["mday"]), false), $content);
        $content = str_replace("{h:ddl_to}", $this->DrawDate("to", $this->arrToday["year"], $this->ConvertToDecimal($this->arrToday["mon"]), $this->ConvertToDecimal($this->arrToday["mday"]), false), $content);

        $content = str_replace("{h:lan_or}", $this->lang['or'], $content);
        $content = str_replace("{h:lan_cancel}", $this->lang['cancel'], $content);
        $content = str_replace("{h:lan_delete_events}", $this->lang['delete_events'], $content);

        echo $content;
    }

    /**
     * Draw events management
     */
    private function DrawEventsManagement()
    {
        $hid_sort_by = ($this->GetParameter("hid_sort_by") != "") ? $this->GetParameter("hid_sort_by") : "name";
        $hid_sort_direction = ($this->GetParameter("hid_sort_direction") != "") ? $this->GetParameter("hid_sort_direction") : "ASC";
        if(strtolower($hid_sort_direction) == "asc") $next_sort_direction = "desc";
        else $next_sort_direction = "asc";

        $sql = "SELECT COUNT(*) as cnt FROM ".DB_PREFIX."events";
        $result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
        $total_records = $result['cnt'];
        $page_size = 20;
        $total_pages = (int)$result['cnt']/$page_size;
        $total_pages_partualy = $result['cnt'] % $page_size;
        if($total_pages_partualy != 0) $total_pages +=1;

        $limit_start = ($this->arrParameters["page"]-1) * $page_size;
        if($limit_start < 0) $limit_start = "0";
        $limit_end = $page_size;

        if(!$this->allowEventsMultipleOccurrences){
            $sql  = "SELECT
						".DB_PREFIX."events.id,
						".DB_PREFIX."events.name,
						".DB_PREFIX."events.description,
						MIN(".DB_PREFIX."calendar.event_date) as event_date,
						(SELECT c1.event_time FROM ".DB_PREFIX."calendar c1 WHERE c1.event_id = ".DB_PREFIX."events.id AND c1.event_date = MIN(".DB_PREFIX."calendar.event_date) ORDER BY c1.event_time ASC LIMIT 0,1) as event_time
					FROM ".DB_PREFIX."events
						LEFT OUTER JOIN ".DB_PREFIX."calendar ON ".DB_PREFIX."events.id = ".DB_PREFIX."calendar.event_id
					GROUP BY ".DB_PREFIX."events.id ";
        }else{
            $sql  = "SELECT
						".DB_PREFIX."events.id,
						".DB_PREFIX."events.name,
						".DB_PREFIX."events.description,
					FROM ".DB_PREFIX."events ";
        }
        $sql .= "ORDER BY ".$hid_sort_by." ".$hid_sort_direction." ";
        $sql .= "LIMIT ".$limit_start.", ".$limit_end."";

        $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
        $content = file_get_contents($this->calUrl."template/events_management_row.tpl");

        echo "<fieldset class='cal_fieldset'>";
        $separators = 0;
        $legend = "<legend class='cal_legend'>";
        if($this->arrEventsOperations['manage']){ $separators++; $legend .= "<a href=\"javascript:__doPostBack('view', '".$this->arrParameters["view_type"]."', '".$this->arrParameters['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."', 'events_management')\"><strong><u>".$this->lang['events_management']."</u></strong></a>"; }
        if($this->arrEventsOperations['add']){ if($separators++ > 0) $legend .= " :: "; $legend .= "<a href=\"javascript:__doPostBack('view', '".$this->arrParameters["view_type"]."', '".$this->arrParameters['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."', 'events_add')\">".$this->lang['add_event']."</a>"; }
        if($this->arrEventsOperations['delete_by_range']){ if($separators++ > 0) $legend .= " :: "; $legend .= " :: <a href=\"javascript:__doPostBack('view', '".$this->arrParameters["view_type"]."', '".$this->arrParameters['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."', 'events_by_range')\">".$this->lang['delete_by_range']."</a>"; }
        $legend .= "</legend>";
        echo $legend;

        echo "<table align='center' border='0' width='100%'>
			  <tr><td colspan='6' nowrap='nowrap' height='3px'></td></tr>
			  <tr>
				<th width='15px'></th>
				<th align='left'><a href=\"javascript:__EventsSort('name', '".$next_sort_direction."')\">".$this->lang['event_name']."</a></th>";
        ///<th align='left'>".$this->lang['event_description']."</th>
        // display categories if allowed
        if($this->arrCatOperations['manage']){
            echo "<th align='left'><a href=\"javascript:__EventsSort('category_name', '".$next_sort_direction."')\">".$this->lang['category_name']."</a></th>";
        }else{
            echo "<th></th>";
        }
        // show event's datetime info, if allowed
        if(!$this->allowEventsMultipleOccurrences){
            echo "<th align='center' width='90px'><a href=\"javascript:__EventsSort('event_date', '".$next_sort_direction."')\">".$this->lang['event_date']."</a></th>";
            echo "<th align='center' width='90px'><a href=\"javascript:__EventsSort('event_time', '".$next_sort_direction."')\">".$this->lang['start_time']."</a></th>";
        }else{
            echo "<th></th>";
            echo "<th></th>";
        }
        echo "<th align='center' width='140px'>".((!$this->arrEventsOperations['edit'] && !$this->arrEventsOperations['details'] && !$this->arrEventsOperations['delete']) ? "" : $this->lang['actions'])."</th>
			  </tr>
			  <tr><td colspan='6' nowrap='nowrap' height='2px'></td></tr>";
        $events_count = 0;
        foreach($result[0] as $key => $val){
            $temp_content = $content;
            $events_count++;

            $drawed_buttons = 0;
            $edit_button = "";
            $details_button = "";
            $delete_button = "";
            if($this->arrEventsOperations['details']){ $details_button = (($drawed_buttons > 0) ? " | " : "")."<a href='javascript:__EventsDetails({h:event_id});'>".$this->lang['details']."</a>"; $drawed_buttons++; }
            if($this->arrEventsOperations['edit'])   { $edit_button    = (($drawed_buttons > 0) ? " | " : "")."<a href='javascript:__EventsEdit({h:event_id});'>".$this->lang['edit']."</a>"; $drawed_buttons++; }
            if($this->arrEventsOperations['delete']) { $delete_button  = (($drawed_buttons > 0) ? " | " : "")."<a href='javascript:void(0);' onclick='__EventsDelete({h:event_id});'>".$this->lang['delete']."</a>"; $drawed_buttons++; }

            $temp_content = str_replace("{h:edit_button}", $edit_button, $temp_content);
            $temp_content = str_replace("{h:details_button}", $details_button, $temp_content);
            $temp_content = str_replace("{h:delete_button}", $delete_button, $temp_content);

            $description = (strlen($val["description"]) > 50) ? substr($val["description"], 0, 50)."..." : $val["description"];
            $temp_content = str_replace("{h:event_id}", $val["id"], $temp_content);
            $temp_content = str_replace("{h:event_num}", $limit_start+$events_count.". ", $temp_content);
            $temp_content = str_replace("{h:event_name}", "<span style='color:".$val["color"].";'>".$val["name"]."</span>", $temp_content);
            ///$temp_content = str_replace("{h:event_description}", $description, $temp_content);
            // display categories if allowed
            if($this->arrCatOperations['manage']){
                $temp_content = str_replace("{h:event_category}", $val["category_name"], $temp_content);
            }else{
                $temp_content = str_replace("{h:event_category}", "", $temp_content);
            }
            // show event's datetime info, if allowed
            if(!$this->allowEventsMultipleOccurrences){
                $temp_content = str_replace("{h:event_date}", $val["event_date"], $temp_content);
                $temp_content = str_replace("{h:event_time}", $val["event_time"], $temp_content);
            }else{
                $temp_content = str_replace(array("{h:event_date}","{h:event_time}"), "", $temp_content);
            }
            echo $temp_content;
        }
        echo "<tr><td colspan='6' style='border-bottom:1px solid #eeeeee;'>&nbsp;</td></tr>";
        echo "<tr>";
        echo "<td align='left' style='padding-left:10px' colspan='5'>".$this->lang['pages'].": ";
        for($i_pages = 1; $i_pages <= $total_pages; $i_pages++){
            if($i_pages > 1) echo ", ";
            $i_pages_a = ($i_pages == $this->arrParameters["page"]) ? "<strong>".$i_pages."</strong>" : $i_pages;
            echo "<a href=\"javascript:__doPostBack('view', '".$this->arrParameters["view_type"]."', '".$this->arrParameters['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."', 'events_management', '', '".$i_pages."');\">".$i_pages_a."</a>";
        }
        echo "</td>";
        echo "<td align='right' style='padding-right:10px'>".$this->lang['total_events'].": ".$total_records."</td>";
        echo "</tr>";
        echo "</table>
		      </fieldset>";
    }

    /**
     * Draws availability legend
     * @param $draw
     */
    private function DrawAvailabilityLegend($draw = true)
    {
        $output = "<td width='60px'>".$this->lang['legend'].": </td>								
        <td width='120px'>
            <div style='border:1px solid #ccc; background-color:".(($this->arrParameters["view_type"] == "yearly") ? "#ccffcc" : $this->colorGreen).";display:block;width:15px;height:14px;float:".$this->defined_left.";'></div>
            &nbsp;- ".$this->lang['all_available']."
        </td>								
        <td width='140px'><div style='border:1px solid #ccc; background-color:#ffcc00;display:block;width:15px;height:14px;float:".$this->defined_left.";'></div>
            &nbsp;- ".$this->lang['partially_booked']."
        </td>
        <td width='130px'><div style='border:1px solid #ccc; background-color:#777777;display:block;width:15px;height:14px;float:".$this->defined_left.";'></div>
            &nbsp;- ".$this->lang['not_avaliable']."
        </td>								
        <td><div style='border:1px solid #ccc; background-color:".(($this->arrParameters["view_type"] == "yearly") ? "#ff6600" : "#db4c0d").";display:block;width:15px;height:14px;float:".$this->defined_left.";'></div>
            &nbsp;- ".$this->lang['fully_booked']."
        </td>";

        if($draw) echo $output;
        else return $output;
    }

    ////////////////////////////////////////////////////////////////////////////
    // CATEGORIES

    /**
     * Draw categories additing form
     *
     */
    private function DrawCategoriesAddForm()
    {
        // draw Add Event Form from template
        $content = file_get_contents($this->calUrl."template/categories_add_form.tpl");

        $legend = "<legend class='cal_legend'>
					<a href=\"javascript:__doPostBack('view', '".$this->arrParameters["view_type"]."', '".$this->arrParameters['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."', 'categories_management')\">".$this->lang['events_categories']."</a> ::
					<a href=\"javascript:__doPostBack('view', '".$this->arrParameters["view_type"]."', '".$this->arrParameters['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."', 'categories_add')\"><strong><u>".$this->lang['add_category']."</u></strong></a>
			  </legend>";

        $content = str_replace("{h:legend}", $legend, $content);
        $content = str_replace("{h:lan_cat_name}", $this->lang['category_name'], $content);
        $content = str_replace("{h:lan_cat_description}", $this->lang['category_description'], $content);
        $content = str_replace("{h:lan_add_category}", "+ ".$this->lang['add_new_category'], $content);
        $content = str_replace("{h:lan_cancel}", $this->lang['cancel'], $content);
        $content = str_replace("{h:lan_or}", $this->lang['or'], $content);
        $content = str_replace("{h:lan_duration}", $this->lang['duration'], $content);

        $content = str_replace("{h:ddl_durations}", $this->DrawCategoryDurations("", true), $content);

        if($this->arrCatOperations['allow_colors']){
            $content = str_replace("{h:lan_cat_color}", $this->lang['category_color'].":", $content);
            $content = str_replace("{h:ddl_colors}", $this->DrawColors("", true, true), $content);
        }else{
            $content = str_replace("{h:lan_cat_color}", "", $content);
            $content = str_replace("{h:ddl_colors}", "", $content);
        }

        if($content !== false){
            echo $content;
        }
    }

    /**
     * Draw categories edit form
     *
     */
    private function DrawCategoriesEditForm($category_id)
    {
        // draw Details Event Form from template
        $content = file_get_contents($this->calUrl."template/categories_edit_form.tpl");

        $content = str_replace("{h:lan_edit_category}", $this->lang['edit_category'], $content);
        $content = str_replace("{h:lan_category_name}", $this->lang['category_name'], $content);
        $content = str_replace("{h:lan_category_description}", $this->lang['category_description'], $content);
        $content = str_replace("{h:lan_duration}", $this->lang['duration'], $content);

        $content = str_replace("{h:lan_or}", $this->lang['or'], $content);
        $content = str_replace("{h:lan_cancel}", $this->lang['cancel'], $content);
        $content = str_replace("{h:lan_update_category}", $this->lang['update_category'], $content);

        if($content !== false){
            $sql = "SELECT id, name, description, color, duration
					FROM ".DB_PREFIX."events_categories
					WHERE id = ".(int)$category_id;

            $result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
            if(!empty($result)){
                $content = str_replace("{h:cat_name}", $result["name"], $content);
                $content = str_replace("{h:cat_description}", $result["description"], $content);
                $content = str_replace("{h:category_id}", $result["id"], $content);
                $content = str_replace("{h:ddl_durations}", $this->DrawCategoryDurations($result["duration"], true), $content);

                if($this->arrCatOperations['allow_colors']){
                    $content = str_replace("{h:lan_category_color}", $this->lang['category_color'].":", $content);
                    $content = str_replace("{h:ddl_colors}", $this->DrawColors($result["color"], true, true), $content);
                }else{
                    $content = str_replace("{h:lan_category_color}", "", $content);
                    $content = str_replace("{h:ddl_colors}", "", $content);
                }

                echo $content;
            }
        }
    }

    /**
     * Draw categories details form
     *
     */
    private function DrawCategoriesDetailsForm($category_id)
    {
        // draw Details Event Form from template
        $content = file_get_contents($this->calUrl."template/categories_details_form.tpl");

        $content = str_replace("{h:lan_category_name}", $this->lang['category_name'], $content);
        $content = str_replace("{h:lan_category_description}", $this->lang['category_description'], $content);
        $content = str_replace("{h:lan_category_details}", $this->lang['category_details'], $content);
        $content = str_replace("{h:lan_back}", $this->lang['back'], $content);
        $content = str_replace("{h:lan_duration}", $this->lang['duration'], $content);

        if($content !== false){
            $sql = "SELECT id, name, description, color, duration
					FROM ".DB_PREFIX."events_categories
					WHERE id = ".(int)$category_id;

            $result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
            if(!empty($result)){
                $content = str_replace("{h:category_name}", $result["name"], $content);
                $content = str_replace("{h:category_description}", $result["description"], $content);
                $content = str_replace("{h:js_back_function}", '__CategoriesBack()', $content);
                $content = str_replace("{h:ddl_durations}", $this->DrawCategoryDurations($result["duration"], false), $content);

                if($this->arrCatOperations['allow_colors']){
                    $content = str_replace("{h:lan_cat_color}", $this->lang['category_color'].":", $content);
                    $content = str_replace("{h:ddl_colors}", $this->DrawColors($result["color"], true, false), $content);
                }else{
                    $content = str_replace("{h:lan_cat_color}", "", $content);
                    $content = str_replace("{h:ddl_colors}", "", $content);
                }
                echo $content;
            }
        }
    }

    /**
     * Draw events categories management
     *
     */
    private function DrawEventsCategoriesManagement()
    {
        $colspan = 4;

        $hid_sort_by = ($this->GetParameter("hid_sort_by") != "") ? $this->GetParameter("hid_sort_by") : "name";
        $hid_sort_direction = ($this->GetParameter("hid_sort_direction") != "") ? $this->GetParameter("hid_sort_direction") : "ASC";
        if(strtolower($hid_sort_direction) == "asc") $next_sort_direction = "desc";
        else $next_sort_direction = "asc";

        $sql = "SELECT COUNT(*) as cnt FROM ".DB_PREFIX."events_categories";
        $result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
        $total_records = $result['cnt'];
        $page_size = 20;
        $total_pages = (int)$result['cnt']/$page_size;
        $total_pages_partualy = $result['cnt'] % $page_size;
        if($total_pages_partualy != 0) $total_pages +=1;

        $limit_start = ($this->arrParameters["page"]-1) * $page_size;
        if($limit_start < 0) $limit_start = "0";
        $limit_end = $page_size;

        $sql  = "SELECT id, name, description, color, duration ";
        $sql .= "FROM ".DB_PREFIX."events_categories ";
        $sql .= "ORDER BY ".$hid_sort_by." ".$hid_sort_direction." ";
        $sql .= "LIMIT ".$limit_start.", ".$limit_end."";

        $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
        if($this->isDebug) $this->arrSQLs[] =  $sql."<br />".database_error()."<br />";
        $content = file_get_contents($this->calUrl."template/categories_management_row.tpl");

        echo "<fieldset class='cal_fieldset'>";
        if($this->arrCatOperations['add'] || $this->arrCatOperations['edit']) echo "<legend class='cal_legend'>";
        if($this->arrCatOperations['edit']) echo "<a href=\"javascript:__doPostBack('view', '".$this->arrParameters["view_type"]."', '".$this->arrParameters['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."', 'categories_management')\"><strong><u>".$this->lang['events_categories']."</u></strong></a> :: ";
        if($this->arrCatOperations['add']) echo "<a href=\"javascript:__doPostBack('view', '".$this->arrParameters["view_type"]."', '".$this->arrParameters['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."', 'categories_add')\">".$this->lang['add_category']."</a>";
        if($this->arrCatOperations['add'] || $this->arrCatOperations['edit']) echo "</legend>";
        echo "<table align='center' border='0' width='100%'>
			  <tr><th colspan='".($colspan+1)."'></th></tr>
			  <tr>
				<th width='25px'></th>
				<th align='left'><a href=\"javascript:__CategoriesSort('name', '".$next_sort_direction."')\">".$this->lang['category_name']."</a></th>
				<th align='left'".(($this->arrCatOperations['allow_colors']) ? " width='130px'><a href=\"javascript:__CategoriesSort('color', '".$next_sort_direction."')\">".$this->lang['category_color'] : " width='1px'>")."</a></th>
				<th align='left' width='90px'><a href=\"javascript:__CategoriesSort('duration', '".$next_sort_direction."')\">".$this->lang['duration']."</a></th>
				<th align='center'".((!$this->arrCatOperations['edit'] && !$this->arrCatOperations['details'] && !$this->arrCatOperations['delete']) ? " width='1px'" : " width='140px'>".$this->lang['actions'])."</th>
			  </tr>
			  <tr><th colspan='".($colspan+1)."' nowrap='nowrap' height='2px'></th></tr>";
        ///<th align='left'>".$this->lang['category_description']."</th>

        $categories_count = 0;
        foreach($result[0] as $key => $val){
            $temp_content = $content;
            $categories_count++;

            $drawed_buttons = 0;
            $edit_button = "";
            $details_button = "";
            $delete_button = "";
            if($this->arrCatOperations['details']){ $details_button = (($drawed_buttons > 0) ? " | " : "")."<a href='javascript:__CategoriesDetails({h:category_id});'>".$this->lang['details']."</a>"; $drawed_buttons++; }
            if($this->arrCatOperations['edit'])   { $edit_button    = (($drawed_buttons > 0) ? " | " : "")."<a href='javascript:__CategoriesEdit({h:category_id});'>".$this->lang['edit']."</a>"; $drawed_buttons++; }
            if($this->arrCatOperations['delete']) { $delete_button  = (($drawed_buttons > 0) ? " | " : "")."<a href='javascript:void(0);' onclick='javascript:__CategoriesDelete({h:category_id});'>".$this->lang['delete']."</a>"; $drawed_buttons++; }

            $temp_content = str_replace("{h:edit_button}", $edit_button, $temp_content);
            $temp_content = str_replace("{h:details_button}", $details_button, $temp_content);
            $temp_content = str_replace("{h:delete_button}", $delete_button, $temp_content);

            $temp_content = str_replace("{h:category_id}", $val["id"], $temp_content);
            $temp_content = str_replace("{h:category_num}", $limit_start+$categories_count.". ", $temp_content);
            $temp_content = str_replace("{h:category_name}", $val["name"], $temp_content);
            ///$description = (strlen($val["description"]) > 50) ? substr($val["description"], 0, 50)."..." : $val["description"];
            ///$temp_content = str_replace("{h:category_description}", $description, $temp_content);
            if($this->arrCatOperations['allow_colors']){
                $temp_content = str_replace("{h:category_color}", $this->DrawColors($val["color"], true, false), $temp_content);
            }else{
                $temp_content = str_replace("{h:category_color}", "", $temp_content);
            }
            $temp_content = str_replace("{h:category_duration}", (($val["duration"] != "") ? $val["duration"]." ".$this->lang['min']."." : "<span style='color:#777777'>- ".$this->lang['not_defined']." -</span>"), $temp_content);
            echo $temp_content;
        }
        echo "<tr><td colspan='".($colspan+1)."' style='border-bottom:1px solid #eeeeee;'>&nbsp;</td></tr>";
        echo "<tr>";
        if(!$this->arrCatOperations['edit'] && !$this->arrCatOperations['details'] && !$this->arrCatOperations['delete']) $colspan = 3;
        echo "<td align='left' style='padding-left:10px' colspan='".$colspan."'>".$this->lang['pages'].": ";
        for($i_pages = 1; $i_pages <= $total_pages; $i_pages++){
            if($i_pages > 1) echo ", ";
            $i_pages_a = ($i_pages == $this->arrParameters["page"]) ? "<strong>".$i_pages."</strong>" : $i_pages;
            echo "<a href=\"javascript:__doPostBack('view', '".$this->arrParameters["view_type"]."', '".$this->arrParameters['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."', 'categories_management', '', '".$i_pages."');\">".$i_pages_a."</a>";
        }
        echo "</td>";
        echo "<td align='right' style='padding-right:10px'>".$this->lang['total_categories'].": ".$total_records."</td>";
        echo "</tr>";
        echo "</table>
		      </fieldset>";
    }

    /**
     * Draw events statistics
     *
     */
    private function DrawEventsStatistics()
    {
        echo "<fieldset class='cal_fieldset' style='margin-top:20px; padding-bottom:7px;'>
			  <legend class='cal_legend bold'>".$this->lang["events_statistics"]."</legend>";
        echo "<div style='float:left;padding:7px;'>
		        ".$this->lang["select_chart_type"].": <br />
				<select onchange=\"javascript:__doPostBack('view', '".$this->arrParameters["view_type"]."', '".$this->arrParameters['year']."', '".$this->arrParameters['month']."', '".$this->arrParameters['day']."', 'events_statistics', '', '', this.value)\">
				<option value='columnchart' ".(($this->arrParameters["chart_type"] == "columnchart") ? "selected='selected'" : "").">".$this->lang["chart_column"]."</option>
				<option value='barchart' ".(($this->arrParameters["chart_type"] == "barchart") ? "selected='selected'" : "").">".$this->lang["chart_bar"]."</option>
				<option value='piechart' ".(($this->arrParameters["chart_type"] == "piechart") ? "selected='selected'" : "").">".$this->lang["chart_pie"]."</option>
		        </select></div>";
        echo "<div id='div_visualization' style='float:right; width:".(intval($this->calWidth)*0.82)."px; height:310px;'><img src='".$this->calUrl."style/".$this->cssStyle."/images/loading.gif' style='margin:100px auto;' alt='loading...'></div>";
        echo "</fieldset>";
    }

    /**
     * Draw calendar types changer
     * @param $draw - draw or return
     */
    private function DrawTypesChanger($draw = true)
    {
        $output = "";
        $options = "";
        foreach($this->arrViewTypes as $key => $val){
            if($val['enabled'] == true){
                $options .= "<option value='".$key."'".(($this->arrParameters['view_type'] == $key) ? " selected='selected'" : "").">".$val['name']."</option>";
            }
        }
        if($options != ""){
            $output = "<select class='".$this->select_calss."' name='view_type' id='view_type' onchange=\"javascript:__doPostBack('view', this.value)\">";
            $output .= $options;
            $output .= "</select>";
        }

        if($draw){
            echo $output;
        }else{
            return $output;
        }
    }

    /**
     * Draw today jumper
     * @param $draw - draw or return
     */
    private function DrawTodayJumper($draw = true)
    {
        $result = "<input class='mgrid_button' type='button' value='".$this->lang["today"]."' onclick=\"javascript:__JumpTodayDate()\" />";
        if($draw){
            echo $result;
        }else{
            return $result;
        }
    }

    /**
     * Draw date jumper
     * @param $draw - draw or return
     */
    private function DrawDateJumper($draw = true, $draw_day = true, $draw_month = true, $draw_year = true)
    {
        $result = "";

        // draw days ddl
        if($draw_day){
            $result = "<select class='".$this->select_calss."' name='jump_day' id='jump_day'>";
            for($i=1; $i <= 31; $i++){
                $i_converted = $this->ConvertToDecimal($i);
                $result .= "<option value='".$i_converted."'".(($this->arrParameters["day"] == $i_converted) ? " selected='selected'" : "").">".$i_converted."</option>";
            }
            $result .= "</select> ";
        }else{
            $result .= "<input type='hidden' name='jump_day' id='jump_day' value='".$this->arrToday["mday"]."' />";
        }

        // draw months ddl
        if($draw_month){
            $result .= "<select class='".$this->select_calss."' name='jump_month' id='jump_month' onchange='javascript:__refillDaysInMonth(\"jump_\")'>";
            for($i=1; $i <= 12; $i++){
                $i_converted = $this->ConvertToDecimal($i);
                $result .= "<option value='".$i_converted."'".(($this->arrParameters["month"] == $i_converted) ? " selected='selected'" : "").">".$this->arrMonths[$i]."</option>";
            }
            $result .= "</select> ";
        }else{
            $result .= "<input type='hidden' name='jump_month' id='jump_month' value='".$this->ConvertToDecimal($this->arrToday["mon"])."' />";
        }

        // draw years ddl
        if($draw_year){
            $result .= "<select class='".$this->select_calss."' name='jump_year' id='jump_year' ".(($draw_month) ? "onchange='javascript:__refillDaysInMonth(\"jump_\")'" : "").">";
            for($i=$this->arrParameters["year"]-10; $i <= $this->arrParameters["year"]+10; $i++){
                $result .= "<option value='".$i."'".(($this->arrParameters["year"] == $i) ? " selected='selected'" : "").">".$i."</option>";
            }
            $result .= "</select> ";
        }else{
            $result .= "<input type='hidden' name='jump_year' id='jump_year' value='".$this->arrToday["year"]."' />";
        }

        $result .= "<input class='mgrid_button' type='button' value='".$this->lang["go"]."' onclick='javascript:__JumpToDate()' />";

        if($draw_month){
            $this->arrInitJsFunction[] = "__refillDaysInMonth('jump_');";
        }

        if($draw){
            echo $result;
        }else{
            return $result;
        }
    }

    /**
     * Draw categories dropdown list
     * @param $selected_item - selected option
     * @param $on_js_event
     * @param $style
     * @param $name
     */
    private function DrawCategoriesDDL($selected_item = "", $on_js_event = "", $style = "", $name = "sel_category_name")
    {
        $style = (!$style) ? "width:230px;" : $style;

        // select categories
        $sql = "SELECT id, name, description, color, duration
				FROM ".DB_PREFIX."events_categories
				WHERE duration = '' OR duration >= ".$this->timeSlot."
				ORDER BY name ASC
				LIMIT 0, 1000";

        $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);

        $output = "<select id='".$name."' name='".$name."' style='".$style."' ".$on_js_event.">";
        $select_category_lang = ($name == "sel_category_name_change") ? $this->lang['show_all'] : $this->lang['select_category'];
        $output .= "<option value=''>-- ".$select_category_lang." --</option>";
        foreach($result[0] as $key => $val){
            $selected = ($selected_item == $val["id"]) ? " selected='selected'" : "";
            $val_duration = ($name == "sel_category_name" && $val["duration"] != "") ? "#".$val["duration"] : "";
            $output .= "<option value='".$val["id"].$val_duration."'".$selected.">".$val["name"].(($val["duration"] != "") ? " (".$val["duration"]." ".$this->lang['min'].")" : "")."</option>";
        }
        $output .= "</select>";

        return $output;
    }

    /**
     * Draw events dropdown list
     * @param $draw - draw or return
     * @param $selected_item - selected option
     */
    private function DrawEventsDDL($selected_item = "", $on_js_event = "", $style = "")
    {
        // draw Add Event Form from template
        $sql = "SELECT
					e.id,
					e.category_id,
					e.name,
					e.description,
					ec.name as category_name,
					ec.duration
				FROM ".DB_PREFIX."events e
					LEFT OUTER JOIN ".DB_PREFIX."events_categories ec ON e.category_id = ec.id
				WHERE ec.id IS NULL OR (ec.duration = '' OR ec.duration >= ".$this->timeSlot.")	
				ORDER BY e.category_id ASC
				LIMIT 0, 1000";

        $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);

        $output = "<select id='sel_event_name' name='sel_event_name' style='width:230px;".$style."' ".$on_js_event.">";
        $output .= "<option value=''>-- ".$this->lang['select_event']." --</option>";
        $cur_category = "";
        foreach($result[0] as $key => $val){
            if($this->arrCatOperations['manage'] && ($cur_category != $val["category_id"])){
                if($cur_category != "") $output .= "</optgroup>";
                $output .= "<optgroup label='".(($val['category_name']) ? $val['category_name'] : $this->lang['undefined']." *").(($val["duration"] != "") ? " (".$val["duration"]." ".$this->lang['min'].")" : "")."'>";
                $cur_category = $val['category_id'];
            }
            $selected = ($selected_item == $val["id"]) ? " selected='selected'" : "";
            $output .= "<option value='".$val["id"].(($val["duration"] != "") ? "#".$val["duration"] : "")."'".$selected.">".$val["name"]."</option>";
        }
        $output .= "</select>";

        return $output;
    }

    /**
     * Draw datetime calendar
     * @param $draw - draw or return
     * @param $type - from|to
     */
    private function DrawDateTime($type = "from", $year = "", $month = "", $day = "", $hour = "", $draw = true, $disabled = false)
    {
        $result  = $this->DrawDate($type, $year, $month, $day, false, $disabled);
        $result .= $this->DrawTime($type, $hour, false, $disabled);

        if($draw){
            echo $result;
        }else{
            return $result;
        }
    }

    /**
     * Draw date calendar
     * @param $draw - draw or return
     * @param $type - from|to
     */
    private function DrawDate($type = "from", $year = "", $month = "", $day = "", $draw = true, $disabled = false)
    {
        $result = "";
        $disabled = ($disabled) ? " disabled='disabled'" : "";

        // draw days ddl
        $result = "<select class='".$this->select_calss."' name='event_".$type."_day' id='event_".$type."_day'".$disabled.">";
        for($i=1; $i <= 31; $i++){
            $i_converted = $this->ConvertToDecimal($i);
            $result .= "<option value='".$i_converted."'".(($day == $i_converted) ? " selected='selected'" : "").">".$i_converted."</option>";
        }
        $result .= "</select> ";

        // draw months ddl
        $result .= "<select class='".$this->select_calss."' onchange='javascript:__refillDaysInMonth(\"event_".$type."_\")' name='event_".$type."_month' id='event_".$type."_month'".$disabled.">";
        for($i=1; $i <= 12; $i++){
            $i_converted = $this->ConvertToDecimal($i);
            $result .= "<option value='".$i_converted."'".(($month == $i_converted) ? " selected='selected'" : "").">".$this->arrMonths[$i]."</option>";
        }
        $result .= "</select> ";

        // draw years ddl
        $result .= "<select class='".$this->select_calss."' onchange='javascript:__refillDaysInMonth(\"event_".$type."_\")' name='event_".$type."_year' id='event_".$type."_year'".$disabled.">";
        for($i=$year-10; $i <= $year+10; $i++){
            $result .= "<option value='".$i."'".(($year == $i) ? " selected='selected'" : "").">".$i."</option>";
        }
        $result .= "</select> ";

        $this->arrInitJsFunction[] = "__refillDaysInMonth('event_".$type."_');";

        if($draw){
            echo $result;
        }else{
            return $result;
        }
    }

    /**
     * Draw ime calendar
     * @param $draw - draw or return
     * @param $type - from|to
     */
    private function DrawTime($type = "from", $hour = "", $draw = true, $disabled = false)
    {
        $disabled = ($disabled) ? " disabled='disabled'" : "";

        // draw hours ddl
        $result = "<select class='".$this->select_calss."' name='event_".$type."_hour' id='event_".$type."_hour'".$disabled.">";
        if($this->timeSlot == "15"){
            for($i_hour=$this->fromHour; $i_hour<$this->toHour; $i_hour++){
                for($i_block=0; $i_block<4; $i_block++){
                    $result .= $this->PrepareOption($hour, $i_hour, $i_block);
                }
            }
            if($type == "to") $result .= $this->PrepareOption($hour, $i_hour, $i_block);
        }elseif($this->timeSlot == "30"){
            for($i_hour=$this->fromHour; $i_hour<$this->toHour; $i_hour++){
                for($i_block=0; $i_block<2; $i_block++){
                    $result .= $this->PrepareOption($hour, $i_hour, $i_block);
                }
            }
            if($type == "to") $result .= $this->PrepareOption($hour, $i_hour, $i_block);
        }elseif($this->timeSlot == "45"){
            $blocks_count = 0;
            for($i_hour=$this->fromHour; $i_hour<$this->toHour; $i_hour++){
                for($i_block=0; $i_block<4; $i_block++, $blocks_count++){
                    $result .= $this->PrepareOption($hour, $i_hour, $i_block, $blocks_count);
                }
            }
            if($type == "to") $result .= $this->PrepareOption($hour, $i_hour, $i_block);
        }else{
            for($i_hour=$this->fromHour; $i_hour < $this->toHour; $i_hour++){
                $result .= $this->PrepareOption($hour, $i_hour);
            }
            if($type == "to") $result .= $this->PrepareOption($hour, $i_hour);
        }
        $result .= "</select> ";

        if($draw){
            echo $result;
        }else{
            return $result;
        }
    }

    /**
     * Delete all cache files
     */
    function DeleteCache(){
        if ($hdl = opendir($this->cacheDir)){
            while(false !== ($obj = @readdir($hdl))){
                if($obj == '.' || $obj == '..'){
                    continue;
                }
                @unlink($this->cacheDir.$obj);
            }
        }
    }

    /**
     * Save http request vars
     */
    function SaveHttpRequestVars($http_request_vars = array()){
        $this->httpRequestVars = $http_request_vars;
    }

    /**
     * Draw category durations dropdown box
     * @param $duration - selected
     * @param $draw_ddl - draw ddl or return selected value
     */
    private function DrawCategoryDurations($duration = "", $draw_ddl = true)
    {
        if(!$draw_ddl){
            return $duration." ".(($duration != "") ? $this->lang['min']."." : "<span style='color:#777777'>- ".$this->lang['not_defined']." -</span>");
        }

        $output = "";
        $output .= "<select class='".$this->select_calss."' name='category_duration' id='category_duration'>";
        $output .= "<option ".(($duration == "") ? "selected='selected'" : "")." value=''>-- ".$this->lang['default']." --</option>";
        for($i=$this->timeSlot; $i<=60; $i+=15){
            $output .= "<option ".(($duration == $i) ? "selected='selected'" : "")." value='".$i."'>".$i." ".$this->lang['min'].".</option>";
        }
        $output .= "</select> ";

        return $output;
    }

    /**
     * Draw colors dropdown box
     * @param $color - selected
     * @param $return - draw or return
     * @param $draw_ddl - draw ddl or return selected value
     */
    private function DrawColors($color = "", $return = false, $draw_ddl = true)
    {
        $output = "";

        // draw colors ddl
        $arr_colors = self::GetColorsByName();
        $output = "<table align='left' border='0' cellpadding='0' cellspacing='0'><tr>";
        if($draw_ddl){
            $output .= "<td align='left' valign='middle' style='padding-right:5px'>";
            $output .= "<select class='".$this->select_calss."' name='category_color' id='category_color' onChange='javascript:__ChangeColor(\"colorBox\", this.value);'>";
            $output .= "<option value=''>-- ".$this->lang['default']." --</option>";
            foreach($arr_colors as $key => $val){
                $output .= "<optgroup label='".$key."'>";
                foreach($val as $v_key => $v_val){
                    $selected = "";
                    if($color == $v_key){
                        $selected = " selected='selected'";
                    }
                    $output .= "<option value='".$v_key."'".$selected.">".$v_val."</option>";
                }
                $output .= "</optgroup>";
            }
            $output .= "</select> ";
            $output .= "</td>";
        }
        if(!$draw_ddl) $width = $height = "13px";
        else $width = $height = "17px";
        $output .= "<td align='left' valign='top'>";
        $output .= "<div id='colorBox' style='border:1px solid #cecece;width:".$width.";height:".$height.";background-color:".$color.";layer-background-color:".$color.";'></div>";
        $output .= "</td>";

        if(!$draw_ddl){
            $output .= "<td align='left' valign='middle' style='padding-left:5px'>";
            $color_name = self::GetColorNameByValue($color);
            $output .= ($color_name != "") ? $color_name : "<span style='color:#777777'>- ".$this->lang['default']." -</span>";
            $output .= "</td>";
        }

        $output .= "</tr></table>";

        if($return) return $output;
        else echo $output;
    }

    /**
     * Get colors by name
     */
    static private function GetColorsByName()
    {
        $colors = array(
            "Reds" => array(
                "#CD5C5C" => "Indian Red",
                "#F08080" => "Light Coral",
                "#FA8072" => "Salmon",
                "#E9967A" => "Dark Salmon",
                "#FFA07A" => "Light Salmon",
                "#DC143C" => "Crimson",
                "#FF0000" => "Red",
                "#B22222" => "Fire Brick",
                "#8B0000" => "Dark Red"
            ),

            "Pinks" => array(
                "#FFC0CB" => "Pink",
                "#FFB6C1" => "Light Pink",
                "#FF69B4" => "Hot Pink",
                "#FF1493" => "Deep Pink",
                "#C71585" => "Medium Violet Red",
                "#DB7093" => "Pale Violet Red"
            ),

            "Oranges" => array(
                "#FFA07A" => "Light Salmon",
                "#FF7F50" => "Coral",
                "#FF6347" => "Tomato",
                "#FF4500" => "Orange Red",
                "#FF8C00" => "Dark Orange",
                "#FFA500" => "Orange"
            ),

            "Yellows" => array(
                "#FFD700" => "Gold",
                "#FFFF00" => "Yellow",
                "#FFFFE0" => "Light Yellow",
                "#FFFACD" => "Lemon Chiffon",
                "#FAFAD2" => "Light Goldenrod Yellow",
                "#FFEFD5" => "Papaya Whip",
                "#FFE4B5" => "Moccasin",
                "#FFDAB9" => "Peach Puff",
                "#EEE8AA" => "Pale Goldenrod",
                "#F0E68C" => "Khaki",
                "#BDB76B" => "Dark Khaki"
            ),

            "Purples" => array(
                "#E6E6FA" => "Lavender",
                "#D8BFD8" => "Thistle",
                "#DDA0DD" => "Plum",
                "#EE82EE" => "Violet",
                "#DA70D6" => "Orchid",
                "#FF00FF" => "Fuchsia",
                "#FF00FF" => "Magenta",
                "#BA55D3" => "Medium Orchid",
                "#9370DB" => "Medium Purple",
                "#8A2BE2" => "Blue Violet",
                "#9400D3" => "Dark Violet",
                "#9932CC" => "Dark Orchid",
                "#8B008B" => "Dark Magenta",
                "#800080" => "Purple",
                "#4B0082" => "Indigo",
                "#6A5ACD" => "Slate Blue",
                "#483D8B" => "Dark Slate Blue"
            ),

            "Greens" => array(
                "#ADFF2F" => "Green Yellow",
                "#7FFF00" => "Chartreuse",
                "#7CFC00" => "Lawn Green",
                "#00FF00" => "Lime",
                "#32CD32" => "Lime Green",
                "#98FB98" => "Pale Green",
                "#90EE90" => "Light	Green",
                "#00FA9A" => "Medium Spring Green",
                "#00FF7F" => "Spring Green",
                "#3CB371" => "Medium Sea Green",
                "#2E8B57" => "Sea Green",
                "#228B22" => "Forest Green",
                "#008000" => "Green",
                "#006400" => "Dark Green",
                "#9ACD32" => "Yellow Green",
                "#6B8E23" => "Olive Drab",
                "#808000" => "Olive",
                "#556B2F" => "Dark Olive Green",
                "#66CDAA" => "Medium Aquamarine",
                "#8FBC8F" => "Dark Sea Green",
                "#20B2AA" => "Light Sea Green",
                "#008B8B" => "Dark Cyan",
                "#008080" => "Teal"
            ),

            "Blues" => array(
                "#00FFFF" => "Aqua",
                "#00FFFF" => "Cyan",
                "#E0FFFF" => "Light Cyan",
                "#AFEEEE" => "Pale Turquoise",
                "#7FFFD4" => "Aquamarine",
                "#40E0D0" => "Turquoise",
                "#48D1CC" => "Medium Turquoise",
                "#00CED1" => "Dark Turquoise",
                "#5F9EA0" => "Cadet Blue",
                "#4682B4" => "Steel Blue",
                "#B0C4DE" => "Light Steel Blue",
                "#B0E0E6" => "Powder Blue",
                "#ADD8E6" => "Light Blue",
                "#87CEEB" => "Sky Blue",
                "#87CEFA" => "Light Sky Blue",
                "#00BFFF" => "Deep Sky Blue",
                "#1E90FF" => "Dodger Blue",
                "#6495ED" => "Cornflower Blue",
                "#7B68EE" => "Medium Slate Blue",
                "#4169E1" => "Royal Blue",
                "#0000FF" => "Blue",
                "#0000CD" => "Medium Blue",
                "#00008B" => "Dark Blue",
                "#000080" => "Navy",
                "#191970" => "Midnight Blue"
            ),

            "Browns" => array(
                "#FFF8DC" => "Cornsilk",
                "#FFEBCD" => "Blanched Almond",
                "#FFE4C4" => "Bisque",
                "#FFDEAD" => "Navajo White",
                "#F5DEB3" => "Wheat",
                "#DEB887" => "Burly Wood",
                "#D2B48C" => "Tan",
                "#BC8F8F" => "Rosy Brown",
                "#F4A460" => "Sandy Brown",
                "#DAA520" => "Goldenrod",
                "#B8860B" => "Dark Goldenrod",
                "#CD853F" => "Peru",
                "#D2691E" => "Chocolate",
                "#8B4513" => "Saddle Brown",
                "#A0522D" => "Sienna",
                "#A52A2A" => "Brown",
                "#800000" => "Maroon"
            ),

            "Whites" => array(
                "#FFFFFF" => "White",
                "#FFFAFA" => "Snow",
                "#F0FFF0" => "Honeydew",
                "#F5FFFA" => "Mint Cream",
                "#F0FFFF" => "Azure",
                "#F0F8FF" => "Alice Blue",
                "#F8F8FF" => "Ghost White",
                "#F5F5F5" => "White Smoke",
                "#FFF5EE" => "Seashell",
                "#F5F5DC" => "Beige",
                "#FDF5E6" => "Old Lace",
                "#FFFAF0" => "Floral White",
                "#FFFFF0" => "Ivory",
                "#FAEBD7" => "Antique White",
                "#FAF0E6" => "Linen",
                "#FFF0F5" => "Lavender Blush",
                "#FFE4E1" => "Misty Rose"
            ),

            "Greys" => array(
                "#DCDCDC" => "Gainsboro",
                "#D3D3D3" => "Light Grey",
                "#C0C0C0" => "Silver",
                "#A9A9A9" => "Dark Gray",
                "#808080" => "Gray",
                "#696969" => "Dim Gray",
                "#778899" => "Light Slate Gray",
                "#708090" => "Slate Gray",
                "#2F4F4F" => "Dark Slate Gray",
                "#000000" => "Black"
            )
        );
        return $colors;
    }

    private static function GetColorNameByValue($color_value)
    {
        $arr_colors = self::GetColorsByName();
        foreach($arr_colors as $key => $val){
            if(is_array($val)){
                foreach($val as $v_key => $v_val){
                    if($v_key == $color_value) return $v_val;
                }
            }
        }
        return "";
    }

    ////////////////////////////////////////////////////////////////////////////
    // Auxilary
    ////////////////////////////////////////////////////////////////////////////
    /**
     * Set language
     */
    private function SetLanguage()
    {
        if (@file_exists($this->calDir.'languages/'.$this->langName.'.php')) {
            include_once($this->calDir.'languages/'.$this->langName.'.php');
            if(@function_exists('setLanguage')){
                $this->lang = setLanguage();
            }else{
                if($this->isDebug) $this->arrErrors[] = "Your language interface option is turned on, but the system was failed to open correctly stream: <b>'languages/".$this->langName.".php'</b>. <br />The structure of the file is corrupted or invalid. Please check it or return the language option to default value: <b>'en'</b>!";
            }
        }else{
            if($this->isDebug) $this->arrErrors[] = "Your language interface option is turned on, but the system was failed to open stream: <b>'languages/".$this->langName.".php'</b>. <br />No such file or directory. Please check it or return the language option to default value: <b>'en'</b>!";
        }

        $this->arrWeekDays[0] = array("short"=>$this->lang["sun"], "long"=>$this->lang["sunday"]);
        $this->arrWeekDays[1] = array("short"=>$this->lang["mon"], "long"=>$this->lang["monday"]);
        $this->arrWeekDays[2] = array("short"=>$this->lang["tue"], "long"=>$this->lang["tuesday"]);
        $this->arrWeekDays[3] = array("short"=>$this->lang["wed"], "long"=>$this->lang["wednesday"]);
        $this->arrWeekDays[4] = array("short"=>$this->lang["thu"], "long"=>$this->lang["thursday"]);
        $this->arrWeekDays[5] = array("short"=>$this->lang["fri"], "long"=>$this->lang["friday"]);
        $this->arrWeekDays[6] = array("short"=>$this->lang["sat"], "long"=>$this->lang["saturday"]);

        $this->arrMonths["1"] = $this->lang["months"][1];
        $this->arrMonths["2"] = $this->lang["months"][2];
        $this->arrMonths["3"] = $this->lang["months"][3];
        $this->arrMonths["4"] = $this->lang["months"][4];
        $this->arrMonths["5"] = $this->lang["months"][5];
        $this->arrMonths["6"] = $this->lang["months"][6];
        $this->arrMonths["7"] = $this->lang["months"][7];
        $this->arrMonths["8"] = $this->lang["months"][8];
        $this->arrMonths["9"] = $this->lang["months"][9];
        $this->arrMonths["10"] = $this->lang["months"][10];
        $this->arrMonths["11"] = $this->lang["months"][11];
        $this->arrMonths["12"] = $this->lang["months"][12];

        $this->arrViewTypes["daily"]["name"]     = $this->lang["daily"];
        $this->arrViewTypes["weekly"]["name"]    = $this->lang["weekly"];
        $this->arrViewTypes["monthly"]["name"] 	 = $this->lang["monthly"];
        $this->arrViewTypes["yearly"]["name"] 	 = $this->lang["yearly"];
        $this->arrViewTypes["list_view"]["name"] = $this->lang["list_view"];
    }


    /**
     * Check chache files
     */
    private function CheckCacheFiles()
    {
        $files_count = 0;
        $oldest_file_name = "";
        $oldest_file_time = date("Y-m-d H:i:s");
        if ($hdl = opendir($this->cacheDir)){
            while (false !== ($obj = @readdir($hdl))){
                if($obj == '.' || $obj == '..'){
                    continue;
                }
                $file_time = date("Y-m-d H:i:s", filectime($this->cacheDir.$obj));
                if($file_time < $oldest_file_time){
                    $oldest_file_time = $file_time;
                    $oldest_file_name = $this->cacheDir.$obj;
                }
                $files_count++;
            }
        }
        if($files_count > $this->maxCacheFiles){
            @unlink($oldest_file_name);
        }
    }

    /**
     * Start Caching of page
     * @param $cachefile - name of file to be cached
     */
    private function StartCaching($cachefile)
    {
        if($cachefile != "" && file_exists($cachefile)) {
            // minutes - how many time save the cache
            $cachetime = (int)$this->cacheLifetime * 60;
            // Serve from the cache if it is younger than $cachetime
            if(file_exists($cachefile) && (filesize($cachefile) > 0) && ((time() - $cachetime) < filemtime($cachefile))){
                // the page has been cached from an earlier request
                // output the contents of the cache file
                include_once($cachefile);
                echo "<!-- Generated from cache at ".date('H:i', filemtime($cachefile))." -->".$this->crLt;
                return true;
            }
        }
        // start the output buffer
        ob_start();
    }

    /**
     * Finish Caching of page
     * @param $cachefile - name of file to be cached
     */
    private function FinishCaching($cachefile)
    {
        if($cachefile != ""){
            // open the cache file for writing
            $fp = fopen($cachefile, 'w');
            // save the contents of output buffer to the file
            fwrite($fp, ob_get_contents());
            // close the file
            fclose($fp);
            // Send the output to the browser
            ob_end_flush();
            // check if we exeeded max number of cache files
            $this->CheckCacheFiles();
        }
    }

    /**
     * Returns list of events for week day for certain hour
     * @param $events - array of events
     */
    private function GetEventsListForWeekDay($events = array())
    {
        $output = "";
        $events_count = count($events);

        if($this->eventsDisplayType['weekly'] == "tooltip"){
            if($events_count > 0){
                $output .= "<div class='events_list_tooltip' style='width:".$this->weekColWidth.";' title='";
                foreach($events as $key => $cal){
                    $output .= "&#8226; <label ".(($this->arrCatOperations['allow_colors']) ? "style=color:".$cal['color'] : "").">".$cal['name']."</label><br />";
                }
                $output .= "'>".$this->lang["view"]." (".$events_count.") &raquo;</div>";
            }
        }else{
            foreach($events as $key => $cal){
                $cal_name = (strlen($cal['name']) > 10) ? substr($cal['name'], 0, 10)."..." : $cal['name'];
                $output .= "<span class='event_wrapper' title='".$cal['description']."' style='".(($this->arrCatOperations['allow_colors']) ? "color:".$cal['color'].";" : "")."width:".$this->weekColWidth.";'>";
                $output .= "&#8226;<img onclick=\"__DeleteEvent('".$cal['id']."');\" title='".$this->lang["click_to_delete"]."' src='".$this->calUrl."style/".$this->cssStyle."/images/delete.gif' alt='delete' /><label>".$cal_name."</label>";
                $output .= "</span>";
            }
        }
        return $output;
    }

    /**
     * Returns list of events for certain hour
     * @param $events - array of events
     */
    private function GetEventsListForHour($events = array(), $max_length = "")
    {
        $output = "";
        foreach($events as $key => $cal){
            if($output != "") $output .= ", ";
            $output .= "<span title='".$cal['description']."' ".(($this->arrCatOperations['allow_colors']) ? "style='color:".$cal['color']."'" : "").">".$cal['name']."</span>";
            if($max_length == "" && $this->arrEventsOperations['delete']){
                $output .= " <a href='javascript:void(0);' onclick=\"javascript:__DeleteEvent('".$cal['id']."');\" title='".$this->lang['click_to_delete']."'><img src='".$this->calUrl."style/".$this->cssStyle."/images/delete.gif' title='".$this->lang["click_to_delete"]."' style='border:0px;vertical-align:middle;' alt='delete' /></a>";
            }
        }
        if($max_length != "" && strlen($output) > $max_length){
            $output = "<label title='".$output."' style='cursor:help;'>".substr($output, 0, $max_length)."...</label>";
        }
        return $output;
    }

    /**
     * Returns count of events for the certain day
     * @param $event_date - day of events
     */
    private function GetEventCountForDay($event_date = "")
    {
        // prepare events for this day of week
        $sql = "SELECT
					".DB_PREFIX."calendar.event_date,
					".DB_PREFIX."calendar.event_time,
					DATE_FORMAT(".DB_PREFIX."calendar.event_time, '%H') as event_time_formatted,
					".DB_PREFIX."events.name,
					".DB_PREFIX."events.description
				FROM ".DB_PREFIX."calendar
					INNER JOIN ".DB_PREFIX."events ON ".DB_PREFIX."calendar.event_id = ".DB_PREFIX."events.id
				WHERE
					".DB_PREFIX."calendar.event_date = ".$event_date."
					".(($this->userID) ? " AND ".DB_PREFIX."calendar.user_id = ".(int)$this->userID : "")."
					".$this->PrepareWhereClauseEventTime()."
				GROUP BY ".DB_PREFIX."events.id";
        $result = database_query($sql, ROWS_ONLY);
        if($this->isDebug) $this->arrSQLs[] =  $sql."<br />".database_error()."<br />";
        return $result;
    }

    /**
     * Returns count of events for the certain month
     * @param $event_year - $year of events
     * @param $event_month - $month of events
     */
    private function GetEventsCountForMonth($event_year = "", $event_month = "")
    {
        // prepare events for this day of week
        $sql = "SELECT
					GROUP_CONCAT(DISTINCT ".DB_PREFIX."events.id ORDER BY ".DB_PREFIX."events.id ASC SEPARATOR '".$this->separator."') as cnt,
					".DB_PREFIX."calendar.event_date,
					SUBSTRING(".DB_PREFIX."calendar.event_date, 9, 2) as day
				FROM ".DB_PREFIX."calendar
					INNER JOIN ".DB_PREFIX."events ON ".DB_PREFIX."calendar.event_id = ".DB_PREFIX."events.id
				WHERE
					SUBSTRING(".DB_PREFIX."calendar.event_date, 1, 4) =  '".$event_year."' AND 
					SUBSTRING(".DB_PREFIX."calendar.event_date, 6, 2) =  '".$event_month."'
					".(($this->userID) ? " AND ".DB_PREFIX."calendar.user_id = ".(int)$this->userID : "")."
					".$this->PrepareWhereClauseCategory()."
					".$this->PrepareWhereClauseEventTime()."
				GROUP BY
					SUBSTRING(".DB_PREFIX."calendar.event_date, 9, 2)";
        $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
        if($this->isDebug) $this->arrSQLs[] = $sql."<br />".database_error()."<br />";

        $arrEventsCount = array(
            "01"=>0, "02"=>0, "03"=>0, "04"=>0, "05"=>0, "06"=>0, "07"=>0, "08"=>0, "09"=>0, "10"=>0, "11"=>0, "12"=>0, "13"=>0, "14"=>0, "15"=>0,
            "16"=>0, "17"=>0, "18"=>0, "19"=>0, "20"=>0, "21"=>0, "22"=>0, "23"=>0, "24"=>0, "25"=>0, "26"=>0, "27"=>0, "28"=>0, "29"=>0, "30"=>0, "31"=>0);

        foreach($result[0] as $key => $val){
            $cnt_array = explode($this->separator, $val['cnt']);
            $arrEventsCount[$val['day']] = count($cnt_array);
        }
        return $arrEventsCount;
    }

    /**
     * Returns list of events for the certain month
     * @param $event_year - $year of events
     * @param $event_month - $month of events
     */
    private function GetEventsListForMonth($event_year = "", $event_month = "", $show_id = false)
    {
        // prepare events for this day of week
        $sql  = "SELECT 
		            ".TABLE_BOOKINGS.".booking_number,					
					".TABLE_BOOKINGS_ROOMS.".room_id,
					".TABLE_BOOKINGS_ROOMS.".rooms,
					".TABLE_BOOKINGS_ROOMS.".checkin,
					".TABLE_BOOKINGS_ROOMS.".checkout,
					SUBSTRING(".TABLE_BOOKINGS_ROOMS.".checkin, 1, 4) as checkin_year,
					SUBSTRING(".TABLE_BOOKINGS_ROOMS.".checkout, 1, 4) as checkout_year,
					SUBSTRING(".TABLE_BOOKINGS_ROOMS.".checkin, 6, 2) as checkin_month,
					SUBSTRING(".TABLE_BOOKINGS_ROOMS.".checkout, 6, 2) as checkout_month,
					SUBSTRING(".TABLE_BOOKINGS_ROOMS.".checkin, 9, 2) as checkin_day,
					SUBSTRING(".TABLE_BOOKINGS_ROOMS.".checkout, 9, 2) as checkout_day
				FROM ".TABLE_BOOKINGS."
					INNER JOIN ".TABLE_BOOKINGS_ROOMS." ON ".TABLE_BOOKINGS.".booking_number = ".TABLE_BOOKINGS_ROOMS.".booking_number
					INNER JOIN ".TABLE_ROOMS." ON ".TABLE_BOOKINGS_ROOMS.".room_id = ".TABLE_ROOMS.".id
				WHERE
                    ".(($this->hotelId != "") ? TABLE_ROOMS.".hotel_id = ".(int)$this->hotelId." AND " : "")."
					".(($this->roomTypes != "") ? TABLE_ROOMS.".id = ".(int)$this->roomTypes." AND " : "")."
					".(($this->showAs == "reserved_and_completed") ? "(".TABLE_BOOKINGS.".status = 2 OR ".TABLE_BOOKINGS.".status = 3) " : DB_PREFIX."bookings.status = 3 ")." AND					
					SUBSTRING(".TABLE_BOOKINGS_ROOMS.".checkin, 1, 7) <= '".$event_year."-".$event_month."' AND
					SUBSTRING(".TABLE_BOOKINGS_ROOMS.".checkout, 1, 7) >= '".$event_year."-".$event_month."'
				";

        $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
        //echo "<pre>";
        //print_r($result);
        //echo "</pre>";

        #[booking_number] => A94YFXTULL
        # [checkin] => 2010-07-31
        # [checkout] => 2010-08-01
        # [checkin_year] => 2010
        # [checkout_year] => 2010
        # [checkin_month] => 07
        # [checkout_month] => 08
        # [checkin_day] => 31
        # [checkout_day] => 01

        if($this->isDebug) $this->arrSQLs[] = htmlentities($sql)."<br />".database_error()."<br />";

        $arrEventsList = array(
            "01"=>0, "02"=>0, "03"=>0, "04"=>0, "05"=>0, "06"=>0, "07"=>0, "08"=>0, "09"=>0, "10"=>0, "11"=>0, "12"=>0, "13"=>0, "14"=>0, "15"=>0,
            "16"=>0, "17"=>0, "18"=>0, "19"=>0, "20"=>0, "21"=>0, "22"=>0, "23"=>0, "24"=>0, "25"=>0, "26"=>0, "27"=>0, "28"=>0, "29"=>0, "30"=>0, "31"=>0);

        ////////////////////////////////////////////////////////////////////////
        // prepare not-available rooms
        $arrRoomsAvailabilities = Rooms::GetRoomAvalibilityForMonth($this->arrRoomTypes, $event_year, $event_month);
        if($this->roomTypes != ""){
            $roomAvailability = array();
            for($i = 0; $i < count($arrRoomsAvailabilities); $i++){
                if($arrRoomsAvailabilities[$i]['id'] == $this->roomTypes) $roomAvailability = $arrRoomsAvailabilities[$i]['availability'];
            }
            foreach($roomAvailability as $key => $val){
                if($val == "0") $arrEventsList[$key] = "-1";
            }
        }
        ////////////////////////////////////////////////////////////////////////

        foreach($result[0] as $key => $val){
            //$cnt_array = explode($this->separator, $val['cnt']);
            //$arrEventsList[$val['checkin_day']] = $cnt_array;
            //$checkin_day  = ($val['checkout_day'] > $val['checkin_day']) ? $val['checkout_day'] : $this->GetMonthLastDay(date("m"), date("Y"));

            if(($val['checkout_month'] == $val['checkin_month']) && ($val['checkout_day'] > $val['checkin_day'])){
                $checkout_day = $val['checkout_day'];
                $checkin_day  = $val['checkin_day'];
            }elseif(($val['checkout_year'] == $val['checkin_year']) && ($val['checkout_month'] > $val['checkin_month'])){
                if($val['checkin_month'] == $event_month){
                    $checkout_day = $this->GetMonthLastDay($event_month, date("Y"))+1;
                    $checkin_day  = $val['checkin_day'];
                }elseif($val['checkout_month'] > $event_month){
                    $checkout_day = $this->GetMonthLastDay($event_month, date("Y"))+1;
                    $checkin_day  = "01";
                }else{
                    $checkout_day = $val['checkout_day'];
                    $checkin_day  = "01";
                }
            }elseif($val['checkout_year'] > $val['checkin_year']){
                if($val['checkin_month'] == $event_month){
                    $checkout_day = $this->GetMonthLastDay($event_month, date("Y"))+1;
                    $checkin_day  = $val['checkin_day'];
                }elseif($val['checkout_month'] == $event_month){
                    $checkout_day = $val['checkout_day'];
                    $checkin_day  = "01";
                }else{
                    $checkout_day = $this->GetMonthLastDay(date("m"), date("Y"))+1;
                    $checkin_day  = "01";
                }
            }

            //echo $val['checkin']."--".$val['checkout']." ".$val['room_id']." ".$val['rooms']." || ".$checkin_day." ".$checkout_day."<br />";
            for($i=$checkin_day; $i<$checkout_day; $i++){
                $ind = $this->ConvertToDecimal($i);
                if(!is_array($arrEventsList[$ind])){
                    $arrEventsList[$ind] = array();
                }
                if(!isset($arrEventsList[$ind][$val['room_id']])){
                    $arrEventsList[$ind][$val['room_id']] = $val['rooms'];
                }else{
                    $arrEventsList[$ind][$val['room_id']] += $val['rooms'];
                }

                if(!isset($arrEventsList[$ind][0])) $arrEventsList[$ind][0] = 0;
                $arrEventsList[$ind][0] += $val['rooms'];
            }
        }

        //echo "<pre>";
        //print_r($arrEventsList);
        //echo "</pre>";

        return $arrEventsList;
    }

    /**
     * Returns list of events for the certain month's day
     * @param $events - array of month's events
     * @param $actday - day of month
     */
    function GetMonthlyDayEvents($events = array(), $actday, $total = "")
    {
        $room_list = "";
        $events_count = 0;
        // display events

        if(isset($events[$this->ConvertToDecimal($actday)])){
            $arrEvents = $this->RemoveDuplications($events[$this->ConvertToDecimal($actday)]);
        }else{
            $arrEvents = array();
        }

        $cur_year_mon = $this->arrParameters['year'].$this->arrParameters['month'];
        $today = date("Ymd");
        $is_past = ($today > $cur_year_mon.$this->ConvertToDecimal($actday)) ? true : false;
        $two_years = (nights_diff($today, $cur_year_mon.$this->ConvertToDecimal($actday)) > 720) ? true : false;

        //echo "<pre>";
        //print_r($events[$actday]);
        //echo "</pre>";

        //echo "<pre>";
        //print_r($this->arrRoomTypes);
        //echo "</pre>";

        if(isset($events[$this->ConvertToDecimal($actday)])){
            if(is_array($events[$this->ConvertToDecimal($actday)])){

                $room_list .= "<div class='events_list_inline'>";
                foreach($this->arrRoomTypes as $room){
                    if(!isset($room['room_type']) || !isset($room['id'])) continue;
                    if($this->roomTypes != "" && $this->roomTypes != $room['id']) continue;

                    $room_count = isset($room['availability'][$actday]) ? $room['availability'][$actday] : $room['room_count'];
                    $rooms_total = $room_count;
                    if(is_array($events[$actday]) && array_key_exists($room['id'], $events[$actday])){
                        if(isset($events[$actday][$room['id']])){
                            $rooms_total = ($rooms_total - $events[$actday][$room['id']] > 0) ? $rooms_total - $events[$actday][$room['id']] : 0;
                        }
                    }

                    if(!isset($room['availability'][$actday]) || !$room['availability'][$actday]){
                        $room_color = ($is_past) ? $this->colorGray : $this->colorDarkGray;
                        $room_list .= "&#8226; <span style=color:".$room_color."><strike>".$room['room_type']."</strike></span><br />";
                    }else{
                        if($rooms_total == 0){
                            $room_color = ($is_past) ? $this->colorGray : $this->colorRed;
                            $room_list .= "&#8226; <span style=color:".$room_color."><strike>".$room['room_type']." ".$rooms_total."</strike></span><br />";
                        }elseif($rooms_total < $room_count){
                            $room_color = ($is_past) ? $this->colorGray : $this->colorYellow;
                            $room_list .= "&#8226; <span style=color:".$room_color.">".$room['room_type']." ".$rooms_total."</span><br />";
                        }else{
                            $room_color = ($is_past) ? $this->colorGray : $this->colorGreen;
                            $room_list .= "&#8226; <span style=color:".$room_color.">".$room['room_type']." ".$rooms_total."</span><br />";
                        }
                    }
                }
                $room_list .= "</div>";

            }else{
                $room_list .= "<div class='events_list_inline'>";
                foreach($this->arrRoomTypes as $room){
                    if(!isset($room['room_type']) || !isset($room['id'])) continue;
                    if($this->roomTypes != "" && $this->roomTypes != $room['id']) continue;

                    $room_count = isset($room['availability'][$actday]) ? $room['availability'][$actday] : $room['room_count'];
                    if(!$two_years && (!isset($room['availability'][$actday]) || !$room['availability'][$actday])){
                        $room_color = ($is_past) ? $this->colorGray : $this->colorDarkGray;
                        $room_list .= "&#8226; <span style=color:".$room_color."><strike>".$room['room_type']."</strike></span><br />";
                    }else{
                        $room_color = ($is_past) ? $this->colorGray : $this->colorGreen;
                        $room_list .= "&#8226; <span style=color:".$room_color.">".$room['room_type']." ".$room_count."</span><br />";
                    }
                }
                $room_list .= "</div>";
            }
        }
        return $room_list;
    }

    /**
     * Draws monthly day cell with events and scrolling
     * @param $events_count - count of events
     * @param $arrEventsList - list of events for this day
     * @param $actday - day of month
     */
    private function DrawMonthlyDayCell($events_count, $arrEventsList, $actday)
    {
        // [20.10.2012] - commented to prevent scrolling for big ammount of rooms

        //if($events_count > 5){
        //	$cel_height = number_format(((int)$this->calHeight)/6 * 4/5, "0")."px";
        //	$cel_id = $this->arrParameters["month"].$this->ConvertToDecimal($actday);
        //	if($this->eventsDisplayType['monthly'] == "inline"){
        //		echo "<div style='height:".$cel_height.";overflow-y:hidden;overflow-x:hidden;' id='divDayEventContainer".$cel_id."'>";
        //	}
        //	echo $this->GetMonthlyDayEvents($arrEventsList, $this->ConvertToDecimal($actday));
        //	if($this->eventsDisplayType['monthly'] == "inline"){
        //		echo "</div>";
        //		echo "<a id='dayEventLinkShow".$cel_id."' style='display:' href='javascript:__toggleCellScroll(\"".$cel_id."\");'>".$this->lang['show_all']." &raquo;</a>";
        //		echo "<a id='dayEventLinkCollapse".$cel_id."' style='display:none;' href='javascript:__toggleCellScroll(\"".$cel_id."\");'>&laquo; ".$this->lang['close_lc']."</a>";
        //	}
        //}else{
        echo $this->GetMonthlyDayEvents($arrEventsList, $this->ConvertToDecimal($actday));
        //}
    }

    /**
     * Check if parameters is 4-digit year
     * @param $year - string to be checked if it's 4-digit year
     */
    private function IsYear($year = "")
    {
        if(!strlen($year) == 4 || !is_numeric($year)) return false;
        for($i = 0; $i < 4; $i++){
            if(!(isset($year[$i]) && $year[$i] >= 0 && $year[$i] <= 9)){
                return false;
            }
        }
        return true;
    }

    /**
     * Check if parameters is month
     * @param $month - string to be checked if it's 2-digit month
     */
    private function IsMonth($month = "")
    {
        if(!strlen($month) == 2 || !is_numeric($month)) return false;
        for($i = 0; $i < 2; $i++){
            if(!(isset($month[$i]) && $month[$i] >= 0 && $month[$i] <= 9)){
                return false;
            }
        }
        return true;
    }

    /**
     * Check if parameters is day
     * @param $day - string to be checked if it's 2-digit day
     */
    private function IsDay($day = "")
    {
        if(!strlen($day) == 2 || !is_numeric($day)) return false;
        for($i = 0; $i < 2; $i++){
            if(!(isset($day[$i]) && $day[$i] >= 0 && $day[$i] <= 9)){
                return false;
            }
        }
        return true;
    }

    /**
     * Remove duplications from array
     * @param $arr_input
     */
    private function RemoveDuplications($arr_input = array()){
        $arr_output = array();
        if(is_array($arr_input)){
            foreach($arr_input as $key => $val){
                $arr_output[$val] = $val;
            }
        }
        return $arr_output;
    }

    /**
     * Prepare minutes and houres
     * @param $minutes_part
     * @param &$hours_part
     */
    private function PrepareMinutesHoures(&$minutes_part, &$hours_part)
    {
        if($minutes_part == "0" || $minutes_part == "60"){
            $minutes_part = "00";
            if($minutes_part == "60") $hours_part += 1;
        }
    }

    /**
     * Convert to decimal number with leading zero
     * @param $number
     */
    private function ConvertToDecimal($number)
    {
        return (($number < 0) ? "-" : "").((abs($number) < 10) ? "0" : "").abs($number);
    }

    /**
     * Convert to hour formar with leading zero
     * @param $number
     */
    private function ConvertToHour($number, $minutes = "00", $use_format = false)
    {
        $number = (($number < 10 && strlen($number) == 1) ? "0".$number : $number);
        if($use_format){
            if($this->timeFormat == "24"){
                return $number.":".$minutes;
            }else{
                return (($number <= 12) ? $number : ($number - 12)).":".$minutes." ".(($number < 12) ? "AM" : "PM");
            }
        }else{
            return $number.":".$minutes;
        }
    }

    /**
     * Parse hour from hour format string
     * @param $hour
     */
    private function ParseHour($hour)
    {
        $hour_array = explode(":", $hour);
        return (isset($hour_array[0]) ? $hour_array[0] : "0");
    }

    /**
     * Parse minutes from hour format string
     * @param $hour
     */
    private function ParseMinutes($hour)
    {
        $hour_array = explode(":", $hour);
        return (isset($hour_array[1]) ? $hour_array[1] : "0");
    }

    /**
     * Get formatted microtime
     */
    private function GetFormattedMicrotime(){
        list($usec, $sec) = explode(' ', microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * Prepare option for hours dropdown box
     */
    function PrepareOption($hour, $i_hour, $i_block = "", $blocks_count = 0){
        if($this->timeSlot == "15"){
            $hours_part = $i_hour;
            $minutes_part = ($i_block)*$this->timeSlot;
            $this->PrepareMinutesHoures($minutes_part, $hours_part);
            $i_converted = $this->ConvertToDecimal($hours_part);
            $i_converted_hour = $this->ConvertToHour($hours_part, $minutes_part);
            $i_converted_hour_formated = $this->ConvertToHour($hours_part, $minutes_part, true);
            return "<option value='".$i_converted_hour."'".(($hour == $i_converted) ? " selected='selected'" : "").">".(($this->timeFormat == "24") ? $i_converted_hour : $i_converted_hour_formated)."</option>";
        }elseif($this->timeSlot == "30"){
            $hours_part = $i_hour;
            $minutes_part = ($i_block)*$this->timeSlot;
            $this->PrepareMinutesHoures($minutes_part, $hours_part);
            $i_converted = $this->ConvertToDecimal($hours_part);
            $i_converted_hour = $this->ConvertToHour($hours_part, $minutes_part);
            $i_converted_hour_formated = $this->ConvertToHour($hours_part, $minutes_part, true);
            return "<option value='".$i_converted_hour."'".(($hour == $i_converted) ? " selected='selected'" : "").">".(($this->timeFormat == "24") ? $i_converted_hour : $i_converted_hour_formated)."</option>";
        }elseif($this->timeSlot == "45"){
            if($blocks_count % 3 == 0){
                $hours_part = $i_hour;
                $minutes_part = ($i_block)*15;
                $this->PrepareMinutesHoures($minutes_part, $hours_part);
                $i_converted = $this->ConvertToDecimal($hours_part);
                $i_converted_hour = $this->ConvertToHour($hours_part, $minutes_part);
                $i_converted_hour_formated = $this->ConvertToHour($hours_part, $minutes_part, true);
                return "<option value='".$i_converted_hour."'".(($hour == $i_converted) ? " selected='selected'" : "").">".(($this->timeFormat == "24") ? $i_converted_hour : $i_converted_hour_formated)."</option>";
            }else{
                return "";
            }
        }else{
            $i_converted = $this->ConvertToDecimal($i_hour);
            $i_converted_hour = $this->ConvertToHour($i_hour);
            $i_converted_hour_formated = $this->ConvertToHour($i_hour, "00", true);
            return "<option value='".$i_converted_hour."'".(($hour == $i_converted) ? " selected='selected'" : "").">".(($this->timeFormat == "24") ? $i_converted_hour : $i_converted_hour_formated)."</option>";
        }
    }

    /**
     * Prepare text for SQL
     */
    function PrepareText($str){
        $str = str_replace("'", "\'", $str);
        return $str;
    }

    /**
     * Prepare text for HTML
     */
    function PrepareFormatedText($str){
        $str = str_replace("'", "&#039;", $str);
        return $str;
    }

    /**
     * Prepare selected category where clause for SELECT SQL
     */
    function PrepareWhereClauseCategory(){
        $output = "";
        ///if($this->arrParameters["selected_category"]) $output = "AND ".DB_PREFIX."events_categories.id = ".(int)$this->arrParameters["selected_category"];
        return $output;
    }

    /**
     * Prepare Event Type where clause for SELECT SQL
     */
    function PrepareWhereClauseEventTime(){
        $output = "";
        if($this->timeSlot == "60"){
            $output = " AND SUBSTRING(".DB_PREFIX."calendar.event_time, 4, 2) = '00' ";
        }elseif($this->timeSlot == "30"){
            $output = " AND (SUBSTRING(".DB_PREFIX."calendar.event_time, 4, 2) = '00' OR SUBSTRING(".DB_PREFIX."calendar.event_time, 4, 2) = '30') ";
        }else{
            // 45 min. - all variations
            // 15 min. - all variations
        }
        return $output;
    }

    /**
     * Prepare From-To where clause for SELECT SQL
     */
    function PrepareWhereClauseFromToTime(){
        $output = "";
        if($this->fromHour != "0") $output .= " AND SUBSTRING(".DB_PREFIX."calendar.event_time, 1, 2) >= '".$this->ConvertToDecimal($this->fromHour)."' ";
        if($this->toHour != "0") $output .= " AND SUBSTRING(".DB_PREFIX."calendar.event_time, 1, 2) < '".$this->ConvertToDecimal($this->toHour)."' ";
        return $output;
    }

    /**
     * Insert Events Occurrences
     */
    function InsertEventsOccurrencesRepeatedly($insert_id, $start_date, $finish_date, $from_time, $to_time, $week_days = array()){

        $current_date  = $start_date." ".$from_time;
        $finish_date   = $finish_date." ".$to_time;
        $start_hour    = $this->ParseHour($from_time);
        $start_minutes = $this->ParseMinutes($from_time);
        $event_from_year  = substr($start_date, 0, 4);
        $event_from_month = substr($start_date, 5, 2);
        $event_from_day   = substr($start_date, 8, 2);

        $sql = "INSERT INTO ".DB_PREFIX."calendar (id, user_id, event_id, event_date, event_time) VALUES ";
        $offset = 0;
        $can_add = true;

        $sql_parts = 0;
        while($current_date < $finish_date){
            $current = getdate(mktime($start_hour,$start_minutes+$offset,0,$event_from_month,$event_from_day,$event_from_year));
            $curr_date = $current['year']."-".$this->ConvertToDecimal($current['mon'])."-".$this->ConvertToDecimal($current['mday']);
            $curr_time = $this->ConvertToDecimal($current['hours']).":".$this->ConvertToDecimal($current['minutes']);
            $current_date = $curr_date." ".$curr_time;

            // check if can insert in this week day
            if($week_days[$current['wday']] == "on"){
                if(!$this->allowEditingEventsInPast && ($current_date < date("Y-m-d H:i:s"))){
                    $can_add = false;
                }
                if($current_date < $finish_date && ($curr_time >= $from_time) && ($curr_time < $to_time)){
                    if($sql_parts++ > 0) $sql .= ", ";
                    $sql .= "(NULL, ".(int)$this->userID.", ".(int)$insert_id.", '".$curr_date."', '".$curr_time."')";
                }
            }
            $offset += $this->timeSlot;
        }
        if(!$sql_parts) $can_add = false;
        if(!$can_add){
            if(!$sql_parts) $this->arrMessages[] = $this->GetMessage("error", $this->lang['error_no_dates_found']);
            else $this->arrMessages[] = $this->GetMessage("error", $this->lang['msg_editing_event_in_past']);
        }else{
            // add event occurrences to calendar
            if($this->isDebug) $this->arrSQLs[] = $sql."<br />".database_error()."<br />";
            if(!database_void_query($sql)){
                $this->arrMessages[] = $this->GetMessage("error", $this->lang['error_inserting_new_events']);
            }else{
                return true;
                $this->DeleteCache();
            }
        }
        return false;
    }

    /**
     * Insert Events Occurrences
     */
    function InsertEventsOccurrences($insert_id, $start_date, $finish_date, $event_from_hour, $event_from_day, $event_from_month, $event_from_year, $check_if_exists = true){
        $current_date = $start_date;
        $start_hour = $this->ParseHour($event_from_hour);
        $start_minutes = $this->ParseMinutes($event_from_hour);
        // check for double occurrences of events
        if(!$this->allowEventsMultipleOccurrences){
            $sql_check = "SELECT id FROM ".DB_PREFIX."calendar WHERE user_id = ".(int)$this->userID." AND (";
            $check_if_exists = true;
        }else{
            $sql_check = "SELECT id FROM ".DB_PREFIX."calendar WHERE user_id = ".(int)$this->userID." AND event_id = ".(int)$insert_id." AND (";
        }
        $sql = "INSERT INTO ".DB_PREFIX."calendar (id, user_id, event_id, event_date, event_time) VALUES ";
        $offset = 0;
        $can_add = true;
        $convToHour = $this->ConvertToHour($this->toHour);
        $convFromHour = $this->ConvertToHour($this->fromHour);
        $sql_check_inner = "";
        while($current_date < $finish_date){
            $current = getdate(mktime($start_hour,$start_minutes+$offset,0,$event_from_month,$event_from_day,$event_from_year));
            $curr_date = $current['year']."-".$this->ConvertToDecimal($current['mon'])."-".$this->ConvertToDecimal($current['mday']);
            $curr_time = $this->ConvertToDecimal($current['hours']).":".$this->ConvertToDecimal($current['minutes']);
            $current_date = $curr_date." ".$curr_time;
            if(!$this->allowEditingEventsInPast && ($current_date < date("Y-m-d H:i:s"))){
                $can_add = false;
            }
            if($current_date < $finish_date && ($curr_time < $convToHour) && ($curr_time >= $convFromHour)){
                if($offset > 0) $sql .= ", ";
                $sql .= "(NULL, ".(int)$this->userID.", ".(int)$insert_id.", '".$curr_date."', '".$curr_time."')";
                if($offset > 0) $sql_check_inner .= " OR ";
                $sql_check_inner .= "(event_date = '".$curr_date."' AND event_time = '".$curr_time."')";
            }
            $offset += $this->timeSlot;
        }
        $sql_check .= (($sql_check_inner != "") ? $sql_check_inner : "1=1").")";
        if(!$can_add){
            $this->arrMessages[] = $this->GetMessage("error", $this->lang['msg_editing_event_in_past']);
        }else{
            // check if such event occurrences already exist
            if($this->isDebug) $this->arrSQLs[] = $sql_check."<br />".database_error()."<br />";
            if($check_if_exists && database_query($sql_check, ROWS_ONLY, FIRST_ROW_ONLY) > 0){
                if(!$this->allowEventsMultipleOccurrences){
                    $this->arrMessages[] = $this->GetMessage("error", $this->lang['error_duplicate_events_inserting']);
                }else{
                    $this->arrMessages[] = $this->GetMessage("error", $this->lang['error_duplicate_event_inserting']);
                }
            }else{
                // add event occurrences to calendar
                if($this->isDebug) $this->arrSQLs[] = $sql."<br />".database_error()."<br />";
                if(!database_void_query($sql)){
                    $this->arrMessages[] = $this->GetMessage("error", $this->lang['error_inserting_new_events']);
                }else{
                    return true;
                    $this->DeleteCache();
                }
            }
        }
        return false;
    }

    /**
     * Check action type
     * @param $event_action - event action
     */
    private function IsActionType($event_action){
        $type = "";
        switch($event_action)
        {
            // Categories actions
            case "categories_add":
            case "categories_edit":
            case "categories_details":
            case "categories_management":
            case "categories_delete":
            case "categories_update":
            case "categories_insert":
                $type = "category";
                break;

            // Statistics actions
            case "events_statistics":
                $type = "statistics";
                break;

            // Events actions
            case "events_add":
            case "events_edit":
            case "events_details":
            case "events_management":
            case "events_delete":
            case "events_update":
            case "events_insert":
            case "events_delete_by_range":
                $type = "event";
                break;
            default:
                break;
        }
        return $type;
    }

    /**
     * Get query string parameter value
     * @param $var - parameter
     * @param $method - method
     **/
    private function GetParameter($var = "", $default_value = "", $method = "request"){
        $output = "";
        switch($method){
            case "get":
                $output = isset($_GET[$var]) ? $_GET[$var] : $default_value;
                break;
            case "post":
                $output = isset($_POST[$var]) ? $_POST[$var] : $default_value;
                break;
            default:
                $output = isset($_REQUEST[$var]) ? $_REQUEST[$var] : $default_value;
                break;
        }
        if(!$this->CheckInput($output)) $output = "";
        return $output;
    }

    /**
     * Check input for bad characters
     * @param $input
     */
    private function CheckInput($input){

        if($input == "") return true;

        $error = 0;
        $bad_string = array("%20union%20", "/*", "*/union/*", "+union+", "load_file", "outfile", "document.cookie", "onmouse", "<script", "<iframe", "<applet", "<meta", "<style", "<form", "<img", "<body", "<link", "_GLOBALS", "_REQUEST", "_GET", "_POST", "include_path", "prefix", "http://", "https://", "ftp://", "smb://" );
        foreach($bad_string as $string_value){
            if(strstr($input, $string_value)) $error = 1;
        }

        if((preg_match("/<[^>]*script*\"?[^>]*>/i", $input)) ||
            (preg_match("/<[^>]*object*\"?[^>]*>/i", $input)) ||
            (preg_match("/<[^>]*iframe*\"?[^>]*>/i", $input)) ||
            (preg_match("/<[^>]*applet*\"?[^>]*>/i", $input)) ||
            (preg_match("/<[^>]*meta*\"?[^>]*>/i", $input)) ||
            (preg_match("/<[^>]*style*\"?[^>]*>/i", $input)) ||
            (preg_match("/<[^>]*form*\"?[^>]*>/i", $input)) ||
            (preg_match("/<[^>]*img*\"?[^>]*>/i", $input)) ||
            (preg_match("/<[^>]*onmouseover*\"?[^>]*>/i", $input)) ||
            (preg_match("/<[^>]*body*\"?[^>]*>/i", $input)) ||
            (preg_match("/\([^>]*\"?[^)]*\)/i", $input)) ||
            (preg_match("/ftp:\/\//i", $input)) ||
            (preg_match("/https:\/\//i", $input)) ||
            (preg_match("/http:\/\//i", $input)) )
        {
            $error = 1;
        }

        $ss = $_SERVER['HTTP_USER_AGENT'];

        if((preg_match("/libwww/i",$ss)) ||
            (preg_match("/^lwp/i",$ss))  ||
            (preg_match("/^Jigsaw/i",$ss)) ||
            (preg_match("/^Wget/i",$ss)) ||
            (preg_match("/^Indy\ Library/i",$ss)) )
        {
            $error = 1;
        }

        if($error){
            return false;
        }
        return true;
    }

    /**
     * Remove bad chars from input
     * @param $str_words - input
     */
    private function RemoveBadChars($str_words){
        $found = false;
        $bad_string = array("select", "drop", ";", "--", "insert","delete", "xp_", "%20union%20", "/*", "*/union/*", "+union+", "load_file", "outfile", "document.cookie", "onmouse", "<script", "<iframe", "<applet", "<meta", "<style", "<form", "<img", "<body", "<link", "_GLOBALS", "_REQUEST", "_GET", "_POST", "include_path", "prefix", "http://", "https://", "ftp://", "smb://", "onmouseover=", "onmouseout=");
        for ($i = 0; $i < count($bad_string); $i++){
            $str_words = str_replace($bad_string[$i], "", $str_words);
        }
        return $str_words;
    }

    /**
     * Get max day for month
     * @param $month - month
     * @param $day - day
     */
    function GetDayForMonth($month = "", $day = ""){
        if($day < 29){
            return $day;
        }elseif($day == 29){
            if((int)$month == 2){
                return 28;
            }else{
                return 29;
            }
        }elseif($day == 30){
            if((int)$month != 2){
                return 30;
            }else{
                return 28;
            }
        }elseif($day == 31){
            if((int)$month == 2){
                return 28;
            }elseif((int)$month == 4 || (int)$month == 6 || (int)$month == 9 || (int)$month == 11){
                return 30;
            }else{
                return 31;
            }
        }else{
            return 30;
        }
    }

    /**
     * Draws messages
     * @param $type - message type
     * @param $message - message text
     * @param $draw
     */
    function GetMessage($type, $message, $return=true)
    {
        if (!empty($message)) $message = "<div class='message_box_".$type."'><div class='message_content'>".$message."</div></div>";
        if($return) return $message;
        else echo $message;
    }

    private function GetMonthLastDay($month, $year){
        if(empty($month)) {
            $month = date('m');
        }
        if(empty($year)) {
            $year = date('Y');
        }
        $result = strtotime("{$year}-{$month}-01");
        $result = strtotime('-1 second', strtotime('+1 month', $result));
        return date('d', $result);
    }
}
