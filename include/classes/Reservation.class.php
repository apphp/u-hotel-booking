<?php

/***
 *	Class Reservation
 *  -------------- 
 *  Description : encapsulates Booking properties
 *  Updated	    : 22.09.2017
 *	Written by  : ApPHP
 *	
 *	PUBLIC:					STATIC:						PRIVATE:
 *  -----------				-----------					-----------
 *  __construct				RoomIsReserved				GetVatPercentDecimalPoints
 *  __destruct              DrawWidgetReservationCart  CheckHotelIdExists
 *  AddToReservation                                	GetReservationHotelId
 *  RemoveReservation									GetReservationCheckinForHotel
 *  ShowReservationInfo									GetReservationCheckoutForHotel
 *  ShowCheckoutInfo
 *  EmptyCart
 *  GetCartItems
 *  GetAdultsInCart
 *  GetChildrenInCart
 *  IsCartEmpty
 *  PlaceBooking
 *  DoReservation
 *  SendOrderEmail
 *  SendCancelOrderEmail
 *  DrawReservation
 *  LoadDiscountInfo
 *  ApplyDiscountCoupon
 *  RemoveDiscountCoupon
 *  EditToReservation
 *  GetInfoByRoomID
 *  GenerateBookingNumber
 *  GetVatPercent
 *  GetPackagesForReservedRooms
 *  GetCountReservationRooms
 *  GetReservationHotelIds
 *  IsCorrectReservedNumberRooms
 *  
 **/

class Reservation {

	public $arrReservation;
	public $error;
	public $message;

	private $fieldDateFormat;
	private $cartItems;
	private $roomsCount;
	private $cartTotalSum;
	private $firstNightSum;
	private $currentCustomerID;
	private $selectedUser;
	private $vatPercent;
	private $discountType;
	private $discountValue;
	private $discountCampaignID;
	private $discountCoupon;
	private $discountTitle;
	private $currencyFormat;
	private $lang;
	private $paypal_form_type;
	private $paypal_form_fields;
	private $paypal_form_fields_count;
	private $first_night_possible;
	private $firstNightCalculationType = 'real';
	private $bookingInitialFee = '0';
	private $guestTax = '0';
	private $vatIncludedInPrice = 'no';
	private $maximumAllowedReservations = '10';

    /** @const int */
	const PAYMENT_POA             = 0;
	const PAYMENT_ONLINE_ORDER    = 1;
	const PAYMENT_PAYPAL          = 2;
	const PAYMENT_2CO             = 3;
	const PAYMENT_AUTHORIZE_NET   = 4;
	const PAYMENT_BANK_TRANSFER   = 5;
	const PAYMENT_ACCOUNT_BALANCE = 6;


	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{
		global $objSettings;
		global $objLogin;

		if(!$objLogin->IsLoggedIn()){
			$this->currentCustomerID = (int)Session::Get('current_customer_id');
			$this->selectedUser = 'customer';
		}elseif($objLogin->IsLoggedInAsCustomer()){
			$this->currentCustomerID = $objLogin->GetLoggedID();
			$this->selectedUser = 'customer';			
		}else{
			if(Session::IsExists('sel_current_customer_id') && (int)Session::Get('sel_current_customer_id') != 0){
				$this->currentCustomerID = (int)Session::Get('sel_current_customer_id');
				$this->selectedUser = 'customer';
			}else{
				$this->currentCustomerID = $objLogin->GetLoggedID();
				$this->selectedUser = 'admin';
			}
		}

		// prepare currency info
		if(Application::Get('currency_code') == ''){
			$this->currencyCode = Currencies::GetDefaultCurrency();
			$this->currencyRate = '1';
		}else{
			//$default_currency_info = Currencies::GetDefaultCurrencyInfo();
			$this->currencyCode = Application::Get('currency_code');
			$this->currencyRate = Application::Get('currency_rate');
		}		

		$this->affiliate_id = isset($_COOKIE['affiliate_id']) ? prepare_input_alphanumeric($_COOKIE['affiliate_id']) : '';

		// prepare Booking settings
		$this->firstNightCalculationType = ModulesSettings::Get('booking', 'first_night_calculating_type');
		$this->bookingInitialFee = ModulesSettings::Get('booking', 'booking_initial_fee');
		$this->vatIncludedInPrice = ModulesSettings::Get('booking', 'vat_included_in_price');
		$this->maximumAllowedReservations = ModulesSettings::Get('booking', 'maximum_allowed_reservations');
		$this->guestTax = ModulesSettings::Get('booking', 'special_tax') * $this->currencyRate;
		
		// prepare VAT percent
		$this->vatPercent = $this->GetVatPercent();

		// preapre datetime format
		if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
			$this->fieldDateFormat = 'M d, Y';
		}else{
			$this->fieldDateFormat = 'd M, Y';
		}
		
		$this->lang = Application::Get('lang');
		$this->arrReservation = &$_SESSION[INSTALLATION_KEY.'reservation'];
		$this->arrReservationInfo = &$_SESSION[INSTALLATION_KEY.'reservation_info'];
		$this->cartItems = 0;
		$this->roomsCount = 0;
		$this->cartTotalSum = 0;
		$this->firstNightSum = 0;
		$this->first_night_possible = false;
		$this->paypal_form_type = 'multiple'; // single | multiple
		$this->paypal_form_fields = '';
		$this->currencyFormat = get_currency_format();		  

		if(count($this->arrReservation) > 0){
			$this->paypal_form_fields_count = 0;
			foreach($this->arrReservation as $key => $val){
				$room_price_w_meal_extrabeds = ($val['price'] + $val['meal_plan_price'] + $val['extra_beds_charge']);
				$this->cartItems += 1;
				$this->roomsCount += $val['rooms'];
				$this->cartTotalSum += ($room_price_w_meal_extrabeds * $this->currencyRate);
				if($this->firstNightCalculationType == 'average'){
					$this->firstNightSum += ($room_price_w_meal_extrabeds / $val['nights']);
				}else{				
					$this->firstNightSum += Rooms::GetPriceForDate($key, $val['from_date']);	
				}
				if($val['nights'] > 1) $this->first_night_possible = true;
				
				if($this->paypal_form_type == 'multiple' && $val['rooms'] > 0){
					
					$this->paypal_form_fields_count++;
					$this->paypal_form_fields .= draw_hidden_field('item_name_'.$this->paypal_form_fields_count, (FLATS_INSTEAD_OF_HOTELS ? _FLAT : _ROOM_TYPE).': '.$val['room_type'], false);
					$this->paypal_form_fields .= draw_hidden_field('quantity_'.$this->paypal_form_fields_count, $val['rooms'], false);
					$this->paypal_form_fields .= draw_hidden_field('amount_'.$this->paypal_form_fields_count, number_format((($val['price'] * $this->currencyRate) / $val['rooms']), '2', '.', ''), false);
					
					if(!empty($val['meal_plan_price'])){
                        $meal_plan_info = MealPlans::GetPlanInfo($val['meal_plan_id']);
                        $meal_plan_name = isset($meal_plan_info['name']) ? $meal_plan_info['name'] : '';
						$this->paypal_form_fields_count++;
						$this->paypal_form_fields .= draw_hidden_field('item_name_'.$this->paypal_form_fields_count, _MEAL_PLANS.': '.$meal_plan_name, false);
						$this->paypal_form_fields .= draw_hidden_field('quantity_'.$this->paypal_form_fields_count, 1, false);
						$this->paypal_form_fields .= draw_hidden_field('amount_'.$this->paypal_form_fields_count, number_format($val['meal_plan_price'], '2', '.', ''), false);
					}
					
					if($this->guestTax != 0){
						$total_guests = $val['rooms'] * ($val['adults'] + $val['children']);
						$this->paypal_form_fields_count++;
						$this->paypal_form_fields .= draw_hidden_field('item_name_'.$this->paypal_form_fields_count, _GUEST_TAX, false);
						$this->paypal_form_fields .= draw_hidden_field('quantity_'.$this->paypal_form_fields_count, 1, false);
						$this->paypal_form_fields .= draw_hidden_field('amount_'.$this->paypal_form_fields_count, number_format(($this->guestTax * $total_guests), '2', '.', ''), false);
					}
				}
			}
		}
		$this->cartTotalSum = number_format($this->cartTotalSum, 2, '.', '');

		// Prepare discount info
		$this->LoadDiscountInfo();

		$this->message = '';
		$this->error = '';
		
