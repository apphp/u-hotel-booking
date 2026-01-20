<?php

/***
 *	Class ToursReservations
 *  --------------
 *  Description : encapsulates Tours Reservations properties
 *  Updated	    : 27.02.2020
 *	Written by  : ApPHP
 *
 *	PUBLIC:					STATIC:					PRIVATE:
 *  -----------				-----------				-----------
 *  __construct										GetVatPercent
 *  __destruct                                      GenerateReservationNumber
 *  AddToReservation                                GetVatPercentDecimalPoints
 *  RemoveReservation								CheckHotelIdExists
 *  ShowReservationInfo								GetReservationHotelId
 *  ShowCheckoutInfo
 *  EmptyCart
 *  GetCartItems
 *  IsCartEmpty
 *  PlaceBooking
 *  DoReservation
 *  SendOrderEmail
 *  SendCancelOrderEmail
 *  DrawReservation
 *
 **/

class ToursReservations {

    public $arrReservation;
    public $error;
    public $message;

    private $fieldDateFormat;
    private $cartItems;
    private $cartTotalSum;
    private $currentCustomerID;
    private $selectedUser;
    private $vatPercent;
    private $currencyFormat;
    private $lang;
    private $paypal_form_type;
    private $paypal_form_fields;
    private $paypal_form_fields_count;
    private $vatIncludedInPrice = 'no';
    private $maximumAllowedReservations = '10';


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

        // prepare Booking settings
        $this->vatIncludedInPrice = ModulesSettings::Get('tours_and_attractions', 'vat_included_in_price');

        // prepare currency info
        if(Application::Get('currency_code') == ''){
            $this->currencyCode = Currencies::GetDefaultCurrency();
            $this->currencyRate = '1';
        }else{
            //$default_currency_info = Currencies::GetDefaultCurrencyInfo();
            $this->currencyCode = Application::Get('currency_code');
            $this->currencyRate = Application::Get('currency_rate');
        }

        // prepare VAT percent
        $this->vatPercent = $this->GetVatPercent();