		//echo $this->firstNightSum;
		//echo '<pre>';
		//print_r($this->arrReservation);
		//echo '</pre>';
	}

	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	/**
	 * Add room to reservation
	 * @param $room_id
	 * @param $from_date
	 * @param $to_date
	 * @param $nights
	 * @param $rooms
	 * @param $price
	 * @param $adults
	 * @param $children
	 * @param $meal_plan_id
	 * @param $hotel_id
	 * @param $extra_beds
	 * @param $extra_bed_charge
	 */	
	public function AddToReservation($room_id, $from_date, $to_date, $nights, $rooms, $price, $adults, $children, $meal_plan_id, $hotel_id, $extra_beds, $extra_bed_charge)
	{
		if(!empty($room_id)){
            $this->CleanDiscountCoupon();
			$meal_plan_info = MealPlans::GetPlanInfo($meal_plan_id);
			
			// Specify meal multiplier
			if(COUNT_MEAL_FOR_CHILDREN){
				$meal_multiplier = $nights * $rooms * ($adults + $children);
			}else{
				$meal_multiplier = $nights * $rooms * $adults;
			}

            // check "allow_separate_gateways" settings
            if(ModulesSettings::Get('booking', 'allow_separate_gateways') == 'yes' && count($this->arrReservation) > 0){
                if(!$this->CheckHotelIdExists($hotel_id)){
                    $this->error = draw_important_message(FLATS_INSTEAD_OF_HOTELS ? _FLATS_ALERT_SAME_HOTEL_ROOMS : _ALERT_SAME_HOTEL_ROOMS, false);
                    return false;
                }                
            } 

			$room_info = Rooms::GetRoomFullInfo($room_id);
			$cancel_reservation_day = $room_info['cancel_reservation_day'] > 0 ? $room_info['cancel_reservation_day'] : ModulesSettings::Get('booking', 'customers_cancel_reservation');

			if(isset($this->arrReservation[$room_id])){
				// add new info for this room
				$this->arrReservation[$room_id]['from_date'] 	= $from_date;
				$this->arrReservation[$room_id]['to_date'] 	 	= $to_date;
				$this->arrReservation[$room_id]['nights'] 	 	= $nights;
				$this->arrReservation[$room_id]['rooms'] 	 	= $rooms;
				$this->arrReservation[$room_id]['price'] 	 	= $price;
				$this->arrReservation[$room_id]['adults'] 	 	= $adults;
				$this->arrReservation[$room_id]['children']  	= $children;
				$this->arrReservation[$room_id]['hotel_id']  	= (int)$hotel_id;
				$this->arrReservation[$room_id]['meal_plan_id'] = (int)$meal_plan_id;
				$this->arrReservation[$room_id]['meal_plan_price'] = isset($meal_plan_info['price']) ? number_format($meal_plan_info['price'] * $meal_multiplier, 2, '.', '') : 0;
				$this->arrReservation[$room_id]['room_id']  	= (int)$room_id;
				$this->arrReservation[$room_id]['room_type'] 	= $room_info['room_type'];
				$this->arrReservation[$room_id]['extra_beds']   = $extra_beds;
				$this->arrReservation[$room_id]['extra_beds_charge'] = number_format($extra_bed_charge * $nights * $rooms, 2, '.', '');
				$this->arrReservation[$room_id]['cancel_reservation_day'] = $cancel_reservation_day;
			}else{
				// just add new room
				$this->arrReservation[$room_id] = array(
					'from_date' => $from_date,
					'to_date'   => $to_date,
					'nights'    => $nights,
					'rooms'     => $rooms,
					'price'     => $price,
					'adults'    => $adults,
					'children'  => $children,
					'hotel_id'  => (int)$hotel_id,
					'meal_plan_id' => (int)$meal_plan_id,
					'meal_plan_price'  => isset($meal_plan_info['price']) ? number_format($meal_plan_info['price'] * $meal_multiplier, 2, '.', '') : 0,
					'room_type' => Rooms::GetRoomInfo($room_id, 'room_type'),
					'room_id'  	=> (int)$room_id,
					'extra_beds'    => $extra_beds,
					'extra_beds_charge' => number_format($extra_bed_charge * $nights * $rooms, 2, '.', ''),
					'cancel_reservation_day' => $cancel_reservation_day
				);
			}			
			$this->error = draw_success_message(FLATS_INSTEAD_OF_HOTELS ? _FLAT_WAS_ADDED : _ROOM_WAS_ADDED, false);
		}else{
			$this->error = draw_important_message(_WRONG_PARAMETER_PASSED, false);
		}
	}

	/**
	 * Edit room to reservation
	 * @param int $room_id
	 * @param array $params
	 * @return boolean
	 */	
	public function EditToReservation($room_id, $params)
	{
		if(empty($room_id) || !isset($this->arrReservation[$room_id])){
			return false;
		}

		$from_date              = isset($params['from_date'])              ? $params['from_date']              : $this->arrReservation[$room_id]['from_date'];
		$to_date                = isset($params['to_date'])                ? $params['to_date']                : $this->arrReservation[$room_id]['to_date'];
		$nights                 = isset($params['nights'])                 ? $params['nights']                 : $this->arrReservation[$room_id]['nights'];
		$rooms                  = isset($params['rooms'])                  ? $params['rooms']                  : $this->arrReservation[$room_id]['rooms'];
		$price                  = isset($params['price'])                  ? $params['price']                  : $this->arrReservation[$room_id]['price'];
		$adults                 = isset($params['adults'])                 ? $params['adults']                 : $this->arrReservation[$room_id]['adults'];
		$children               = isset($params['children'])               ? $params['children']               : $this->arrReservation[$room_id]['children'];
		$hotel_id               = isset($params['hotel_id'])               ? $params['hotel_id']               : $this->arrReservation[$room_id]['hotel_id'];
		$meal_plan_id           = isset($params['meal_plan_id'])           ? $params['meal_plan_id']           : $this->arrReservation[$room_id]['meal_plan_id'];
		$meal_plan_price        = isset($params['meal_plan_price'])        ? $params['meal_plan_price']        : $this->arrReservation[$room_id]['meal_plan_price'];
		$room_type              = isset($params['room_type'])              ? $params['room_type']              : $this->arrReservation[$room_id]['room_type'];
		$extra_beds             = isset($params['extra_beds'])             ? $params['extra_beds']             : $this->arrReservation[$room_id]['extra_beds'];
		$extra_beds_charge      = isset($params['extra_beds_charge'])      ? $params['extra_beds_charge']      : $this->arrReservation[$room_id]['extra_beds_charge'];
		$cancel_reservation_day = isset($params['cancel_reservation_day']) ? $params['cancel_reservation_day'] : $this->arrReservation[$room_id]['cancel_reservation_day'];
		// edit info for room_id
		$this->arrReservation[$room_id]['from_date']              = $from_date;
		$this->arrReservation[$room_id]['to_date']                = $to_date;
		$this->arrReservation[$room_id]['nights']                 = $nights;
		$this->arrReservation[$room_id]['rooms']                  = $rooms;
		$this->arrReservation[$room_id]['price']                  = $price;
		$this->arrReservation[$room_id]['adults']                 = $adults;
		$this->arrReservation[$room_id]['children']               = $children;
		$this->arrReservation[$room_id]['hotel_id']               = (int)$hotel_id;
		$this->arrReservation[$room_id]['meal_plan_id']           = (int)$meal_plan_id;
		$this->arrReservation[$room_id]['meal_plan_price']        = $meal_plan_price;
		$this->arrReservation[$room_id]['room_type']              = $room_type;
		$this->arrReservation[$room_id]['room_id']  			  = (int)$room_id;
		$this->arrReservation[$room_id]['extra_beds']             = $extra_beds;
		$this->arrReservation[$room_id]['extra_beds_charge']      = $extra_beds_charge;
		$this->arrReservation[$room_id]['cancel_reservation_day'] = $cancel_reservation_day;

		return true;
	}

	/**
	 * Get count reservation rooms for hotel
	 * @param int $hotel_id
	 * @return int
	 */
	public function GetCountReservationRooms($hotel_id = 0)
	{
		$count_rooms = 0;

		if(!empty($this->arrReservation)){
			foreach($this->arrReservation as $reservation_room){
				if($hotel_id == 0 || $reservation_room['hotel_id'] == $hotel_id){
					$count_rooms += $reservation_room['rooms'];
				}
			}
		}

		return $count_rooms;
	}

	/**
	 * Get info by room id reservation
	 * @param int $room_id
	 * @return array
	 */	
	public function GetInfoByRoomID($room_id)
	{
		if(empty($room_id) || !isset($this->arrReservation[$room_id])){
			return array();
		}

		return $this->arrReservation[$room_id];
	}

	/**
	 * Remove room from the reservation cart
	 * @param $room_id
	 * @return void
	 */
	public function RemoveReservation($room_id)
	{
		if((int)$room_id > 0){
			if(isset($this->arrReservation[$room_id]) && $this->arrReservation[$room_id] > 0){
				unset($this->arrReservation[$room_id]);
				$this->error = draw_success_message(FLATS_INSTEAD_OF_HOTELS ? _FLAT_WAS_REMOVED : _ROOM_WAS_REMOVED, false);
			}else{
				$this->error = draw_important_message(FLATS_INSTEAD_OF_HOTELS ? _FLAT_NOT_FOUND : _ROOM_NOT_FOUND, false);
			}
		}else{
			$this->error = draw_important_message(FLATS_INSTEAD_OF_HOTELS ? _FLAT_NOT_FOUND : _ROOM_NOT_FOUND, false);
		}
	}	

    /** 
	 * Show Reservation Cart on the screen
	 * @param $draw
	 */	
	public function ShowReservationInfo($draw = true)
	{
		if(function_exists('Template_ShowReservationInfo')){				
            Template_ShowReservationInfo($this);
            return;
        }

		global $objLogin, $objSettings;
		
		$align_left = Application::Get('defined_left');
		$align_right = Application::Get('defined_right');
		$class_left = 'rc_'.Application::Get('defined_left');
		$class_right = 'rc_'.Application::Get('defined_right');
		$allow_children = ModulesSettings::Get('rooms', 'allow_children');
		$allow_extra_beds = ModulesSettings::Get('rooms', 'allow_extra_beds');
		$meal_plans_count = MealPlans::MealPlansCount();
		$total_adults = 0;
		$total_children = 0;
		$rooms_guests = array();
		// Count all guests
		$all_adults_count = 0;
		$show_button_next = true;
		$output = '';

		if(TYPE_FILTER_TO_NUMBER_ROOMS == 'hotel'){
			$room_packages = $this->GetPackagesForReservedRooms();
			$reservation_hotel_ids = $this->GetReservationHotelIds();
			foreach($reservation_hotel_ids as $hotel_id){
				if(isset($room_packages[$hotel_id])){
					$min_rooms = !empty($room_packages[$hotel_id]['min_rooms_packages']['minimum_rooms']) ? $room_packages[$hotel_id]['min_rooms_packages']['minimum_rooms'] : 0;
					$max_rooms = !empty($room_packages[$hotel_id]['max_rooms_packages']['maximum_rooms']) ? $room_packages[$hotel_id]['max_rooms_packages']['maximum_rooms'] : -1;
					$count_reservation_rooms = $this->GetCountReservationRooms($hotel_id);
                    // Сheck that there are less rooms in the hotel than min. rooms
                    if(RESIDUE_ROOMS_IS_AVAILABILITY){
						$availability_rooms_for_hotel = 0;
                        foreach($this->arrReservation as $room_id => $reservation_room){
                            if($hotel_id != $reservation_room['hotel_id']){
                                continue;
                            }

                            $checkin = $reservation_room['from_date'];
                            $checkout = $reservation_room['to_date'];
                            $rooms = $reservation_room['rooms'];

                            $availability_rooms = Rooms::GetCountAvailabilityRoomsById($room_id, $checkin, $checkout);
                            $availability_rooms_for_hotel += $availability_rooms;
                        }
                        $min_rooms = $availability_rooms_for_hotel < $min_rooms ? $availability_rooms_for_hotel : $min_rooms;
                    }
					$show_error_message = false;
					$message = '';

					if($min_rooms > 1 && $max_rooms > 0){
						$message = str_replace(array('_HOTEL_NAME_', '_MIN_ROOMS_', '_MAX_ROOMS_'), array($room_packages[$hotel_id]['min_rooms_packages']['hotel_name'], $min_rooms, $max_rooms), _HOTEL_RESTRICTIONS_MIN_MAX_ROOMS);
					}elseif($min_rooms > 1){
						$message = str_replace(array('_HOTEL_NAME_', '_MIN_ROOMS_'), array($room_packages[$hotel_id]['min_rooms_packages']['hotel_name'], $min_rooms), _HOTEL_RESTRICTIONS_MIN_ROOMS);
					}elseif($max_rooms > 0){
						$message = str_replace(array('_HOTEL_NAME_', '_MAX_ROOMS_'), array($room_packages[$hotel_id]['max_rooms_packages']['hotel_name'], $max_rooms), _HOTEL_RESTRICTIONS_MAX_ROOMS);
					}

					if(!empty($message)){
						$output .= draw_default_message($message, false);
					}

					if($max_rooms != '-1' && $count_reservation_rooms > $max_rooms){
						$error_message = str_replace('_NUM_ROOMS_', $max_rooms, _CANNOT_BOOKING_MORE);
						$show_error_message = true;
						$show_button_next = false;
					}elseif($count_reservation_rooms < $min_rooms){
						$error_message = str_replace('_NUM_ROOMS_', $min_rooms, _CONTINUE_MUST_BOOK_MIN);
						$show_error_message = true;
						$show_button_next = false;
					}
					
					if($show_error_message){
						$output .= draw_important_message($error_message, false);
					}
				}
			}
		}else{
			foreach($this->arrReservation as $room_id => $reservation_room){
				$show_error_message = false;

				$checkin = $reservation_room['from_date'];
				$checkout = $reservation_room['to_date'];
				$rooms = $reservation_room['rooms'];
				$room_type = $reservation_room['room_type'];
				$hotel_id = $reservation_room['hotel_id'];

				$min_rooms_packages = Packages::GetMinimumRooms($checkin, $checkout, $hotel_id, false);
				$max_rooms_packages = Packages::GetMaximumRooms($checkin, $checkout, $hotel_id, false);

				$hotel_name = !empty($min_rooms_packages['hotel_name']) ? $min_rooms_packages['hotel_name'] : $max_rooms_packages['hotel_name'];

				$maximum_rooms = $max_rooms_packages['maximum_rooms'];
                if(RESIDUE_ROOMS_IS_AVAILABILITY){
                    $availability_rooms = Rooms::GetCountAvailabilityRoomsById($room_id, $checkin, $checkout);
	    			$minimum_rooms = $availability_rooms < $min_rooms_packages['minimum_rooms'] ? $availability_rooms : $min_rooms_packages['minimum_rooms'];
                }else{
                    $minimum_rooms = $min_rooms_packages['minimum_rooms'];
                }

				if(!empty($maximum_rooms) && $maximum_rooms != '-1' && $rooms > $maximum_rooms){
					$error_message  = '<b>'.$room_type.' <small>('.$hotel_name.')</small></b>';
					$error_message .= '<br>'.str_replace('_NUM_ROOMS_', $maximum_rooms, _CANNOT_BOOKING_MORE);
					$show_error_message = true;
					$show_button_next = false;
				}elseif($rooms < $minimum_rooms){
					$error_message  = '<b>'.$room_type.' <small>('.$hotel_name.')</small></b>';
					$error_message .= '<br>'.str_replace('_NUM_ROOMS_', $minimum_rooms, _CONTINUE_MUST_BOOK_MIN);
					$show_error_message = true;
					$show_button_next = false;
				}
				
				if($show_error_message){
					$output .= draw_important_message($error_message, false);
				}
			}
		}
	
		if(count($this->arrReservation) > 0){
	
			$output .= '<div class="table-responsive div-room-prices offset-1">';
			$output .= '<form id="update-room-form" action="index.php?page=booking" method="post">
			'.draw_hidden_field('act', 'update', false).'
			'.draw_hidden_field('room_id', '', false).'
			'.draw_hidden_field('from_date', '', false).'
			'.draw_hidden_field('to_date', '', false).'
			'.draw_hidden_field('adults', '', false).'
			'.draw_hidden_field('children', '', false).'
			'.draw_token_field(false).'
			</form>';
			
			$output .= '<table class="reservation_cart" width="100%" align="center"><thead>';
			$output .= '<tr class="header">				
				<th '.(FLATS_INSTEAD_OF_HOTELS ? 'colspan="2" ' : '').'class="'.$class_left.'" width="30px">&nbsp;</th>
				<th align="'.$align_left.'">'.(FLATS_INSTEAD_OF_HOTELS ? _FLAT : _ROOM_TYPE).'</th>
				<th class="center" width="60px">'._FROM.'</th>
				<th class="center" width="60px">'._TO.'</th>
				<th width="40px" class="center">&nbsp;'._NIGHTS.'&nbsp;</th>
				'.(FLATS_INSTEAD_OF_HOTELS ? '' : '<th width="40px" class="center">&nbsp;'._ROOMS.'&nbsp;</th>').'
				<th width="70px" colspan="3" class="center">'._OCCUPANCY.'</th>
				'.(($meal_plans_count) ? '<th width="60px" class="center">'._MEAL_PLANS.'</th>' : '<th style="padding:0px;">&nbsp;</th>').'
				<th width="65px" class="'.$class_right.'">'._PRICE.'</th>
			</tr>';

			$output .= '<tr style="font-size:10px;background-color:transparent;">				
				<th colspan="6"></th>
				<th class="center">'._ADULTS.'</th>
				'.(($allow_children == 'yes') ? '<th class="center">'._CHILDREN.'</th>' : '<th></th>').'
				'.(($allow_extra_beds == 'yes') ? '<th class="center">'._EXTRA_BED.'</th>' : '<th></th>').' 
				<th colspan="2"></th>
			</tr>';
			
			$output .= '</thead>
			<tr><td colspan="11" nowrap height="5px"></td></tr>';

			$order_price = 0;
			$min_nights = ModulesSettings::Get('booking', 'minimum_nights');
			$search_availability_period = ModulesSettings::Get('rooms', 'search_availability_period');
			$objRoom = new Rooms();
			$discount_night_value = 0;
			$max_rooms = 0;
			
			foreach($this->arrReservation as $key => $val)
			{
				$sql = 'SELECT
							'.TABLE_ROOMS.'.id,
							'.TABLE_ROOMS.'.hotel_id,
                            '.TABLE_ROOMS.'.room_type,
                            '.TABLE_ROOMS.'.room_icon_thumb,
							'.TABLE_ROOMS.'.max_adults,
							'.TABLE_ROOMS.'.max_children, 
							'.TABLE_ROOMS.'.default_price as price,
							'.TABLE_ROOMS.'.discount_guests_type,
							'.TABLE_ROOMS.'.discount_guests_3,
							'.TABLE_ROOMS.'.discount_guests_4,
							'.TABLE_ROOMS.'.discount_guests_5,
							'.TABLE_ROOMS.'.discount_night_type,
							'.TABLE_ROOMS.'.discount_night_3,
							'.TABLE_ROOMS.'.discount_night_4,
							'.TABLE_ROOMS.'.discount_night_5,
							'.TABLE_ROOMS_DESCRIPTION.'.room_type as loc_room_type,
							'.TABLE_ROOMS_DESCRIPTION.'.room_short_description as loc_room_short_description,
							'.TABLE_HOTELS_DESCRIPTION.'.name as hotel_name
						FROM '.TABLE_ROOMS.'
							INNER JOIN '.TABLE_ROOMS_DESCRIPTION.' ON '.TABLE_ROOMS.'.id = '.TABLE_ROOMS_DESCRIPTION.'.room_id
							INNER JOIN '.TABLE_HOTELS_DESCRIPTION.' ON '.TABLE_ROOMS.'.hotel_id	= '.TABLE_HOTELS_DESCRIPTION.'.hotel_id
						WHERE
							'.TABLE_ROOMS.'.id = '.$key.' AND
							'.TABLE_ROOMS_DESCRIPTION.'.language_id = \''.$this->lang.'\' AND
							'.TABLE_HOTELS_DESCRIPTION.'.language_id = \''.$this->lang.'\' ';
							
				$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($result[1] > 0){			
                    $meal_plan_info = MealPlans::GetPlanInfo($val['meal_plan_id']);
                    $meal_plan_name = isset($meal_plan_info['name']) ? $meal_plan_info['name'] : '';
					$room_icon_thumb = ($result[0]['room_icon_thumb'] != '') ? $result[0]['room_icon_thumb'] : 'no_image.png';
					$room_price_w_meal_extrabeds = ($val['price'] + $val['meal_plan_price'] + $val['extra_beds_charge']);

					if(ModulesSettings::Get('rooms', 'guests_stay_discount') == 'yes'){
						$rooms_guests[$key] = array(
							'price' => $val['price'] / ($val['rooms'] * $val['nights']),
							'rooms_count' => $val['rooms'],
							'adults_count' => $val['adults'] * $val['rooms'],
							'nights' => $val['nights'],
							'discount_type' => $result[0]['discount_guests_type'],
							'discount_guests_3' => $result[0]['discount_guests_3'],
							'discount_guests_4' => $result[0]['discount_guests_4'],
							'discount_guests_5' => $result[0]['discount_guests_5'],
							'rooms_price' => $val['price'],
						);
						$all_adults_count += $val['adults'] * $val['rooms'];
						$max_rooms = $val['rooms'] > $max_rooms ? $val['rooms'] : $max_rooms;
					}
					
					$date_form = format_date($val['from_date'], $objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'm/d/Y' : 'd/m/Y', '', false);
					$date_to = format_date($val['to_date'], $objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'm/d/Y' : 'd/m/Y', '', false);
					$output .= '<tr>
							<td '.(FLATS_INSTEAD_OF_HOTELS ? 'colspan="2" ' : '').'align="center"><a href="index.php?page=booking&act=remove&rid='.$key.'"><img src="images/remove.gif" width="16" height="16" border="0" title="'._REMOVE_ROOM_FROM_CART.'" alt="" /></a></td>							
							<td>
							    '.(FLATS_INSTEAD_OF_HOTELS ? '' : ('<b>'.prepare_link('rooms', 'room_id', $result[0]['id'], $result[0]['loc_room_type'], $result[0]['loc_room_type'], '', _CLICK_TO_VIEW).'</b><br />')).'
								'.prepare_link(FLATS_INSTEAD_OF_HOTELS ? 'flats' : 'hotels', FLATS_INSTEAD_OF_HOTELS ? 'fid' : 'hid', $result[0]['hotel_id'], $result[0]['hotel_name'], $result[0]['hotel_name'], '', _CLICK_TO_VIEW).'
							</td>
							<td colspan="2">
								<table>
									<tr>
										<td align="center"><input class="form-control from_date" id="from_date_'.$result[0]['id'].'" data-id="'.$result[0]['id'].'" placeholder="'.$objSettings->GetParameter('date_format').'" type="text" data-value="'.$date_form.'" value="'.$date_form.'"></td>
										<td align="center"><input class="form-control to_date" id="to_date_'.$result[0]['id'].'" data-id="'.$result[0]['id'].'" placeholder="'.$objSettings->GetParameter('date_format').'" type="text" data-value="'.$date_to.'" value="'.$date_to.'"></td>
									</tr>
									<tr><td colspan="2" style="text-align:center;font-size:12px;"><a href="javascript:void(0);" data-id="'.$result[0]['id'].'" class="apply-change-date">'._APPLY_CHANGES.'</a></td></tr>
								</table>
							</td>
							<td align="center">'.$val['nights'].'</td>
							'.(FLATS_INSTEAD_OF_HOTELS ? '' : '<td align="center">'.$val['rooms'].'</td>');
							
							// Adults
							$output .= '<td align="center">';
							if(ALLOW_ADULTS_SELECTING){
								$output .= '<select name="adults" class="form-control adults adults_ddl w50" data-id="'.$result[0]['id'].'">';
								for($i = 1; $i <= $result[0]['max_adults']; $i++){
									$output .= '<option value="'.$i.'"'.($i == $val['adults'] ? ' selected="selected"' : '').'>'.$i.'</option>';
								}
								$output .= '</select>';								
							}else{
								$output .= $val['adults'];
							}
							$output .= '</td>';
							
							// Children
							if($allow_children == 'yes'){
								$output .= '<td align="center">';	
								if(ALLOW_CHILDREN_SELECTING){
									$output .= '<select name="children" class="form-control children child_ddl w50" data-id="'.$result[0]['id'].'">';
									for($i = 0; $i <= $result[0]['max_children']; $i++){
										$output .= '<option value="'.$i.'"'.($i == $val['children'] ? ' selected="selected"' : '').'>'.$i.'</option>';
									}
									$output .= '</select>';									
								}else{
									$output .= $val['children'];
								}								
								$output .= '</td>';	
							}
							
							$output .= (($allow_extra_beds == 'yes') ? '<td align="center">'.$val['extra_beds'].'</td>' : '<td></td>').'
							'.(($meal_plans_count) ? '<td align="center" style="cursor:help;" title="'.(($val['nights'] > 1) ? _RATE_PER_NIGHT_AVG : _RATE_PER_NIGHT).'">'.$meal_plan_name.'</td>' : '<td></td>').'
							<td align="'.$align_right.'">'.Currencies::PriceFormat($room_price_w_meal_extrabeds * $this->currencyRate, '', '', $this->currencyFormat).'&nbsp;<a class="price_link" href="javascript:void(0);" onclick="javascript:appToggleElement(\'row_prices_'.$key.'\')" title="'._CLICK_TO_SEE_PRICES.'">(+)</a></td>
						</tr>
						<tr><td colspan="11" nowrap height="1px"></td></tr>
					    <tr><td colspan="11" align="'.$align_left.'">
								<span id="row_prices_'.$key.'" style="margin:5px 10px;display:none;">								
								<table width="100%">
								<tr>
									<td width="90px"><img src="images/rooms/'.$room_icon_thumb.'" alt="" width="80px" height="65px" /></td>
									<td>
									'._ROOM_PRICE.': '.Currencies::PriceFormat(($val['price'] * $this->currencyRate), '', '', $this->currencyFormat).'<br>
									'._MEAL_PLANS.': '.Currencies::PriceFormat(($val['meal_plan_price'] * $this->currencyRate), '', '', $this->currencyFormat).'<br>
									'._EXTRA_BEDS.': '.Currencies::PriceFormat(($val['extra_beds_charge'] * $this->currencyRate), '', '', $this->currencyFormat).'<br>
									'._RATE_PER_NIGHT.': '.Currencies::PriceFormat(($room_price_w_meal_extrabeds * $this->currencyRate) / $val['nights'], '', '', $this->currencyFormat).'<br>
									</td>
								</tr>
								<tr><td colspan="2">'.Rooms::GetRoomPricesTable($key).'</td></tr>
								</table>
								</span>
							</td>
						</tr>';
					$output .= '<script>
						jQuery(function() {
							var minDate = new Date();
							var maxDate = new Date();
							// min date + 1 day
							minDate.setTime(Date.parse("'.$val['from_date'].'") + ('.((int)$min_nights > 0 ? (int)$min_nights.' * ' : '' ).'24 * 60 * 60 * 1000));
                            '.(ModulesSettings::Get('booking', 'allow_booking_in_past') == 'yes' && $objLogin->IsLoggedInAsAdmin()
                                ? 'minDate.setFullYear(minDate.getFullYear() - 1);'
                                : '').'
                            jQuery("#from_date_'.$result[0]['id'].'").datepicker({
                                '.(ModulesSettings::Get('booking', 'allow_booking_in_past') == 'yes' && $objLogin->IsLoggedInAsAdmin()
                                    ? 'defaultDate: "-1y",minDate: "-1y",'
                                    : 'minDate: 0,').'
								
								maxDate: "+'.(int)$search_availability_period.'y",
								numberOfMonths: 1,
                                dateFormat: "'.($objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'mm/dd/yy' : 'dd/mm/yy').'"
							});
							jQuery("#to_date_'.$result[0]['id'].'").datepicker({
                                '.(ModulesSettings::Get('booking', 'allow_booking_in_past') == 'yes' && $objLogin->IsLoggedInAsAdmin()
                                    ? 'defaultDate: "-1y",'
                                    : '').'
								minDate: minDate,
								maxDate: "+'.(int)$search_availability_period.'y +'.((int)$min_nights > 0 ? (int)$min_nights : '1').'d",
								numberOfMonths: 1,
                                dateFormat: "'.($objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'mm/dd/yy' : 'dd/mm/yy').'"
							});
						});
					</script>';
					$order_price += ($room_price_w_meal_extrabeds * $this->currencyRate);
					$total_adults += $val['rooms'] * $val['adults'];
					$total_children += ($allow_children == 'yes') ? $val['rooms'] * $val['children'] : 0;
					
					// If has been ordered 3 and more nights, check an additional discount on the night
					if(ModulesSettings::Get('rooms', 'long_term_stay_discount') == 'yes'){
						if($val['nights'] >= 3){
							$discount_value = 0;
							if($val['nights'] == 3 && !empty($result[0]['discount_night_3'])){
								$discount_value = $result[0]['discount_night_3'];
							}elseif($val['nights'] == 4 && !empty($result[0]['discount_night_4'])){
								$discount_value = $result[0]['discount_night_4'];
							}else{
								$discount_value = $result[0]['discount_night_5'];
							}

							// 0 - Fixed price, 1 - Percentage
							if($result[0]['discount_night_type'] == 1){
								$discount_nights = ($val['price'] * ($discount_value / 100));
                                if(!empty($rooms_guests[$key])){
    								$rooms_guests[$key]['price'] -= $rooms_guests[$key]['price'] * ($discount_value / 100);
	    							$rooms_guests[$key]['rooms_price'] -= $rooms_guests[$key]['rooms_price'] * ($discount_value / 100);
                                }
								
							}else{
								if($val['price'] > $discount_value){
                                    $discount_nights = ($val['price'] / ($val['rooms'] * $val['nights'])  > $discount_value ? $discount_value * $val['rooms'] * $val['nights'] : $val['price']);
                                    if(!empty($rooms_guests[$key])){
    									$rooms_guests[$key]['price'] -= $discount_value;
	    								$rooms_guests[$key]['rooms_price'] -= $discount_nights;
                                    }
								}else{
                                    $discount_nights = $val['price'];
                                    if(!empty($rooms_guests[$key])){
    									$rooms_guests[$key]['price'] = 0;
	    								$rooms_guests[$key]['rooms_price'] = 0;
                                    }
								}
							}
							// The discount can't exceed the cost per room
							$discount_nights = $discount_nights > $val['price'] ? $val['price'] : $discount_nights;
							$discount_night_value += $discount_nights * $this->currencyRate;
                            $this->arrReservation[$key]['discount_nights'] = $discount_nights;
						}
					}
				}
			}

			// draw sub-total row			
			$output .= '<tr>
					<td colspan="7"></td>
					<td class="td '.$class_left.'" colspan="3"><b>'._SUBTOTAL.': </b></td>
					<td class="td '.$class_right.'" align="'.$align_right.'"><b>'.Currencies::PriceFormat($order_price, '', '', $this->currencyFormat).'</b></td>
				</tr>';				

			$output .= '<tr><td colspan="11" nowrap="nowrap" height="15px"></td></tr>';

			if($discount_night_value > 0){
				$order_price -= $discount_night_value;
				$output .= '<tr>
						<td colspan="7"></td>
						<td class="td '.$class_left.'" colspan="3"><b><span style="color:#a60000">'._LONG_TERM_STAY_DISCOUNT.': </span></b></td>
						<td class="td '.$class_right.'" align="'.$align_right.'"><b><span style="color:#a60000">- '.Currencies::PriceFormat($discount_night_value, '', '', $this->currencyFormat).'</span></b></td>
					</tr>';				
			}

			if(ModulesSettings::Get('rooms', 'guests_stay_discount') == 'yes'){
				$discount_value = 0;
				if(TYPE_DISCOUNT_GUEST == 'guests'){
					$count_adults_or_guests = $all_adults_count;
				}else{
					$count_adults_or_guests = $max_rooms;
				}
				if($count_adults_or_guests >= 3){
					foreach($rooms_guests as $key => $info_room){
						if(TYPE_DISCOUNT_GUEST != 'guests'){
							$count_adults_or_guests = $info_room['rooms_count'];
							if($count_adults_or_guests < 3){
								continue;
							}
						}

						$discount_guests = 0;
						if($count_adults_or_guests == 3 && !empty($info_room['discount_guests_3'])){
							$discount_guests = $info_room['discount_guests_3'];
						}elseif($count_adults_or_guests == 4 && !empty($info_room['discount_guests_4'])){
							$discount_guests = $info_room['discount_guests_4'];
						}else{
							$discount_guests = $info_room['discount_guests_5'];
						}
						
						// 0 - Fixed price, 1 - Percentage
						if($info_room['discount_type'] == 1){
							$discount_room = ($info_room['rooms_price'] * ($discount_guests / 100));
						}else{
							$discount_room = ($info_room['price'] > $discount_guests ? $discount_guests : $info_room['price']) * $info_room['rooms_count'] * $info_room['nights'];
						}

						// The discount can't exceed the cost per room
						$discount_room = $discount_room > $info_room['rooms_price'] ? $info_room['rooms_price'] : $discount_room;
						$discount_value += $discount_room * $this->currencyRate;
                        $this->arrReservation[$key]['discount_guests'] = $discount_room;
					}
				}

				if($discount_value > 0){
					$order_price -= $discount_value;
					$output .= '<tr>
							<td colspan="7"></td>
							<td class="td '.$class_left.'" colspan="3"><b><span style="color:#a60000">'.(TYPE_DISCOUNT_GUEST == 'guests' ? _GUESTS_DISCOUNT : _ROOMS_DISCOUNT).': </span></b></td>
							<td class="td '.$class_right.'" align="'.$align_right.'"><b><span style="color:#a60000">- '.Currencies::PriceFormat($discount_value, '', '', $this->currencyFormat).'</span></b></td>
						</tr>';				
				}
			}			
		
			// Calculate discount			
			$discount_value = ($this->discountType == '1' ? ($order_price * ($this->discountValue / 100)) : $this->discountValue);
			$order_price -= $discount_value;
			
			// Calculate VAT 
			$vat_cost = (($order_price + $this->bookingInitialFee + ($this->guestTax * ($total_adults + $total_children))) * ($this->vatPercent / 100));
			$cart_total = ($order_price + $this->bookingInitialFee + ($this->guestTax * ($total_adults + $total_children))) + $vat_cost;

			if($this->discountCampaignID != '' || $this->discountCoupon != ''){
				if(empty($this->discountTitle)){
					$this->discountTitle = $this->discountCoupon ? _COUPON_DISCOUNT : _DISCOUNT;
				}
				$output .= '<tr>
						<td colspan="7"></td>
						<td class="td '.$class_left.'" colspan="3"><b><span style="color:#a60000">'.$this->discountTitle.': (';
						
						// 0 - Fixed price, 1 - Percentage
						if($this->discountType == 1){
							$output .= Currencies::PriceFormat($this->discountValue, '%', 'after', $this->currencyFormat);
						}else{
							$output .= Currencies::PriceFormat($this->discountValue, Application::Get('currency_symbol'), 'before', $this->currencyFormat);
						}
						
						$output .= ')</span></b></td>
						<td class="td '.$class_right.'" align="'.$align_right.'"><b><span style="color:#a60000">- '.Currencies::PriceFormat($discount_value, '', '', $this->currencyFormat).'</span></b></td>
					</tr>';				
			}
			if(!empty($this->bookingInitialFee)){
				$output .= '<tr>
						<td colspan="7"></td>
						<td class="td '.$class_left.'" colspan="3"><b>'._INITIAL_FEE.': </b></td>
						<td class="td '.$class_right.'" align="'.$align_right.'"><b>'.Currencies::PriceFormat($this->bookingInitialFee, '', '', $this->currencyFormat).'</b></td>
					</tr>';								
			}
			if(!empty($this->guestTax)){
				$output .= '<tr>
						<td colspan="7"></td>
						<td class="td '.$class_left.'" colspan="3"><b>'._GUEST_TAX.': </b></td>
						<td class="td '.$class_right.'" align="'.$align_right.'"><b>'.Currencies::PriceFormat($this->guestTax * ($total_adults + $total_children), '', '', $this->currencyFormat).'</b></td>
					</tr>';								
			}
			if($this->vatIncludedInPrice == 'no'){
				$output .= '<tr> 
						<td colspan="7"></td>
						<td class="td '.$class_left.'" colspan="3"><b>'._VAT.': ('.Currencies::PriceFormat($this->vatPercent, '%', 'after', $this->currencyFormat, $this->GetVatPercentDecimalPoints($this->vatPercent)).')</b></td>
						<td class="td '.$class_right.'" align="'.$align_right.'"><b>'.Currencies::PriceFormat($vat_cost, '', '', $this->currencyFormat).'</b></td>
					</tr>';
			}
			$output .= '<tr><td colspan="11" nowrap height="5px"></td></tr>
				<tr class="tr-footer">
					<td colspan="7"></td>
					<td class="td '.$class_left.'" colspan="3"><b>'._TOTAL.':</b></td>
					<td class="td '.$class_right.'" align="'.$align_right.'"><b>'.Currencies::PriceFormat($cart_total, '', '', $this->currencyFormat).'</b></td>
				</tr>
				<tr><td colspan="11" nowrap height="15px"></td></tr>';
			if($show_button_next){
				$output .= '<tr>
					<td colspan="7"></td>
					<td colspan="4" align="'.$align_left.'">
						<div id="dialog-alert" title="'.htmlspecialchars(_APPLY_CHANGES).'" style="display:none;">
							<p><span id="dialog-message" style="font-size:14px;">'._DATES_CHANGED_ALERT.'</span></p>
						</div>
						<input type="button" class="form_button" onclick="javascript:appGoToBookingDetails(\'page=booking_details\', this)" value="'._BOOK.'" />
				</tr>';
			}

			$output .= '</table>
				</div>';
			$output .= '<script>
				jQuery("a.apply-change-date").click(function(){
					form = jQuery("#update-room-form");
					var rid = jQuery(this).data("id");
					var checkin_val = jQuery("input.from_date[data-id=" + rid + "]").val();
					var checkout_val = jQuery("input.to_date[data-id=" + rid + "]").val();
					form.find("input[name=room_id]").val(rid);
					form.find("input[name=from_date]").val(checkin_val);
					form.find("input[name=to_date]").val(checkout_val);
					'.(ALLOW_ADULTS_SELECTING ? 'form.find("input[name=adults]").val( jQuery("select.adults[data-id=" + rid + "]").val() );' : '').'
					'.(($allow_children == 'yes' && ALLOW_CHILDREN_SELECTING) ? 'form.find("input[name=children]").val( jQuery("select.children[data-id=" + rid + "]").val() );' : '').'
					form.submit();
				});
				jQuery("input.from_date").change(function(){
					form = jQuery("#update-room-form");
					var rid = jQuery(this).data("id");
					var checkin_val = jQuery(this).val();
                    '.($objSettings->GetParameter('date_format') == 'dd/mm/yyyy' 
                        // make american format
                        ? 'checkin_val = checkin_val.replace(/^(\d\d)(\/)(\d\d)(\/\d+)$/g,"$3$2$1$4")' 
                        : '').'
					var set_date = new Date();

					// checkin_val + 1d
					set_date.setTime(Date.parse(checkin_val) + ('.((int)$min_nights > 0 ? (int)$min_nights.' * ' : '' ).'24 * 60 * 60 * 1000));
					jQuery("input.to_date[data-id=" + rid + "]").datepicker("option", "minDate", set_date);
				});
				jQuery("input.to_date").change(function(){
					form = jQuery("#update-room-form");
					var rid = jQuery(this).data("id");
					var checkout_val = jQuery(this).val();
                    '.($objSettings->GetParameter('date_format') == 'dd/mm/yyyy' 
                        // make american format
                        ? 'checkout_val = checkout_val.replace(/^(\d\d)(\/)(\d\d)(\/\d+)$/g,"$3$2$1$4")' 
                        : '').'
					var set_date = new Date();

					// checkout_val - 1d
					set_date.setTime(Date.parse(checkout_val) - ('.((int)$min_nights > 0 ? (int)$min_nights.' * ' : '' ).'24 * 60 * 60 * 1000));
					jQuery("input.from_date[data-id=" + rid + "]").datepicker("option", "maxDate", set_date);
				});
				</script>';
		}else{
			$output .= draw_message(_RESERVATION_CART_IS_EMPTY_ALERT, false, true);
		}
		
		if($draw) echo $output;
		else return $output;
	}

    /** 
	 * Show checkout info
	 */	
	public function ShowCheckoutInfo()
	{
		if(function_exists('Template_ShowCheckoutInfo')){				
            Template_ShowCheckoutInfo($this);
            return;
        }

		global $objLogin, $objSettings;
		
		$lang = Application::Get('lang');
		
		$class_left = 'rc_'.Application::Get('defined_left');
		$class_right = 'rc_'.Application::Get('defined_right');
		
		$default_payment_system = ModulesSettings::Get('booking', 'default_payment_system');		
		$pre_payment_type       = ModulesSettings::Get('booking', 'pre_payment_type');
		$pre_payment_type_post  = isset($_POST['pre_payment_type']) ? prepare_input($_POST['pre_payment_type']) : $pre_payment_type;
		$pre_payment_value      = ModulesSettings::Get('booking', 'pre_payment_value');
		$payment_type_poa       = ModulesSettings::Get('booking', 'payment_type_poa');
		$payment_type_online    = ModulesSettings::Get('booking', 'payment_type_online');
		$payment_type_bank_transfer = ModulesSettings::Get('booking', 'payment_type_bank_transfer');
		$payment_type_paypal    = ModulesSettings::Get('booking', 'payment_type_paypal');
		$payment_type_2co       = ModulesSettings::Get('booking', 'payment_type_2co');
		$payment_type_authorize = ModulesSettings::Get('booking', 'payment_type_authorize');
		$payment_type           = isset($_POST['payment_type']) ? prepare_input($_POST['payment_type']) : $default_payment_system;
		$submition_type         = isset($_POST['submition_type']) ? prepare_input($_POST['submition_type']) : '';
		$is_agency 				= $objLogin->GetCustomerType() == 1 ? true : false;
		$allow_payment_with_balance = ModulesSettings::Get('booking', 'allow_payment_with_balance') == 'yes' ? true : false;

		$payment_type_poa 		    = ($payment_type_poa == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_poa == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_poa == 'Frontend Only')) ? 'yes' : 'no';
		$payment_type_online 		= ($payment_type_online == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_online == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_online == 'Frontend Only')) ? 'yes' : 'no';
		$payment_type_bank_transfer = ($payment_type_bank_transfer == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_bank_transfer == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_bank_transfer == 'Frontend Only')) ? 'yes' : 'no';
		$payment_type_paypal 		= ($payment_type_paypal == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_paypal == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_paypal == 'Frontend Only')) ? 'yes' : 'no';
		$payment_type_2co 			= ($payment_type_2co == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_2co == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_2co == 'Frontend Only')) ? 'yes' : 'no';
		$payment_type_authorize 	= ($payment_type_authorize == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_authorize == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_authorize == 'Frontend Only')) ? 'yes' : 'no';
		$payment_type_balance		= (($is_agency || $objLogin->IsLoggedInAsAdmin()) && $allow_payment_with_balance) ? 'yes' : 'no';

        if(ModulesSettings::Get('booking', 'allow_separate_gateways') == 'yes'){
            $hotel_id = $this->GetReservationHotelId();
			$sql = "SELECT * FROM ".TABLE_HOTEL_PAYMENT_GATEWAYS." WHERE hotel_id = ".(int)$hotel_id.' AND is_active = 0';
			$info = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
			if($info[1] > 0){
				$arr_payment_types = array('poa'=>'payment_type_poa', 'online'=>'payment_type_online', 'paypal'=>'payment_type_paypal', '2co'=>'payment_type_2co', 'authorize.net'=>'payment_type_authorize', 'bank.transfer'=>'payment_type_bank_transfer', 'account.balance'=>'payment_type_balance');
				for($i = 0; $i < $info[1]; $i++){
					$tmp_payment_type = $arr_payment_types[$info[0][$i]['payment_type']];
					$$tmp_payment_type = 'no';
				}
			}
		}

		$payment_type_cnt = (($payment_type_poa === 'yes')+
		                    ($payment_type_online === 'yes')+
							($payment_type_bank_transfer === 'yes')+
							($payment_type_paypal === 'yes')+
							($payment_type_2co === 'yes')+
							($payment_type_authorize === 'yes')+
							($payment_type_balance === 'yes'));
		$payment_types_defined  = true;
		$allow_children = ModulesSettings::Get('rooms', 'allow_children');
		$allow_extra_beds = ModulesSettings::Get('rooms', 'allow_extra_beds');
		$meal_plans_count = MealPlans::MealPlansCount();
		
		$find_user = isset($_GET['cl']) ? prepare_input($_GET['cl']) : '';
		$cid = isset($_GET['cid']) ? prepare_input($_GET['cid']) : '';
		if($cid != ''){
			if($cid != 'admin'){
				$this->currentCustomerID = $cid;
				Session::Set('sel_current_customer_id', $cid);
				$this->selectedUser = 'customer';			
			}else{
				$this->currentCustomerID = $objLogin->GetLoggedID();
				$this->selectedUser = 'admin';
				Session::Set('sel_current_customer_id', '');
			}
		}
						    
		if($objLogin->IsLoggedInAsAdmin() && $this->selectedUser == 'admin'){
			$table_name = TABLE_ACCOUNTS;
			$sql='SELECT '.$table_name.'.*
				  FROM '.$table_name.'
				  WHERE '.$table_name.'.id = '.(int)$this->currentCustomerID;
		}else{
			$table_name = TABLE_CUSTOMERS;
			$sql = 'SELECT
					'.$table_name.'.*,
					cd.name as country_name,
					c.vat_value,
					IF(st.name IS NOT NULL, st.name, '.$table_name.'.b_state) as b_state
				FROM '.$table_name.'
					LEFT OUTER JOIN '.TABLE_COUNTRIES.' c ON '.$table_name.'.b_country = c.abbrv AND c.is_active = 1
					LEFT OUTER JOIN '.TABLE_COUNTRIES_DESCRIPTION.' cd ON c.id = cd.country_id AND cd.language_id = \''.$lang.'\'
					LEFT OUTER JOIN '.TABLE_STATES.' st ON '.$table_name.'.b_state = st.abbrv AND st.country_id = c.id AND st.is_active = 1
				WHERE '.$table_name.'.id = '.(int)$this->currentCustomerID;				  
		}
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] <= 0){
			draw_message(_RESERVATION_CART_IS_EMPTY_ALERT, true, true);
			return false;
		}

		if(count($this->arrReservation) > 0){
			$hotel_id = 0;
			foreach($this->arrReservation as $key => $val){
				if($hotel_id == 0){
					$hotel_id = $val['hotel_id'];
				}elseif($hotel_id != $val['hotel_id']){
					$hotel_id = 0;
					break;
				}
			}
			if($hotel_id == 0){
				$extras = Extras::GetAllExtras();
			}else{
				$extras = Extras::GetAllExtras(array(0, $hotel_id));
			}
			echo "\n".'<script type="text/javascript">'."\n";			
			echo 'var arrExtras = new Array('.$extras[1].');'."\n";
			echo 'var arrExtrasSelected = new Array('.$extras[1].');'."\n";			
			if($extras[1]){
				for($i=0; $i<$extras[1]; $i++){
					echo 'arrExtras['.$i.'] = "'.($extras[0][$i]['price'] * $this->currencyRate).'";'."\n";
					echo 'arrExtrasSelected['.$i.'] = 0;'."\n";
				}
			}						
			echo '</script>'."\n";

			echo '<form id="update-room-form" action="index.php?page=booking_checkout&m=3" method="post">
			'.draw_hidden_field('submition_type', 'update', false).'
			'.draw_hidden_field('room_id', '', false).'
			'.draw_hidden_field('from_date', '', false).'
			'.draw_hidden_field('to_date', '', false).'
			'.draw_token_field(false).'
			</form>';
			
			echo '<form id="checkout-form" action="index.php?page=booking_payment" method="post">
			'.draw_hidden_field('task', 'do_booking', false).'
			'.draw_hidden_field('submition_type', '', false).'
			'.draw_hidden_field('selected_user', $this->selectedUser, false).'
			'.draw_token_field(false);
			
			echo '<table class="reservation_cart" width="99%" align="center">
			<tr>
				<td colspan="2"><h4>'._BILLING_DETAILS.' &nbsp;';
					if($objLogin->IsLoggedIn()){
						if($objLogin->IsLoggedInAsCustomer()){
							echo '<a style="font-size:13px;" href="javascript:void(0);" onclick="javascript:appGoTo(\'customer=my_account\')">['._EDIT_WORD.']</a>	';
						}elseif($objLogin->IsLoggedInAsAdmin()){
						
							echo '<br>'._CHANGE_CUSTOMER.': 
							<input type="text" id="find_user" name="find_user" value="" size="10" maxlength="40" />
							<input type="button" class="form_button" value="'._SEARCH.'" onclick="javascript:appGoTo(\'page=booking_checkout&cl=\'+jQuery(\'#find_user\').val())" />
							<select name="sel_customer" id="sel_customer">';
								if($find_user == ''){
									if($this->selectedUser == 'admin'){
										echo '<option value="admin">'.$result[0]['first_name'].' '.$result[0]['last_name'].' ('.$result[0]['user_name'].')</option>';										
									}else{
										echo '<option value="'.$result[0]['id'].'">ID:'.$result[0]['id'].' '.$result[0]['first_name'].' '.$result[0]['last_name'].' ('.(($result[0]['user_name'] != '') ? $result[0]['user_name'] : _WITHOUT_ACCOUNT).')'.'</option>';										
									}
								}else{
									$objCustomers = new Customers();
									$result_customers = $objCustomers->GetAllCustomers(' AND (last_name like \''.$find_user.'%\' OR first_name like \''.$find_user.'%\' OR user_name like \''.$find_user.'%\') ');
									if($result_customers[1] > 0){
										for($i = 0; $i < $result_customers[1]; $i++){
											echo '<option value="'.$result_customers[0][$i]['id'].'">ID:'.$result_customers[0][$i]['id'].' '.$result_customers[0][$i]['first_name'].' '.$result_customers[0][$i]['last_name'].' ('.(($result_customers[0][$i]['user_name'] != '') ? $result_customers[0][$i]['user_name'] : _WITHOUT_ACCOUNT).')'.'</option>';
										}								
									}else{
										echo '<option value="admin">'.$result[0]['first_name'].' '.$result[0]['last_name'].' ('.$result[0]['user_name'].')</option>';
									}
								}								
							echo '</select> ';
							if($find_user != '') echo '<input type="button" class="form_button" value="'._APPLY.'" onclick="javascript:appGoTo(\'page=booking_checkout&cid=\'+jQuery(\'#sel_customer\').val())"/> ';
							echo '<input type="button" class="form_button" value="'._SET_ADMIN.'" onclick="javascript:appGoTo(\'page=booking_checkout&cid=admin\')"/>';
							if($find_user != '' && $result_customers[1] == 0) echo ' '._NO_CUSTOMER_FOUND;
						}
					}else{
						echo '<a style="font-size:13px;" href="javascript:void(0);" onclick="javascript:appGoTo(\'page=booking_details\',\'&m=edit\')">['._EDIT_WORD.']</a>	';
					}
					echo '</h4>
				</td>
			</tr>
			<tr>
				<td style="padding-left:10px;">
					'._FIRST_NAME.': '.$result[0]['first_name'].'<br />
					'._LAST_NAME.': '.$result[0]['last_name'].'<br />';				
					if(!$objLogin->IsLoggedInAsAdmin()){					
						echo _ADDRESS.': '.$result[0]['b_address'].'<br />';
						echo _ADDRESS_2.': '.$result[0]['b_address_2'].'<br />';
						echo _CITY.': '.$result[0]['b_city'].'<br />';
						echo _ZIP_CODE.': '.$result[0]['b_zipcode'].'<br />';
						echo _COUNTRY.': '.$result[0]['country_name'].'<br />';
						echo _STATE.': '.$result[0]['b_state'].'<br />';
					}				
				echo '</td>
				<td></td>
			</tr>
			</table><br />';

			echo '<table class="reservation_cart" width="99%" align="center">
			<tr><td colspan="10"><h4>'._RESERVATION_DETAILS.'</h4></td></tr>
			<tr class="header">
				<th '.(FLATS_INSTEAD_OF_HOTELS ? 'colspan="2" ' : '').'class="'.$class_left.'" width="40px">&nbsp;</th>
				<th align="'.$class_left.'">'.(FLATS_INSTEAD_OF_HOTELS ? _FLAT : _ROOM_TYPE).'</th>
				<th class="center">'._FROM.'</th>
				<th class="center">'._TO.'</th>
				<th width="60px" class="center">'._NIGHTS.'</th>								
				'.(FLATS_INSTEAD_OF_HOTELS ? '' : '<th width="50px" class="center">'._ROOMS.'</th>').'
				<th width="70px" colspan="3" class="center">'._OCCUPANCY.'</th>
				'.(($meal_plans_count) ? '<th width="60px" class="center">'._MEAL_PLANS.'</th>' : '<th style="padding:0px;">&nbsp;</th>').'
				<th class="'.$class_right.'" width="80px" class="'.$class_right.'">'._PRICE.'</th>
			</tr>
			<tr><td colspan="11" nowrap="nowrap" height="5px"></td></tr>';

			echo '<tr style="font-size:10px;background-color:transparent;">				
				<th colspan="6"></th>
				<th class="center">'._ADULTS.'</th>
				'.(($allow_children == 'yes') ? '<th class="center">&nbsp;'._CHILDREN.'&nbsp;</th>' : '<th></th>').'
				'.(($allow_extra_beds == 'yes') ? '<th class="center">'._EXTRA_BEDS.'</th>' : '<th></th>').' 
				<th colspan="2"></th>
			</tr>';
			
			$order_price = 0;
			$total_adults = 0;
			$total_children = 0;
			$rooms_guests = array();
			// Count all guests
			$all_adults_count = 0;
			$max_rooms = 0;
			$discount_night_value = 0;
			$min_nights = ModulesSettings::Get('booking', 'minimum_nights');
			$search_availability_period = ModulesSettings::Get('rooms', 'search_availability_period');
			
			foreach($this->arrReservation as $key => $val){
				$sql = 'SELECT
							'.TABLE_ROOMS.'.id,
							'.TABLE_ROOMS.'.room_type,
							'.TABLE_ROOMS.'.room_icon_thumb,
							'.TABLE_ROOMS.'.hotel_id,
							'.TABLE_ROOMS.'.max_adults,
							'.TABLE_ROOMS.'.max_children, 
							'.TABLE_ROOMS.'.default_price as price,
							'.TABLE_ROOMS.'.discount_guests_type,
							'.TABLE_ROOMS.'.discount_guests_3,
							'.TABLE_ROOMS.'.discount_guests_4,
							'.TABLE_ROOMS.'.discount_guests_5,
							'.TABLE_ROOMS.'.discount_night_type,
							'.TABLE_ROOMS.'.discount_night_3,
							'.TABLE_ROOMS.'.discount_night_4,
							'.TABLE_ROOMS.'.discount_night_5,
							'.TABLE_ROOMS_DESCRIPTION.'.room_type as loc_room_type,
							'.TABLE_ROOMS_DESCRIPTION.'.room_short_description as loc_room_short_description,
							'.TABLE_HOTELS_DESCRIPTION.'.name as hotel_name
						FROM '.TABLE_ROOMS.'
							INNER JOIN '.TABLE_ROOMS_DESCRIPTION.' ON '.TABLE_ROOMS.'.id = '.TABLE_ROOMS_DESCRIPTION.'.room_id
							INNER JOIN '.TABLE_HOTELS_DESCRIPTION.' ON '.TABLE_ROOMS.'.hotel_id	= '.TABLE_HOTELS_DESCRIPTION.'.hotel_id
						WHERE
							'.TABLE_ROOMS.'.id = '.(int)$key.' AND
							'.TABLE_ROOMS_DESCRIPTION.'.language_id = \''.$this->lang.'\' AND
							'.TABLE_HOTELS_DESCRIPTION.'.language_id = \''.$this->lang.'\' ';

				$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($result[1] > 0){
					$room_icon_thumb = ($result[0]['room_icon_thumb'] != '') ? $result[0]['room_icon_thumb'] : 'no_image.png';
					$room_price_w_meal_extrabeds = ($val['price'] + $val['meal_plan_price'] + $val['extra_beds_charge']);
                    $meal_plan_info = MealPlans::GetPlanInfo($val['meal_plan_id']);
                    $meal_plan_name = isset($meal_plan_info['name']) ? $meal_plan_info['name'] : '';
					$total_adults_in_room = $val['adults'] * $val['rooms'];

					if(ModulesSettings::Get('rooms', 'guests_stay_discount') == 'yes'){
						$rooms_guests[$key] = array(
							'price' => $val['price'] / ($val['rooms'] * $val['nights']),
							'rooms_count' => $val['rooms'],
							'adults_count' => $total_adults_in_room,
							'nights' => $val['nights'],
							'discount_type' => $result[0]['discount_guests_type'],
							'discount_guests_3' => $result[0]['discount_guests_3'],
							'discount_guests_4' => $result[0]['discount_guests_4'],
							'discount_guests_5' => $result[0]['discount_guests_5'],
							'rooms_price' => $val['price'],
						);
						$all_adults_count += $total_adults_in_room;						
						$max_rooms = $val['rooms'] > $max_rooms ? $val['rooms'] : $max_rooms;
					}
					$date_form = format_date($val['from_date'], $objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'm/d/Y' : 'd/m/Y', '', true);
					$date_to = format_date($val['to_date'], $objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'm/d/Y' : 'd/m/Y', '', true);
					echo '<tr>
							<td'.(FLATS_INSTEAD_OF_HOTELS ? ' colspan="2"' : '').'><img src="images/rooms/'.$room_icon_thumb.'" alt="icon" width="32px" height="32px" /></td>							
							<td>
								'.(FLATS_INSTEAD_OF_HOTELS ? '' : ('<b>'.prepare_link('rooms', 'room_id', $result[0]['id'], $result[0]['loc_room_type'], $result[0]['loc_room_type'], '', _CLICK_TO_VIEW).'</b><br>')).'
								'.prepare_link(FLATS_INSTEAD_OF_HOTELS ? 'flats' : 'hotels', FLATS_INSTEAD_OF_HOTELS ? 'fid' : 'hid', $result[0]['hotel_id'], $result[0]['hotel_name'], $result[0]['hotel_name'], '', _CLICK_TO_VIEW).'
							</td>							
							<td colspan="2">
								<table>
									<tr>
										<td align="center"><input class="form-control from_date" id="from_date_'.$result[0]['id'].'" data-id="'.$result[0]['id'].'" placeholder="'.$objSettings->GetParameter('date_format').'" type="text" data-value="'.$date_form.'" value="'.$date_form.'"></td>
                                        <td align="center"><input class="form-control to_date" id="to_date_'.$result[0]['id'].'" data-id="'.$result[0]['id'].'" placeholder="'.$objSettings->GetParameter('date_format').'" type="text" data-value="'.$date_to.'" value="'.$date_to.'"></td>
									</tr>
									<tr><td colspan="2" style="text-align:center;font-size:12px;"><a href="javascript:void(0);" data-id="'.$result[0]['id'].'" class="apply-change-date">'._APPLY_CHANGES.'</a></td></tr>
								</table>
							</td>
							<!--td align="center">'.format_date($val['from_date'], $this->fieldDateFormat, '', true).'</td>
							<td align="center">'.format_date($val['to_date'], $this->fieldDateFormat, '', true).'</td-->
							<td align="center">'.$val['nights'].'</td>
							'.(FLATS_INSTEAD_OF_HOTELS ? '' : '<td align="center">'.$val['rooms'].'</td>').'
							<td align="center">'.$val['adults'].'</td>
							'.(($allow_children == 'yes') ? '<td align="center">'.$val['children'].'</td>' : '<td></td>').'
							'.(($allow_extra_beds == 'yes') ? '<td align="center">'.$val['extra_beds'].'</td>' : '<td></td>').'
							'.(($meal_plans_count) ? '<td align="center">'.$meal_plan_name.'</td>' : '<td></td>').'
							<td class="'.$class_right.'">'.Currencies::PriceFormat($room_price_w_meal_extrabeds * $this->currencyRate, '', '', $this->currencyFormat).'&nbsp;</td>
						</tr>
						<tr><td colspan="11" nowrap height="3px"></td></tr>';
					echo '<script>
						jQuery(function() {
							var minDate = new Date();
							var maxDate = new Date();
							// min date + 1 day
							minDate.setTime(Date.parse("'.$val['from_date'].'") + ('.((int)$min_nights > 0 ? (int)$min_nights.' * ' : '' ).'24 * 60 * 60 * 1000));
                            '.(ModulesSettings::Get('booking', 'allow_booking_in_past') == 'yes' && $objLogin->IsLoggedInAsAdmin()
                                ? 'minDate.setFullYear(minDate.getFullYear() - 1);'
                                : '').'
                            jQuery("#from_date_'.$result[0]['id'].'").datepicker({
                                '.(ModulesSettings::Get('booking', 'allow_booking_in_past') == 'yes' && $objLogin->IsLoggedInAsAdmin()
                                    ? 'defaultDate: "-1y",minDate: "-1y",'
                                    : 'minDate: 0,').'
								maxDate: "+'.(int)$search_availability_period.'y",
								numberOfMonths: 1,
                                dateFormat: "'.($objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'mm/dd/yy' : 'dd/mm/yy').'"
							});
							jQuery("#to_date_'.$result[0]['id'].'").datepicker({
								minDate: minDate,
								maxDate: "+'.(int)$search_availability_period.'y +'.((int)$min_nights > 0 ? (int)$min_nights : '1').'d",
								numberOfMonths: 1,
                                dateFormat: "'.($objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'mm/dd/yy' : 'dd/mm/yy').'"
							});
						});
					</script>';
					$order_price += ($room_price_w_meal_extrabeds * $this->currencyRate);
					$total_adults += $val['rooms'] * $val['adults'];
					$total_children += ($allow_children == 'yes') ? $val['rooms'] * $val['children'] : 0;

					// If has been ordered 3 and more nights, check an additional discount on the night
					if(ModulesSettings::Get('rooms', 'long_term_stay_discount') == 'yes'){
						if($val['nights'] >= 3){
							$discount_value = 0;
							if($val['nights'] == 3 && !empty($result[0]['discount_night_3'])){
								$discount_value = $result[0]['discount_night_3'];
							}elseif($val['nights'] == 4 && !empty($result[0]['discount_night_4'])){
								$discount_value = $result[0]['discount_night_4'];
							}else{
								$discount_value = $result[0]['discount_night_5'];
							}

							// 0 - Fixed price, 1 - Percentage
							if($result[0]['discount_night_type'] == 1){
								$discount_nights = ($val['price'] * ($discount_value / 100));
								$rooms_guests[$key]['price'] -= $rooms_guests[$key]['price'] * ($discount_value / 100);
								$rooms_guests[$key]['rooms_price'] -= $rooms_guests[$key]['rooms_price'] * ($discount_value / 100);
								
							}else{
								if($val['price'] > $discount_value){
									$discount_nights = ($val['price'] / ($val['rooms'] * $val['nights'])  > $discount_value ? $discount_value * $val['rooms'] * $val['nights'] : $val['price']);
									$rooms_guests[$key]['price'] -= $discount_value;
									$rooms_guests[$key]['rooms_price'] -= $discount_nights;
								}else{
									$discount_nights = $val['price'];
									$rooms_guests[$key]['price'] = 0;
									$rooms_guests[$key]['rooms_price'] = 0;
								}
							}
							// The discount can't exceed the cost per room
							$discount_nights = $discount_nights > $val['price'] ? $val['price'] : $discount_nights;
							$discount_night_value += $discount_nights * $this->currencyRate;
                            $this->arrReservation[$key]['discount_nights'] = $discount_nights;
						}
					}
				}
			}
			
			// draw sub-total row			
			echo '<tr>
					<td colspan="7"></td>
					<td class="td '.$class_left.'" colspan="3"><b>'._SUBTOTAL.':</b></td>
					<td class="td '.$class_right.'" align="'.$class_right.'">
						<b>'.Currencies::PriceFormat($order_price, '', '', $this->currencyFormat).'</b>
					</td>
				 </tr>';

			//echo '<tr><td colspan="10" nowrap height="5px"></td></tr>';
			
			// EXTRAS
			// ------------------------------------------------------------
			if($extras[1]){
				echo '<tr><td colspan="11" nowrap height="10px"></td></tr>';
				echo '<tr><td colspan="11"><h4>'._EXTRAS.'</h4></td></tr>';				
				echo '<tr><td colspan="11"><table width="340px">';				
				for($i=0; $i<$extras[1]; $i++){
					$extras_id = (($submition_type == 'apply_coupon') && isset($_POST['extras_'.$extras[0][$i]['id']])) ? $_POST['extras_'.$extras[0][$i]['id']] : 0;
					echo '<tr>';
					echo '<td wrap="wrap">'.$extras[0][$i]['name'].' <span class="help" title="'.$extras[0][$i]['description'].'">[?]</span></td>';
					echo '<td>&nbsp;</td>';
					echo '<td align="right">'.Currencies::PriceFormat($extras[0][$i]['price'] * $this->currencyRate, '', '', $this->currencyFormat).'</td>';
					echo '<td>&nbsp;</td>';
					echo '<td>'.draw_numbers_select_field('extras_'.$extras[0][$i]['id'], $extras_id, '0', $extras[0][$i]['maximum_count'], 1, 'extras_ddl form-control', 'onchange="appUpdateTotalSum('.$i.',this.value,'.(int)$extras[1].')"', false).'</td>';
					echo '</tr>';
				}
				echo '</table></td></tr>';								
			}
			
			echo '<script>
			jQuery("a.apply-change-date").click(function(){
				form = jQuery("#update-room-form");
				var rid = jQuery(this).data("id");
				var checkin_val = jQuery("input.from_date[data-id=" + rid + "]").val();
				var checkout_val = jQuery("input.to_date[data-id=" + rid + "]").val();

				form.find("input[name=room_id]").val(rid);
				form.find("input[name=from_date]").val(checkin_val);
				form.find("input[name=to_date]").val(checkout_val);
				form.submit();
			});
			jQuery("input.from_date").change(function(){
				form = jQuery("#update-room-form");
				var rid = jQuery(this).data("id");
				var checkin_val = jQuery(this).val();
                '.($objSettings->GetParameter('date_format') == 'dd/mm/yyyy' 
                    // make american format
                    ? 'checkin_val = checkin_val.replace(/^(\d\d)(\/)(\d\d)(\/\d+)$/g,"$3$2$1$4")' 
                    : '').'
				var set_date = new Date();

				// checkin_val + 1d
				set_date.setTime(Date.parse(checkin_val) + ('.((int)$min_nights > 0 ? (int)$min_nights.' * ' : '' ).'24 * 60 * 60 * 1000));
				jQuery("input.to_date[data-id=" + rid + "]").datepicker("option", "minDate", set_date);
			});
			jQuery("input.to_date").change(function(){
				form = jQuery("#update-room-form");
				var rid = jQuery(this).data("id");
				var checkout_val = jQuery(this).val();
                '.($objSettings->GetParameter('date_format') == 'dd/mm/yyyy' 
                    // make american format
                    ? 'checkout_val = checkout_val.replace(/^(\d\d)(\/)(\d\d)(\/\d+)$/g,"$3$2$1$4")' 
                    : '').'
				var set_date = new Date();

				// checkout_val - 1d
				set_date.setTime(Date.parse(checkout_val) - ('.((int)$min_nights > 0 ? (int)$min_nights.' * ' : '' ).'24 * 60 * 60 * 1000));
				jQuery("input.from_date[data-id=" + rid + "]").datepicker("option", "maxDate", set_date);
			});
			</script>';

			if($discount_night_value > 0){
				$align_right = Application::Get('defined_right');
				$order_price -= $discount_night_value;
				echo '<tr>
						<td colspan="7"></td>
						<td class="td '.$class_left.'" colspan="3"><b><span style="color:#a60000">'._LONG_TERM_STAY_DISCOUNT.': </span></b></td>
						<td class="td '.$class_right.'" align="'.$align_right.'"><b><span style="color:#a60000">- '.Currencies::PriceFormat($discount_night_value, '', '', $this->currencyFormat).'</span></b></td>
					</tr>';				
			}

			// If has been ordered 3 and more rooms, check an additional discount on the number of rooms
			if(ModulesSettings::Get('rooms', 'guests_stay_discount') == 'yes'){
				$discount_value = 0;
				if(TYPE_DISCOUNT_GUEST == 'guests'){
					$count_adults_or_guests = $all_adults_count;
				}else{
					$count_adults_or_guests = $max_rooms;
				}
				if($count_adults_or_guests >= 3){
					foreach($rooms_guests as $info_room){
						if(TYPE_DISCOUNT_GUEST != 'guests'){
							$count_adults_or_guests = $info_room['rooms_count'];
						}
						$discount_value = 0;
						if($count_adults_or_guests == 3 && !empty($info_room['discount_guests_3'])){
							$discount_value = $info_room['discount_guests_3'];
						}elseif($count_adults_or_guests == 4 && !empty($info_room['discount_guests_4'])){
							$discount_value = $info_room['discount_guests_4'];
						}else{
							$discount_value = $info_room['discount_guests_5'];
						}

						// 0 - Fixed price, 1 - Percentage
						if($info_room['discount_type'] == 1){
							$discount_room = ($info_room['rooms_price'] * ($discount_value / 100));
						}else{
							$discount_room = ($info_room['price'] > $discount_value ? $discount_value : $info_room['price']) * $info_room['rooms_count'] * $info_room['nights'];
						}
						// The discount can't exceed the cost per room
						$discount_room = $discount_room > $info_room['rooms_price'] ? $info_room['rooms_price'] : $discount_room;
						$discount_value += $discount_room * $this->currencyRate;
                        $this->arrReservation[$key]['discount_guests'] = $discount_room;
					}
				}

				if($discount_value > 0){
					$order_price -= $discount_value;
					echo '<tr>
							<td colspan="7"></td>
							<td class="td '.$class_left.'" colspan="3"><b><span style="color:#a60000">'.(TYPE_DISCOUNT_GUEST == 'guests' ? _GUESTS_DISCOUNT : _ROOMS_DISCOUNT).': </span></b></td>
							<td class="td '.$class_right.'" align="'.$class_right.'"><b><span style="color:#a60000">- '.Currencies::PriceFormat($discount_value, '', '', $this->currencyFormat).'</span></b></td>
						</tr>';				
				}
			}
			
			// Calculate discount
			$discount_value = ($this->discountType == '1' ? ($order_price * ($this->discountValue / 100)) : $this->discountValue);
			$order_price -= $discount_value;
			
			// Calculate VAT
			$vat_cost = (($order_price + $this->bookingInitialFee + ($this->guestTax * ($total_adults + $total_children))) * ($this->vatPercent / 100));
			$cart_total = ($order_price + $this->bookingInitialFee + ($this->guestTax * ($total_adults + $total_children))) + $vat_cost;

			if($this->discountCampaignID != '' || $this->discountCoupon != ''){
				if(empty($this->discountTitle)){
					$this->discountTitle = $this->discountCoupon ? _COUPON_DISCOUNT : _DISCOUNT;					
				}
				echo '<tr>
						<td colspan="7"></td>
						<td class="td '.$class_left.'" colspan="3"><b><span style="color:#a60000">'.$this->discountTitle.': (';
						// 0 - Fixed price, 1 - Percentage
						if($this->discountType == 1){
							echo Currencies::PriceFormat($this->discountValue, '%', 'after', $this->currencyFormat);
						}else{
							echo Currencies::PriceFormat($this->discountValue, Application::Get('currency_symbol'), 'before', $this->currencyFormat);
						}
						echo ')</span></b></td>
						<td class="td '.$class_right.'" align="'.$class_right.'"><b><span style="color:#a60000">- '.Currencies::PriceFormat($discount_value, '', '', $this->currencyFormat).'</span></b></td>
					</tr>';				
			}
			if(!empty($this->bookingInitialFee)){
				echo '<tr>
						<td colspan="7"></td>
						<td class="td '.$class_left.'" colspan="3"><b>'._INITIAL_FEE.': </b></td>
						<td class="td '.$class_right.'" align="'.$class_right.'"><b>'.Currencies::PriceFormat($this->bookingInitialFee, '', '', $this->currencyFormat).'</b></td>
					</tr>';								
			}
			if(!empty($this->guestTax)){
				echo '<tr>
						<td colspan="7"></td>
						<td class="td '.$class_left.'" colspan="3"><b>'._GUEST_TAX.': </b></td>
						<td class="td '.$class_right.'" align="'.$class_right.'">
							<b><label id="guest_tax" data-price="'.(float)($this->guestTax * ($total_adults + $total_children)).'">'.Currencies::PriceFormat(($this->guestTax * ($total_adults + $total_children)), '', '', $this->currencyFormat).'</label></b>
						</td>
					</tr>';								
			}
			if($this->vatIncludedInPrice == 'no'){
				echo '<tr>
						<td colspan="7"></td>
						<td class="td '.$class_left.'" colspan="3"><b>'._VAT.': ('.Currencies::PriceFormat($this->vatPercent, '%', 'after', $this->currencyFormat, $this->GetVatPercentDecimalPoints($this->vatPercent)).')</b></td>
						<td class="td '.$class_right.'" align="'.$class_right.'">
							<b><label id="reservation_vat">'.Currencies::PriceFormat($vat_cost, '', '', $this->currencyFormat).'</label></b>
						</td>
					 </tr>';
			}
			echo '<tr><td colspan="11" nowrap height="5px"></td></tr>
				 <tr class="tr-footer">
					<td colspan="7"></td>
					<td class="td '.$class_left.'" colspan="3"><b>'._TOTAL.':</b></td>
					<td class="td '.$class_right.'" align="'.$class_right.'">
						<b><label id="reservation_total">'.Currencies::PriceFormat($cart_total, '', '', $this->currencyFormat).'</label></b>
					</td>
				 </tr>';

			// PAYMENT DETAILS
			// ------------------------------------------------------------
			echo '<tr><td colspan="11" nowrap height="12px"></td></tr>';
			echo '<tr><td colspan="11"><h4>'._PAYMENT_DETAILS.'</h4></td></tr>';
			echo '<tr><td colspan="11">';
			echo '<table border="0" width="100%">';
				if($payment_type_cnt > 1){
					if(SHOW_PAYMENT_FOR_CHECKBOX){
						echo '<tr><td width="130px" style="vertical-align:top;">'._PAYMENT_TYPE.': &nbsp;</td><td>';
						if($payment_type_poa == 'yes')			 echo '<input type="radio" name="payment_type" id="payment_type_poa" value="poa" '.(($payment_type == 'poa') ? 'checked="checked"' : '').'/> <label for="payment_type_poa">'.(file_exists('images/payments/poa.png') ? '<img src="images/payments/poa.png" alt="'._PAY_ON_ARRIVAL.'" title="'._PAY_ON_ARRIVAL.'" style="margin-left:10px;height:35px;">' : _PAY_ON_ARRIVAL).'</label><br><br>';
						if($payment_type_online == 'yes')		 echo '<input type="radio" name="payment_type" id="payment_type_online" value="online" '.(($payment_type == 'online') ? 'checked="checked"' : '').'/> <label for="payment_type_online">'.(file_exists('images/payments/creditcard.png') ? '<img src="images/payments/creditcard.png" alt="'._ONLINE_ORDER.'" title="'._ONLINE_ORDER.'" style="margin-left:10px;height:35px;">' : _ONLINE_ORDER).'</label><br><br>';	
						if($payment_type_bank_transfer == 'yes') echo '<input type="radio" name="payment_type" id="payment_type_transfer" value="bank.transfer" '.(($payment_type == 'bank.transfer') ? 'checked="checked"' : '').'/> <label for="payment_type_transfer">'.(file_exists('images/payments/bank_transfer.png') ? '<img src="images/payments/bank_transfer.png" alt="'._BANK_TRANSFER.'" title="'._BANK_TRANSFER.'" style="margin-left:10px;height:35px;">' : _BANK_TRANSFER).'</label><br><br>';
						if($payment_type_paypal == 'yes')		 echo '<input type="radio" name="payment_type" id="payment_type_paypal" value="paypal" '.(($payment_type == 'paypal') ? 'checked="checked"' : '').'/> <label for="payment_type_paypal">'.(file_exists('images/payments/paypal.png') ? '<img src="images/payments/paypal.png" alt="'._PAYPAL.'" title="'._PAYPAL.'" style="margin-left:10px;height:35px;">' : _PAYPAL).'</label><br><br>';
						if($payment_type_2co == 'yes')			 echo '<input type="radio" name="payment_type" id="payment_type_2co" value="2co" '.(($payment_type == '2co') ? 'checked="checked"' : '').'/> <label for="payment_type_2co">'.(file_exists('images/payments/2co.png') ? '<img src="images/payments/2co.png" alt="2CO" title="2CO" style="margin-left:10px;height:35px;">' : '2CO').'</label><br><br>';	
						if($payment_type_authorize == 'yes')	 echo '<input type="radio" name="payment_type" id="payment_type_authorize" value="authorize.net" '.(($payment_type == 'authorize.net') ? 'checked="checked"' : '').'/> <label for="payment_type_authorize">'.(file_exists('images/payments/authorize.png') ? '<img src="images/payments/authorize.png" alt="Authorize.net" title="Authorize.net" style="margin-left:10px;height:35px;">' : 'Authorize.net').'</label><br><br>';
						if($payment_type_balance == 'yes')		 echo '<input type="radio" name="payment_type" id="payment_type_balance" value="account.balance" '.(($payment_type == 'account.balance') ? 'checked="checked"' : '').'/> <label for="payment_type_balance">'.(file_exists('images/payments/balance.png') ? '<img src="images/payments/balance.png" alt="'._PAY_WITH_BALANCE.'" title="'._PAY_WITH_BALANCE.'" style="margin-left:10px;height:35px;">' : _PAY_WITH_BALANCE).'</label><br><br>';	
					}else{
						echo '<tr><td width="130px" nowrap>'._PAYMENT_TYPE.': &nbsp;</td><td>';
						echo '<select name="payment_type" class="form-control payment_type" id="payment_type">';
							if($payment_type_poa == 'yes') echo '<option value="poa" '.(($payment_type == 'poa') ? 'selected="selected"' : '').'>'._PAY_ON_ARRIVAL.'</option>';
							if($payment_type_online == 'yes') echo '<option value="online" '.(($payment_type == 'online') ? 'selected="selected"' : '').'>'._ONLINE_ORDER.'</option>';	
							if($payment_type_bank_transfer == 'yes') echo '<option value="bank.transfer" '.(($payment_type == 'bank.transfer') ? 'selected="selected"' : '').'>'._BANK_TRANSFER.'</option>';
							if($payment_type_paypal == 'yes') echo '<option value="paypal" '.(($payment_type == 'paypal') ? 'selected="selected"' : '').'>'._PAYPAL.'</option>';
							if($payment_type_2co == 'yes') echo '<option value="2co" '.(($payment_type == '2co') ? 'selected="selected"' : '').'>2CO</option>';	
							if($payment_type_authorize == 'yes') echo '<option value="authorize.net" '.(($payment_type == 'authorize.net') ? 'selected="selected"' : '').'>Authorize.Net</option>';
							if($payment_type_balance == 'yes') echo '<option value="account.balance" '.(($payment_type == 'account.balance') ? 'selected="selected"' : '').'>'._PAY_WITH_BALANCE.'</option>';	
						echo '</select>';
					}
					echo '</td></tr>';
				}elseif($payment_type_cnt == 1){
					if($payment_type_poa == 'yes') $payment_type_hidden = 'poa';
					else if($payment_type_online == 'yes') $payment_type_hidden = 'online';
					else if($payment_type_bank_transfer == 'yes') $payment_type_hidden = 'bank.transfer';
					else if($payment_type_paypal == 'yes') $payment_type_hidden = 'paypal';
					else if($payment_type_2co == 'yes') $payment_type_hidden = '2co';
					else if($payment_type_authorize == 'yes') $payment_type_hidden = 'authorize.net';
					else if($payment_type_balance == 'yes') $payment_type_hidden = 'account.balance';
					else{
						$payment_type_hidden = '';
						$payment_types_defined = false;
					}
					echo '<tr><td width="130px" nowrap>'._PAYMENT_TYPE.': &nbsp;</td><td>'.draw_hidden_field('payment_type', $payment_type_hidden, false, 'payment_type');
					echo '<b>';
						if($payment_type_poa == 'yes') echo _PAY_ON_ARRIVAL;
						if($payment_type_online == 'yes') echo _ONLINE_ORDER;
						if($payment_type_bank_transfer == 'yes') echo _BANK_TRANSFER;
						if($payment_type_paypal == 'yes') echo _PAYPAL;
						if($payment_type_2co == 'yes') echo '2CO';	
						if($payment_type_authorize == 'yes') echo 'Authorize.Net';
						if($payment_type_balance == 'yes') echo _PAY_WITH_BALANCE;
					echo '</b>';
					echo '</td></tr>';
				}else{
					$payment_types_defined = false;
					echo '<tr><td colspan="2">'.draw_important_message(_NO_PAYMENT_METHODS_ALERT, false).'</td></tr>';
				}						
				echo '<tr>';
					if($pre_payment_type == 'first night' && $this->first_night_possible){
						echo '<td width="130px">'._PAYMENT_METHOD.': </td>';
						echo '<td>';
						echo '<input type="radio" name="pre_payment_type" id="pre_payment_fully" value="full price" checked="checked /> <label for="pre_payment_fully">'._FULL_PRICE.'</label> &nbsp;&nbsp;';
						echo '<input type="radio" name="pre_payment_type" id="pre_payment_partially" value="first night" /> <label for="pre_payment_partially">'._FIRST_NIGHT.'</label>';
						echo draw_hidden_field('pre_payment_value', $pre_payment_value, false, 'pre_payment_full');
						echo '</td>';
					}elseif($pre_payment_type == 'percentage' && $pre_payment_value > '0' && $pre_payment_value < '100'){
						echo '<td width="130px">'._PAYMENT_METHOD.': </td>';
						echo '<td>';
						echo '<input type="radio" name="pre_payment_type" id="pre_payment_fully" value="full price" checked="checked" /> <label for="pre_payment_fully">'._FULL_PRICE.'</label> &nbsp;&nbsp;';
						echo '<input type="radio" name="pre_payment_type" id="pre_payment_partially" value="percentage" /> <label for="pre_payment_partially">'._PRE_PAYMENT.' ('.Currencies::PriceFormat($pre_payment_value, '%', 'after', $this->currencyFormat).')</label>';
						echo draw_hidden_field('pre_payment_value', $pre_payment_value, false, 'pre_payment_full');
						echo '</td>';
					}elseif($pre_payment_type == 'fixed sum' && $pre_payment_value > '0'){
						echo '<td width="130px">'._PAYMENT_METHOD.': </td>';
						echo '<td>';
						echo '<input type="radio" name="pre_payment_type" id="pre_payment_fully" value="full price" checked="checked" /> <label for="pre_payment_fully">'._FULL_PRICE.'</label> &nbsp;&nbsp;';
						echo '<input type="radio" name="pre_payment_type" id="pre_payment_partially" value="fixed sum" /> <label for="pre_payment_partially">'._PRE_PAYMENT.' ('.Currencies::PriceFormat($pre_payment_value * $this->currencyRate, '', '', $this->currencyFormat).')</label>';
						echo draw_hidden_field('pre_payment_value', $pre_payment_value, false, 'pre_payment_full');
						echo '</td>';
					}else{
						echo '<td colspan="2">';
						// full price payment
						if($payment_type_cnt <= 1 && $payment_types_defined) echo _FULL_PRICE;
						echo draw_hidden_field('pre_payment_type', 'full price', false, 'pre_payment_fully');
						echo draw_hidden_field('pre_payment_value', '100', false, 'pre_payment_full');
						echo '</td>';
					}					
				echo '</tr>';			
			echo '</table></td></tr>';
			
			if($payment_types_defined){			
				// PROMO CODES OR DISCOUNT COUPONS
				// ------------------------------------------------------------
				echo '<tr><td colspan="11" nowrap height="12px"></td></tr>';
				echo '<tr><td colspan="11"><h4>'._PROMO_CODE_OR_COUPON.'</h4></td></tr>';
				echo '<tr><td colspan="11">'._PROMO_COUPON_NOTICE.'</td></tr>';
				echo '<tr>';
				echo '<td colspan="11">';
				if(!empty($this->discountCoupon)){				
					echo '<input type="text" class="discount_coupon" name="discount_coupon" id="discount_coupon" value="'.$this->discountCoupon.'" readonly="readonly" maxlength="32" autocomplete="off" />&nbsp;&nbsp;&nbsp;';
					echo '<input type="button" class="form_button" id="discount_button" value="'._REMOVE.'" onclick="appSubmitCoupon(\'remove_coupon\')" />';
				}else{				
					echo '<input type="text" class="discount_coupon" name="discount_coupon" id="discount_coupon" value="'.$this->discountCoupon.'" maxlength="32" autocomplete="off" />&nbsp;&nbsp;&nbsp;';
					echo '<input type="button" class="form_button" id="discount_button" value="'._APPLY.'" onclick="appSubmitCoupon(\'apply_coupon\')" />';
				}
				echo '</td>';
				echo '</tr>';					
		
				echo '<tr><td colspan="11" nowrap height="15px"></td></tr>
					  <tr valign="middle">
						<td colspan="11" nowrap height="15px">
							<h4 style="cursor:pointer;" onclick="appToggleElement(\'additional_info\')">'._ADDITIONAL_INFO.' +</h4>
							<textarea name="additional_info" id="additional_info" style="display:none;width:100%;height:75px"></textarea>
						</td>
					  </tr>
					  <tr><td colspan="11" nowrap height="5px"></td></tr>
					  <tr valign="middle">
						<td colspan="8" align="'.$class_right.'"></td>
						<td align="'.$class_right.'" colspan="3">';
							if(($payment_types_defined)){
								echo '<div id="dialog-alert" title="'.htmlspecialchars(_APPLY_CHANGES).'" style="display:none;">
									<p><span id="dialog-message" style="font-size:14px;">'._DATES_CHANGED_ALERT.'</span></p>
								</div>
								<input type="button" class="form_button" onclick="javascript:appGoToBookingDetails(\'\', this)" value="'._SUBMIT_BOOKING.'" />';
							}
						echo '</td>
					</tr>';
				echo '</table>';
				echo '<input type="hidden" id="hid_vat_percent" value="'.$this->vatPercent.'" />';
				echo '<input type="hidden" id="hid_booking_initial_fee" value="'.$this->bookingInitialFee.'" />';
				echo '<input type="hidden" id="hid_booking_guest_tax" value="'.($this->guestTax * ($total_adults + $total_children)).'" />';
				echo '<input type="hidden" id="hid_order_price" value="'.$order_price.'" />';
				echo '<input type="hidden" id="hid_currency_symbol" value="'.Application::Get('currency_symbol').'" />';
				echo '<input type="hidden" id="hid_currency_format" value="'.$this->currencyFormat.'" />';
				echo '</form><br>';
				
				if($submition_type == 'apply_coupon'){
					echo "\n".'<script type="text/javascript">'."\n";			
					for($i=0; $i<$extras[1]; $i++){
						$extras_id = isset($_POST['extras_'.$extras[0][$i]['id']]) ? $_POST['extras_'.$extras[0][$i]['id']] : 0;
						if($extras_id > 0){
							echo 'appUpdateTotalSum('.$i.','.$extras_id.','.$extras[1].')'.";\n";		
						}
					}
					echo '</script>'."\n";					
				}
			}else{
				echo '</table>';
				echo '</form>';				
				return '';
			}
		}else{
			draw_message(_RESERVATION_CART_IS_EMPTY_ALERT, true, true);
		}
	}	

    /** 
	 * Show checkout info
	 */	
	public function ShowWidgetCheckoutInfo($homeurl = '')
	{
		global $objLogin, $objSettings;
		
		$lang = Application::Get('lang');
		
		$class_left = 'rc_'.Application::Get('defined_left');
		$class_right = 'rc_'.Application::Get('defined_right');
		$align_right = Application::Get('defined_right');
		
		$default_payment_system = ModulesSettings::Get('booking', 'default_payment_system');		
		$pre_payment_type       = ModulesSettings::Get('booking', 'pre_payment_type');
		$pre_payment_type_post  = isset($_POST['pre_payment_type']) ? prepare_input($_POST['pre_payment_type']) : $pre_payment_type;
		$pre_payment_value      = ModulesSettings::Get('booking', 'pre_payment_value');
		$payment_type_poa       = ModulesSettings::Get('booking', 'payment_type_poa');
		$payment_type_online    = ModulesSettings::Get('booking', 'payment_type_online');
		$payment_type_bank_transfer = ModulesSettings::Get('booking', 'payment_type_bank_transfer');
		$payment_type_paypal    = ModulesSettings::Get('booking', 'payment_type_paypal');
		$payment_type_2co       = ModulesSettings::Get('booking', 'payment_type_2co');
		$payment_type_authorize = ModulesSettings::Get('booking', 'payment_type_authorize');
		$payment_type           = isset($_POST['payment_type']) ? prepare_input($_POST['payment_type']) : $default_payment_system;
		$submition_type         = isset($_POST['submition_type']) ? prepare_input($_POST['submition_type']) : '';
		$is_agency 				= $objLogin->GetCustomerType() == 1 ? true : false;
		$allow_payment_with_balance = ModulesSettings::Get('booking', 'allow_payment_with_balance') == 'yes' ? true : false;

		$payment_type_poa 		    = ($payment_type_poa == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_poa == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_poa == 'Frontend Only')) ? 'yes' : 'no';
		$payment_type_online 		= ($payment_type_online == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_online == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_online == 'Frontend Only')) ? 'yes' : 'no';
		$payment_type_bank_transfer = ($payment_type_bank_transfer == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_bank_transfer == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_bank_transfer == 'Frontend Only')) ? 'yes' : 'no';
		$payment_type_paypal 		= ($payment_type_paypal == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_paypal == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_paypal == 'Frontend Only')) ? 'yes' : 'no';
		$payment_type_2co 			= ($payment_type_2co == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_2co == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_2co == 'Frontend Only')) ? 'yes' : 'no';
		$payment_type_authorize 	= ($payment_type_authorize == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_authorize == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_authorize == 'Frontend Only')) ? 'yes' : 'no';
		$payment_type_balance		= (($is_agency || $objLogin->IsLoggedInAsAdmin()) && $allow_payment_with_balance) ? 'yes' : 'no';

        if(ModulesSettings::Get('booking', 'allow_separate_gateways') == 'yes'){
            $hotel_id = $this->GetReservationHotelId();
			$sql = "SELECT * FROM ".TABLE_HOTEL_PAYMENT_GATEWAYS." WHERE hotel_id = ".(int)$hotel_id.' AND is_active = 0';
			$info = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
			if($info[1] > 0){
				$arr_payment_types = array('poa'=>'payment_type_poa', 'online'=>'payment_type_online', 'paypal'=>'payment_type_paypal', '2co'=>'payment_type_2co', 'authorize.net'=>'payment_type_authorize', 'bank.transfer'=>'payment_type_bank_transfer', 'account.balance'=>'payment_type_balance');
				for($i = 0; $i < $info[1]; $i++){
					$tmp_payment_type = $arr_payment_types[$info[0][$i]['payment_type']];
					$$tmp_payment_type = 'no';
				}
			}
		}

		$payment_type_cnt = (($payment_type_poa === 'yes')+
		                    ($payment_type_online === 'yes')+
							($payment_type_bank_transfer === 'yes')+
							($payment_type_paypal === 'yes')+
							($payment_type_2co === 'yes')+
							($payment_type_authorize === 'yes')+
							($payment_type_balance === 'yes'));
		$payment_types_defined  = true;
		$allow_children = ModulesSettings::Get('rooms', 'allow_children');
		$allow_extra_beds = ModulesSettings::Get('rooms', 'allow_extra_beds');
		$meal_plans_count = MealPlans::MealPlansCount();
		
		$find_user = isset($_GET['cl']) ? prepare_input($_GET['cl']) : '';
		$cid = isset($_GET['cid']) ? prepare_input($_GET['cid']) : '';
		if($cid != ''){
			if($cid != 'admin'){
				$this->currentCustomerID = $cid;
				Session::Set('sel_current_customer_id', $cid);
				$this->selectedUser = 'customer';			
			}else{
				$this->currentCustomerID = $objLogin->GetLoggedID();
				$this->selectedUser = 'admin';
				Session::Set('sel_current_customer_id', '');
			}
		}
						    
		if($objLogin->IsLoggedInAsAdmin() && $this->selectedUser == 'admin'){
			$table_name = TABLE_ACCOUNTS;
			$sql='SELECT '.$table_name.'.*
				  FROM '.$table_name.'
				  WHERE '.$table_name.'.id = '.(int)$this->currentCustomerID;
		}else{
			$table_name = TABLE_CUSTOMERS;
			$sql = 'SELECT
					'.$table_name.'.*,
					cd.name as country_name,
					c.vat_value,
					IF(st.name IS NOT NULL, st.name, '.$table_name.'.b_state) as b_state
				FROM '.$table_name.'
					LEFT OUTER JOIN '.TABLE_COUNTRIES.' c ON '.$table_name.'.b_country = c.abbrv AND c.is_active = 1
					LEFT OUTER JOIN '.TABLE_COUNTRIES_DESCRIPTION.' cd ON c.id = cd.country_id AND cd.language_id = \''.$lang.'\'
					LEFT OUTER JOIN '.TABLE_STATES.' st ON '.$table_name.'.b_state = st.abbrv AND st.country_id = c.id AND st.is_active = 1
				WHERE '.$table_name.'.id = '.(int)$this->currentCustomerID;				  
		}
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] <= 0){
			draw_message(_RESERVATION_CART_IS_EMPTY_ALERT, true, true);
			return false;
		}

		if(count($this->arrReservation) > 0){
			$hotel_id = 0;
			foreach($this->arrReservation as $key => $val){
				if($hotel_id == 0){
					$hotel_id = $val['hotel_id'];
				}elseif($hotel_id != $val['hotel_id']){
					$hotel_id = 0;
					break;
				}
			}
			if($hotel_id == 0){
				$extras = Extras::GetAllExtras();
			}else{
				$extras = Extras::GetAllExtras(array(0, $hotel_id));
			}
			echo "\n".'<script type="text/javascript">'."\n";			
			echo 'var arrExtras = new Array('.$extras[1].');'."\n";
			echo 'var arrExtrasSelected = new Array('.$extras[1].');'."\n";			
			if($extras[1]){
				for($i=0; $i<$extras[1]; $i++){
					echo 'arrExtras['.$i.'] = "'.($extras[0][$i]['price'] * $this->currencyRate).'";'."\n";
					echo 'arrExtrasSelected['.$i.'] = 0;'."\n";
				}
			}						
			echo '</script>'."\n";

			echo '<form id="update-room-form" action="'.(!empty($homeurl) ? rtrim($homeurl, '/').'/' : '').'index.php?page=booking_checkout&m=3" method="post">
			'.draw_hidden_field('submition_type', 'update', false).'
			'.draw_hidden_field('room_id', '', false).'
			'.draw_hidden_field('from_date', '', false).'
			'.draw_hidden_field('to_date', '', false).'
			'.draw_token_field(false).'
			</form>';
			
			echo '<form id="checkout-form" action="'.(!empty($homeurl) ? rtrim($homeurl, '/').'/' : '').'index.php?page=booking_payment" method="post">
			'.draw_hidden_field('task', 'do_booking', false).'
			'.draw_hidden_field('submition_type', '', false).'
			'.draw_hidden_field('selected_user', $this->selectedUser, false).'
			'.draw_token_field(false);
			
			echo '<table class="reservation_cart" width="99%" align="center">
			<tr>
				<td colspan="2"><h4>'._BILLING_DETAILS.' &nbsp;';
					if($objLogin->IsLoggedIn()){
						if($objLogin->IsLoggedInAsCustomer()){
							echo '<a style="font-size:13px;" href="javascript:void(0);" onclick="javascript:appGoTo(\'page=booking_details&m=edit&'.WIDGET_HASH.'\')">['._EDIT_WORD.']</a>	';
						}elseif($objLogin->IsLoggedInAsAdmin()){						
							echo '<br>'._CHANGE_CUSTOMER.': 
							<input type="text" id="find_user" name="find_user" value="" size="10" maxlength="40" />
							<input type="button" class="form_button" value="'._SEARCH.'" onclick="javascript:appGoTo(\'page=booking_checkout&cl=\'+jQuery(\'#find_user\').val())" />
							<select name="sel_customer" id="sel_customer">';
								if($find_user == ''){
									if($this->selectedUser == 'admin'){
										echo '<option value="admin">'.$result[0]['first_name'].' '.$result[0]['last_name'].' ('.$result[0]['user_name'].')</option>';										
									}else{
										echo '<option value="'.$result[0]['id'].'">ID:'.$result[0]['id'].' '.$result[0]['first_name'].' '.$result[0]['last_name'].' ('.(($result[0]['user_name'] != '') ? $result[0]['user_name'] : _WITHOUT_ACCOUNT).')'.'</option>';										
									}
								}else{
									$objCustomers = new Customers();
									$result_customers = $objCustomers->GetAllCustomers(' AND (last_name like \''.$find_user.'%\' OR first_name like \''.$find_user.'%\' OR user_name like \''.$find_user.'%\') ');
									if($result_customers[1] > 0){
										for($i = 0; $i < $result_customers[1]; $i++){
											echo '<option value="'.$result_customers[0][$i]['id'].'">ID:'.$result_customers[0][$i]['id'].' '.$result_customers[0][$i]['first_name'].' '.$result_customers[0][$i]['last_name'].' ('.(($result_customers[0][$i]['user_name'] != '') ? $result_customers[0][$i]['user_name'] : _WITHOUT_ACCOUNT).')'.'</option>';
										}								
									}else{
										echo '<option value="admin">'.$result[0]['first_name'].' '.$result[0]['last_name'].' ('.$result[0]['user_name'].')</option>';
									}
								}								
							echo '</select> ';
							if($find_user != '') echo '<input type="button" class="form_button" value="'._APPLY.'" onclick="javascript:appGoTo(\'page=booking_checkout&cid=\'+jQuery(\'#sel_customer\').val())"/> ';
							echo '<input type="button" class="form_button" value="'._SET_ADMIN.'" onclick="javascript:appGoTo(\'page=booking_checkout&cid=admin\')"/>';
							if($find_user != '' && $result_customers[1] == 0) echo ' '._NO_CUSTOMER_FOUND;
						}
					}else{
						echo '<a style="font-size:13px;" href="javascript:void(0);" onclick="javascript:appGoTo(\'page=booking_details\',\'&m=edit\')">['._EDIT_WORD.']</a>	';
					}
					echo '</h4>
				</td>
			</tr>
			<tr>
				<td style="padding-left:10px;">
					'._FIRST_NAME.': '.$result[0]['first_name'].'<br />
					'._LAST_NAME.': '.$result[0]['last_name'].'<br />';				
					if(!$objLogin->IsLoggedInAsAdmin()){					
						echo _ADDRESS.': '.$result[0]['b_address'].'<br />';
						echo _ADDRESS_2.': '.$result[0]['b_address_2'].'<br />';
						echo _CITY.': '.$result[0]['b_city'].'<br />';
						echo _ZIP_CODE.': '.$result[0]['b_zipcode'].'<br />';
						echo _COUNTRY.': '.$result[0]['country_name'].'<br />';
						echo _STATE.': '.$result[0]['b_state'].'<br />';
					}				
				echo '</td>
				<td></td>
			</tr>
			</table><br />';

			echo '<table class="reservation_cart responsive-table" width="99%" align="center">
			<tr><td colspan="10" class="responsive-hidden-before"><h4>'._RESERVATION_DETAILS.'</h4></td></tr>
			<tr class="header">
				<th'.(FLATS_INSTEAD_OF_HOTELS ? ' colspan="2"' : '').' class="'.$class_left.'" width="40px">&nbsp;</th>
				<th align="'.$class_left.'">'.(FLATS_INSTEAD_OF_HOTELS ? _FLAT : _ROOM_TYPE).'</th>
				<th align="center">'._FROM.'</th>
				<th align="center">'._TO.'</th>
				<th width="60px" align="center">'._NIGHTS.'</th>								
				'.(FLATS_INSTEAD_OF_HOTELS ? '' : '<th width="50px" align="center">'._ROOMS.'</th>').'
				<th width="70px" colspan="3" align="center">'._OCCUPANCY.'</th>
				'.(($meal_plans_count) ? '<th width="60px" align="center">'._MEAL_PLANS.'</th>' : '<th style="padding:0px;">&nbsp;</th>').'
				<th class="'.$class_right.'" width="80px" align="'.$class_right.'">'._PRICE.'</th>
			</tr>
			<tr class="responsive-hidden"><td colspan="11" nowrap="nowrap" height="5px"></td></tr>';

			echo '<tr style="font-size:10px;background-color:transparent;" class="responsive-hidden">				
				<th colspan="6"></th>
				<th align="center">'._ADULTS.'</th>
				'.(($allow_children == 'yes') ? '<th align="center">'._CHILDREN.'</th>' : '<th></th>').'
				'.(($allow_extra_beds == 'yes') ? '<th align="center">'._EXTRA_BEDS.'</th>' : '<th></th>').' 
				<th colspan="2"></th>
			</tr>';
			
			$order_price = 0;
			$total_adults = 0;
			$total_children = 0;
			$rooms_guests = array();
			// Count all guests
			$all_adults_count = 0;
			$discount_night_value = 0;
			$max_rooms = 0;
			$min_nights = ModulesSettings::Get('booking', 'minimum_nights');
			$search_availability_period = ModulesSettings::Get('rooms', 'search_availability_period');
			foreach($this->arrReservation as $key => $val){
				$sql = 'SELECT
							'.TABLE_ROOMS.'.id,
							'.TABLE_ROOMS.'.room_type,
							'.TABLE_ROOMS.'.room_icon_thumb,
							'.TABLE_ROOMS.'.hotel_id,
							'.TABLE_ROOMS.'.max_adults,
							'.TABLE_ROOMS.'.max_children, 
							'.TABLE_ROOMS.'.default_price as price,
							'.TABLE_ROOMS.'.discount_guests_type,
							'.TABLE_ROOMS.'.discount_guests_3,
							'.TABLE_ROOMS.'.discount_guests_4,
							'.TABLE_ROOMS.'.discount_guests_5,
							'.TABLE_ROOMS.'.discount_night_type,
							'.TABLE_ROOMS.'.discount_night_3,
							'.TABLE_ROOMS.'.discount_night_4,
							'.TABLE_ROOMS.'.discount_night_5,
							'.TABLE_ROOMS_DESCRIPTION.'.room_type as loc_room_type,
							'.TABLE_ROOMS_DESCRIPTION.'.room_short_description as loc_room_short_description,
							'.TABLE_HOTELS_DESCRIPTION.'.name as hotel_name
						FROM '.TABLE_ROOMS.'
							INNER JOIN '.TABLE_ROOMS_DESCRIPTION.' ON '.TABLE_ROOMS.'.id = '.TABLE_ROOMS_DESCRIPTION.'.room_id
							INNER JOIN '.TABLE_HOTELS_DESCRIPTION.' ON '.TABLE_ROOMS.'.hotel_id	= '.TABLE_HOTELS_DESCRIPTION.'.hotel_id
						WHERE
							'.TABLE_ROOMS.'.id = '.(int)$key.' AND
							'.TABLE_ROOMS_DESCRIPTION.'.language_id = \''.$this->lang.'\' AND
							'.TABLE_HOTELS_DESCRIPTION.'.language_id = \''.$this->lang.'\' ';

				$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($result[1] > 0){
					$room_icon_thumb = ($result[0]['room_icon_thumb'] != '') ? $result[0]['room_icon_thumb'] : 'no_image.png';
					$room_price_w_meal_extrabeds = ($val['price'] + $val['meal_plan_price'] + $val['extra_beds_charge']);
                    $meal_plan_info = MealPlans::GetPlanInfo($val['meal_plan_id']);
                    $meal_plan_name = isset($meal_plan_info['name']) ? $meal_plan_info['name'] : '';

					if(ModulesSettings::Get('rooms', 'guests_stay_discount') == 'yes'){
						$rooms_guests[$key] = array(
							'price' => $val['price'] / ($val['rooms'] * $val['nights']),
							'rooms_count' => $val['rooms'],
							'adults_count' => $val['adults'] * $val['rooms'],
							'nights' => $val['nights'],
							'discount_type' => $result[0]['discount_guests_type'],
							'discount_guests_3' => $result[0]['discount_guests_3'],
							'discount_guests_4' => $result[0]['discount_guests_4'],
							'discount_guests_5' => $result[0]['discount_guests_5'],
							'rooms_price' => $val['price'],
						);
						$all_adults_count += $val['adults'] * $val['rooms'];
					}
					$date_form = format_date($val['from_date'], $objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'm/d/Y' : 'd/m/Y', '', false);
					$date_to = format_date($val['to_date'], $objSettings->GetParameter('date_format') == 'mm/dd/yyyy' ? 'm/d/Y' : 'd/m/Y', '', false);
					echo '<tr>
							<td'.(FLATS_INSTEAD_OF_HOTELS ? ' colspan="2"' : '').' data-th="'.htmlentities(_IMAGE).'"><img src="images/rooms/'.$room_icon_thumb.'" alt="icon" width="32px" height="32px" /></td>							
                            <td data-th="'.htmlentities(FLATS_INSTEAD_OF_HOTELS ? _FLAT : _ROOM_TYPE).'">';
//                    echo '<b>'.prepare_link('rooms', 'room_id', $result[0]['id'], $result[0]['loc_room_type'], $result[0]['loc_room_type'], '', _CLICK_TO_VIEW).'</b><br>
//                                '.prepare_link('hotels', 'hid', $result[0]['hotel_id'], $result[0]['hotel_name'], $result[0]['hotel_name'], '', _CLICK_TO_VIEW);
                    echo '<b>'.(FLATS_INSTEAD_OF_HOTELS ? '' : $result[0]['loc_room_type']).'</b><br>
                                '.$result[0]['hotel_name'];
					echo '</td>							
                            <td align="center" data-th="'.htmlentities(_FROM).'">'.$date_form.'</td>
                            <td align="center" data-th="'.htmlentities(_TO).'">'.$date_to.'</td>
							<td align="center" data-th="'.htmlentities(_NIGHTS).'">'.$val['nights'].'</td>
							'.(FLATS_INSTEAD_OF_HOTELS ? '' : '<td align="center" data-th="'.htmlentities(_ROOMS).'">'.$val['rooms'].'</td>').'
							<td align="center" data-th="'.htmlentities(_ADULT).'">'.$val['adults'].'</td>
							'.(($allow_children == 'yes') ? '<td align="center" data-th="'.htmlentities(_CHILD).'">'.$val['children'].'</td>' : '<td></td>').'
							'.(($allow_extra_beds == 'yes') ? '<td align="center" data-th="'.htmlentities(_EXTRA_BEDS).'">'.$val['extra_beds'].'</td>' : '<td></td>').'
							'.(($meal_plans_count) ? '<td align="center" data-th="'.htmlentities(_MEAL_PLANS).'">'.$meal_plan_name.'</td>' : '<td></td>').'
							<td class="'.$class_right.'" data-th="'.htmlentities(_PRICE).'">'.Currencies::PriceFormat($room_price_w_meal_extrabeds * $this->currencyRate, '', '', $this->currencyFormat).'&nbsp;</td>
						</tr>
						<tr class="responsive-hidden"><td colspan="11" nowrap height="3px"></td></tr>';
					$order_price += ($room_price_w_meal_extrabeds * $this->currencyRate);
					$total_adults += $val['rooms'] * $val['adults'];
					$total_children += ($allow_children == 'yes') ? $val['rooms'] * $val['children'] : 0;

					// If has been ordered 3 and more nights, check an additional discount on the night
					if(ModulesSettings::Get('rooms', 'long_term_stay_discount') == 'yes'){
						if($val['nights'] >= 3){
							$discount_value = 0;
							if($val['nights'] == 3 && !empty($result[0]['discount_night_3'])){
								$discount_value = $result[0]['discount_night_3'];
							}elseif($val['nights'] == 4 && !empty($result[0]['discount_night_4'])){
								$discount_value = $result[0]['discount_night_4'];
							}else{
								$discount_value = $result[0]['discount_night_5'];
							}

							// 0 - Fixed price, 1 - Percentage
							if($result[0]['discount_night_type'] == 1){
								$discount_nights = ($val['price'] * ($discount_value / 100));
								$rooms_guests[$key]['price'] -= $rooms_guests[$key]['price'] * ($discount_value / 100);
								$rooms_guests[$key]['rooms_price'] -= $rooms_guests[$key]['rooms_price'] * ($discount_value / 100);
								
							}else{
								if($val['price'] > $discount_value){
									$discount_nights = ($val['price'] / ($val['rooms'] * $val['nights'])  > $discount_value ? $discount_value * $val['rooms'] * $val['nights'] : $val['price']);
									$rooms_guests[$key]['price'] -= $discount_value;
									$rooms_guests[$key]['rooms_price'] -= $discount_nights;
								}else{
									$discount_nights = $val['price'];
									$rooms_guests[$key]['price'] = 0;
									$rooms_guests[$key]['rooms_price'] = 0;
								}
							}
							// The discount can't exceed the cost per room
							$discount_nights = $discount_nights > $val['price'] ? $val['price'] : $discount_nights;
							$discount_night_value += $discount_nights * $this->currencyRate;
                            $this->arrReservation[$key]['discount_nights'] = $discount_nights;
						}
					}
				}
			}
			
			// draw sub-total row			
			echo '<tr>
					<td colspan="7" class="responsive-hidden"></td>
					<td class="td '.$class_left.' responsive-hidden" colspan="3"><b>'._SUBTOTAL.':</b></td>
					<td class="td '.$class_right.'" align="'.$class_right.'" data-th="'.htmlentities(_SUBTOTAL).'">
						<b>'.Currencies::PriceFormat($order_price, '', '', $this->currencyFormat).'</b>
					</td>
				 </tr>';

			//echo '<tr><td colspan="10" nowrap height="5px"></td></tr>';
			
			// EXTRAS
			// ------------------------------------------------------------
			if($extras[1]){
				echo '<tr class="responsive-hidden"><td colspan="11" nowrap height="10px"></td></tr>';
				echo '<tr><td colspan="11" class="responsive-hidden-before"><h4>'._EXTRAS.'</h4></td></tr>';				
				echo '<tr><td colspan="11" class="responsive-hidden-before"><table width="340px" class="extras-table">';				
				for($i=0; $i<$extras[1]; $i++){
					$extras_id = (($submition_type == 'apply_coupon') && isset($_POST['extras_'.$extras[0][$i]['id']])) ? $_POST['extras_'.$extras[0][$i]['id']] : 0;
					echo '<tr>';
					echo '<td wrap="wrap">'.$extras[0][$i]['name'].' <span class="help" title="'.$extras[0][$i]['description'].'">[?]</span></td>';
					echo '<td>&nbsp;</td>';
					echo '<td align="right">'.Currencies::PriceFormat($extras[0][$i]['price'] * $this->currencyRate, '', '', $this->currencyFormat).'</td>';
					echo '<td>&nbsp;</td>';
					echo '<td>'.draw_numbers_select_field('extras_'.$extras[0][$i]['id'], $extras_id, '0', $extras[0][$i]['maximum_count'], 1, 'extras_ddl form-control', 'onchange="appUpdateTotalSum('.$i.',this.value,'.(int)$extras[1].')"', false).'</td>';
					echo '</tr>';
				}
				echo '</table></td></tr>';								
			}

			if($discount_night_value > 0){
				$order_price -= $discount_night_value;
				echo '<tr>
						<td colspan="7"></td>
						<td class="td '.$class_left.'" colspan="3"><b><span style="color:#a60000">'._LONG_TERM_STAY_DISCOUNT.': </span></b></td>
						<td class="td '.$class_right.'" align="'.$align_right.'"><b><span style="color:#a60000">- '.Currencies::PriceFormat($discount_night_value, '', '', $this->currencyFormat).'</span></b></td>
					</tr>';				
			}

			// If has been ordered 3 and more rooms, check an additional discount on the number of rooms
			if(ModulesSettings::Get('rooms', 'guests_stay_discount') == 'yes'){
				$discount_value = 0;
				if(TYPE_DISCOUNT_GUEST == 'guests'){
					$count_adults_or_guests = $all_adults_count;
				}else{
					$count_adults_or_guests = $val['rooms'];
				}
				if($count_adults_or_guests >= 3){
					foreach($rooms_guests as $info_room){
						$discount_value = 0;
						if($count_adults_or_guests == 3 && !empty($info_room['discount_guests_3'])){
							$discount_value = $info_room['discount_guests_3'];
						}elseif($count_adults_or_guests == 4 && !empty($info_room['discount_guests_4'])){
							$discount_value = $info_room['discount_guests_4'];
						}else{
							$discount_value = $info_room['discount_guests_5'];
						}

						// 0 - Fixed price, 1 - Percentage
						if($info_room['discount_type'] == 1){
							$discount_value += ($info_room['rooms_price'] * ($discount_value / 100)) * $this->currencyRate;
						}else{
							$discount_value += ($info_room['price'] > $discount_value ? $discount_value : $info_room['price']) * $info_room['rooms_count'] * $this->currencyRate * $info_room['nights'];
						}
					}
				}

				if($discount_value > 0){
					$order_price -= $discount_value;
					echo '<tr>
							<td colspan="7" class="responsive-hidden"></td>
							<td class="td '.$class_left.' responsive-hidden" colspan="3"><b><span style="color:#a60000">'.(TYPE_DISCOUNT_GUEST == 'guests' ? _GUESTS_DISCOUNT : _ROOMS_DISCOUNT).': </span></b></td>
							<td class="td '.$class_right.'" align="'.$class_right.'" data-th="'.htmlentities(TYPE_DISCOUNT_GUEST == 'guests' ? _GUESTS_DISCOUNT : _ROOMS_DISCOUNT).'"><b><span style="color:#a60000">- '.Currencies::PriceFormat($discount_value, '', '', $this->currencyFormat).'</span></b></td>
						</tr>';				
				}
			}
			// Calculate discount
			$discount_value = ($this->discountType == '1' ? ($order_price * ($this->discountValue / 100)) : $this->discountValue);
			$order_price -= $discount_value;
			
			// Calculate percent
			$vat_cost = (($order_price + $this->bookingInitialFee + ($this->guestTax * ($total_adults + $total_children))) * ($this->vatPercent / 100));
			$cart_total = ($order_price + $this->bookingInitialFee + ($this->guestTax * ($total_adults + $total_children))) + $vat_cost;

			if($this->discountCampaignID != '' || $this->discountCoupon != ''){
				if(empty($this->discountTitle)){
					$this->discountTitle = $this->discountCoupon ? _COUPON_DISCOUNT : _DISCOUNT;
				}
				$discount_title = $this->discountTitle.': (';
				// 0 - Fixed price, 1 - Percentage
				if($this->discountType == 1){
					echo Currencies::PriceFormat($this->discountValue, '%', 'after', $this->currencyFormat);
				}else{
					echo Currencies::PriceFormat($this->discountValue, Application::Get('currency_symbol'), 'before', $this->currencyFormat);
				}
				echo ')';
				echo '<tr>
						<td colspan="7" class="responsive-hidden"></td>
						<td class="td '.$class_left.' responsive-hidden" colspan="3"><b><span style="color:#a60000">'.$discount_title.'</span></b></td>
						<td class="td '.$class_right.'" align="'.$class_right.'" data-th="'.htmlentities($discount_title).'"><b><span style="color:#a60000">- '.Currencies::PriceFormat($discount_value, '', '', $this->currencyFormat).'</span></b></td>
					</tr>';				
			}
			if(!empty($this->bookingInitialFee)){
				echo '<tr>
						<td colspan="7" class="responsive-hidden"></td>
						<td class="td '.$class_left.' responsive-hidden" colspan="3"><b>'._INITIAL_FEE.': </b></td>
						<td class="td '.$class_right.'" align="'.$class_right.'" data-th="'.htmlentities(_INITIAL_FEE).'"><b>'.Currencies::PriceFormat($this->bookingInitialFee, '', '', $this->currencyFormat).'</b></td>
					</tr>';								
			}
			if(!empty($this->guestTax)){
				echo '<tr>
						<td colspan="7" class="responsive-hidden"></td>
						<td class="td '.$class_left.' responsive-hidden" colspan="3"><b>'._GUEST_TAX.': </b></td>
						<td class="td '.$class_right.'" align="'.$class_right.'" data-th="'.htmlentities(_GUEST_TAX).'">
							<b><label id="guest_tax" data-price="'.(float)($this->guestTax * ($total_adults + $total_children)).'">'.Currencies::PriceFormat(($this->guestTax * ($total_adults + $total_children)), '', '', $this->currencyFormat).'</label></b>
						</td>
					</tr>';								
			}
			if($this->vatIncludedInPrice == 'no'){
				$vat_title = _VAT.': ('.Currencies::PriceFormat($this->vatPercent, '%', 'after', $this->currencyFormat, $this->GetVatPercentDecimalPoints($this->vatPercent)).')';
				echo '<tr>
						<td colspan="7" class="responsive-hidden"></td>
						<td class="td '.$class_left.' responsive-hidden" colspan="3"><b>'.$vat_title.'</b></td>
						<td class="td '.$class_right.'" align="'.$class_right.'" data-th="'.htmlentities($vat_title).'">
							<b><label id="reservation_vat">'.Currencies::PriceFormat($vat_cost, '', '', $this->currencyFormat).'</label></b>
						</td>
					 </tr>';
			}
			echo '<tr class="responsive-hidden"><td colspan="11" nowrap height="5px"></td></tr>
				 <tr class="tr-footer">
					<td colspan="7" class="responsive-hidden"></td>
					<td class="td '.$class_left.' responsive-hidden" colspan="3"><b>'._TOTAL.':</b></td>
					<td class="td '.$class_right.'" align="'.$class_right.'" data-th="'.htmlentities(_TOTAL).'">
						<b><label id="reservation_total">'.Currencies::PriceFormat($cart_total, '', '', $this->currencyFormat).'</label></b>
					</td>
				 </tr>';

			// PAYMENT DETAILS
			// ------------------------------------------------------------
			echo '<tr class="responsive-hidden"><td colspan="11" nowrap height="12px"></td></tr>';
			echo '<tr><td colspan="11" class="responsive-hidden-before"><h4>'._PAYMENT_DETAILS.'</h4></td></tr>';
			echo '<tr><td colspan="11" class="responsive-hidden-before">';
			echo '<table border="0" width="100%">';
				if($payment_type_cnt > 1){
					echo '<tr><td width="130px" nowrap class="payment-details payment-type">'._PAYMENT_TYPE.': &nbsp;</td><td> 
					<select name="payment_type" class="form-control payment_type" id="payment_type">';
						if($payment_type_poa == 'yes') echo '<option value="poa" '.(($payment_type == 'poa') ? 'selected="selected"' : '').'>'._PAY_ON_ARRIVAL.'</option>';
						if($payment_type_online == 'yes') echo '<option value="online" '.(($payment_type == 'online') ? 'selected="selected"' : '').'>'._ONLINE_ORDER.'</option>';	
						if($payment_type_bank_transfer == 'yes') echo '<option value="bank.transfer" '.(($payment_type == 'bank.transfer') ? 'selected="selected"' : '').'>'._BANK_TRANSFER.'</option>';
						if($payment_type_paypal == 'yes') echo '<option value="paypal" '.(($payment_type == 'paypal') ? 'selected="selected"' : '').'>'._PAYPAL.'</option>';
						if($payment_type_2co == 'yes') echo '<option value="2co" '.(($payment_type == '2co') ? 'selected="selected"' : '').'>2CO</option>';	
						if($payment_type_authorize == 'yes') echo '<option value="authorize.net" '.(($payment_type == 'authorize.net') ? 'selected="selected"' : '').'>Authorize.Net</option>';
						if($payment_type_balance == 'yes') echo '<option value="account.balance" '.(($payment_type == 'account.balance') ? 'selected="selected"' : '').'>'._PAY_WITH_BALANCE.'</option>';	
					echo '</select>';
					echo '</td></tr>';
				}elseif($payment_type_cnt == 1){
					if($payment_type_poa == 'yes') $payment_type_hidden = 'poa';
					else if($payment_type_online == 'yes') $payment_type_hidden = 'online';
					else if($payment_type_bank_transfer == 'yes') $payment_type_hidden = 'bank.transfer';
					else if($payment_type_paypal == 'yes') $payment_type_hidden = 'paypal';
					else if($payment_type_2co == 'yes') $payment_type_hidden = '2co';
					else if($payment_type_authorize == 'yes') $payment_type_hidden = 'authorize.net';
					else if($payment_type_balance == 'yes') $payment_type_hidden = 'account.balance';
					else{
						$payment_type_hidden = '';
						$payment_types_defined = false;
					}
					echo '<tr><td>'.draw_hidden_field('payment_type', $payment_type_hidden, false, 'payment_type').'</td></tr>';
				}else{
					$payment_types_defined = false;
					echo '<tr><td colspan="2">'.draw_important_message(_NO_PAYMENT_METHODS_ALERT, false).'</td></tr>';
				}						
				echo '<tr>';
					if($pre_payment_type == 'first night' && $this->first_night_possible){
						echo '<td>'._PAYMENT_METHOD.': </td>';
						echo '<td>';
						echo '<input type="radio" name="pre_payment_type" id="pre_payment_fully" value="full price" checked="checked /> <label for="pre_payment_fully">'._FULL_PRICE.'</label> &nbsp;&nbsp;';
						echo '<input type="radio" name="pre_payment_type" id="pre_payment_partially" value="first night" /> <label for="pre_payment_partially">'._FIRST_NIGHT.'</label>';
						echo draw_hidden_field('pre_payment_value', $pre_payment_value, false, 'pre_payment_full');
						echo '</td>';
					}elseif($pre_payment_type == 'percentage' && $pre_payment_value > '0' && $pre_payment_value < '100'){
						echo '<td>'._PAYMENT_METHOD.': </td>';
						echo '<td>';
						echo '<input type="radio" name="pre_payment_type" id="pre_payment_fully" value="full price" checked="checked" /> <label for="pre_payment_fully">'._FULL_PRICE.'</label> &nbsp;&nbsp;';
						echo '<input type="radio" name="pre_payment_type" id="pre_payment_partially" value="percentage" /> <label for="pre_payment_partially">'._PRE_PAYMENT.' ('.Currencies::PriceFormat($pre_payment_value, '%', 'after', $this->currencyFormat).')</label>';
						echo draw_hidden_field('pre_payment_value', $pre_payment_value, false, 'pre_payment_full');
						echo '</td>';
					}elseif($pre_payment_type == 'fixed sum' && $pre_payment_value > '0'){
						echo '<td>'._PAYMENT_METHOD.': </td>';
						echo '<td>';
						echo '<input type="radio" name="pre_payment_type" id="pre_payment_fully" value="full price" checked="checked" /> <label for="pre_payment_fully">'._FULL_PRICE.'</label> &nbsp;&nbsp;';
						echo '<input type="radio" name="pre_payment_type" id="pre_payment_partially" value="fixed sum" /> <label for="pre_payment_partially">'._PRE_PAYMENT.' ('.Currencies::PriceFormat($pre_payment_value * $this->currencyRate, '', '', $this->currencyFormat).')</label>';
						echo draw_hidden_field('pre_payment_value', $pre_payment_value, false, 'pre_payment_full');
						echo '</td>';
					}else{
						echo '<td colspan="2">';
						// full price payment
						if($payment_type_cnt <= 1 && $payment_types_defined) echo _FULL_PRICE;
						echo draw_hidden_field('pre_payment_type', 'full price', false, 'pre_payment_fully');
						echo draw_hidden_field('pre_payment_value', '100', false, 'pre_payment_full');
						echo '</td>';
					}					
				echo '</tr>';			
			echo '</table></td></tr>';
			
			if($payment_types_defined){			
				// PROMO CODES OR DISCOUNT COUPONS
				// ------------------------------------------------------------
				echo '<table>';
				echo '<tr class="responsive-hidden"><td colspan="11" nowrap height="12px"></td></tr>';
				echo '<tr class="responsive-hidden-before"><td colspan="11"><h4>'._PROMO_CODE_OR_COUPON.'</h4></td></tr>';
				echo '<tr class="responsive-hidden-before"><td colspan="11">'._PROMO_COUPON_NOTICE.'</td></tr>';
				echo '<tr>';
				echo '<td colspan="11">';
				if(!empty($this->discountCoupon)){				
					echo '<input type="text" class="discount_coupon" name="discount_coupon" id="discount_coupon" value="'.$this->discountCoupon.'" readonly="readonly" maxlength="32" autocomplete="off" />&nbsp;&nbsp;&nbsp;';
					echo '<input type="button" class="form_button" id="discount_button" value="'._REMOVE.'" onclick="appSubmitCoupon(\'remove_coupon\')" />';
				}else{				
					echo '<input type="text" class="discount_coupon" name="discount_coupon" id="discount_coupon" value="'.$this->discountCoupon.'" maxlength="32" autocomplete="off" />&nbsp;&nbsp;&nbsp;';
					echo '<input type="button" class="form_button" id="discount_button" value="'._APPLY.'" onclick="appSubmitCoupon(\'apply_coupon\')" />';
				}
				echo '</td>';
				echo '</tr>';					
		
				echo '<tr><td colspan="11" nowrap height="15px"></td></tr>
					  <tr valign="middle">
						<td colspan="11" nowrap height="15px">
							<h4 style="cursor:pointer;" onclick="appToggleElement(\'additional_info\')">'._ADDITIONAL_INFO.' +</h4>
							<textarea name="additional_info" id="additional_info" style="display:none;width:100%;height:75px"></textarea>
						</td>
					  </tr>
					  <tr><td colspan="11" nowrap height="5px"></td></tr>
					  <tr valign="middle">
						<td colspan="8" align="'.$class_right.'"></td>
						<td align="'.$class_right.'" colspan="3">
							'.(($payment_types_defined) ? '<input class="form_button" type="submit" value="'._SUBMIT_BOOKING.'" />' : '').' 
						</td>
					</tr>';
				echo '</table>';
				echo '<input type="hidden" id="hid_vat_percent" value="'.$this->vatPercent.'" />';
				echo '<input type="hidden" id="hid_booking_initial_fee" value="'.$this->bookingInitialFee.'" />';
				echo '<input type="hidden" id="hid_booking_guest_tax" value="'.($this->guestTax * ($total_adults + $total_children)).'" />';
				echo '<input type="hidden" id="hid_order_price" value="'.$order_price.'" />';
				echo '<input type="hidden" id="hid_currency_symbol" value="'.Application::Get('currency_symbol').'" />';
				echo '<input type="hidden" id="hid_currency_format" value="'.$this->currencyFormat.'" />';
				echo '</form><br>';
				
				if($submition_type == 'apply_coupon'){
					echo "\n".'<script type="text/javascript">'."\n";			
					for($i=0; $i<$extras[1]; $i++){
						$extras_id = isset($_POST['extras_'.$extras[0][$i]['id']]) ? $_POST['extras_'.$extras[0][$i]['id']] : 0;
						if($extras_id > 0){
							echo 'appUpdateTotalSum('.$i.','.$extras_id.','.$extras[1].')'.";\n";		
						}
					}
					echo '</script>'."\n";					
				}
			}else{
				echo '</table>';
				echo '</form>';				
				return '';
			}
		}else{
			draw_message(_RESERVATION_CART_IS_EMPTY_ALERT, true, true);
		}
	}	


	/**
	 * Empty Reservation Cart
	 */
	public function EmptyCart()
	{
		$this->arrReservation = array();
		$this->arrReservationInfo = array();
		Session::Set('current_customer_id', '');
	}
	
	/**
	 * Returns amount of items in Reservation Cart
	 */
	public function GetCartItems()
	{
		return $this->cartItems;
	}	
	
	/**
	 * Returns total number of adults in Reservation Cart
	 */
	public function GetAdultsInCart()
	{
		$adults_number = 0;
		
		if(count($this->arrReservation) > 0){			
			foreach($this->arrReservation as $key => $val){
				$adults_number += isset($val['adults']) ? $val['rooms'] * $val['adults'] : 0;
			}
		}
		
		return $adults_number;
	}

	/**
	 * Returns total number of children in Reservation Cart
	 */
	public function GetChildrenInCart()
	{
		$children_number = 0;
		
		if(count($this->arrReservation) > 0){			
			foreach($this->arrReservation as $key => $val){
				$children_number += isset($val['children']) ? $val['rooms'] * $val['children'] : 0;
		}
		}
		return $children_number;
	}


	public function GetArrReservation()
	{
		return $this->arrReservation;
	}

	/**
	 * Checks if cart is empty 
	 */
	public function IsCartEmpty()
	{
		return ($this->cartItems > 0) ? false : true;
	}	

	/**
	 * Draw reservation info
	 * @param $payment_type
	 * @param $additional_info
	 * @param $extras
	 * @param $pre_payment_type
	 * @param $pre_payment_value
	 * @param $draw
	 */
	public function DrawReservation($payment_type, $additional_info, $extras = array(), $pre_payment_type = '', $pre_payment_value = '', $draw = true)
	{
		global $objLogin;

		$class_left = Application::Get('defined_left');
		$class_right = Application::Get('defined_right');
		$output = '';

		$cc_type 		  = isset($_POST['cc_type']) ? prepare_input($_POST['cc_type']) : '';
		$cc_holder_name   = isset($_POST['cc_holder_name']) ? prepare_input($_POST['cc_holder_name']) : '';
		$cc_number 		  = isset($_POST['cc_number']) ? prepare_input($_POST['cc_number']) : '';
		$cc_expires_month = isset($_POST['cc_expires_month']) ? prepare_input($_POST['cc_expires_month']) : '01';
		$cc_expires_year  = isset($_POST['cc_expires_year']) ? prepare_input($_POST['cc_expires_year']) : date('Y');
		$cc_cvv_code 	  = isset($_POST['cc_cvv_code']) ? prepare_input($_POST['cc_cvv_code']) : '';

		$paypal_email        	= ModulesSettings::Get('booking', 'paypal_email');
		$credit_card_required	= ModulesSettings::Get('booking', 'online_credit_card_required');
		$two_checkout_vendor 	= ModulesSettings::Get('booking', 'two_checkout_vendor');
		$authorize_login_id  	= ModulesSettings::Get('booking', 'authorize_login_id');
		$authorize_transaction_key = ModulesSettings::Get('booking', 'authorize_transaction_key');
		$bank_transfer_info   	= ModulesSettings::Get('booking', 'bank_transfer_info');
		$mode                	= ModulesSettings::Get('booking', 'mode');
		
        // specify API login and key for separate hotels        
        if(ModulesSettings::Get('booking', 'allow_separate_gateways') == 'yes'){
            $hotel_id = $this->GetReservationHotelId();
            $info = HotelPaymentGateways::GetPaymentInfo($hotel_id, $payment_type);
            
			if(isset($info[0]['is_active']) && $info[0]['is_active'] == 1){
				if($payment_type == 'paypal'){
					$paypal_email = isset($info[0]['api_login']) ? $info[0]['api_login'] : '';
				}elseif($payment_type == '2co'){
					$two_checkout_vendor = $info[0]['api_login'];
				}elseif($payment_type == 'authorize.net'){
					$authorize_login_id = $info[0]['api_login'];
					$authorize_transaction_key = isset($info[0]['api_key']) ? $info[0]['api_key'] : '';
				}elseif($payment_type == 'bank.transfer'){
					$bank_transfer_info = isset($info[0]['payment_info']) ? $info[0]['payment_info'] : '';
				}
			}
        }

		// prepare customers info 
		$sql='SELECT * FROM '.TABLE_CUSTOMERS.' WHERE id = '.(int)$this->currentCustomerID;
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		$customer_info = array();
		$customer_info['first_name'] = isset($result[0]['first_name']) ? $result[0]['first_name'] : '';
		$customer_info['last_name'] = isset($result[0]['last_name']) ? $result[0]['last_name'] : '';
		$customer_info['address1'] = isset($result[0]['b_address']) ? $result[0]['b_address'] : '';
		$customer_info['address2'] = isset($result[0]['b_address2']) ? $result[0]['b_address2'] : '';
		$customer_info['city'] = isset($result[0]['b_city']) ? $result[0]['b_city'] : '';
		$customer_info['state'] = isset($result[0]['b_state']) ? $result[0]['b_state'] : '';
		$customer_info['zip'] = isset($result[0]['b_zipcode']) ? $result[0]['b_zipcode'] : '';
		$customer_info['country'] = isset($result[0]['b_country']) ? $result[0]['b_country'] : '';
		$customer_info['email'] = isset($result[0]['email']) ? $result[0]['email'] : '';
		$customer_info['company'] = isset($result[0]['company']) ? $result[0]['company'] : '';
		$customer_info['phone'] = isset($result[0]['phone']) ? $result[0]['phone'] : '';
		$customer_info['fax'] = isset($result[0]['fax']) ? $result[0]['fax'] : '';
		$customer_info['balance'] = isset($result[0]['balance']) ? $result[0]['balance'] : 0; 	
		
		if($cc_holder_name == ''){
			if($objLogin->IsLoggedIn()){
				$cc_holder_name = $objLogin->GetLoggedFirstName().' '.$objLogin->GetLoggedLastName();
			}else{
				$cc_holder_name = $customer_info['first_name'].' '.$customer_info['last_name'];
			}
		}		
		
		// Check if prepared booking exists and replace it		
		$sql='SELECT id, booking_number, affiliate_id
			  FROM '.TABLE_BOOKINGS.'
			  WHERE customer_id = '.(int)$this->currentCustomerID.' AND
					status = 0 AND  
					is_admin_reservation = '.(($this->selectedUser == 'admin') ? '1' : '0').'
			  ORDER BY id DESC';	
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$booking_number = $result[0]['booking_number'];
			$order_price = $this->cartTotalSum;
			
			// Calculate total number of adults 
			$total_adults = $this->GetAdultsInCart();
			
			// Calculate total number of children 
			$total_children = $this->GetChildrenInCart();

			// prepare extras
			$extras_text = '';
			$extras_param = '';
			$extras_sub_total = 0;
			if(count($extras) > 0){
				$extras_text_header = '<tr><td colspan="3" nowrap height="10px"></td></tr>
					  <tr><td colspan="3"><h4>'._EXTRAS.'</h4></td></tr>
					  <tr><td colspan="3">';				
					$extras_text_middle = '';
					foreach($extras as $key => $val){
						$extr = Extras::GetExtrasInfo($key);
						if($val){
							$extras_sub_total += ($extr['price'] * $this->currencyRate) * $val;
							$extras_text_middle .= '<tr><td nowrap="nowrap">'.$extr['name'].'&nbsp;</td>';
							$extras_text_middle .= '<td> : </td>';
							$extras_text_middle .= '<td> '.Currencies::PriceFormat($extr['price'] * $this->currencyRate, '', '', $this->currencyFormat).' x '.$val;
							$extras_text_middle .= draw_hidden_field('extras_'.$key, $val, false)."\n";
							$extras_param .= draw_hidden_field('extras_'.$key, $val, false)."\n";
							$extras_text_middle .= '</td></tr>';
						}
					}
				$extras_text_footer  = '<tr><td>'._EXTRAS_SUBTOTAL.' </td><td> : </td><td> <b>'.Currencies::PriceFormat($extras_sub_total, '', '', $this->currencyFormat).'</b></td></tr>';								  
				$extras_text_footer .= '</td></tr>';			
	
				if($extras_sub_total >= 0){
					$extras_text = $extras_text_header.$extras_text_middle.$extras_text_footer;
				}
			}
			
			// Calculate discount
			$arr_discounts = $this->GetGuestsAndNightsDiscount();
			$guests_discount = $arr_discounts['discount_guests'];
			$nights_discount = $arr_discounts['discount_nights'];
			$order_price_after_discount_g_n = $order_price - ($guests_discount + $nights_discount);
			$discount_value = ($this->discountType == '1' ? ($order_price_after_discount_g_n * ($this->discountValue / 100)) : $this->discountValue);
			$order_price_after_discount = $order_price_after_discount_g_n - $discount_value;
	
			// Calculate VAT
			$cart_total_wo_vat = round($order_price_after_discount + $extras_sub_total, 2);
			$vat_cost = (($cart_total_wo_vat + $this->bookingInitialFee + ($this->guestTax * ($total_adults + $total_children))) * ($this->vatPercent / 100));
			$cart_total = round($cart_total_wo_vat, 2) + $this->bookingInitialFee + ($this->guestTax * ($total_adults + $total_children)) + $vat_cost;

			if($pre_payment_type == 'first night'){
				$is_prepayment = true;			
				$cart_total = ($this->firstNightSum * (1 + $this->vatPercent / 100));
				$prepayment_text = _FIRST_NIGHT;
			}elseif(($pre_payment_type == 'percentage') && (int)$pre_payment_value > 0 && (int)$pre_payment_value < 100){
				$is_prepayment = true;			
				$cart_total = ($cart_total * ($pre_payment_value / 100));
				$prepayment_text = $pre_payment_value.'%';
			}elseif(($pre_payment_type == 'fixed sum') && (int)$pre_payment_value > 0){
				$is_prepayment = true;			
				$cart_total = round($pre_payment_value * $this->currencyRate, 2);
				$prepayment_text = _FIXED_SUM;  
			}else{
				$prepayment_text = '';
				$is_prepayment = false;
			}
	
			$pp_params = array(
				'api_login'       	=> '',
				'transaction_key' 	=> '',
				'payment_info'    	=> $bank_transfer_info,
				
				'booking_number'    => $booking_number,			
				
				'address1'      	=> $customer_info['address1'],
				'address2'      	=> $customer_info['address2'],
				'city'          	=> $customer_info['city'],
				'zip'           	=> $customer_info['zip'],
				'country'       	=> $customer_info['country'],
				'state'         	=> $customer_info['state'],
				'first_name'    	=> $customer_info['first_name'],
				'last_name'     	=> $customer_info['last_name'],
				'email'         	=> $customer_info['email'],
				'company'       	=> $customer_info['company'],
				'phone'         	=> $customer_info['phone'],
				'fax'           	=> $customer_info['fax'],
				
				'notify'        	=> '',
				'return'        	=> 'index.php?page=booking_return',
				//'cancel_return' 	=> 'index.php?page=booking_cancel',
				'cancel_return' 	=> 'index.php?page=booking',
							
				'paypal_form_type'   	   => '',
				'paypal_form_fields' 	   => '',
				'paypal_form_fields_count' => '',
				
				'credit_card_required' => '',
				'cc_type'             => '',
				'cc_holder_name'      => '',
				'cc_number'           => '',
				'cc_cvv_code'         => '',
				'cc_expires_month'    => '',
				'cc_expires_year'     => '',
				
				'currency_code'      => Application::Get('currency_code'),
				'additional_info'    => $additional_info,
				'discount_type'      => $this->discountType,
				'discount_value'     => $discount_value,
				'guests_discount'    => $guests_discount,
				'nights_discount'    => $nights_discount,
				'extras_param'       => $extras_param,
				'extras_sub_total'   => $extras_sub_total,
				'vat_cost'           => $vat_cost,
				'cart_total' 		 => number_format((float)$cart_total, (int)Application::Get('currency_decimals'), '.', ''),
				'is_prepayment'      => $is_prepayment,
				'pre_payment_type'   => $pre_payment_type,
				'pre_payment_value'  => $pre_payment_value,
				'account_balance'  	 => $customer_info['balance'],
				'module'			 => 'booking',
			);
			
			$fisrt_part = '<table border="0" width="97%" align="center">
				<tr><td width="30%">'._BOOKING_DATE.' </td><td width="2%"> : </td><td> '.format_date(date('Y-m-d H:i:s'), $this->fieldDateFormat, '', true).'</td></tr>						
				'.(FLATS_INSTEAD_OF_HOTELS ? '' : '<tr><td>'._ROOMS.' </td><td> : </td><td> '.(int)$this->roomsCount.'</td></tr>').'
				<tr><td>'._BOOKING_PRICE.' </td><td width="2%"> : </td><td> '.Currencies::PriceFormat($order_price, '', '', $this->currencyFormat).'</td></tr>';
			if($nights_discount > 0){
				$fisrt_part .= '<tr><td><span style="color:#a60000">'._LONG_TERM_STAY_DISCOUNT.'</span> </td><td> <span style="color:#a60000">:</span> </td><td> <b><span style="color:#a60000">- '.Currencies::PriceFormat($nights_discount, '', '', $this->currencyFormat).'</span></b></td></tr>';
			}
			if($guests_discount > 0){
				$fisrt_part .= '<tr><td><span style="color:#a60000">'.(TYPE_DISCOUNT_GUEST == 'guests' ? _GUESTS_DISCOUNT : _ROOMS_DISCOUNT).'</span> </td><td> <span style="color:#a60000">:</span> </td><td> <b><span style="color:#a60000">- '.Currencies::PriceFormat($guests_discount, '', '', $this->currencyFormat).'</span></b></td></tr>';
			}
            if($nights_discount > 0 || $guests_discount > 0){
				$fisrt_part .= 	'<tr><td>'._BOOKING_SUBTOTAL.' </td><td> : </td><td> <b>'.Currencies::PriceFormat($order_price_after_discount_g_n, '', '', $this->currencyFormat).'</b></td></tr>';
            }

			$fisrt_part .= ((count($extras) > 0) ? $extras_text : '').'
				<tr><td colspan="3" nowrap height="10px"></td></tr>
				<tr><td colspan="3"><h4>'._TOTAL.'</h4></td></tr>';
			if($discount_value != ''){
				if(empty($this->discountTitle)){
					$this->discountTitle = $this->discountCoupon ? _COUPON_DISCOUNT : _DISCOUNT;
				}
				$fisrt_part .= '<tr><td><span style="color:#a60000">'.$this->discountTitle.'</span> </td><td> <span style="color:#a60000">:</span> </td><td> <b><span style="color:#a60000">- '.Currencies::PriceFormat($discount_value, '', '', $this->currencyFormat).' (';
				// 0 - Fixed price, 1 - Percentage
				if($this->discountType == 1){
					$fisrt_part .= Currencies::PriceFormat($this->discountValue, '%', 'after', $this->currencyFormat);
				}else{
					$fisrt_part .= Currencies::PriceFormat($this->discountValue, Application::Get('currency_symbol'), 'before', $this->currencyFormat);
				}
				$fisrt_part .= ')</span></b></td></tr>';
			}			
			$fisrt_part .= '<tr><td>'._SUBTOTAL.' </td><td> : </td><td> '.Currencies::PriceFormat($cart_total_wo_vat, '', '', $this->currencyFormat).'</td></tr>';

			if(!empty($this->bookingInitialFee)){
				$fisrt_part .= '<tr><td>'._INITIAL_FEE.' </td><td> : </td><td> '.Currencies::PriceFormat($this->bookingInitialFee, '', '', $this->currencyFormat).'</td></tr>';
			}
			if(!empty($this->guestTax)){
				$fisrt_part .= '<tr><td>'._GUEST_TAX.' </td><td> : </td><td> '.Currencies::PriceFormat(($this->guestTax * ($total_adults + $total_children)), '', '', $this->currencyFormat).'</td></tr>';
			}
			if($this->vatIncludedInPrice == 'no'){
				$fisrt_part .= '<tr><td>'._VAT.' ('.Currencies::PriceFormat($this->vatPercent, '%', 'after', $this->currencyFormat, $this->GetVatPercentDecimalPoints($this->vatPercent)).') </td><td> : </td><td> '.Currencies::PriceFormat($vat_cost, '', '', $this->currencyFormat).'</td></tr>';
			}
			if($is_prepayment){
				$fisrt_part .= '<tr><td>'._PAYMENT_SUM.' </td><td> : </td><td> <b>'.Currencies::PriceFormat($order_price_after_discount + $extras_sub_total + $this->bookingInitialFee + ($this->guestTax * ($total_adults + $total_children)) + $vat_cost, '', '', $this->currencyFormat).'</b></td></tr>';
				$fisrt_part .= '<tr><td>'._PRE_PAYMENT.'</td><td> : </td> <td>'.Currencies::PriceFormat($cart_total, '', '', $this->currencyFormat).' ('.$prepayment_text.')</td></tr>';
			}else{
				$fisrt_part .= '<tr><td>'._PAYMENT_SUM.' </td><td> : </td><td> <b>'.Currencies::PriceFormat($order_price_after_discount + $extras_sub_total + $this->bookingInitialFee + ($this->guestTax * ($total_adults + $total_children)) + $vat_cost, '', '', $this->currencyFormat).'</b></td></tr>';
				///echo '<tr><td>'._PRE_PAYMENT.'</td><td> : </td> <td>'._FULL_PRICE.'</td></tr>';
			}
			if($additional_info != ''){
				$fisrt_part .= '<tr><td colspan="3" nowrap height="10px"></td></tr>';
				$fisrt_part .= '<tr><td colspan="3"><h4>'._ADDITIONAL_INFO.'</h4>'.$additional_info.'</td></tr>';							
			}
			
			$second_part = '</table><br />';
	
			if($payment_type == 'poa'){	
				
				$output .= $fisrt_part;
				$output .= PaymentIPN::DrawPaymentForm('poa', $pp_params, (($mode == 'TEST MODE') ? 'test' : 'real'), false);
				$output .= $second_part;
				
			}elseif($payment_type == 'online'){
	
				$output .= $fisrt_part;
					$pp_params['credit_card_required'] = $credit_card_required;
					$pp_params['cc_type']             = $cc_type;
					$pp_params['cc_holder_name']      = $cc_holder_name;
					$pp_params['cc_number']           = $cc_number;
					$pp_params['cc_cvv_code']         = $cc_cvv_code;
					$pp_params['cc_expires_month']    = $cc_expires_month;
					$pp_params['cc_expires_year']     = $cc_expires_year;
				$output .= PaymentIPN::DrawPaymentForm('online', $pp_params, (($mode == 'TEST MODE') ? 'test' : 'real'), false);
				$output .= $second_part;			
		
			}elseif($payment_type == 'paypal'){							
			
				$output .= $fisrt_part;
					$pp_params['api_login']                = $paypal_email;
					$pp_params['notify']        		   = 'index.php?page=booking_notify_paypal';
					$pp_params['paypal_form_type']   	   = $this->paypal_form_type;
					$pp_params['paypal_form_fields'] 	   = $this->paypal_form_fields;
					$pp_params['paypal_form_fields_count'] = $this->paypal_form_fields_count;						
				$output .= PaymentIPN::DrawPaymentForm('paypal', $pp_params, (($mode == 'TEST MODE') ? 'test' : 'real'), false);
				$output .= $second_part;		
			
			}elseif($payment_type == '2co'){				
	
				$output .= $fisrt_part;
					$pp_params['api_login'] = $two_checkout_vendor;			
					$pp_params['notify']    = 'index.php?page=booking_notify_2co';
				$output .= PaymentIPN::DrawPaymentForm('2co', $pp_params, (($mode == 'TEST MODE') ? 'test' : 'real'), false);
				$output .= $second_part;
	
			}elseif($payment_type == 'authorize.net'){
	
				$output .= $fisrt_part;
					$pp_params['api_login'] 	  = $authorize_login_id;
					$pp_params['transaction_key'] = $authorize_transaction_key;
					$pp_params['notify']    	  = 'index.php?page=booking_notify_autorize_net';
					// authorize.net accepts only USD, so we need to convert the sum into USD
					$pp_params['cart_total']      = number_format((($pp_params['cart_total'] * Application::Get('currency_rate'))), '2', '.', ',');												
				$output .= PaymentIPN::DrawPaymentForm('authorize.net', $pp_params, (($mode == 'TEST MODE') ? 'test' : 'real'), false);
				$output .= $second_part;
	
			}elseif($payment_type == 'bank.transfer'){
				
				$output .= $fisrt_part;
				$output .= PaymentIPN::DrawPaymentForm('bank.transfer', $pp_params, (($mode == 'TEST MODE') ? 'test' : 'real'), false);
				$output .= $second_part;							

			}elseif($payment_type == 'account.balance'){

				$output .= $fisrt_part;
				$output .= PaymentIPN::DrawPaymentForm('account.balance', $pp_params, (($mode == 'TEST MODE') ? 'test' : 'real'), false);
				$output .= $second_part;
			
			}			
		}else{
			///echo $sql.database_error();
			$output .= draw_important_message(_ORDER_ERROR, false);
		}
		
		if($draw) echo $output;
		else return $output;
	}

	/**
	 * Place booking
	 * @param $additional_info
	 * @param $cc_params
	 * @param $payment_type
	 */
	public function PlaceBooking($additional_info = '', $cc_params = array(), $payment_type = '')
	{
		global $objLogin;
		$additional_info = substr_by_word($additional_info, 1024);
		$is_agency = $objLogin->GetCustomerType() == 1 ? true : false;
		
        if(SITE_MODE == 'demo'){
           $this->message = draw_important_message(_OPERATION_BLOCKED, false);
		   return false;
        }
		
		// check if prepared booking exists
		$sql = 'SELECT id, booking_number, payment_type, payment_sum, currency
				FROM '.TABLE_BOOKINGS.'
				WHERE customer_id = '.(int)$this->currentCustomerID.' AND
					  is_admin_reservation = '.(($this->selectedUser == 'admin') ? '1' : '0').' AND
					  status = 0
				ORDER BY id DESC';

		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
        $payment_type_in_database = $result[0]['payment_type'];
		if($result[1] > 0){
			$booking_number = $result[0]['booking_number'];
			if($this->selectedUser == 'admin' || $objLogin->IsLoggedInAsAdmin()){
				// For admin always make status: 2 - reserved
				$status = 2;
			}elseif($payment_type_in_database == self::PAYMENT_ACCOUNT_BALANCE){
				// For balance payments: 3 - complete
				$status = 3;
			}elseif($payment_type_in_database == self::PAYMENT_POA || $payment_type_in_database == self::PAYMENT_BANK_TRANSFER){
				// For pay on arrival or bank transfer: 1- pending
				$status = 1;
			}else{
				// Other: 2 - reserved
				$status = 2;	
			}			
			$currency_info = Currencies::GetCurrencyInfo($result[0]['currency']);
			$payment_sum = $result[0]['payment_sum'] / $currency_info['rate'];
			
			if($status == 2 || $status == 3){
				$this->message = Bookings::UpdateRoomsAvailability($result[0]['booking_number'], 'decrease', 'Reserved or Completed');
				if($this->message != ''){
					return false;
				}
			}
			
			$sql = 'UPDATE '.TABLE_BOOKINGS.'
					SET
						status_changed = \''.date('Y-m-d H:i:s').'\',
						additional_info = \''.$additional_info.'\',
						cc_type = \''.$cc_params['cc_type'].'\',
						cc_holder_name = \''.$cc_params['cc_holder_name'].'\',
						cc_number = AES_ENCRYPT(\''.$cc_params['cc_number'].'\', \''.PASSWORDS_ENCRYPT_KEY.'\'),
						cc_expires_month = \''.$cc_params['cc_expires_month'].'\',
						cc_expires_year = \''.$cc_params['cc_expires_year'].'\',
						cc_cvv_code = AES_ENCRYPT(\''.$cc_params['cc_cvv_code'].'\', \''.PASSWORDS_ENCRYPT_KEY.'\'),
						'.($payment_type_in_database == self::PAYMENT_ACCOUNT_BALANCE ? 'payment_date = \''.date('Y-m-d H:i:s').'\',' : '').'
						status = \''.$status.'\'
					WHERE booking_number = \''.$booking_number.'\'';
			database_void_query($sql);

			// update customer bookings/rooms amount
			$sql = 'UPDATE '.TABLE_CUSTOMERS.' SET 
						orders_count = orders_count + 1,
						'.(($payment_type == 'account.balance' && ($is_agency || $objLogin->IsLoggedInAsAdmin())) ? 'balance = balance - '.$payment_sum.', ' : '').'
						rooms_count = rooms_count + '.$this->roomsCount.'
					WHERE id = '.(int)$this->currentCustomerID;
			database_void_query($sql);
			if(!$objLogin->IsLoggedIn()){
				// clear selected user ID for non-registered visitors
				Session::Set('sel_current_customer_id', '');
			}

			$this->message = draw_success_message(str_replace('_BOOKING_NUMBER_', '<b>'.$booking_number.'</b>', _ORDER_PLACED_MSG), false);
			if($this->SendOrderEmail($booking_number, 'placed', $this->currentCustomerID)){
				$this->message .= draw_success_message(_EMAIL_SUCCESSFULLY_SENT, false);
			}else{
				if($objLogin->IsLoggedInAsAdmin()){
					$this->message .= draw_important_message(_EMAIL_SEND_ERROR, false);					
				}
			}
		}else{
			$this->message = draw_important_message(_EMAIL_SEND_ERROR, false);					
		}
		
		if(SITE_MODE == 'development' && database_error() != '') $this->message .= '<br>'.$sql.'<br>'.database_error();		
		
		$this->EmptyCart();		
	}	

	/**
	 * Makes reservation
	 * @param $payment_type
	 * @param $additional_info
	 * @param $extras
	 * @param $pre_payment_type
	 * @param $pre_payment_value
	 */
	public function DoReservation($payment_type = '', $additional_info = '', $extras = array(), $pre_payment_type = '', $pre_payment_value = '0')
	{
		global $objLogin;
		
        if(SITE_MODE == 'demo'){
           $this->error = draw_important_message(_OPERATION_BLOCKED, false);
		   return false;
        }
		
		// check the maximum allowed room reservation per customer
		if($this->selectedUser == 'customer'){
			$sql = 'SELECT COUNT(*) as cnt FROM '.TABLE_BOOKINGS.' WHERE customer_id = '.(int)$this->currentCustomerID.' AND status < 3';
			$result = database_query($sql, DATA_ONLY);
			$cnt = isset($result[0]['cnt']) ? (int)$result[0]['cnt'] : 0;
			if($cnt >= $this->maximumAllowedReservations){
				$this->error = draw_important_message(_MAX_RESERVATIONS_ERROR, false);
				return false;
			}		
		}

		$is_agency = $objLogin->GetCustomerType() == 1 ? true : false;
		$booking_placed = false;
		$booking_number = '';
		$additional_info = substr_by_word($additional_info, 1024);

		$order_price = $this->cartTotalSum;

		// Calculate extras
		$extras_sub_total = '0';
		$extras_info = array();		
		foreach($extras as $key => $val){
			$extr = Extras::GetExtrasInfo($key);
			$extras_sub_total += ($extr['price'] * $this->currencyRate) * $val;
			$extras_info[$key] = $val;
		}
		///$order_price += $extras_sub_total;

		// Calculate discount			
		$arr_discounts = $this->GetGuestsAndNightsDiscount();
		$guests_discount = $arr_discounts['discount_guests'];
		$nights_discount = $arr_discounts['discount_nights'];
		$order_price_after_discount_g_n = $order_price - ($guests_discount + $nights_discount);
		$discount_fee = ($this->discountType == '1' ? ($order_price_after_discount_g_n * ($this->discountValue / 100)) : $this->discountValue);
		$order_price_after_discount = $order_price_after_discount_g_n - $discount_fee;
		
		//dbug($discount_fee);
		//dbug($this->discountType);
		//dbug($this->discountValue,1);

		// Calculate total number of adults 
		$total_adults = $this->GetAdultsInCart();

		// Calculate total number of children
		$total_children = $this->GetChildrenInCart();

		// Calculate VAT			 
		$cart_total_wo_vat = round($order_price_after_discount + $extras_sub_total, 2);
		$vat_cost = (($cart_total_wo_vat + $this->bookingInitialFee + ($this->guestTax * ($total_adults + $total_children))) * ($this->vatPercent / 100));
		$cart_total = round($cart_total_wo_vat, 2) + $this->bookingInitialFee + ($this->guestTax * ($total_adults + $total_children)) + $vat_cost;

		if($pre_payment_type == 'first night'){			
			$cart_total = ($this->firstNightSum * (1 + $this->vatPercent / 100));
		}elseif(($pre_payment_type == 'percentage') && (int)$pre_payment_value > 0 && (int)$pre_payment_value < 100){
			$cart_total = ($cart_total * ($pre_payment_value / 100));
		}elseif(($pre_payment_type == 'fixed sum') && (int)$pre_payment_value > 0){
			$cart_total = round($pre_payment_value * $this->currencyRate, 2);
		}else{			
			// $cart_total
		}		

		// Determine the date of cancellation
		$cancel_payment_date = 0;
		foreach($this->arrReservation as $key => $val){					
			// 60 (sec) * 60 (min) * 24 (hours)
			$current_cancel_time = strtotime($val['from_date']) - 60 * 60 * 24 * $val['cancel_reservation_day'];
			if(strtotime($cancel_payment_date) > $current_cancel_time || $cancel_payment_date == 0){
				$cancel_payment_date = date('Y-m-d', $current_cancel_time);
			}
		}

		if($this->cartItems > 0){
            // add order to database
			if(in_array($payment_type, array('poa', 'online', 'paypal', '2co', 'authorize.net', 'bank.transfer', 'account.balance', 'quick_reservation'))){
				if(($is_agency || $objLogin->IsLoggedInAsAdmin()) && $payment_type == 'account.balance'){
					$payed_by = '6';
					$status = '0';
				}elseif($payment_type == 'bank.transfer'){
					$payed_by = '5';
					$status = '0';
				}elseif($payment_type == 'authorize.net'){
					$payed_by = '4';
					$status = '0';
				}elseif($payment_type == '2co'){
					$payed_by = '3';
					$status = '0';
				}elseif($payment_type == 'paypal'){
					$payed_by = '2';
					$status = '0';
				}elseif($payment_type == 'online'){
					$payed_by = '1';
					$status = '0';
				}elseif($payment_type == 'quick_reservation'){
					$payed_by = '0';
					$status = '3';
				}else{
					$payed_by = '0';
					$status = '0';
				}
				
				// Reset Affiliate ID (if necessary)
				if(Modules::IsModuleInstalled('affiliates') && !empty($this->affiliate_id)){
					if(ModulesSettings::Get('affiliates', 'number_orders') == 'first'){
						$sql='SELECT id, booking_number, affiliate_id
							  FROM '.TABLE_BOOKINGS.'
							  WHERE customer_id = '.(int)$this->currentCustomerID.' AND
									affiliate_id = \''.$this->affiliate_id.'\' AND
									DATEDIFF(NOW(), created_date) <= 30 AND
									status != 0
							  ORDER BY id DESC';
						$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
						if($result[1] > 0){
							setcookie('affiliate_id', '', time() - 3600);
							$this->affiliate_id = '';
						}
					}
				}
				// check if prepared booking exists and replace it
				$sql = 'SELECT id, booking_number
						FROM '.TABLE_BOOKINGS.'
						WHERE customer_id = '.(int)$this->currentCustomerID.' AND
							  is_admin_reservation = '.(($this->selectedUser == 'admin') ? '1' : '0').' AND
							  status = 0
						ORDER BY id DESC';
				$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($result[1] > 0){
					$booking_number = $result[0]['booking_number'];
					// booking exists - replace it with new					
					$sql = 'DELETE FROM '.TABLE_BOOKINGS_ROOMS.' WHERE booking_number = \''.$booking_number.'\'';		
					if(!database_void_query($sql)){ /* echo 'error!'; */ }
					
					$sql = 'UPDATE '.TABLE_BOOKINGS.' SET ';
					$sql_end = ' WHERE booking_number = \''.$booking_number.'\'';
					$is_new_record = false;
				}else{
					$sql = 'INSERT INTO '.TABLE_BOOKINGS.' SET booking_number = \'\',';
					$sql_end = '';
					$is_new_record = true;
				}

				$sql .= 'booking_description = \''._ROOMS_RESERVATION.'\',
						order_price = '.number_format((float)$order_price, (int)Application::Get('currency_decimals'), '.', '').',
						pre_payment_type = \''.$pre_payment_type.'\',
						pre_payment_value = \''.(($pre_payment_type != 'full price') ? $pre_payment_value : '0').'\',
						discount_campaign_id = '.(int)$this->discountCampaignID.',
						discount_type = '.$this->discountType.',
						discount_value = '.$this->discountValue.',
						discount_fee = '.$discount_fee.',
						guests_discount = '.$guests_discount.',
						nights_discount = '.$nights_discount.',
						vat_fee = '.$vat_cost.',
						vat_percent = '.$this->vatPercent.',
						initial_fee = '.$this->bookingInitialFee.',
						guest_tax = '.($this->guestTax * ($total_adults + $total_children)).',
						extras = \''.serialize($extras_info).'\',
						extras_fee = \''.$extras_sub_total.'\',
						payment_sum = '.number_format((float)$cart_total, (int)Application::Get('currency_decimals'), '.', '').',
						additional_payment = 0,						
						currency = \''.$this->currencyCode.'\',
						rooms_amount = '.(int)$this->roomsCount.',						
						customer_id = '.(int)$this->currentCustomerID.',
						is_admin_reservation = '.(($this->selectedUser == 'admin') ? '1' : '0').',
						transaction_number = \'\',
						created_date = \''.date('Y-m-d H:i:s').'\',
						cancel_payment_date = \''.$cancel_payment_date.'\',
						payment_type = '.$payed_by.',
						payment_method = 0,
						coupon_code = \''.$this->discountCoupon.'\',						
						additional_info = \''.$additional_info.'\',
						cc_type = \'\',
						cc_holder_name = \'\', 
						cc_number = \'\', 
						cc_expires_month = \'\', 
						cc_expires_year = \'\', 
						cc_cvv_code = \'\',
						status = '.(int)$status.',
						status_description = \'\',
						affiliate_id = \''.$this->affiliate_id.'\'';
				$sql .= $sql_end;

                // handle booking details
				if(database_void_query($sql)){					
					if($is_new_record){
						$insert_id = database_insert_id();
						$booking_number = $this->GenerateBookingNumber($insert_id); 
						$sql = 'UPDATE '.TABLE_BOOKINGS.' SET booking_number = \''.$booking_number.'\' WHERE id = '.(int)$insert_id;
						if(!database_void_query($sql)){
							$this->error = draw_important_message(_ORDER_ERROR, false);
						}
					}

					$sql = 'INSERT INTO '.TABLE_BOOKINGS_ROOMS.'
								(id, booking_number, hotel_id, room_id, room_numbers, checkin, checkout, adults, children, rooms, price, discount, extra_beds, extra_beds_charge, meal_plan_id, meal_plan_price)
							VALUES ';
					$items_count = 0;
                    foreach($this->arrReservation as $key => $val){
                        $discount = isset($val['discount_nights']) ? $val['discount_nights'] : 0;
                        $discount += isset($val['discount_guests']) ? $val['discount_guests'] : 0;
						$sql .= ($items_count++ > 0) ? ',' : '';
						$sql .= '(NULL, \''.$booking_number.'\', '.(int)$val['hotel_id'].', '.(int)$key.', \'\', \''.$val['from_date'].'\', \''.$val['to_date'].'\', \''.$val['adults'].'\', \''.$val['children'].'\', '.(int)$val['rooms'].', '.($val['price'] * $this->currencyRate).', '.($discount * $this->currencyRate).', '.(int)$val['extra_beds'].', '.($val['extra_beds_charge'] * $this->currencyRate).', '.(int)$val['meal_plan_id'].', '.($val['meal_plan_price'] * $this->currencyRate).')'; 
					}
					if(database_void_query($sql)){
						$booking_placed = true;						
                        if($status == 2 || $status == 3){
                            $this->error = Bookings::UpdateRoomsAvailability($booking_number, 'decrease', 'Reserved or Completed');
                        }
					}else{
						$this->error = draw_important_message(_ORDER_ERROR, false);
					}
				}else{
					$this->error = draw_important_message(_ORDER_ERROR, false);
				}
			}else{
				$this->error = draw_important_message(_ORDER_ERROR, false);
			}
		}else{
			$this->error = draw_message(_RESERVATION_CART_IS_EMPTY_ALERT, false, true);
		}
		
		if(SITE_MODE == 'development' && !empty($this->error)) $this->error .= '<br>'.$sql.'<br>'.database_error();		
		
		return $booking_placed;		
	}	

	/**
	 * Sends booking email
	 * @param booking_number
	 * @param $order_type
	 * @param $customer_id
	 */
	public function SendOrderEmail($booking_number, $order_type = 'placed', $customer_id = '', $additional_text = '')
	{		
		global $objSettings;
		
		$lang = Application::Get('lang');
		$return = true;
		$allow_children = ModulesSettings::Get('rooms', 'allow_children');
		$allow_extra_beds = ModulesSettings::Get('rooms', 'allow_extra_beds');
		$hotels_count = Hotels::HotelsCount();
		$meal_plans_count = MealPlans::MealPlansCount();
		$default_lang = Languages::GetDefaultLang();
		$old_lang = Application::Get('lang');
		$arr_lang = array($default_lang);

		// send email to customer
		$sql = 'SELECT
			'.TABLE_BOOKINGS.'.id,
			'.TABLE_BOOKINGS.'.booking_number,
			'.TABLE_BOOKINGS.'.booking_description,
			'.TABLE_BOOKINGS.'.order_price,
			'.TABLE_BOOKINGS.'.discount_fee,
			'.TABLE_BOOKINGS.'.discount_type, 
			'.TABLE_BOOKINGS.'.discount_value, 
			'.TABLE_BOOKINGS.'.guests_discount, 
			'.TABLE_BOOKINGS.'.nights_discount, 
			'.TABLE_BOOKINGS.'.coupon_code,
			'.TABLE_BOOKINGS.'.vat_fee,
			'.TABLE_BOOKINGS.'.vat_percent,			
			'.TABLE_BOOKINGS.'.initial_fee,
			'.TABLE_BOOKINGS.'.guest_tax,
			'.TABLE_BOOKINGS.'.extras,
			'.TABLE_BOOKINGS.'.extras_fee,
			'.TABLE_BOOKINGS.'.payment_sum,
			'.TABLE_BOOKINGS.'.currency,
			'.TABLE_BOOKINGS.'.rooms_amount,
			'.TABLE_BOOKINGS.'.customer_id,
			'.TABLE_BOOKINGS.'.transaction_number,
			'.TABLE_BOOKINGS.'.created_date,
			'.TABLE_BOOKINGS.'.payment_date,
			'.TABLE_BOOKINGS.'.payment_type,
			'.TABLE_BOOKINGS.'.payment_method,
			DATE_FORMAT('.TABLE_BOOKINGS.'.cancel_payment_date, \''.(($this->fieldDateFormat == 'M d, Y') ? '%b %d, %Y %h:%i %p' : '%d %b %Y %h:%i %p').'\') as cancel_payment_date_formated,
			'.TABLE_BOOKINGS.'.status,
			'.TABLE_BOOKINGS.'.status_description,  
			'.TABLE_BOOKINGS.'.email_sent,
			'.TABLE_BOOKINGS.'.additional_info,
			'.TABLE_BOOKINGS.'.is_admin_reservation,
			CASE
				WHEN '.TABLE_BOOKINGS.'.payment_method = 0 THEN \''.encode_text(_PAYMENT_COMPANY_ACCOUNT).'\'
				WHEN '.TABLE_BOOKINGS.'.payment_method = 1 THEN \''.encode_text(_CREDIT_CARD).'\'
				WHEN '.TABLE_BOOKINGS.'.payment_method = 2 THEN \''.encode_text(_ECHECK).'\'
				ELSE \''.encode_text(_UNKNOWN).'\'
			END as mod_payment_method,						
			IF((('.TABLE_BOOKINGS.'.order_price - '.TABLE_BOOKINGS.'.discount_fee) + '.TABLE_BOOKINGS.'.initial_fee + '.TABLE_BOOKINGS.'.extras_fee + '.TABLE_BOOKINGS.'.vat_fee - ('.TABLE_BOOKINGS.'.payment_sum + '.TABLE_BOOKINGS.'.additional_payment) > 0),
			   (('.TABLE_BOOKINGS.'.order_price - '.TABLE_BOOKINGS.'.discount_fee) + '.TABLE_BOOKINGS.'.initial_fee + '.TABLE_BOOKINGS.'.extras_fee + '.TABLE_BOOKINGS.'.vat_fee - ('.TABLE_BOOKINGS.'.payment_sum + '.TABLE_BOOKINGS.'.additional_payment)),
			   0
			) as mod_have_to_pay,								
			'.TABLE_CUSTOMERS.'.first_name,
			'.TABLE_CUSTOMERS.'.last_name,
			'.TABLE_CUSTOMERS.'.user_name as customer_name,
			'.TABLE_CUSTOMERS.'.email,
			'.TABLE_CUSTOMERS.'.preferred_language,
			'.TABLE_CUSTOMERS.'.b_address,
			'.TABLE_CUSTOMERS.'.b_address_2,
			'.TABLE_CUSTOMERS.'.b_city,
			'.TABLE_CUSTOMERS.'.b_zipcode,
			'.TABLE_CUSTOMERS.'.phone,
			'.TABLE_CUSTOMERS.'.fax,
			'.TABLE_CURRENCIES.'.symbol,
			'.TABLE_CURRENCIES.'.symbol_placement,
			'.TABLE_CAMPAIGNS.'.campaign_name,
			'.TABLE_COUNTRIES.'.abbrv as b_country,
			IF('.TABLE_STATES.'.name IS NOT NULL, '.TABLE_STATES.'.name, '.TABLE_CUSTOMERS.'.b_state) as b_state
		FROM '.TABLE_BOOKINGS.'
			INNER JOIN '.TABLE_CURRENCIES.' ON '.TABLE_BOOKINGS.'.currency = '.TABLE_CURRENCIES.'.code
			LEFT OUTER JOIN '.TABLE_CAMPAIGNS.' ON '.TABLE_BOOKINGS.'.discount_campaign_id = '.TABLE_CAMPAIGNS.'.id
			LEFT OUTER JOIN '.TABLE_CUSTOMERS.' ON '.TABLE_BOOKINGS.'.customer_id = '.TABLE_CUSTOMERS.'.id
			LEFT OUTER JOIN '.TABLE_COUNTRIES.' ON '.TABLE_CUSTOMERS.'.b_country = '.TABLE_COUNTRIES.'.abbrv AND '.TABLE_COUNTRIES.'.is_active = 1
			LEFT OUTER JOIN '.TABLE_STATES.' ON '.TABLE_CUSTOMERS.'.b_state = '.TABLE_STATES.'.abbrv AND '.TABLE_STATES.'.country_id = '.TABLE_COUNTRIES.'.id AND '.TABLE_STATES.'.is_active = 1			
		WHERE
			'.TABLE_BOOKINGS.'.customer_id = '.$customer_id.' AND
			'.TABLE_BOOKINGS.'.booking_number = \''.$booking_number.'\'';
		
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$recipient = $result[0]['email'];
			$first_name = $result[0]['first_name'];
			$last_name = $result[0]['last_name'];
			$email_sent = $result[0]['email_sent'];
			$status = $result[0]['status'];
			$status_description['default'] = $result[0]['status_description'];
			$preferred_language = $result[0]['preferred_language'];
			$is_admin_reservation = $result[0]['is_admin_reservation'];
			$payment_type = (int)$result[0]['payment_type'];
			$cancel_payment_date_formated = $result[0]['cancel_payment_date_formated'];

			$_t = array($default_lang => array());
			if($preferred_language != $default_lang){
				$arr_lang[] = $preferred_language;
				$_t[$preferred_language] = array();
			}
			$arr_trans_val = array(
				'_PERSONAL_INFORMATION','_FIRST_NAME','_LAST_NAME','_EMAIL_ADDRESS',
				'_BILLING_DETAILS','_ADDRESS','_CITY','_STATE','_COUNTRY','_ZIP_CODE','_PHONE','_FAX',
				'_BOOKING_DESCRIPTION','_CREATED_DATE','_NOT_PAID_YET','_PAYMENT_DATE','_PAYMENT_TYPE','_PAYMENT_METHOD','_CURRENCY','_ROOMS','_BOOKING_PRICE',
				'_DISCOUNT','_COUPON_DISCOUNT','_GUESTS_DISCOUNT','_LONG_TERM_STAY_DISCOUNT','_ROOMS_DISCOUNT','_BOOKING_SUBTOTAL','_AFTER_DISCOUNT','_EXTRAS_SUBTOTAL',
				'_INITIAL_FEE','_GUEST_TAX','_VAT','_PAYMENT_SUM','_PAYMENT_REQUIRED','_ADDITIONAL_INFO','_RESERVATION_DETAILS',
				'_ROOM_TYPE','_CHECK_IN','_CHECK_OUT','_CHILD','_NIGHTS','_ADULT','_EXTRA_BEDS','_MEAL_PLANS','_PER_NIGHT','_PRICE','_BANK_PAYMENT_INFO',
				'_CANCELLATION_POLICY','_FREE_OF_CHARGE','_AFTER','_UNKNOWN','_COUPON_CODE','_STATUS','_PAY_ON_ARRIVAL','_ONLINE_ORDER','_PAYPAL',
				'_BANK_TRANSFER','_ACCOUNT_BALANCE','_PREBOOKING','_PREBOOKING','_PENDING','_RESERVED','_COMPLETED','_REFUNDED','_PAYMENT_REQUIRED',
				'_CANCELED','_PAYMENT_ERROR','_HOTEL', '_FLAT',
			);

			$sql = 'SELECT language_id, key_value, key_text FROM '.TABLE_VOCABULARY.' WHERE language_id'.($preferred_language != $default_lang ? ' IN (\''.$preferred_language.'\',\''.$default_lang.'\')' : ' = \''.$default_lang.'\'').' AND key_value IN (\''.implode('\',\'', $arr_trans_val).'\')';
			$translations = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
			if($translations[1] > 0){
				foreach($translations[0] as $trans_info){
					$_t[$trans_info['language_id']][$trans_info['key_value']] = $trans_info['key_text'];
				}
			}else{
				$arr_lang = array();
			}
			foreach($arr_trans_val as $val){
				$arr_transmissions['default'][$val] = constant($val);
			}
				
			for($i = 0, $max = count($arr_lang) > 0 ? count($arr_lang) : 1; $i < $max; $i++){
				$this_lang = isset($arr_lang[$i]) ? $arr_lang[$i] : $old_lang;
				$arr_payment_types = array(
					'0'=>$_t[$this_lang]['_PAY_ON_ARRIVAL'],
					'1'=>$_t[$this_lang]['_ONLINE_ORDER'],
					'2'=>$_t[$this_lang]['_PAYPAL'],
					'3'=>'2CO',
					'4'=>'Authorize.Net',
					'5'=>$_t[$this_lang]['_BANK_TRANSFER'],
					'6'=>$_t[$this_lang]['_ACCOUNT_BALANCE']
				);

				$arr_statuses = array(
					'0'=>$_t[$this_lang]['_PREBOOKING'],
					'1'=>$_t[$this_lang]['_PENDING'],
					'2'=>$_t[$this_lang]['_RESERVED'],
					'3'=>$_t[$this_lang]['_COMPLETED'],
					'4'=>$_t[$this_lang]['_REFUNDED'],
					'5'=>$_t[$this_lang]['_PAYMENT_ERROR'],
					'6'=>$_t[$this_lang]['_CANCELED'],
					'-1'=>$_t[$this_lang]['_UNKNOWN']
				);
			}

			$personal_information = array();
			$billing_information = array();
			$booking_details = array();
			$refund_information = array();
			$status_description = array();
			$hotel_description = array();
			for($j = 0, $max = count($arr_lang) > 0 ? count($arr_lang) : 1; $j < $max; $j++){
				$this_lang = isset($arr_lang[$j]) ? $arr_lang[$j] : $old_lang;
				Application::Set('lang', $this_lang);

				$personal_information[$this_lang] = '';
				$billing_information[$this_lang] = '';
				$booking_details[$this_lang] = '';
				$refund_information[$this_lang] = '';
				$status_description[$this_lang] = '';
				$hotel_description[$this_lang] = '';

				if(ModulesSettings::Get('booking', 'mode') == 'TEST MODE'){
					$personal_information[$this_lang] .= '<div style="text-align:center;padding:10px;color:#a60000;border:1px dashed #a60000;width:100px">TEST MODE!</div><br />';	
				}
				$personal_information[$this_lang] .= '<b>'.$_t[$this_lang]['_PERSONAL_INFORMATION'].':</b>';
				$personal_information[$this_lang] .= '<br />-----------------------------<br />';
				$personal_information[$this_lang] .= $_t[$this_lang]['_FIRST_NAME'].' : '.$result[0]['first_name'].'<br />';
				$personal_information[$this_lang] .= $_t[$this_lang]['_LAST_NAME'].' : '.$result[0]['last_name'].'<br />';
				$personal_information[$this_lang] .= $_t[$this_lang]['_EMAIL_ADDRESS'].' : '.$result[0]['email'].'<br />';
			
				$billing_information[$this_lang]  = '<b>'.$_t[$this_lang]['_BILLING_DETAILS'].':</b>';
				$billing_information[$this_lang] .= '<br />-----------------------------<br />';
				$billing_information[$this_lang] .= $_t[$this_lang]['_ADDRESS'].' : '.$result[0]['b_address'].' '.$result[0]['b_address_2'].'<br />';
				$billing_information[$this_lang] .= $_t[$this_lang]['_CITY'].' : '.$result[0]['b_city'].'<br />';
				$billing_information[$this_lang] .= $_t[$this_lang]['_STATE'].' : '.$result[0]['b_state'].'<br />';
				$billing_information[$this_lang] .= $_t[$this_lang]['_COUNTRY'].' : '.$result[0]['b_country'].'<br />';
				$billing_information[$this_lang] .= $_t[$this_lang]['_ZIP_CODE'].' : '.$result[0]['b_zipcode'].'<br />';
				if(!empty($result[0]['phone'])) $billing_information[$this_lang] .= $_t[$this_lang]['_PHONE'].' : '.$result[0]['phone'].'<br />';
				if(!empty($result[0]['fax'])) $billing_information[$this_lang] .= $_t[$this_lang]['_FAX'].' : '.$result[0]['fax'].'<br />';
	
				$booking_details[$this_lang]  = $_t[$this_lang]['_BOOKING_DESCRIPTION'].': '.$result[0]['booking_description'].'<br />';
				$booking_details[$this_lang] .= $_t[$this_lang]['_CREATED_DATE'].': '.format_datetime($result[0]['created_date'], $this->fieldDateFormat.' H:i:s', '', true).'<br />';
				$payment_date = format_datetime($result[0]['payment_date'], $this->fieldDateFormat.' H:i:s', '', true);
				if(empty($payment_date)) $payment_date = $_t[$this_lang]['_NOT_PAID_YET'];
				$booking_details[$this_lang] .= $_t[$this_lang]['_PAYMENT_DATE'].': '.$payment_date.'<br />';
				$booking_details[$this_lang] .= $_t[$this_lang]['_PAYMENT_TYPE'].': '.((isset($arr_payment_types[$payment_type])) ? $arr_payment_types[$payment_type] : '').'<br />';
				$booking_details[$this_lang] .= $_t[$this_lang]['_PAYMENT_METHOD'].': '.$result[0]['mod_payment_method'].'<br />';
				$booking_details[$this_lang] .= $_t[$this_lang]['_CURRENCY'].': '.$result[0]['currency'].'<br />';
				$booking_details[$this_lang] .= $_t[$this_lang]['_ROOMS'].': '.$result[0]['rooms_amount'].'<br />';
				$booking_details[$this_lang] .= $_t[$this_lang]['_BOOKING_PRICE'].': '.Currencies::PriceFormat($result[0]['order_price'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'<br />';
				$booking_details[$this_lang] .= (($result[0]['campaign_name'] != '') ? $_t[$this_lang]['_DISCOUNT'].': - '.Currencies::PriceFormat($result[0]['discount_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).' ('.($result[0]['discount_type'] == '1' ? Currencies::PriceFormat($result[0]['discount_value'], '%', 'after', $this->currencyFormat) : Currencies::PriceFormat($result[0]['discount_value'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat)).' - '.$result[0]['campaign_name'].')<br />' : '');
				$booking_details[$this_lang] .= (($result[0]['coupon_code'] != '') ? $_t[$this_lang]['_COUPON_DISCOUNT'].': - '.Currencies::PriceFormat($result[0]['discount_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).' ('.($result[0]['discount_type'] == '1' ? Currencies::PriceFormat($result[0]['discount_value'], '%', 'after', $this->currencyFormat) : Currencies::PriceFormat($result[0]['discount_value'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat)).' - '.$_t[$this_lang]['_COUPON_CODE'].': '.$result[0]['coupon_code'].')<br />' : '');
				$booking_details[$this_lang] .= (($result[0]['guests_discount'] > 0) ? (TYPE_DISCOUNT_GUEST == 'guests' ? $_t[$this_lang]['_GUESTS_DISCOUNT'] : $_t[$this_lang]['_ROOMS_DISCOUNT']).': - '.Currencies::PriceFormat($result[0]['guests_discount'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'<br />' : '');
				$booking_details[$this_lang] .= (($result[0]['nights_discount'] > 0) ? $_t[$this_lang]['_LONG_TERM_STAY_DISCOUNT'].': - '.Currencies::PriceFormat($result[0]['nights_discount'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'<br />' : '');
				$booking_details[$this_lang] .= $_t[$this_lang]['_BOOKING_SUBTOTAL'].(($result[0]['campaign_name'] != '') ? ' ('.$_t[$this_lang]['_AFTER_DISCOUNT'].')' : '').': '.Currencies::PriceFormat($result[0]['order_price'] - $result[0]['discount_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'<br />';

				if(!empty($result[0]['extras'])) $booking_details[$this_lang] .= $_t[$this_lang]['_EXTRAS_SUBTOTAL'].': '.Currencies::PriceFormat($result[0]['extras_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'<br />';

				if(!empty($this->bookingInitialFee)) $booking_details[$this_lang] .= $_t[$this_lang]['_INITIAL_FEE'].': '.Currencies::PriceFormat($result[0]['initial_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'<br />';
				if(!empty($this->guestTax)) $booking_details[$this_lang] .= $_t[$this_lang]['_GUEST_TAX'].': '.Currencies::PriceFormat($result[0]['guest_tax'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'<br />';
				if($this->vatIncludedInPrice == 'no'){
					$booking_details[$this_lang] .= $_t[$this_lang]['_VAT'].': '.Currencies::PriceFormat($result[0]['vat_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).' ('.Currencies::PriceFormat($result[0]['vat_percent'], '%', 'after', $this->currencyFormat, $this->GetVatPercentDecimalPoints($result[0]['vat_percent'])).')<br />';
				}
				$booking_details[$this_lang] .= $_t[$this_lang]['_PAYMENT_SUM'].': '.Currencies::PriceFormat($result[0]['payment_sum'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'<br />';
				$booking_details[$this_lang] .= $_t[$this_lang]['_PAYMENT_REQUIRED'].': '.Currencies::PriceFormat($result[0]['mod_have_to_pay'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'<br />';
				if($result[0]['additional_info'] != '') $booking_details[$this_lang] .= $_t[$this_lang]['_ADDITIONAL_INFO'].': '.nl2br($result[0]['additional_info']).'<br />';
				$booking_details[$this_lang] .= '<br />';

				// display list of extras in order
				// -----------------------------------------------------------------------------
				$booking_details[$this_lang] .= Extras::GetExtrasList(unserialize($result[0]['extras']), $result[0]['currency'], 'email');
				// display list of rooms in order
				// -----------------------------------------------------------------------------
				$booking_details[$this_lang] .= '<b>'.$_t[$this_lang]['_RESERVATION_DETAILS'].':</b>';
				$booking_details[$this_lang] .= '<br />-----------------------------<br />';

				$sql = 'SELECT
						'.TABLE_BOOKINGS_ROOMS.'.booking_number,
						'.TABLE_BOOKINGS_ROOMS.'.rooms,
						'.TABLE_BOOKINGS_ROOMS.'.adults,
						'.TABLE_BOOKINGS_ROOMS.'.children,
						'.TABLE_BOOKINGS_ROOMS.'.extra_beds,
						'.TABLE_BOOKINGS_ROOMS.'.checkin,
						'.TABLE_BOOKINGS_ROOMS.'.checkout,
						'.TABLE_BOOKINGS_ROOMS.'.price,
						'.TABLE_BOOKINGS_ROOMS.'.meal_plan_price,
						'.TABLE_BOOKINGS_ROOMS.'.extra_beds_charge,
						'.TABLE_BOOKINGS.'.currency,
						'.TABLE_CURRENCIES.'.symbol,
						'.TABLE_ROOMS_DESCRIPTION.'.room_type,
						'.TABLE_HOTELS.'.email as hotel_email,
						'.TABLE_HOTELS.'.id as hotel_id,
						'.TABLE_HOTELS_DESCRIPTION.'.name as hotel_name,
						'.TABLE_MEAL_PLANS_DESCRIPTION.'.name as meal_plan_name,
						'.TABLE_HOTELS_DESCRIPTION.'.name as hotel_name
					FROM '.TABLE_BOOKINGS.'
						INNER JOIN '.TABLE_BOOKINGS_ROOMS.' ON '.TABLE_BOOKINGS.'.booking_number = '.TABLE_BOOKINGS_ROOMS.'.booking_number
						INNER JOIN '.TABLE_ROOMS.' ON '.TABLE_BOOKINGS_ROOMS.'.room_id = '.TABLE_ROOMS.'.id
						INNER JOIN '.TABLE_ROOMS_DESCRIPTION.' ON '.TABLE_ROOMS.'.id = '.TABLE_ROOMS_DESCRIPTION.'.room_id AND '.TABLE_ROOMS_DESCRIPTION.'.language_id = \''.$this_lang.'\'
						LEFT OUTER JOIN '.TABLE_CURRENCIES.' ON '.TABLE_BOOKINGS.'.currency = '.TABLE_CURRENCIES.'.code
						LEFT OUTER JOIN '.TABLE_CUSTOMERS.' ON '.TABLE_BOOKINGS.'.customer_id = '.TABLE_CUSTOMERS.'.id
						LEFT OUTER JOIN '.TABLE_HOTELS.' ON '.TABLE_BOOKINGS_ROOMS.'.hotel_id = '.TABLE_HOTELS.'.id
						LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' ON '.TABLE_BOOKINGS_ROOMS.'.hotel_id = '.TABLE_HOTELS_DESCRIPTION.'.hotel_id AND '.TABLE_HOTELS_DESCRIPTION.'.language_id = \''.$this_lang.'\'
						LEFT OUTER JOIN '.TABLE_MEAL_PLANS_DESCRIPTION.' ON '.TABLE_BOOKINGS_ROOMS.'.meal_plan_id = '.TABLE_MEAL_PLANS_DESCRIPTION.'.meal_plan_id AND '.TABLE_MEAL_PLANS_DESCRIPTION.'.language_id = \''.$this_lang.'\'
					WHERE
						'.TABLE_BOOKINGS.'.booking_number = \''.$result[0]['booking_number'].'\' ';
	
				$b_result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
				///echo $sql.'----------'.database_error();
				$hotelowner_emails = array();
				$arr_count_rooms = array();

				if($b_result[1] > 0){

					$booking_details[$this_lang] .= '<table class=tbl-reservation-details>';
					$booking_details[$this_lang] .= '<tr align="center">';
					$booking_details[$this_lang] .= '<th>#</th>';
					$booking_details[$this_lang] .= '<th align="left">'.$_t[$this_lang][FLATS_INSTEAD_OF_HOTELS ? '_FLAT' : '_ROOM_TYPE'].'</th>';
					if($hotels_count > 1 && !FLATS_INSTEAD_OF_HOTELS) $booking_details[$this_lang] .= '<th align="left">'.$_t[$this_lang][FLATS_INSTEAD_OF_HOTELS ? '_FLAT' : '_HOTEL'].'</th>';
					$booking_details[$this_lang] .= '<th>'.$_t[$this_lang]['_CHECK_IN'].'</th>';
					$booking_details[$this_lang] .= '<th>'.$_t[$this_lang]['_CHECK_OUT'].'</th>';
					$booking_details[$this_lang] .= '<th>'.$_t[$this_lang]['_NIGHTS'].'</th>';
					$booking_details[$this_lang] .= FLATS_INSTEAD_OF_HOTELS ? '' : '<th>'.$_t[$this_lang]['_ROOMS'].'</th>';
					$booking_details[$this_lang] .= '<th>'.$_t[$this_lang]['_ADULT'].'</th>';
					$booking_details[$this_lang] .= (($allow_children == 'yes') ? '<th>'.$_t[$this_lang]['_CHILD'].'</th>' : '');
					$booking_details[$this_lang] .= (($allow_extra_beds == 'yes') ? '<th>'.$_t[$this_lang]['_EXTRA_BEDS'].'</th>' : '');
					$booking_details[$this_lang] .= (($meal_plans_count) ? '<th>'.$_t[$this_lang]['_MEAL_PLANS'].'</th>' : '');
					$booking_details[$this_lang] .= '<th align="right">'.$_t[$this_lang]['_PER_NIGHT'].'</th>';
					$booking_details[$this_lang] .= '<th align="right">'.$_t[$this_lang]['_PRICE'].'</th>';
					$booking_details[$this_lang] .= '</tr>';
                    $total_price = 0;
                    $colspan = FLATS_INSTEAD_OF_HOTELS ? 4 : 5;
                    if($hotels_count > 1 && !FLATS_INSTEAD_OF_HOTELS) $colspan++;
                    if($allow_children == 'yes') $colspan++;
                    if($allow_extra_beds == 'yes') $colspan++;
                    if($meal_plans_count) $colspan++;
					for($i=0; $i < $b_result[1]; $i++){
						isset($arr_count_rooms[$b_result[0][$i]['hotel_id']]) ? $arr_count_rooms[$b_result[0][$i]['hotel_id']]++ : $arr_count_rooms[$b_result[0][$i]['hotel_id']] = 1;
						$nights = nights_diff($b_result[0][$i]['checkin'], $b_result[0][$i]['checkout']);
						$booking_details[$this_lang] .= '<tr align="center">';
						$booking_details[$this_lang] .= '<td width="30px">'.($i+1).'.</td>';
						$booking_details[$this_lang] .= '<td align="left">'.$b_result[0][$i]['room_type'].'</td>';
						if($hotels_count > 1 && !FLATS_INSTEAD_OF_HOTELS) $booking_details[$this_lang] .= '<td align="left">'.$b_result[0][$i]['hotel_name'].'</td>';
						if(!empty($b_result[0][$i]['hotel_email'])) $hotelowner_emails[] = $b_result[0][$i]['hotel_email'];
						$booking_details[$this_lang] .= '<td>'.format_datetime($b_result[0][$i]['checkin'], $this->fieldDateFormat, '', true).'</td>';
						$booking_details[$this_lang] .= '<td>'.format_datetime($b_result[0][$i]['checkout'], $this->fieldDateFormat, '', true).'</td>';
						$booking_details[$this_lang] .= '<td>'.$nights.'</td>';
						$booking_details[$this_lang] .= FLATS_INSTEAD_OF_HOTELS ? '' : ('<td>'.$b_result[0][$i]['rooms'].'</td>');
						$booking_details[$this_lang] .= '<td>'.$b_result[0][$i]['adults'].'</td>';
						$booking_details[$this_lang] .= (($allow_children == 'yes') ? '<td>'.$b_result[0][$i]['children'].'</td>' : '');
						$booking_details[$this_lang] .= (($allow_extra_beds == 'yes') ? (!empty($b_result[0][$i]['extra_beds']) ? '<td>'.$b_result[0][$i]['extra_beds'].' ('.Currencies::PriceFormat($b_result[0][$i]['extra_beds_charge'], $b_result[0][$i]['symbol'], '', $this->currencyFormat).')</td>' : '<td>0</td>') : '');
						$booking_details[$this_lang] .= (($meal_plans_count) ? ('<td>'.(!empty($b_result[0][$i]['meal_plan_name']) ? $b_result[0][$i]['meal_plan_name'].' (' : '')).Currencies::PriceFormat($b_result[0][$i]['meal_plan_price'], $b_result[0][$i]['symbol'], '', $this->currencyFormat).(!empty($b_result[0][$i]['meal_plan_name']) ? $b_result[0][$i]['meal_plan_name'].')' : '').'</td>' : '');
						$booking_details[$this_lang] .= '<td align="right">'.Currencies::PriceFormat(($b_result[0][$i]['price'] / $nights), $b_result[0][$i]['symbol'], '', $this->currencyFormat).'</td>';
						$booking_details[$this_lang] .= '<td align="right">'.Currencies::PriceFormat(($b_result[0][$i]['price'] + $b_result[0][$i]['meal_plan_price'] + $b_result[0][$i]['extra_beds_charge']), $b_result[0][$i]['symbol'], '', $this->currencyFormat).'</td>';
                        $booking_details[$this_lang] .= '</tr>';
                        $total_price += $b_result[0][$i]['price'] + $b_result[0][$i]['meal_plan_price'] + $b_result[0][$i]['extra_beds_charge'];
					}
                    $booking_details[$this_lang] .= '<tr><td colspan="'.($colspan+4).'" nowrap height="5px"></td></tr>';
                    $booking_details[$this_lang] .= '<tr class="tr-footer">';
                    $booking_details[$this_lang] .= '<td colspan="'.$colspan.'"></td>';
                    $booking_details[$this_lang] .= '<td class="td left" colspan="3"><b>'._TOTAL.':</b></td>';
                    $booking_details[$this_lang] .= '<td class="td right" align="right">';
                    $booking_details[$this_lang] .= '<b><label id="reservation_total">'.Currencies::PriceFormat($total_price, '', '', $this->currencyFormat).'</label></b>';
                    $booking_details[$this_lang] .= '</td>';
                    $booking_details[$this_lang] .= '</tr>';
					$booking_details[$this_lang] .= '</table>';
				}
				
				// add  info for bank transfer payments
				if($payment_type == 5){
					$booking_details[$this_lang] .= '<br />';
					$booking_details[$this_lang] .= '<b>'.$_t[$this_lang]['_BANK_PAYMENT_INFO'].':</b>';
					$booking_details[$this_lang] .= '<br />-----------------------------<br />';
					$booking_details[$this_lang] .= ModulesSettings::Get('booking', 'bank_transfer_info');
				}
			
				$cancellation_fee = Bookings::CalculateCancellationFee($booking_number);

				$refund_information[$this_lang] = '<br /><h4 style="color:#960000">'.$_t[$this_lang]['_CANCELLATION_POLICY'].':</h4>
					'._TILL.' '.$cancel_payment_date_formated.' - <strong>'.$_t[$this_lang]['_FREE_OF_CHARGE'].'</strong><br>
					'.(!empty($cancellation_fee) ? $_t[$this_lang]['_AFTER'].' '.$cancel_payment_date_formated.' - <strong>'.Currencies::PriceFormat($cancellation_fee).'</strong>' : '').'
				<br />';

				////////////////////////////////////////////////////////////
				$sender = $objSettings->GetParameter('admin_email');
				///$recipient = $result[0]['email'];

				if($order_type == 'reserved'){
					// exit if email already sent
					if($email_sent == '1') return true;
					$email_template = 'order_status_changed';
					$admin_copy_subject = 'Customer order status has been changed (admin copy)';
					$status_description[$this_lang] = isset($arr_statuses[$status]) ? '<b>'.$arr_statuses[$status].'</b>' : $_t[$this_lang]['_UNKNOWN'];
				}elseif($order_type == 'completed'){
					// exit if email already sent
					if($email_sent == '1') return true; 
					$email_template = 'order_paid';
					$admin_copy_subject = 'Customer order has been paid (admin copy)';
				}elseif($order_type == 'canceled'){
					$email_template = 'order_canceled';
					$admin_copy_subject = 'Customer has canceled order (admin copy)';
				}elseif($order_type == 'payment_error'){
					$email_template = 'payment_error';
					$admin_copy_subject = 'Customer order payment error (admin copy)';
				}elseif($order_type == 'refunded'){
					$email_template = 'order_refunded';
					$admin_copy_subject = 'Customer order has been refunded (admin copy)';
				}else{
					$email_template = 'order_placed_online';
					$order_type = isset($arr_payment_types[$payment_type]) ? $arr_payment_types[$payment_type] : '';
					$admin_copy_subject = 'Customer has placed "'.$order_type.'" order (admin copy)';
					if(isset($arr_statuses[$status])) $status_description[$this_lang] = $_t[$this_lang]['_STATUS'].': '.$arr_statuses[$status];
				}
			}
			Application::Set('lang', $old_lang);

			////////////////////////////////////////////////////////////
			for($i = 0, $max = count($arr_lang) > 0 ? count($arr_lang) : 1; $i < count($arr_lang); $i++){
				$this_lang = isset($arr_lang[$i]) ? $arr_lang[$i] : $old_lang;
				Application::Set('lang', $this_lang);

				$hotel_description[$this_lang] = '';
				if(Hotels::HotelsCount() == 1){
					$hotel_info = Hotels::GetHotelFullInfo(0, $preferred_language);
					$hotel_description[$this_lang] .= $hotel_info['name'].'<br>';
					$hotel_description[$this_lang] .= $hotel_info['address'].'<br>';
					$hotel_description[$this_lang] .= $_t[$this_lang]['_PHONE'].':'.$hotel_info['phone'];
					if($hotel_info['fax'] != '') $hotel_description[$this_lang] .= ', '.$_t[$this_lang]['_FAX'].':'.$hotel_info['fax'];
				}elseif(!empty($arr_count_rooms)){
					$arr_hotel_ids = array_keys($arr_count_rooms);
					$arr_hotel_info = Hotels::GetHotelsFullInfo($arr_hotel_ids, $preferred_language);
					foreach($arr_hotel_info as $hotel){
						$hotel_description[$this_lang] .= $hotel['name'].'<br/>';
						$hotel_description[$this_lang] .= $hotel['address'].'<br/>';
						$hotel_description[$this_lang] .= $_t[$this_lang]['_PHONE'].':'.$hotel['phone'];
						if($hotel['fax'] != '') $hotel_description[$this_lang] .= ', '.$_t[$this_lang]['_FAX'].':'.$hotel['fax'].'<br/>';
						$hotel_description[$this_lang] .= '<br/>';
					}
				}
			}
			Application::Set('lang', $old_lang);
			
			if(!$is_admin_reservation){
				$arr_send_email = array('customer');
			}
            if(ModulesSettings::Get('booking', 'send_order_copy_to_admin') == 'yes'){
                $arr_send_email[] = 'admin_copy';
            }
            if(ModulesSettings::Get('booking', 'send_order_copy_to_hotel_owner') == 'yes'){
                $arr_send_email[] = 'hotelowner_copy';
            }

			foreach($arr_send_email as $key){
				$copy_subject = '';
				if($key == 'admin_copy'){
					$email_language = $default_lang;
					$recipient = $sender;
					$copy_subject = $admin_copy_subject;
				}elseif($key == 'hotelowner_copy'){
					$email_language = $default_lang;
					$recipient = implode(',', array_unique($hotelowner_emails));
					$copy_subject = $admin_copy_subject;
				}else{
					$email_language = $preferred_language;
				}

                $objEmailTemplates = new EmailTemplates();
				$email_info = $objEmailTemplates->GetTemplate($email_template, $preferred_language);
				$replace_holders = array(
					'{FIRST NAME}' => $first_name,
					'{LAST NAME}'  => $last_name,
					'{BOOKING NUMBER}'  => $booking_number,
					'{BOOKING DETAILS}' => isset($booking_details[$preferred_language]) ? $booking_details[$preferred_language] : $booking_details[$old_lang],
					'{STATUS DESCRIPTION}' => isset($status_description[$preferred_language]) ? $status_description[$preferred_language] : $status_description[$old_lang],
					'{PERSONAL INFORMATION}' => isset($personal_information[$preferred_language]) ? $personal_information[$preferred_language] : $personal_information[$old_lang],
					'{BILLING INFORMATION}' => isset($billing_information[$preferred_language]) ? $billing_information[$preferred_language] : $personal_information[$old_lang],
					'{REFUND INFORMATION}' => isset($refund_information[$preferred_language]) ? $refund_information[$preferred_language] : $personal_information[$old_lang],
					'{BASE URL}' => APPHP_BASE,
					'{HOTEL INFO}' => ((!empty($hotel_description[$preferred_language]) && !empty($hotel_description[$old_lang])) ? '<br>-----<br>'.(!isset($hotel_description[$preferred_language]) ? $hotel_description[$preferred_language] : $hotel_description[$old_lang]) : ''),
					'{MESSAGE FOOTER}' => EmailTemplates::PrepareMessageFooter($preferred_language),
				);
				$arr_constants = array();
				$arr_values  = array();
				
				foreach($replace_holders as $key => $val){
					$arr_constants[] = $key;
					$arr_values[] = $val;
				}
				
				if(!in_array($key, array('admin_copy', 'hotelowner_copy'))){
					$copy_subject = str_ireplace($arr_constants, $arr_values, $email_info['template_subject']);
				}

				// Add some CSS for tables
                $css = '<style>.tbl-reservation-details tr th,.tbl-reservation-details tr td {border:1px solid #ccc !important; padding:2px 3px;}.tbl-reservation-details tr th{background-color: #f1f2f3;}</style>';

				$body = str_ireplace($arr_constants, $arr_values, $email_info['template_content']);
				$body = nl2br($body);
				if(!empty($additional_text)){
					$body .= '<br/>'.$additional_text;
				}
                $body .= $css;
				send_email_wo_template(
					$recipient,
					$sender,
					$copy_subject,
					$body,
					$preferred_language
				);
			}				
			////////////////////////////////////////////////////////////
			
			if(in_array($order_type, array('completed', 'reserved')) && !$email_sent){
				// exit if email already sent
				$sql = 'UPDATE '.TABLE_BOOKINGS.' SET email_sent = 1 WHERE booking_number = \''.$booking_number.'\'';
				database_void_query($sql);					
			}			
			
			////////////////////////////////////////////////////////////
			return $return;
		}
		return false;
	}	

	/**
	 * Send cancel booking email
	 * @param $rid
	 */
	public function SendCancelOrderEmail($rid)
	{
		$sql = 'SELECT booking_number, customer_id, is_admin_reservation FROM '.TABLE_BOOKINGS.' WHERE id = '.(int)$rid;		
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$booking_number = $result[0]['booking_number'];
			$customer_id = $result[0]['customer_id'];
			$is_admin_reservation = $result[0]['is_admin_reservation'];

			if($is_admin_reservation){
				$this->error = ''; // show empty error on email sending operation
				return false;
			}elseif($this->SendOrderEmail($booking_number, 'canceled', $customer_id)){
				return true;
			}
		}
		$this->error = _EMAIL_SEND_ERROR;
		return false;
	}
	
	/**
	 * Returns VAT percent
	 */
	public function GetVatPercent()
	{
		$lang = Application::Get('lang');
		
		if($this->vatIncludedInPrice == 'no'){
			$sql='SELECT
					cl.*,
					cd.name as country_name,
					c.vat_value
				FROM '.TABLE_CUSTOMERS.' cl
					LEFT OUTER JOIN '.TABLE_COUNTRIES.' c ON cl.b_country = c.abbrv AND c.is_active = 1
					LEFT OUTER JOIN '.TABLE_COUNTRIES_DESCRIPTION.' cd ON c.id = cd.country_id AND cd.language_id = \''.$lang.'\'
				WHERE cl.id = '.(int)$this->currentCustomerID;
			$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			if($result[1] > 0){
				$vat_percent = isset($result[0]['vat_value']) ? $result[0]['vat_value'] : '0';
			}else{
				$vat_percent = ModulesSettings::Get('booking', 'vat_value');
			}			
		}else{
			$vat_percent = '0';
		}		
		return $vat_percent;		
	}
	
	/**
	 * Returns VAT percent
	 */
	private function GetGuestsAndNightsDiscount()
	{
		$debug = false;
		$discount_guests = 0;
		$discount_night_value = 0;
		if(ModulesSettings::Get('rooms', 'guests_stay_discount') == 'yes'){
			$room_ids = array();
			$guests_count = 0;
            $rooms_count = 0;

			// Prepare array rooms
			foreach($this->arrReservation as $key => $val){
                if(!(TYPE_DISCOUNT_GUEST == 'rooms' && $val['rooms'] < 3)){
                    $rooms_guests[$key] = array(
                        'price' => $val['price'] / ($val['rooms'] * $val['nights']),
                        'rooms_count' => $val['rooms'],
                        'adults_count' => $val['adults'] * $val['rooms'],
                        'nights' => $val['nights'],
                        'rooms_price' => $val['price'],
                    );
                }
				if(TYPE_DISCOUNT_GUEST != 'rooms' || $val['rooms'] >= 3){
					$guests_count += $val['adults'] * $val['rooms'];
                }

				$sql = 'SELECT
							'.TABLE_ROOMS.'.id,
							'.TABLE_ROOMS.'.discount_night_type,
							'.TABLE_ROOMS.'.discount_night_3,
							'.TABLE_ROOMS.'.discount_night_4,
							'.TABLE_ROOMS.'.discount_night_5
						FROM '.TABLE_ROOMS.'
						WHERE
							'.TABLE_ROOMS.'.id = '.$key;
							
				$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);

				if($result[1] > 0){
					// If has been ordered 3 and more nights, check an additional discount on the night
					if(ModulesSettings::Get('rooms', 'long_term_stay_discount') == 'yes'){
						if($val['nights'] >= 3){
							$discount_value = 0;
							if($val['nights'] == 3 && !empty($result[0]['discount_night_3'])){
								$discount_value = $result[0]['discount_night_3'];
							}elseif($val['nights'] == 4 && !empty($result[0]['discount_night_4'])){
								$discount_value = $result[0]['discount_night_4'];
							}else{
								$discount_value = $result[0]['discount_night_5'];
							}

							// 0 - Fixed price, 1 - Percentage
							if($result[0]['discount_night_type'] == 1){
								$discount_nights = ($val['price'] * ($discount_value / 100));
                                if(!empty($rooms_guests[$key])){
                                    $rooms_guests[$key]['price'] -= $rooms_guests[$key]['price'] * ($discount_value / 100);
                                    $rooms_guests[$key]['rooms_price'] -= $rooms_guests[$key]['rooms_price'] * ($discount_value / 100);
                                }
								
							}else{
								if($val['price'] > $discount_value){
									$discount_nights = ($val['price'] / ($val['rooms'] * $val['nights'])  > $discount_value ? $discount_value * $val['rooms'] * $val['nights'] : $val['price']);
                                    if(!empty($rooms_guests[$key])){
                                        $rooms_guests[$key]['price'] -= $discount_value;
                                        $rooms_guests[$key]['rooms_price'] -= $discount_nights;
                                    }
								}else{
									$discount_nights = $val['price'];
                                    if(!empty($rooms_guests[$key])){
                                        $rooms_guests[$key]['price'] = 0;
                                        $rooms_guests[$key]['rooms_price'] = 0;
                                    }
								}
							}
							// The discount can't exceed the cost per room
							$discount_nights = $discount_nights > $val['price'] ? $val['price'] : $discount_nights;
							$discount_night_value += $discount_nights * $this->currencyRate;
						}
					}
				}
			}

			if((TYPE_DISCOUNT_GUEST == 'guests' && $guests_count >= 3) || (TYPE_DISCOUNT_GUEST == 'rooms' && !empty($rooms_guests))){
				if(TYPE_DISCOUNT_GUEST == 'guests'){
					if($guests_count == 3){
						$type_discount = 'discount_guests_3';
					}elseif($guests_count == 4){
						$type_discount = 'discount_guests_4';
					}else{
						$type_discount = 'discount_guests_5';
					}
				}

				$room_ids = array_keys($rooms_guests);
				if($debug){
					echo 'Rooms IDs: '.implode(',',$room_ids).'</br>';
				}

				$sql = 'SELECT
							r.id,
							r.discount_guests_type,
							r.discount_guests_3,
							r.discount_guests_4,
							r.discount_guests_5
						FROM '.TABLE_ROOMS.' r 
						WHERE r.is_active = 1 AND r.id IN ('.implode(',',$room_ids).')';

				$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);

				if($result[1] > 0){
					for($i=0; $i<$result[1]; $i++){
						if(TYPE_DISCOUNT_GUEST == 'rooms'){
							$rooms_count = $rooms_guests[$result[0][$i]['id']]['rooms_count'];
							if($rooms_count == 3){
								$type_discount = 'discount_guests_3';
							}elseif($rooms_count == 4){
								$type_discount = 'discount_guests_4';
							}else{
								$type_discount = 'discount_guests_5';
							}
						}
						$percentage = $result[0][$i]['discount_guests_type'] == 1 ? true : false;
						$id = $result[0][$i]['id'];
						if($percentage){
							$discount_guests += ($result[0][$i][$type_discount] / 100) * $rooms_guests[$id]['rooms_price'];
						}else{
							$discount_guests += ($rooms_guests[$id]['price'] > $result[0][$i][$type_discount] ? $result[0][$i][$type_discount] : $rooms_guests[$id]['price']) * $rooms_guests[$id]['rooms_count'] * $rooms_guests[$id]['nights'];
						}
					}
				}
			}
		}
	
		return array('discount_guests' => $discount_guests * $this->currencyRate, 'discount_nights' => $discount_night_value * $this->currencyRate);
	}
	
	/**
	 * Returns VAT percent
	 */
	private function GetGuestsDiscount()
	{
		$debug = false;
		$discount = 0;
		if(ModulesSettings::Get('rooms', 'guests_stay_discount') == 'yes'){
			$room_ids = array();
			$guests_count = 0;

			// Prepare array rooms
			foreach($this->arrReservation as $key => $val){
				if(TYPE_DISCOUNT_GUEST != 'rooms' || $val['rooms'] >= 3){
					$rooms_guests[$key] = array(
						'price' => $val['price'] / ($val['rooms'] * $val['nights']),
						'rooms_count' => $val['rooms'],
						'adults_count' => $val['adults'] * $val['rooms'],
						'nights' => $val['nights'],
						'rooms_price' => $val['price'],
					);
					$guests_count += $val['adults'] * $val['rooms'];
				}
			}

			if((TYPE_DISCOUNT_GUEST == 'guests' && $guests_count >= 3) || (TYPE_DISCOUNT_GUEST == 'rooms' && !empty($rooms_guests))){
				if(TYPE_DISCOUNT_GUEST == 'guests'){
					if($guests_count == 3){
						$type_discount = 'discount_guests_3';
					}elseif($guests_count == 4){
						$type_discount = 'discount_guests_4';
					}else{
						$type_discount = 'discount_guests_5';
					}
				}

				$room_ids = array_keys($rooms_guests);
				if($debug){
					echo 'Rooms IDs: '.implode(',',$room_ids).'</br>';
				}

				$sql = 'SELECT
							r.id,
							r.discount_guests_type,
							r.discount_guests_3,
							r.discount_guests_4,
							r.discount_guests_5
						FROM '.TABLE_ROOMS.' r 
						WHERE r.is_active = 1 AND r.id IN ('.implode(',',$room_ids).')';

				$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);

				if($result[1] > 0){
					for($i=0; $i<$result[1]; $i++){
						if(TYPE_DISCOUNT_GUEST == 'rooms'){
							$rooms_count = $rooms_guests[$result[0][$i]['id']]['rooms_count'];
							if($rooms_count == 3){
								$type_discount = 'discount_guests_3';
							}elseif($rooms_count == 4){
								$type_discount = 'discount_guests_4';
							}else{
								$type_discount = 'discount_guests_5';
							}
						}
						$percentage = $result[0][$i]['discount_guests_type'] == 1 ? true : false;
						$id = $result[0][$i]['id'];
						if($percentage){
							$discount += ($result[0][$i][$type_discount] / 100) * $rooms_guests[$id]['rooms_price'];
						}else{
							$discount += ($rooms_guests[$id]['price'] > $result[0][$i][$type_discount] ? $result[0][$i][$type_discount] : $rooms_guests[$id]['price']) * $rooms_guests[$id]['rooms_count'] * $rooms_guests[$id]['nights'];
						}
					}
				}
			}
		}
	
		return $discount * $this->currencyRate;
	}
	/**
	 * Generate booking number
	 * @param $booking_id
	 */
	public function GenerateBookingNumber($booking_id = '0')
	{
		$booking_number_type = ModulesSettings::Get('booking', 'booking_number_type');
		if($booking_number_type == 'sequential'){
			return str_pad($booking_id, 10, '0', STR_PAD_LEFT);
		}else{
			return strtoupper(get_random_string(10));		
		}		
	}
	
	/**
	 * Get Vat Percent decimal points
	 * @param $vat_percent
	 */
	private function GetVatPercentDecimalPoints($vat_percent = '0')
	{
		return (substr($vat_percent, -1) == '0') ? 2 : 3;
	}	
	
	/**
	 * Checks if the given hotel id already exists in reservation cart
	 * @param $hotel_id
	 */
	public function CheckHotelIdExists($hotel_id = 0)
	{
        foreach($this->arrReservation as $key => $val){
            if($val['hotel_id'] == $hotel_id){
                return true;
            }
        }                    
        return false;
    }

	/**
	 * Returns reservation hotel ID
	 * @param $hotel_id
	 */
	public function GetReservationHotelId()
	{
        foreach($this->arrReservation as $key => $val){
            if(isset($val['hotel_id'])){
                return $val['hotel_id'];
            }
        }                    
        return 0;
    }

	/**
	 * Load discount info
	 * @param string $from_date
	 * @param string $to_date
	 * @return void
	 */
	public function LoadDiscountInfo($from_date = '', $to_date = '')
	{
		$this->discountCoupon = '';
		$this->discountCampaignID = '';
		$this->discountType = '1';
		$this->discountValue = '0';
		
		// Prepare discount info
		if(isset($this->arrReservationInfo['coupon_code']) && $this->arrReservationInfo['coupon_code'] != ''){
			$this->discountCampaignID = '';
			$this->discountCoupon = $this->arrReservationInfo['coupon_code'];
			$this->discountType = $this->arrReservationInfo['discount_type'];
			$this->discountValue = $this->arrReservationInfo['discount_value'];
		}elseif(GLOBAL_CAMPAIGNS == 'enabled'){
			$campaign_info = Campaigns::GetCampaignInfo('', $from_date, $to_date, 'global');
			if(!empty($campaign_info['id'])){
                $allow_discount = true;
                if(!empty($campaign_info['hotel_id']) && is_array($this->arrReservation)){
                    foreach($this->arrReservation as $key => $val){
                        if($val['hotel_id'] != $campaign_info['hotel_id']){
                            $allow_discount = false;
                            break;
                        }
                    }                    
                }
                
                if($allow_discount){
                    $this->discountCoupon = '';
                    $this->discountCampaignID = $campaign_info['id'];
					$this->discountType = $campaign_info['discount_type'];
                    $this->discountValue = $campaign_info['discount_value'];
                    $this->arrReservationInfo = array(
                        'coupon_code'     	=> '',
                        'discount_type' 	=> '',
						'discount_value' 	=> ''
                    );                                                        
                }else{
                    //echo draw_important_message('Discount can be applied only for single hotel', false);
                }
			}
		}else{
			// Discount for group of guests
			// --------------------------------

			// Calculate total number of adults per hotel/room
			$total_adults_arr = array();
			if(is_array($this->arrReservation)){
				foreach($this->arrReservation as $key => $val){
					if(!isset($total_adults_arr[$val['hotel_id']])){
						$total_adults_arr[$val['hotel_id']] = $val['adults'] * $val['rooms'];
					}else{
						$total_adults_arr[$val['hotel_id']] += $val['adults'] * $val['rooms'];
					}
				}
			}

			// 1 - Find first discount campaign that meets search parameters
			// 2 - Real discount value that calculates discount according to each roon dates from / to 
			$variant = 2; 
			
			if($variant == 1){
				// Find first discount campaign that meets search parameters
				foreach($this->arrReservation as $key => $reservation){
					$max_adults_for_hotel = isset($total_adults_arr[$reservation['hotel_id']]) ? $total_adults_arr[$reservation['hotel_id']] : '';
					if($max_adults_for_hotel){
						$standard_discount = Campaigns::FindCampaigns($reservation['from_date'], $reservation['to_date'], 'standard', 'minimum_adults <= '.$max_adults_for_hotel.' AND maximum_adults >= '.$max_adults_for_hotel.' AND hotel_id = '.$reservation['hotel_id']);
						if(!empty($standard_discount) && $standard_discount[0][0]['apply_per'] == 1){
							$this->discountCampaignID = $standard_discount[0][0]['id'];
							$this->discountType = $standard_discount[0][0]['discount_type'];
							$this->discountValue = $standard_discount[0][0]['discount_value'];
							$this->discountTitle = _GROUP_DISCOUNT;
							break;
						}
					}
				}
			}else{
				// New calculation
				$debug = false;

				// Find discount for each room reservation from / to
				// Works only if all rooms discount is the same type: percentage or fixed price
				$total_discount = 0;
				$discount_type = '';
				$discount_campaign_id = 0;
				
				if($debug) echo '<br>';

				if(is_array($this->arrReservation)) {
					foreach ($this->arrReservation as $key => $reservation) {

						// 1. Get discounts
						// ----------------
						$standard_discount = array();
						$max_adults_for_hotel = isset($total_adults_arr[$reservation['hotel_id']]) ? $total_adults_arr[$reservation['hotel_id']] : '';
						if ($max_adults_for_hotel) {
							$standard_discount = Campaigns::FindCampaigns($reservation['from_date'], $reservation['to_date'], 'standard', 'minimum_adults <= ' . $max_adults_for_hotel . ' AND maximum_adults >= ' . $max_adults_for_hotel . ' AND hotel_id = ' . $reservation['hotel_id'], ' ORDER BY start_date ASC');
							for ($i = 0; $i < $standard_discount[1]; $i++) {
								if ($standard_discount[0][$i]['apply_per'] != 1) continue;
								$discount_type = $standard_discount[0][$i]['discount_type'];
								$discount_campaign_id = $standard_discount[0][$i]['id'];
								///echo '='.$standard_discount[0][$i]['id'];
								//break;
							}
						}

						//dbug($standard_discount);
						//dbug($this->arrReservation,1);

						// 2. Get room prices for the discount periond
						// ----------------
						$params = array();
						$from_date = strtotime($reservation['from_date']);
						$params['max_adults'] = $reservation['adults'];
						$params['max_children'] = $reservation['children'];
						$params['from_day'] = date('d', $from_date);
						$params['from_month'] = date('m', $from_date);
						$params['from_year'] = date('Y', $from_date);
						$from_date = strtotime($reservation['to_date']);
						$params['to_day'] = date('d', $from_date);
						$params['to_month'] = date('m', $from_date);
						$params['to_year'] = date('Y', $from_date);
						$room_price_result = Rooms::GetRoomPrice($reservation['room_id'], $reservation['hotel_id'], $params, 'array');

						// 3. Run throw all days and calculate discount in $
						// ----------------
						foreach ($room_price_result as $rp_date => $room_price) {
							if ($rp_date == 'total_price' || $rp_date == 'original_price') {
								continue;
							}

							if ($debug) echo "<br>$rp_date => $room_price ";

							for ($i = 0; $i < $standard_discount[1]; $i++) {
								if ($rp_date >= $standard_discount[0][$i]['start_date'] && $rp_date <= $standard_discount[0][$i]['finish_date']) {
									$curr_discount_price = $room_price * ($standard_discount[0][$i]['discount_value'] / 100) * $reservation['rooms'];
									if ($debug) echo ' --- ' . $curr_discount_price;
									$total_discount += $curr_discount_price;
									break;
								}
							}
						}

						// GROUP DISC
						if ($debug) {
							echo '<br> Total room discoint ' . $total_discount;
							echo '<br> Total room discoint % ' . ($total_discount / $this->cartTotalSum);
						}
					}
				}

				if(!empty($total_discount)){
					$this->discountCampaignID = $discount_campaign_id;
					$this->discountType = $discount_type;
					$this->discountValue = ($total_discount / $this->cartTotalSum) * 100;
					$this->discountTitle = _GROUP_DISCOUNT;
				}
			}			
		}
	}

	public function GetDiscountInfo()
	{
		return array(
			'discountCampaignID' 	=> $this->discountCampaignID,
			'discountCoupon'     	=> $this->discountCoupon,
			'discountType'     		=> $this->discountType,
			'discountValue'    		=> $this->discountValue
		);
	}
	
	/**
	 * Applies discount coupon number
	 * @param string $coupon_code
	 */
	public function ApplyDiscountCoupon($coupon_code = '')
	{
	 	$result = Coupons::GetCouponInfo($coupon_code);
		if(count($result) > 0){

			$count = Bookings::CountAllByCoupon($coupon_code);
			if($result['max_amount'] != '-1' && $count >= $result['max_amount']){
				$this->error = _MAXIMUM_NUM_OF_USES_ALERT;
				return false;
			}
            
			// Check if current coupon may be used for selected hotel
            $allow_discount = true;
            if(!empty($result['hotel_id'])){
                foreach($this->arrReservation as $key => $val){
                    if($val['hotel_id'] != $result['hotel_id']){
                        $allow_discount = false;
                        break;
                    }
                }
            }
            
            if($allow_discount){
                $this->discountCampaignID = '';
				$this->discountType = $result['discount_type'];
                $this->discountValue = $result['discount_value'];
                $this->discountCoupon = $coupon_code;
                $this->arrReservationInfo = array(
                    'coupon_code'   	=> $coupon_code,
					'discount_type'		=> $result['discount_type'],
                    'discount_value'	=> $result['discount_value']
                );
                return true;
            }else{
                $this->error = FLATS_INSTEAD_OF_HOTELS ? _COUPON_FOR_SINGLE_FLAT_ALERT : _COUPON_FOR_SINGLE_HOTEL_ALERT;
                return false;
            }			
		}else{
			$this->discountCoupon = '';
			$this->arrReservationInfo = array(
				'coupon_code'   	=> '',
				'discount_type'  	=> '',
				'discount_value'	=> ''
			);
			
			$this->LoadDiscountInfo();
            $this->error = _WRONG_COUPON_CODE;
			return false;
		}
	}

	/**
	 * Removes discount coupon number
	 */
	public function CleanDiscountCoupon()
	{
        $this->discountCoupon = '';
        $this->arrReservationInfo = array(
			'coupon_code'   	=> '',
			'discount_type'  	=> '',
			'discount_value'	=> ''
        );
        
        $this->LoadDiscountInfo();
	}
	
	/**
	 * Removes discount coupon number
	 */
	public function RemoveDiscountCoupon($coupon_code = '')
	{
		if(empty($coupon_code)){
			return false;
		}else{			
			$this->discountCoupon = '';
			$this->arrReservationInfo = array();
			$this->LoadDiscountInfo();

			return true;		
		}		
	}

	/**
	 * room is reservation
	 * @param int $room_id
	 * @return bool
	 */	
	public static function RoomIsReserved($room_id)
	{
		if(empty($room_id) || !isset($_SESSION[INSTALLATION_KEY.'reservation'][$room_id])){
			return false;
		}

		return true;
	}

	/**
	 * Draw reservation
	 * @param int $room_id
	 * @return bool
	 */	
	public function DrawWidgetReservationCart($homeurl = '', $draw = true)
    {
        $empty_cart = empty($this->arrReservation) ? true : false;
        if($empty_cart != true){
            $first_reservation = current($this->arrReservation);
        }
        $total_price = 0;

        $nl = "\n";
        $output = '';
        $homeurl = rtrim($homeurl, '/').'/';
        $output .= '<div class="reservationcartdiv">'.$nl;
        $output .= '<div class="cartlabel">'.$nl;
        $output .= '<img src="'.$homeurl.'template/images/bell_write.png">'.$nl;
        $output .= '</div>'.$nl;
        $output .= '<div class="reservationcartinner">'.$nl;
        $output .= '<div id="emptycartdiv"'.($empty_cart != true ? ' style="display: none;"' : '').'>'.$nl;
        $output .= '<img src="'.$homeurl.'template/images/emptybad.png">'.$nl;
        $output .= '</div>'.$nl;
        if($empty_cart != true){
            $output .= '<div id="checkinoutinfo" style="overflow:hidden;border-bottom: 1px solid #D9D9D9;">'.$nl;
            $output .= '<div id="cartcheckin">'.$first_reservation['from_date'].'</div>'.$nl; // Checkin
            $output .= '<div id="cartcheckout">'.$first_reservation['to_date'].'</div>'.$nl; // Checkout
            $output .= '</div>';
            $output .= '<div id="cartfields">'.$nl;
            $output .= '<div id="roomcart">'.$nl;
            // -- Start Room -- //
            foreach($this->arrReservation as $room_id => $val){
                $total_price += $val['meal_plan_price'] + $val['price'];
                $room_info = Rooms::GetRoomInfo($room_id);
                $output .= '<div id="eachroomcart4" class="carteachroom">'.$nl;
                $output .= '<div class="destroyeachroomcart" data-id="'.$room_id.'" data-token="'.Application::Get('token').'" data-message-reserve="'.htmlentities(_RESERVE).'!" data-rtl="'.(Application::Get('lang_dir') == 'rtl' ? 'true' : 'false').'">'.$nl;
                $output .= '<img src="'.$homeurl.'template/images/remove.gif" title="'.htmlentities(_REMOVE_ROOM_FROM_CART).'">'.$nl;
                $output .= '</div>'.$nl;
                $output .= '<div class="carteachroomtitle">'.$room_info['room_type'].'</div>'.$nl; // Name Room
                // Count rooms
                $output .= '<div class="cartfields">'.$nl;
                $output .= '<div class="cartleftfield">'._ROOMS.'</div>'.$nl;
                $output .= '<div class="cartrightfield">'.$val['rooms'].'</div>'.$nl;
                $output .= '</div>'.$nl;
                // Hotels
                $output .= '<div class="cartfields">'.$nl;
                $output .= '<div class="cartleftfield">'.(FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL).'</div>'.$nl;
                $output .= '<div class="cartrightfield">'.$room_info['hotel_name'].'</div>'.$nl;
                $output .= '</div>'.$nl;
                // Meal Plans
                $output .= '<div class="cartfields">'.$nl;
                $output .= '<div class="cartleftfield">'._MEAL_PLANS.'</div>'.$nl;
                $output .= '<div class="cartrightfield">'.Currencies::PriceFormat($val['meal_plan_price'] * $this->currencyRate, '', '', $this->currencyFormat).'</div>'.$nl;
                $output .= '</div>'.$nl;
                // rate per night
                $output .= '<div class="cartfields">'.$nl;
                $output .= '<div class="cartleftfield">'._RATE_PER_NIGHT_AVG.'</div>'.$nl;
                $output .= '<div class="cartrightfield">'.Currencies::PriceFormat((($val['price'] + $val['meal_plan_price']) / $val['nights']) * $this->currencyRate, '', '', $this->currencyFormat).'</div>'.$nl;
                $output .= '</div>'.$nl;
                // Room Price
                $output .= '<div class="cartfields roomtotalprice">'.$nl;
                $output .= '<div class="cartleftfield">'._ROOM_PRICE.'</div>'.$nl;
                $output .= '<div class="cartrightfield">'.Currencies::PriceFormat(($val['price']) * $this->currencyRate, '', '', $this->currencyFormat).'</div>'.$nl;
                $output .= '</div>';
                
                $output .= '<input type="hidden" name="price" value="'.$val['price'].'" class="totalroomprice">'.$nl;
                $output .= '<input type="hidden" name="rid" value="'.$room_id.'" class="totalroomprice">'.$nl;
                $output .= '</div>';
            }
            // -- End Room -- //
                                                    
            $output .= '</div>'.$nl;
            $output .= '</div>'.$nl;
            $output .= '<div class="firststeptotalprice" id="carttotalprice">'.$nl;
            // Total Price
            $output .= '<div class="firststeptotalcost" id="carttotalcost">'.$nl;
            $output .= '<div class="cartleftfield">'._TOTAL_PRICE.'</div>'.$nl;
            $output .= '<div class="cartrightfield" id="totalsum">'.Currencies::PriceFormat($total_price * $this->currencyRate, '', '', $this->currencyFormat).'</div>'.$nl;
            $output .= '</div>'.$nl;
            $output .= '<a class="widget-link" href="'.$homeurl.'index.php?page=booking_details">'.$nl;
            $output .= '<div class="reservation_next_step">'._RESERVATION.'</div>'.$nl;
            $output .= '</a>'.$nl;
            $output .= '</div>';
        }

        $output .= '</div>'.$nl;
        $output .= '</div>'.$nl;

        if($draw){
            echo $output;
        }else{
            return $output;
        }
	}

	/**
	 * Get packages for reserved rooms
	 * @param string $checkin
	 * @param string $checkout
	 * @return array
	 */
	public function GetPackagesForReservedRooms()
	{
		$room_packages = array();
		$reservation_hotel_ids = $this->GetReservationHotelIds();
		foreach($reservation_hotel_ids as $hotel_id){
			$checkin  = $this->GetReservationCheckinForHotel($hotel_id);
			$checkout = $this->GetReservationCheckoutForHotel($hotel_id);
			$min_rooms_packages = Packages::GetMinimumRooms($checkin, $checkout, $hotel_id, false);
			$max_rooms_packages = Packages::GetMaximumRooms($checkin, $checkout, $hotel_id, false);
			
			$room_packages[$hotel_id] = array('min_rooms_packages'=>$min_rooms_packages, 'max_rooms_packages'=>$max_rooms_packages);
		}

		return $room_packages;
	}

	/**
	 * Get reservation hotel IDs
	 * @return array
	 */
	public function GetReservationHotelIds()
	{
		if(empty($this->arrReservation))
			return array();

		$hotel_ids = array();
		
		foreach($this->arrReservation as $reservation_room){
			if(!in_array($reservation_room['hotel_id'], $hotel_ids)){
				$hotel_ids[] = $reservation_room['hotel_id'];
			}
		}

		return $hotel_ids;
	}

	/*
	 * Check is correct reserved number of rooms
	 * @return bool
	 */
	public function IsCorrectReservedNumberRooms()
	{
		if(TYPE_FILTER_TO_NUMBER_ROOMS == 'hotel'){
			$room_packages = $this->GetPackagesForReservedRooms();
			$reservation_hotel_ids = $this->GetReservationHotelIds();
			foreach($reservation_hotel_ids as $hotel_id){
				if(isset($room_packages[$hotel_id])){
					$min_rooms = !empty($room_packages[$hotel_id]['min_rooms_packages']['minimum_rooms']) ? $room_packages[$hotel_id]['min_rooms_packages']['minimum_rooms'] : 0;
					$max_rooms = !empty($room_packages[$hotel_id]['max_rooms_packages']['maximum_rooms']) ? $room_packages[$hotel_id]['max_rooms_packages']['maximum_rooms'] : -1;
					$count_reservation_rooms = $this->GetCountReservationRooms($hotel_id);
                    if(RESIDUE_ROOMS_IS_AVAILABILITY){
						$availability_rooms_for_hotel = 0;
                        foreach($this->arrReservation as $room_id => $reservation_room){
                            if($hotel_id != $reservation_room['hotel_id']){
                                continue;
                            }

                            $checkin = $reservation_room['from_date'];
                            $checkout = $reservation_room['to_date'];
                            $rooms = $reservation_room['rooms'];

                            $availability_rooms = Rooms::GetCountAvailabilityRoomsById($room_id, $checkin, $checkout);
                            $availability_rooms_for_hotel += $availability_rooms;
                        }
                        $min_rooms = $availability_rooms_for_hotel < $min_rooms ? $availability_rooms_for_hotel : $min_rooms;
                    }

					if($max_rooms != '-1' && $count_reservation_rooms > $max_rooms || $count_reservation_rooms < $min_rooms){
						return false;
					}
				}
			}
		}else{
			foreach($this->arrReservation as $reservation_room){
				$checkin = $reservation_room['from_date'];
				$checkout = $reservation_room['to_date'];
				$rooms = $reservation_room['rooms'];
				$hotel_id = $reservation_room['hotel_id'];
				$min_rooms_packages = Packages::GetMinimumRooms($checkin, $checkout, $hotel_id, false);
				$max_rooms_packages = Packages::GetMaximumRooms($checkin, $checkout, $hotel_id, false);

				$hotel_name = !empty($min_rooms_packages['hotel_name']) ? $min_rooms_packages['hotel_name'] : $max_rooms_packages['hotel_name'];

				$maximum_rooms = $max_rooms_packages['maximum_rooms'];
                if(RESIDUE_ROOMS_IS_AVAILABILITY){
                    $availability_rooms = Rooms::GetCountAvailabilityRoomsById($room_id, $checkin, $checkout);
	    			$minimum_rooms = $availability_rooms < $min_rooms_packages['minimum_rooms'] ? $availability_rooms : $min_rooms_packages['minimum_rooms'];
                }else{
                    $minimum_rooms = $min_rooms_packages['minimum_rooms'];
                }

				if((!empty($maximum_rooms) && $maximum_rooms != '-1' && $rooms > $maximum_rooms) || $rooms < $minimum_rooms){
					return false;
				}
			}
		}

		return true;
	}

	private function GetReservationCheckinForHotel($hotel_id)
	{
		if(empty($this->arrReservation))
			return '';

		$checkin = '';
		$checkin_to_unix = 0;
		
		foreach($this->arrReservation as $reservation_room){
			if($reservation_room['hotel_id'] != $hotel_id)
				continue;

			$from_date_to_unix = strtotime($reservation_room['from_date']);
			if($from_date_to_unix < $checkin_to_unix || $checkin_to_unix == 0){
				$checkin = $reservation_room['from_date'];
				$checkin_to_unix = $from_date_to_unix;
			}
		}

		return $checkin;
	}

	private function GetReservationCheckoutForHotel($hotel_id)
	{
		if(empty($this->arrReservation))
			return '';

		$checkout = '';
		$checkout_to_unix = 0;
		
		foreach($this->arrReservation as $reservation_room){
			if($reservation_room['hotel_id'] != $hotel_id)
				continue;

			$to_date_to_unix = strtotime($reservation_room['to_date']);
			if($to_date_to_unix > $checkout_to_unix){
				$checkout = $reservation_room['to_date'];
				$checkout_to_unix = $to_date_to_unix;
			}
		}

		return $checkout;
	}
}