        // preapre datetime format
        if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
            $this->fieldDateFormat = 'M d, Y';
        }else{
            $this->fieldDateFormat = 'd M, Y';
        }

        $this->lang = Application::Get('lang');
        $this->arrReservation = &$_SESSION[INSTALLATION_KEY.'reservation_tour'];
        $this->arrReservationInfo = &$_SESSION[INSTALLATION_KEY.'reservation_tour_info'];

        $this->cartItems = 0;
        $this->carsCount = 0;
        $this->cartTotalSum = 0;
        $this->paypal_form_type = 'multiple'; // single | multiple
        $this->paypal_form_fields = '';
        $this->currencyFormat = get_currency_format();

        if(!empty($this->arrReservation)){
            $this->paypal_form_fields_count = 0;
            $tour_price = $this->arrReservation['tour_price'];
            $this->cartItems++;
            $this->cartTotalSum = ($tour_price * $this->currencyRate);
        }
        $this->cartTotalSum = number_format($this->cartTotalSum, 2, '.', '');

        $this->message = '';
        $this->error = '';

        $this->primaryKey 	= 'id';
        $this->tableName 	= DB_PREFIX.'tours_reservations';
        $this->tableTourTypes 	= DB_PREFIX.'tours_types';
        $this->tableTourTypesDescription 	= DB_PREFIX.'tours_types_description';
        $this->tableTourLocationsDescription 	= DB_PREFIX.'tours_locations_description';

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
     * Add tour to reservation
     *
     * @param $tour_id
     * @param $tour_location
     * @param $tour_from_date
     * @param $tour_to_date
     * @param $tour_price
     */
    public function AddToReservation($tour_id, $tour_location, $tour_from_date, $tour_to_date, $tour_price)
    {
        $location_name = '';
        $agency_name = '';

        if(!empty($tour_id)){

            // Just add new tour
            $this->arrReservation = array(
                'tour_id'        => $tour_id,
                'tour_location'  => $tour_location,
                'tour_from_date' => $tour_from_date,
                'tour_to_date'   => $tour_to_date,
                'tour_price'     => $tour_price,
                'tour_name'      => '',
                'type_name'      => '',
                'agency_name'    => '',
            );

            $location_name = '';
            if (!empty($tour_location)) {
                $sql = "SELECT name
					FROM ".DB_PREFIX."tours_locations_description
					WHERE id = '".(int)$tour_location."' AND language_id = '".$this->lang."'
					LIMIT 1";

                $locations = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
                if($locations[1] > 0){
                    $this->arrReservation['tour_location_id'] = $tour_location;
                    $this->arrReservation['tour_location'] = $locations[0]['name'];
                }
            }

            $tour = Tours::GetTourFullInfo($tour_id);
            if(!empty($tour)){
                $this->arrReservation['tour_name'] = $tour['name'];
                $this->arrReservation['type_name'] = $tour['type_name'];
                $this->arrReservation['agency_name'] = $tour['tour_agency_name'];
            }

            $this->error = draw_success_message(_TOUR_WAS_ADDED, false);
        }else{
            $this->error = draw_important_message(_WRONG_PARAMETER_PASSED, false);
        }
    }

    /**
     * Remove room from the reservation cart
     * @param $tour_id
     */
    public function RemoveReservation()
    {
        if(!empty($this->arrReservation)){
            $this->arrReservation = array();
            $this->error = draw_message(_TOUR_WAS_REMOVED, false);
        }else{
            $this->error = draw_important_message(_TOUR_NOT_FOUND, false);
        }
    }

    /**
     * Show Reservation Cart on the screen
     */
    public function ShowReservationInfo()
    {
        global $objLogin;

        $align_left = Application::Get('defined_left');
        $align_right = Application::Get('defined_right');
        $class_left = 'rc_'.Application::Get('defined_left');
        $class_right = 'rc_'.Application::Get('defined_right');

        if(!empty($this->arrReservation)){

            echo '<div class="table-responsive div-room-prices offset-1">';
            echo '<table class="reservation_cart" border="0" width="100%" align="center" cellspacing="0" cellpadding="5"><thead>';
            echo '<tr class="header">				
				<th class="'.$class_left.'" width="30px">&nbsp;</th>
				<th align="'.$align_left.'">'._TOUR.'</th>
				<th align="'.$align_left.'">'._TOUR_TYPE.'</th>
				<th align="'.$align_left.'">'._LOCATION.'</th>
				<th class="rc_center" width="100px">'._FROM.'</th>
				<th class="rc_center" width="100px">'._TO.'</th>
				<th width="65px" class="'.$class_right.'">'._PRICE.'&nbsp;</th>
			</tr>';

            echo '</thead>
			<tr><td colspan="6" nowrap height="5px"></td></tr>';

            $order_price = 0;
            $key = $this->arrReservation['tour_id'];
            $val = $this->arrReservation;
            $sql = 'SELECT
						t.*,
						td.name as tour_name,
						ttd.name as type_name,
                        tld.name as location_name,
						tad.name as agency_name
					FROM '.DB_PREFIX.'tours t
						LEFT OUTER JOIN '.DB_PREFIX.'tours_description td ON t.id = td.tour_id AND td.language_id = \''.$this->lang.'\'
						LEFT OUTER JOIN '.DB_PREFIX.'tours_agencies_description tad ON t.tour_agency_id = tad.agency_id AND tad.language_id = \''.$this->lang.'\'
						LEFT OUTER JOIN '.DB_PREFIX.'tours_locations_description tld ON t.tour_location_id = tld.tour_location_id AND tld.language_id = \''.$this->lang.'\'
						LEFT OUTER JOIN '.DB_PREFIX.'tours_types_description ttd ON t.tour_type_id = ttd.tour_type_id AND ttd.language_id = \''.$this->lang.'\'
					WHERE
						t.id = '.(int)$key.' AND
						t.is_active = 1';

            $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
            ///dd($result[0]);
            //dd($this->arrReservation);
            // zzz

            if($result[1] > 0){

                $tour_price = $this->arrReservation['tour_price'] ? $this->arrReservation['tour_price'] : $result[0]['price_per_person'];
                $tour_from_date = $this->arrReservation['tour_from_date'];
                $tour_to_date = $this->arrReservation['tour_to_date'];
                $tour_id = $this->arrReservation['tour_id'];
                $tour_name = $result[0]['tour_name'];
                $agency_name = $this->arrReservation['agency_name'] = $result[0]['agency_name'];
                $tour_type_name = $this->arrReservation['type_name'] = $result[0]['type_name'];
                $tour_location = $this->arrReservation['tour_location'] = $result[0]['location_name'];
                $this->arrReservation['tour_location_id'] = $result[0]['tour_location_id'];

                echo '<tr>
						<td align="center"><a href="index.php?page=book_now_tour&act=remove&rid='.$key.'"><img src="images/remove.gif" width="16" height="16" border="0" title="'._TOUR_REMOVE_TOUR_FROM_CART.'" alt="" /></a></td>							
						<td>
							<b>'.prepare_link('tours', 'tour_id', $result[0]['id'], $tour_name, $tour_name, '', _CLICK_TO_VIEW, false, '_blank').'</b><br /><small>'.$agency_name.'</small>
						</td>
						<td align="'.$align_left.'">'.$tour_type_name.'</td>
						<td align="'.$align_left.'">'.$tour_location.'</td>
						<td align="center">'.format_date($tour_from_date, $this->fieldDateFormat, '', true).'</td>
						<td align="center">'.format_date($tour_to_date, $this->fieldDateFormat, '', true).'</td>
						<td align="'.$align_right.'">'.Currencies::PriceFormat($tour_price * $this->currencyRate, '', '', $this->currencyFormat).'</td>
					</tr>';
                $order_price += ($tour_price * $this->currencyRate);
            }

            // draw sub-total row
            echo '<tr>
					<tr><td colspan="7" nowrap height="15px"></td></tr>
					<td colspan="4"></td>
					<td class="td '.$class_left.'" colspan="2"><b>'._SUBTOTAL.': </b></td>
					<td class="td '.$class_right.'" align="'.$align_right.'"><b>'.Currencies::PriceFormat($order_price, '', '', $this->currencyFormat).'</b></td>
				</tr>';

            echo '<tr><td colspan="7" nowrap="nowrap" height="15px"></td></tr>';

            // calculate percent
            $vat_cost = (($order_price) * ($this->vatPercent / 100));
            $tour_total = ($order_price) + $vat_cost;

            if($this->vatIncludedInPrice == 'no'){
                echo '<tr> 
						<td colspan="4"></td>
						<td class="td '.$class_left.'" colspan="2"><b>'._VAT.': ('.Currencies::PriceFormat($this->vatPercent, '%', 'after', $this->currencyFormat, $this->GetVatPercentDecimalPoints($this->vatPercent)).')</b></td>
						<td class="td '.$class_right.'" align="'.$align_right.'"><b>'.Currencies::PriceFormat($vat_cost, '', '', $this->currencyFormat).'</b></td>
					</tr>';
            }
            echo '<tr><td colspan="7" nowrap height="15px"></td></tr>
				<tr class="footer">
					<td colspan="4"></td>
					<td class="td '.$class_left.'" colspan="2"><b>'._TOTAL.':</b></td>
					<td class="td '.$class_right.'" align="'.$align_right.'"><b>'.Currencies::PriceFormat($tour_total, '', '', $this->currencyFormat).'</b></td>
				</tr>
				<tr><td colspan="7" nowrap height="15px"></td></tr>
				<tr>
					<td colspan="4"></td>
					<td colspan="3" align="'.$align_left.'">
						<input type="button" class="form_button" onclick="javascript:appGoTo(\'page=booking_tour_details\')" value="'._BOOK.'" />
					</td>
				</tr>
				</table>
				</div>';
        }else{
            draw_message(_RESERVATION_CART_IS_EMPTY_ALERT, true, true);
        }
    }

    /**
     * Show checkout info
     */
    public function ShowCheckoutInfo()
    {
        global $objLogin;

        $lang = Application::Get('lang');

        $align_left = Application::Get('defined_left');
        $align_right = Application::Get('defined_right');
        $class_left = 'rc_'.Application::Get('defined_left');
        $class_right = 'rc_'.Application::Get('defined_right');

        $default_payment_system = ModulesSettings::Get('tours_and_attractions', 'default_payment_system');
        $pre_payment_type       = ModulesSettings::Get('tours_and_attractions', 'pre_payment_type');
        $pre_payment_type_post  = isset($_POST['pre_payment_type']) ? prepare_input($_POST['pre_payment_type']) : $pre_payment_type;
        $pre_payment_value      = ModulesSettings::Get('tours_and_attractions', 'pre_payment_value');
        $payment_type_poa       = ModulesSettings::Get('tours_and_attractions', 'payment_type_poa');
        $payment_type_online    = ModulesSettings::Get('tours_and_attractions', 'payment_type_online');
        $payment_type_bank_transfer = ModulesSettings::Get('tours_and_attractions', 'payment_type_bank_transfer');
        $payment_type_paypal    = ModulesSettings::Get('tours_and_attractions', 'payment_type_paypal');
        $payment_type_2co       = ModulesSettings::Get('tours_and_attractions', 'payment_type_2co');
        $payment_type_authorize = ModulesSettings::Get('tours_and_attractions', 'payment_type_authorize');
        $payment_type           = isset($_POST['payment_type']) ? prepare_input($_POST['payment_type']) : $default_payment_system;
        $submition_type         = isset($_POST['submition_type']) ? prepare_input($_POST['submition_type']) : '';
        $is_agency 				= $objLogin->GetCustomerType() == 1 ? true : false;
        $allow_payment_with_balance = ModulesSettings::Get('tours_and_attractions', 'allow_payment_with_balance') == 'yes' ? true : false;

        $payment_type_poa 		    = ($payment_type_poa == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_poa == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_poa == 'Frontend Only')) ? 'yes' : 'no';
        $payment_type_online 		= ($payment_type_online == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_online == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_online == 'Frontend Only')) ? 'yes' : 'no';
        $payment_type_bank_transfer = ($payment_type_bank_transfer == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_bank_transfer == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_bank_transfer == 'Frontend Only')) ? 'yes' : 'no';
        $payment_type_paypal 		= ($payment_type_paypal == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_paypal == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_paypal == 'Frontend Only')) ? 'yes' : 'no';
        $payment_type_2co 			= ($payment_type_2co == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_2co == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_2co == 'Frontend Only')) ? 'yes' : 'no';
        $payment_type_authorize 	= ($payment_type_authorize == 'Frontend & Backend' || ($objLogin->IsLoggedInAsAdmin() && $payment_type_authorize == 'Backend Only') || (!$objLogin->IsLoggedInAsAdmin() && $payment_type_authorize == 'Frontend Only')) ? 'yes' : 'no';
        $payment_type_balance		= (($is_agency || $objLogin->IsLoggedInAsAdmin()) && $allow_payment_with_balance) ? 'yes' : 'no';

        $payment_type_cnt = (($payment_type_poa === 'yes')+
            ($payment_type_online === 'yes')+
            ($payment_type_bank_transfer === 'yes')+
            ($payment_type_paypal === 'yes')+
            ($payment_type_2co === 'yes')+
            ($payment_type_authorize === 'yes')+
            ($payment_type_balance === 'yes'));
        $payment_types_defined  = true;

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
            $sql='SELECT
					'.$table_name.'.*,
					cd.name as country_name,
					c.vat_value,
					IF(st.name IS NOT NULL, st.name, '.$table_name.'.b_state) as b_state
				FROM '.$table_name.'
					LEFT OUTER JOIN '.TABLE_COUNTRIES.' c ON '.$table_name.'.b_country = c.abbrv AND c.is_active = 1
					LEFT OUTER JOIN '.TABLE_STATES.' st ON '.$table_name.'.b_state = st.abbrv AND st.country_id = c.id AND st.is_active = 1
					LEFT OUTER JOIN '.TABLE_COUNTRIES_DESCRIPTION.' cd ON c.id = cd.country_id AND cd.language_id = \''.$lang.'\'
				WHERE '.$table_name.'.id = '.(int)$this->currentCustomerID;
        }
        $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
        if($result[1] <= 0){
            draw_message(_RESERVATION_CART_IS_EMPTY_ALERT, true, true);
            return false;
        }

        if(!empty($this->arrReservation)){
            echo '<form id="checkout-form" action="index.php?page=booking_tour_payment" method="post">
			'.draw_hidden_field('task', 'do_booking', false).'
			'.draw_hidden_field('submition_type', '', false).'
			'.draw_hidden_field('selected_user', $this->selectedUser, false).'
			'.draw_token_field(false);

            echo '<table class="reservation_cart" border="0" width="99%" align="center" cellspacing="0" cellpadding="5">
			<tr><td colspan="6"><h4>'._RESERVATION_DETAILS.'</h4></td></tr>
            <tr class="header">				
				<th class="'.$class_left.'" width="30px">&nbsp;</th>
				<th align="'.$align_left.'">'._TOUR.'</th>
				<th align="'.$align_left.'">'._TOUR_TYPE.'</th>
				<th align="'.$align_left.'">'._LOCATION.'</th>
				<th class="rc_center" width="100px">'._FROM.'</th>
				<th class="rc_center" width="100px">'._TO.'</th>
				<th width="65px" class="'.$class_right.'">'._PRICE.'&nbsp;</th>
			</tr>
			<tr><td colspan="6" nowrap="nowrap" height="5px"></td></tr>';

            $order_price = 0;
            $key = $this->arrReservation['tour_id'];
            $val = $this->arrReservation;

            $sql = 'SELECT
						t.*,
						td.name as tour_name,
						ttd.name as type_name,
                        tld.name as location_name,
						tad.name as agency_name
					FROM '.DB_PREFIX.'tours t
						LEFT OUTER JOIN '.DB_PREFIX.'tours_description td ON t.id = td.tour_id AND td.language_id = \''.$this->lang.'\'
						LEFT OUTER JOIN '.DB_PREFIX.'tours_agencies_description tad ON t.tour_agency_id = tad.agency_id AND tad.language_id = \''.$this->lang.'\'
						LEFT OUTER JOIN '.DB_PREFIX.'tours_locations_description tld ON t.tour_location_id = tld.tour_location_id AND tld.language_id = \''.$lang.'\'
						LEFT OUTER JOIN '.DB_PREFIX.'tours_types_description ttd ON t.tour_type_id = ttd.tour_type_id AND ttd.language_id = \''.$this->lang.'\'
					WHERE
						t.id = '.(int)$key.' AND
						t.is_active = 1';

            $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
            if($result[1] > 0){
                $tour_price = $this->arrReservation['tour_price'] ? $this->arrReservation['tour_price'] : $result[0]['price_per_person'];
                $tour_from_date = $this->arrReservation['tour_from_date'];
                $tour_to_date = $this->arrReservation['tour_to_date'];
                $tour_id = $this->arrReservation['tour_id'];
                $tour_name = $result[0]['tour_name'];
                $agency_name = $this->arrReservation['agency_name'] = $result[0]['agency_name'];
                $tour_type_name = $this->arrReservation['type_name'] = $result[0]['type_name'];
                $tour_location = $this->arrReservation['tour_location'] = $result[0]['location_name'];
                $this->arrReservation['tour_location_id'] = $result[0]['tour_location_id'];

                echo '<tr>
						<td align="center"><a href="index.php?page=book_now_tour&act=remove&rid='.$key.'"><img src="images/remove.gif" width="16" height="16" border="0" title="'._TOUR_REMOVE_TOUR_FROM_CART.'" alt="" /></a></td>							
						<td>
							<b>'.prepare_link('tours', 'tour_id', $result[0]['id'], $tour_name, $tour_name, '', _CLICK_TO_VIEW, false, '_blank').'</b><br /><small>'.$agency_name.'</small>
						</td>
						<td align="'.$align_left.'">'.$tour_type_name.'</td>
						<td align="'.$align_left.'">'.$tour_location.'</td>
						<td align="center">'.format_date($tour_from_date, $this->fieldDateFormat, '', true).'</td>
						<td align="center">'.format_date($tour_to_date, $this->fieldDateFormat, '', true).'</td>
						<td align="'.$align_right.'">'.Currencies::PriceFormat($tour_price * $this->currencyRate, '', '', $this->currencyFormat).'</td>
					</tr>';

                $order_price += ($tour_price * $this->currencyRate);
            }

            // draw sub-total row
            echo '<tr>
					<td colspan="4"></td>
					<td class="td '.$class_left.'" colspan="2"><b>'._SUBTOTAL.':</b></td>
					<td class="td '.$class_right.'" align="'.$class_right.'">
						<b>'.Currencies::PriceFormat($order_price, '', '', $this->currencyFormat).'</b>
					</td>
				 </tr>';

            //echo '<tr><td colspan="7" nowrap height="5px"></td></tr>';

            // calculate percent
            $vat_cost = (($order_price) * ($this->vatPercent / 100));
            $cart_total = ($order_price) + $vat_cost;

            if($this->vatIncludedInPrice == 'no'){
                echo '<tr>
						<td colspan="4"></td>
						<td class="td '.$class_left.'" colspan="2"><b>'._VAT.': ('.Currencies::PriceFormat($this->vatPercent, '%', 'after', $this->currencyFormat, $this->GetVatPercentDecimalPoints($this->vatPercent)).')</b></td>
						<td class="td '.$class_right.'" align="'.$class_right.'">
							<b><label id="reservation_vat">'.Currencies::PriceFormat($vat_cost, '', '', $this->currencyFormat).'</label></b>
						</td>
					 </tr>';
            }
            echo '<tr><td colspan="7" nowrap height="5px"></td></tr>
				 <tr class="footer">
					<td colspan="4"></td>
					<td class="td '.$class_left.'" colspan="2"><b>'._TOTAL.':</b></td>
					<td class="td '.$class_right.'" align="'.$class_right.'">
						<b><label id="reservation_total">'.Currencies::PriceFormat($cart_total, '', '', $this->currencyFormat).'</label></b>
					</td>
				 </tr>';

            // PAYMENT DETAILS
            // ------------------------------------------------------------
            echo '<tr><td colspan="7" nowrap height="12px"></td></tr>';
            echo '<tr><td colspan="7"><h4>'._PAYMENT_DETAILS.'</h4></td></tr>';
            echo '<tr><td colspan="7">';
            echo '<table border="0" width="100%">';
            if($payment_type_cnt > 1){
                echo '<tr><td width="130px" nowrap>'._PAYMENT_TYPE.': &nbsp;</td><td> 
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
                else if($payment_type_balance) $payment_type_hidden = 'account.balance';
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
            if($pre_payment_type == 'percentage' && $pre_payment_value > '0' && $pre_payment_value < '100'){
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
                echo '<tr><td colspan="7" nowrap height="15px"></td></tr>
					  <tr valign="middle">
						<td colspan="7" nowrap height="15px">
							<h4 style="cursor:pointer;" onclick="appToggleElement(\'additional_info\')">'._ADDITIONAL_INFO.' +</h4>
							<textarea name="additional_info" id="additional_info" style="display:none;width:100%;height:75px"></textarea>
						</td>
					  </tr>
					  <tr><td colspan="7" nowrap height="5px"></td></tr>
					  <tr valign="middle">
						<td colspan="5" align="'.$class_right.'"></td>
						<td align="'.$align_right.'" colspan="2">
							'.(($payment_types_defined) ? '<input class="form_button" type="submit" value="'._SUBMIT.'" />' : '').' 
						</td>
					</tr>';
                echo '</table>';
                echo '<input type="hidden" id="hid_vat_percent" value="'.$this->vatPercent.'" />';
                echo '<input type="hidden" id="hid_order_price" value="'.$order_price.'" />';
                echo '<input type="hidden" id="hid_currency_symbol" value="'.Application::Get('currency_symbol').'" />';
                echo '<input type="hidden" id="hid_currency_format" value="'.$this->currencyFormat.'" />';
                echo '</form><br>';
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
     * @param $pre_payment_type
     * @param $pre_payment_value
     * @param $draw
     */
    public function DrawReservation($payment_type, $additional_info, $pre_payment_type = '', $pre_payment_value = '', $draw = true)
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

        $paypal_email        	= ModulesSettings::Get('tours_and_attractions', 'paypal_email');
        $credit_card_required	= ModulesSettings::Get('tours_and_attractions', 'online_credit_card_required');
        $two_checkout_vendor 	= ModulesSettings::Get('tours_and_attractions', 'two_checkout_vendor');
        $authorize_login_id  	= ModulesSettings::Get('tours_and_attractions', 'authorize_login_id');
        $authorize_transaction_key = ModulesSettings::Get('tours_and_attractions', 'authorize_transaction_key');
        $bank_transfer_info   	= ModulesSettings::Get('tours_and_attractions', 'bank_transfer_info');
        $mode                	= ModulesSettings::Get('tours_and_attractions', 'mode');

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

        // check if prepared booking exists and replace it
        $sql='SELECT id, reservation_number, date_from, time_from, date_to, time_to
			  FROM '.$this->tableName.'
			  WHERE customer_id = '.(int)$this->currentCustomerID.' AND
					status = 0 AND  
					is_admin_reservation = '.(($this->selectedUser == 'admin') ? '1' : '0').'
			  ORDER BY id DESC';
        $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
        if($result[1] > 0){
            $booking_number = $result[0]['reservation_number'];
            $order_price = $this->cartTotalSum;

            // calculate VAT
            $cart_total_wo_vat = round($order_price, 2);
            $vat_cost = ($cart_total_wo_vat * ($this->vatPercent / 100));
            $cart_total = round($cart_total_wo_vat, 2) + $vat_cost;

            if(($pre_payment_type == 'percentage') && (int)$pre_payment_value > 0 && (int)$pre_payment_value < 100){
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
                'return'        	=> 'index.php?page=booking_tour_return',
                //'cancel_return' 	=> 'index.php?page=booking_cancel',
                'cancel_return' 	=> 'index.php?page=book_now_tour',

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
                'discount_value'	 => '',
                'extras_param'       => '',
                'extras_sub_total'   => '',
                'vat_cost'           => $vat_cost,
                'cart_total' 		 => number_format((float)$cart_total, (int)Application::Get('currency_decimals'), '.', ''),
                //				'is_prepayment'      => $is_prepayment,
                'is_prepayment'      => 'multiple',
                'pre_payment_type'   => $pre_payment_type,
                'pre_payment_value'  => $pre_payment_value,
                'cancel_button'		 => 'page=booking_tour_checkout',
                'item_name'			 => _TOUR_RESERVATIONS.': '.$this->arrReservation['tour_name'],
                'item_number'		 => $this->arrReservation['tour_id'],
                'account_balance'  	 => $customer_info['balance'],
                'module'			 => 'tours_and_attractions',
            );

            $first_part = '<table border="0" width="97%" align="center">
				<tr><td width="20%">'._TOUR.' </td><td width="2%"> : </td><td> '.$this->arrReservation['tour_name'].'</td></tr>
				<tr><td width="20%">'._TOUR_TYPE.' </td><td width="2%"> : </td><td> '.$this->arrReservation['type_name'].'</td></tr>
                <tr><td width="20%">'._LOCATION.'</td><td width="2%"> : </td><td> '.$this->arrReservation['tour_location'].'</td></tr>
				<tr><td width="20%">'._FROM.' </td><td width="2%"> : </td><td> '.format_date($this->arrReservation['tour_from_date'], $this->fieldDateFormat, '', true).'</td></tr>
				<tr><td width="20%">'._TO.' </td><td width="2%"> : </td><td> '.format_date($this->arrReservation['tour_to_date'], $this->fieldDateFormat, '', true).'</td></tr>
				<tr><td width="20%">'._BOOKING_DATE.' </td><td width="2%"> : </td><td> '.format_date(date('Y-m-d H:i:s'), $this->fieldDateFormat, '', true).'</td></tr>	
				<tr><td>'._BOOKING_PRICE.' </td><td width="2%"> : </td><td> '.Currencies::PriceFormat($order_price, '', '', $this->currencyFormat).'</td></tr>';

            $first_part .= '<tr><td colspan="3" nowrap height="10px"></td></tr>
				<tr><td colspan="3"><h4>'._TOTAL.'</h4></td></tr>
				<tr><td>'._SUBTOTAL.' </td><td> : </td><td> '.Currencies::PriceFormat($cart_total_wo_vat, '', '', $this->currencyFormat).'</td></tr>';
            if($this->vatIncludedInPrice == 'no'){
                $first_part .= '<tr><td>'._VAT.' ('.Currencies::PriceFormat($this->vatPercent, '%', 'after', $this->currencyFormat, $this->GetVatPercentDecimalPoints($this->vatPercent)).') </td><td> : </td><td> '.Currencies::PriceFormat($vat_cost, '', '', $this->currencyFormat).'</td></tr>';
            }
            if($is_prepayment){
                $first_part .= '<tr><td>'._PAYMENT_SUM.' </td><td> : </td><td> <b>'.Currencies::PriceFormat($order_price + $vat_cost, '', '', $this->currencyFormat).'</b></td></tr>';
                $first_part .= '<tr><td>'._PRE_PAYMENT.'</td><td> : </td> <td>'.Currencies::PriceFormat($cart_total, '', '', $this->currencyFormat).' ('.$prepayment_text.')</td></tr>';
            }else{
                $first_part .= '<tr><td>'._PAYMENT_SUM.' </td><td> : </td><td> <b>'.Currencies::PriceFormat($order_price + $vat_cost, '', '', $this->currencyFormat).'</b></td></tr>';
                ///echo '<tr><td>'._PRE_PAYMENT.'</td><td> : </td> <td>'._FULL_PRICE.'</td></tr>';
            }
            if($additional_info != ''){
                $first_part .= '<tr><td colspan="3" nowrap height="10px"></td></tr>';
                $first_part .= '<tr><td colspan="3"><h4>'._ADDITIONAL_INFO.'</h4>'.$additional_info.'</td></tr>';
            }

            $second_part = '</table><br />';

            if($payment_type == 'poa'){

                $output .= $first_part;
                $pp_params['notify'] = 'index.php?page=booking_tour_payment';
                $output .= PaymentIPN::DrawPaymentForm('poa', $pp_params, (($mode == 'TEST MODE') ? 'test' : 'real'), false);
                $output .= $second_part;

            }elseif($payment_type == 'online'){

                $output .= $first_part;
                $pp_params['notify']        	  = 'index.php?page=booking_tour_payment';
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

                $output .= $first_part;
                $pp_params['api_login']                = $paypal_email;
                $pp_params['notify']        		   = 'index.php?page=booking_tour_notify_paypal';
                $pp_params['paypal_form_type']   	   = $this->paypal_form_type;
                $pp_params['paypal_form_fields'] 	   = $this->paypal_form_fields;
                $pp_params['paypal_form_fields_count'] = $this->paypal_form_fields_count;
                $output .= PaymentIPN::DrawPaymentForm('paypal', $pp_params, (($mode == 'TEST MODE') ? 'test' : 'real'), false);
                $output .= $second_part;

            }elseif($payment_type == '2co'){

                $output .= $first_part;
                $pp_params['api_login'] = $two_checkout_vendor;
                $pp_params['notify']    = 'index.php?page=booking_tour_notify_2co';
                $output .= PaymentIPN::DrawPaymentForm('2co', $pp_params, (($mode == 'TEST MODE') ? 'test' : 'real'), false);
                $output .= $second_part;

            }elseif($payment_type == 'authorize.net'){

                $output .= $first_part;
                $pp_params['api_login'] 	  = $authorize_login_id;
                $pp_params['transaction_key'] = $authorize_transaction_key;
                $pp_params['notify']    	  = 'index.php?page=booking_tour_notify_authorize_net';
                // authorize.net accepts only USD, so we need to convert the sum into USD
                $pp_params['cart_total']      = number_format((($pp_params['cart_total'] * Application::Get('currency_rate'))), '2', '.', ',');
                $output .= PaymentIPN::DrawPaymentForm('authorize.net', $pp_params, (($mode == 'TEST MODE') ? 'test' : 'real'), false);
                $output .= $second_part;

            }elseif($payment_type == 'bank.transfer'){

                $output .= $first_part;
                $pp_params['notify'] = 'index.php?page=booking_tour_payment';
                $output .= PaymentIPN::DrawPaymentForm('bank.transfer', $pp_params, (($mode == 'TEST MODE') ? 'test' : 'real'), false);
                $output .= $second_part;

            }elseif($payment_type == 'account.balance'){

                $output .= $first_part;
                $pp_params['notify'] = 'index.php?page=booking_tour_payment';
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
        $sql = 'SELECT id, reservation_number, payment_type, reservation_price
				FROM '.$this->tableName.'
				WHERE customer_id = '.(int)$this->currentCustomerID.' AND
					  is_admin_reservation = '.(($this->selectedUser == 'admin') ? '1' : '0').' AND
					  status = 0
				ORDER BY id DESC';

        $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
        if($result[1] > 0){
            $reservation_number = $result[0]['reservation_number'];
            if($this->selectedUser == 'admin' || $objLogin->IsLoggedInAsAdmin()){
                // For admin always make status: 2 - reserved
                $status = 2;
            }elseif($result[0]['payment_type'] == 6){
                // For balance payments: 3 - complete
                $status = 3;
            }elseif($result[0]['payment_type'] == 0 || $result[0]['payment_type'] == 5){
                // For pay on arrival or bank transfer: 1- pending
                $status = 1;
            }else{
                // Other: 2 - reserved
                $status = 2;
            }
            $payment_sum = $result[0]['reservation_price'];

            $sql = 'UPDATE '.$this->tableName.'
					SET
						status_changed = \''.date('Y-m-d H:i:s').'\',
						additional_info = \''.$additional_info.'\',
						cc_type = \''.$cc_params['cc_type'].'\',
						cc_holder_name = \''.$cc_params['cc_holder_name'].'\',
						cc_number = AES_ENCRYPT(\''.$cc_params['cc_number'].'\', \''.PASSWORDS_ENCRYPT_KEY.'\'),
						cc_expires_month = \''.$cc_params['cc_expires_month'].'\',
						cc_expires_year = \''.$cc_params['cc_expires_year'].'\',
						cc_cvv_code = AES_ENCRYPT(\''.$cc_params['cc_cvv_code'].'\', \''.PASSWORDS_ENCRYPT_KEY.'\'),
						'.(($payment_type == 'account.balance' && ($is_agency || $objLogin->IsLoggedInAsAdmin())) ? 'reservation_paid = '.$payment_sum.',' : '').'
						status = \''.$status.'\'
					WHERE reservation_number = \''.$reservation_number.'\'';
            database_void_query($sql);

            // update customer bookings/rooms amount
            $sql = 'UPDATE '.TABLE_CUSTOMERS.' SET 
						'.(($payment_type == 'account.balance' && ($is_agency || $objLogin->IsLoggedInAsAdmin())) ? 'balance = balance - '.$payment_sum : '').'
					WHERE id = '.(int)$this->currentCustomerID;
            database_void_query($sql);
            if(!$objLogin->IsLoggedIn()){
                // clear selected user ID for non-registered visitors
                Session::Set('sel_current_customer_id', '');
            }

            $this->message = draw_success_message(str_replace('_BOOKING_NUMBER_', '<b>'.$reservation_number.'</b>', _ORDER_PLACED_MSG), false);
            if($this->SendOrderEmail($reservation_number, 'placed', $this->currentCustomerID)){
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
     * @param $pre_payment_type
     * @param $pre_payment_value
     */
    public function DoReservation($payment_type = '', $additional_info = '', $pre_payment_type = '', $pre_payment_value = '')
    {
        global $objLogin;

        if(SITE_MODE == 'demo'){
            $this->error = draw_important_message(_OPERATION_BLOCKED, false);
            return false;
        }

        // check the maximum allowed room reservation per customer
        if($this->selectedUser == 'customer'){
            $sql = 'SELECT COUNT(*) as cnt FROM '.$this->tableName.' WHERE customer_id = '.(int)$this->currentCustomerID.' AND status < 3';
            $result = database_query($sql, DATA_ONLY);
            $cnt = isset($result[0]['cnt']) ? (int)$result[0]['cnt'] : 0;
            if($cnt >= $this->maximumAllowedReservations){
                $this->error = draw_important_message(_MAX_RESERVATIONS_ERROR, false);
                return false;
            }
        }

        $is_agency = $objLogin->GetCustomerType() == 1 ? true : false;
        $reservation_placed = false;
        $booking_number = '';
        $additional_info = substr_by_word($additional_info, 1024);

        $order_price = $this->cartTotalSum;

        // calculate VAT
        $cart_total_wo_vat = round($order_price, 2);
        $vat_cost = ($cart_total_wo_vat * ($this->vatPercent / 100));
        $cart_total = round($cart_total_wo_vat, 2) + $vat_cost;

        if(($pre_payment_type == 'percentage') && (int)$pre_payment_value > 0 && (int)$pre_payment_value < 100){
            $cart_total = ($cart_total * ($pre_payment_value / 100));
        }elseif(($pre_payment_type == 'fixed sum') && (int)$pre_payment_value > 0){
            $cart_total = round($pre_payment_value * $this->currencyRate, 2);
        }else{
            // $cart_total
        }

        if($this->cartItems > 0){
            // add order to database
            if(in_array($payment_type, array('poa', 'online', 'paypal', '2co', 'authorize.net', 'bank.transfer', 'account.balance'))){

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
                }else{
                    $payed_by = '0';
                    $status = '0';
                }

                // check if prepared booking exists and replace it
                $sql = 'SELECT id, reservation_number
						FROM '.$this->tableName.'
						WHERE customer_id = '.(int)$this->currentCustomerID.' AND
							  is_admin_reservation = '.(($this->selectedUser == 'admin') ? '1' : '0').' AND
							  status = 0
						ORDER BY id DESC';
                $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
                if($result[1] > 0){
                    $reservation_number = $result[0]['reservation_number'];

                    $sql = 'UPDATE '.$this->tableName.' SET ';
                    $sql_end = ' WHERE reservation_number = \''.$reservation_number.'\'';
                }else{
                    $sql = 'INSERT INTO '.$this->tableName.' SET reservation_number = \'\',';
                    $sql_end = '';
                }

                $val = $this->arrReservation;
                $key = $this->arrReservation['tour_id'];

                $sql .= '
						tour_type_id = '.(int)$key.',
						tour_id = '.(int)$key.',
						location_id = '.(int)$val['tour_location_id'].',
						date_from = \''.date('Y-m-d', strtotime($val['tour_from_date'])).'\',
						date_to = \''.date('Y-m-d', strtotime($val['tour_to_date'])).'\',
						reservation_description = \''._TOUR_RESERVATION.'\',
						pre_payment_type = \''.$pre_payment_type.'\',
						pre_payment_value = \''.(($pre_payment_type != 'full price') ? $pre_payment_value : '0').'\',
						reservation_total_price = '.number_format((float)$order_price, (int)Application::Get('currency_decimals'), '.', '').',
						vat_fee = '.$vat_cost.',
						vat_percent = '.$this->vatPercent.',
						reservation_price = '.number_format((float)$cart_total, (int)Application::Get('currency_decimals'), '.', '').',
						additional_payment = 0,
						currency = \''.$this->currencyCode.'\',
						customer_id = '.(int)$this->currentCustomerID.',
						is_admin_reservation = '.(($this->selectedUser == 'admin') ? '1' : '0').',
						transaction_number = \'\',
						created_date = \''.date('Y-m-d H:i:s').'\',
						payment_type = '.$payed_by.',
						payment_method = 0,
						additional_info = \''.$additional_info.'\',
						cc_type = \'\',
						cc_holder_name = \'\', 
						cc_number = \'\', 
						cc_expires_month = \'\', 
						cc_expires_year = \'\', 
						cc_cvv_code = \'\',
						status = '.(int)$status.',
						status_description = \'\'';
                $sql .= $sql_end;

                // handle booking details
                if(database_void_query($sql)){
                    $insert_id = database_insert_id();
                    $reservation_number = $this->GenerateReservationNumber($insert_id);
                    $sql = 'UPDATE '.$this->tableName.' SET reservation_number = \''.$reservation_number.'\' WHERE id = '.(int)$insert_id;

                    if(!database_void_query($sql)){
                        $this->error = draw_important_message(_ORDER_ERROR, false);
                    }else{
                        $reservation_placed = true;
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

        return $reservation_placed;
    }

    /**
     * Sends booking email
     * @param booking_number
     * @param $order_type
     * @param $customer_id
     */
    public function SendOrderEmail($reservation_number, $order_type = 'placed', $customer_id = '')
    {
        global $objSettings;

        $lang = Application::Get('lang');
        $return = true;
        $personal_information = '';
        $hotels_count = Hotels::HotelsCount();

        $arr_payment_types = array(
            '0'=>_PAY_ON_ARRIVAL,
            '1'=>_ONLINE_ORDER,
            '2'=>_PAYPAL,
            '3'=>'2CO',
            '4'=>'Authorize.Net',
            '5'=>_BANK_TRANSFER,
            '6'=>_ACCOUNT_BALANCE
        );

        $arr_statuses = array(
            '0'=>_PREORDERING,
            '1'=>_PENDING,
            '2'=>_RESERVED,
            '3'=>_COMPLETED,
            '4'=>_REFUNDED,
            '5'=>_PAYMENT_ERROR,
            '6'=>_CANCELED,
            '-1'=>_UNKNOWN
        );

        // send email to customer
        $sql = 'SELECT
			'.$this->tableName.'.id,
			'.$this->tableName.'.reservation_number,
			'.$this->tableName.'.reservation_description,
			'.$this->tableName.'.reservation_price,
			'.$this->tableName.'.location_id,
			'.$this->tableName.'.date_from,
			'.$this->tableName.'.time_from,
			'.$this->tableName.'.date_to,
			'.$this->tableName.'.time_to,
			'.$this->tableName.'.vat_fee,
			'.$this->tableName.'.vat_percent,
			'.$this->tableName.'.reservation_total_price,
			'.$this->tableName.'.currency,
			'.$this->tableName.'.customer_id,
			'.$this->tableName.'.transaction_number,
			'.$this->tableName.'.created_date,
			'.$this->tableName.'.payment_date,
			'.$this->tableName.'.payment_type,
			'.$this->tableName.'.payment_method,
			'.$this->tableName.'.status,
			'.$this->tableName.'.status_description,
			'.$this->tableName.'.email_sent,
			'.$this->tableName.'.additional_info,
			'.$this->tableName.'.is_admin_reservation,
			CASE
				WHEN '.$this->tableName.'.payment_method = 0 THEN \''.encode_text(_PAYMENT_COMPANY_ACCOUNT).'\'
				WHEN '.$this->tableName.'.payment_method = 1 THEN \''.encode_text(_CREDIT_CARD).'\'
				WHEN '.$this->tableName.'.payment_method = 2 THEN \''.encode_text(_ECHECK).'\'
				ELSE \''.encode_text(_UNKNOWN).'\'
			END as mod_payment_method,
			IF(('.$this->tableName.'.extras_fee + '.$this->tableName.'.vat_fee - ('.$this->tableName.'.reservation_total_price + '.$this->tableName.'.additional_payment) > 0),
			   ('.$this->tableName.'.reservation_total_price + '.$this->tableName.'.extras_fee + '.$this->tableName.'.vat_fee - ('.$this->tableName.'.reservation_total_price + '.$this->tableName.'.additional_payment)),
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
			'.TABLE_COUNTRIES.'.abbrv as b_country,
			tt.name as tour_type,
			IF('.TABLE_STATES.'.name IS NOT NULL, '.TABLE_STATES.'.name, '.TABLE_CUSTOMERS.'.b_state) as b_state
		FROM '.$this->tableName.'		
			INNER JOIN '.TABLE_CURRENCIES.' ON '.$this->tableName.'.currency = '.TABLE_CURRENCIES.'.code
			INNER JOIN '.$this->tableTourTypesDescription.' tt ON '.$this->tableName.'.tour_type_id = tt.tour_type_id AND tt.language_id = \''.$lang.'\'
			LEFT OUTER JOIN '.TABLE_CUSTOMERS.' ON '.$this->tableName.'.customer_id = '.TABLE_CUSTOMERS.'.id
			LEFT OUTER JOIN '.TABLE_COUNTRIES.' ON '.TABLE_CUSTOMERS.'.b_country = '.TABLE_COUNTRIES.'.abbrv AND '.TABLE_COUNTRIES.'.is_active = 1
			LEFT OUTER JOIN '.TABLE_STATES.' ON '.TABLE_CUSTOMERS.'.b_state = '.TABLE_STATES.'.abbrv AND '.TABLE_STATES.'.country_id = '.TABLE_COUNTRIES.'.id AND '.TABLE_STATES.'.is_active = 1			
		WHERE
			'.$this->tableName.'.customer_id = '.$customer_id.' AND
			'.$this->tableName.'.reservation_number = \''.$reservation_number.'\'';

        $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
        if($result[1] > 0){
            $recipient = $result[0]['email'];
            $first_name = $result[0]['first_name'];
            $last_name = $result[0]['last_name'];
            $email_sent = $result[0]['email_sent'];
            $status = $result[0]['status'];
            $status_description = $result[0]['status_description'];
            $preferred_language = $result[0]['preferred_language'];
            $is_admin_reservation = $result[0]['is_admin_reservation'];
            $payment_type = (int)$result[0]['payment_type'];

            if(ModulesSettings::Get('tours_and_attractions', 'mode') == 'TEST MODE'){
                $personal_information .= '<div style="text-align:center;padding:10px;color:#a60000;border:1px dashed #a60000;width:100px">TEST MODE!</div><br />';
            }

            $personal_information .= '<b>'._PERSONAL_INFORMATION.':</b>';
            $personal_information .= '<br />-----------------------------<br />';
            $personal_information .= _FIRST_NAME.' : '.$result[0]['first_name'].'<br />';
            $personal_information .= _LAST_NAME.' : '.$result[0]['last_name'].'<br />';
            $personal_information .= _EMAIL_ADDRESS.' : '.$result[0]['email'].'<br />';

            $billing_information  = '<b>'._BILLING_DETAILS.':</b>';
            $billing_information .= '<br />-----------------------------<br />';
            $billing_information .= _ADDRESS.' : '.$result[0]['b_address'].' '.$result[0]['b_address_2'].'<br />';
            $billing_information .= _CITY.' : '.$result[0]['b_city'].'<br />';
            $billing_information .= _STATE.' : '.$result[0]['b_state'].'<br />';
            $billing_information .= _COUNTRY.' : '.$result[0]['b_country'].'<br />';
            $billing_information .= _ZIP_CODE.' : '.$result[0]['b_zipcode'].'<br />';
            if(!empty($result[0]['phone'])) $billing_information .= _PHONE.' : '.$result[0]['phone'].'<br />';
            if(!empty($result[0]['fax'])) $billing_information .= _FAX.' : '.$result[0]['fax'].'<br />';

            $booking_details  = _DESCRIPTION.': '.$result[0]['reservation_description'].'<br />';
            $booking_details .= _CREATED_DATE.': '.format_datetime($result[0]['created_date'], $this->fieldDateFormat.' H:i:s', '', true).'<br />';
            $payment_date = format_datetime($result[0]['payment_date'], $this->fieldDateFormat.' H:i:s', '', true);
            if(empty($payment_date)) $payment_date = _NOT_PAID_YET;
            $booking_details .= _PAYMENT_DATE.': '.$payment_date.'<br />';
            $booking_details .= _PAYMENT_TYPE.': '.((isset($arr_payment_types[$payment_type])) ? $arr_payment_types[$payment_type] : '').'<br />';
            $booking_details .= _PAYMENT_METHOD.': '.$result[0]['mod_payment_method'].'<br />';
            $booking_details .= _CURRENCY.': '.$result[0]['currency'].'<br />';
            $booking_details .= _BOOKING_PRICE.': '.Currencies::PriceFormat($result[0]['reservation_total_price'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'<br />';
            $booking_details .= _BOOKING_SUBTOTAL.': '.Currencies::PriceFormat($result[0]['reservation_total_price'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'<br />';

            if($this->vatIncludedInPrice == 'no'){
                $booking_details .= _VAT.': '.Currencies::PriceFormat($result[0]['vat_fee'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).' ('.Currencies::PriceFormat($result[0]['vat_percent'], '%', 'after', $this->currencyFormat, $this->GetVatPercentDecimalPoints($result[0]['vat_percent'])).')<br />';
            }
            $booking_details .= _PAYMENT_SUM.': '.Currencies::PriceFormat($result[0]['reservation_price'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'<br />';
            $booking_details .= _PAYMENT_REQUIRED.': '.Currencies::PriceFormat($result[0]['mod_have_to_pay'], $result[0]['symbol'], $result[0]['symbol_placement'], $this->currencyFormat).'<br />';
            if($result[0]['additional_info'] != '') $booking_details .= _ADDITIONAL_INFO.': '.nl2br($result[0]['additional_info']).'<br />';
            $booking_details .= '<br />';

            $tour_type = $result[0]['tour_type'];
            $pick_up_date = $result[0]['date_from'].' '.$result[0]['time_from'];
            $location_name = '';

            if(!empty($result[0]['location_id'])){
                $sql = 'SELECT
					id,
					name
				FROM '.$this->tableTourLocationsDescription.'
				    WHERE id = '.(int)$result[0]['location_id'].' AND language_id = \''.$lang.'\'
				LIMIT 1';

                $locations = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
                if($locations[1] > 0){
                    $location_name = $locations[0]['name'];
                }
            }
            // display list of rooms in order
            // -----------------------------------------------------------------------------
            $booking_details .= '<b>'._RESERVATION_DETAILS.':</b>';
            $booking_details .= '<br />-----------------------------<br />';
            $booking_details .= '<table>';
            $booking_details .= '<tr align="center">';
            $booking_details .= '<th>'._TOUR_TYPE.'</th>';
            $booking_details .= '<th>'._LOCATION.'</th>';
            $booking_details .= '<th>'._FROM.'</th>';
            $booking_details .= '<th align="right">'._PRICE.'</th>';
            $booking_details .= '</tr>';

            $booking_details .= '<tr align="center">';
            $booking_details .= '<td align="left">'.$tour_type.'</td>';
            $booking_details .= '<td align="left">'.$location_name.'</td>';
            $booking_details .= '<td align="center">'.format_date($pick_up_date, $this->fieldDateFormat, '', true).' ('.date('H:i', strtotime($pick_up_date)).')</td>';
            $booking_details .= '<td align="right">'.Currencies::PriceFormat($result[0]['reservation_total_price'], $result[0]['symbol'], '', $this->currencyFormat).'</td>';
            $booking_details .= '</tr>';
            $booking_details .= '</table>';

            // add  info for bank transfer payments
            if($payment_type == 5){
                $booking_details .= '<br />';
                $booking_details .= '<b>'._BANK_PAYMENT_INFO.':</b>';
                $booking_details .= '<br />-----------------------------<br />';
                $booking_details .= ModulesSettings::Get('tours_and_attractions', 'bank_transfer_info');
            }

            ////////////////////////////////////////////////////////////
            $sender = $objSettings->GetParameter('admin_email');
            ///$recipient = $result[0]['email'];

            if($order_type == 'reserved'){
                // exit if email already sent
                if($email_sent == '1') return true;
                $email_template = 'order_status_changed';
                $admin_copy_subject = 'Customer order status has been changed (admin copy)';
                $status_description = isset($arr_statuses[$status]) ? '<b>'.$arr_statuses[$status].'</b>' : _UNKNOWN;
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
                if(isset($arr_statuses[$status])) $status_description = _STATUS.': '.$arr_statuses[$status];
            }

            ////////////////////////////////////////////////////////////
            if(!$is_admin_reservation){

                $arr_send_email = array('customer');
                if(ModulesSettings::Get('tours_and_attractions', 'send_order_copy_to_admin') == 'yes'){
                    $arr_send_email[] = 'admin_copy';
                }
                if(ModulesSettings::Get('tours_and_attractions', 'send_order_copy_to_agency') == 'yes'){
                    $arr_send_email[] = 'agency_copy';
                }

                $default_lang = Languages::GetDefaultLang();
                foreach($arr_send_email as $key){
                    $copy_subject = '';
                    if($key == 'admin_copy'){
                        $email_language = $default_lang;
                        $recipient = $sender;
                        $copy_subject = $admin_copy_subject;
                    }else{
                        $email_language = $preferred_language;
                    }

                    send_email(
                        $recipient,
                        $sender,
                        $email_template,
                        array(
                            '{FIRST NAME}' => $first_name,
                            '{LAST NAME}'  => $last_name,
                            '{BOOKING NUMBER}'  => $reservation_number,
                            '{BOOKING DETAILS}' => $booking_details,
                            '{STATUS DESCRIPTION}' => $status_description,
                            '{PERSONAL INFORMATION}' => $personal_information,
                            '{BILLING INFORMATION}' => $billing_information,
                            '{REFUND INFORMATION}' => '',
                            '{BASE URL}' => APPHP_BASE,
                            '{HOTEL INFO}' => ((!empty($hotel_description)) ? '<br>-----<br>'.$hotel_description : ''),
                            '{MESSAGE FOOTER}' => EmailTemplates::PrepareMessageFooter(),
                        ),
                        $email_language,
                        '',
                        $copy_subject
                    );
                }
            }

            ////////////////////////////////////////////////////////////

            if(in_array($order_type, array('completed', 'reserved')) && !$email_sent){
                // exit if email already sent
                $sql = 'UPDATE '.$this->tableName.' SET email_sent = 1 WHERE reservation_number = \''.$reservation_number.'\'';
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
        $sql = 'SELECT reservation_number, customer_id, is_admin_reservation FROM '.$this->tableName.' WHERE id = '.(int)$rid;
        $result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
        if($result[1] > 0){
            $reservation_number = $result[0]['reservation_number'];
            $customer_id = $result[0]['customer_id'];
            $is_admin_reservation = $result[0]['is_admin_reservation'];

            if($is_admin_reservation){
                $this->error = ''; // show empty error on email sending operation
                return false;
            }elseif($this->SendOrderEmail($reservation_number, 'canceled', $customer_id)){
                return true;
            }
        }
        $this->error = _EMAIL_SEND_ERROR;
        return false;
    }

    /**
     * Returns VAT percent
     */
    private function GetVatPercent()
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
                $vat_percent = ModulesSettings::Get('tours_and_attractions', 'vat_value');
            }
        }else{
            $vat_percent = '0';
        }
        return $vat_percent;
    }

    /**
     * Generate booking number
     * @param $booking_id
     */
    private function GenerateReservationNumber($booking_id = '0')
    {
        $booking_number_type = ModulesSettings::Get('tours_and_attractions', 'reservation_number_type');
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
}
