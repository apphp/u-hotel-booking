<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site http://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------
if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
	$fieldDateFormat = 'M d, Y';
	$sqlFieldDateFormat = '%b %d, %Y';
	$sqlFieldDateFormatFrom = '%b %d';
	$sqlFieldDatetimeFormat = '%b %d, %Y %H:%i';
}else{
	$fieldDateFormat = 'd M, Y';
	$sqlFieldDateFormat = '%d %b, %Y';
	$sqlFieldDateFormatFrom = '%d %b';
	$sqlFieldDatetimeFormat = '%d %b, %Y %H:%i';
}
$lang = Application::Get('lang');

$sql = 'SELECT
		'.TABLE_BOOKINGS.'.id,
		'.TABLE_BOOKINGS.'.booking_number,
		'.TABLE_BOOKINGS.'.hotel_reservation_id,
		'.TABLE_BOOKINGS.'.booking_description,
		'.TABLE_BOOKINGS.'.additional_info,
		'.TABLE_BOOKINGS.'.order_price,
		'.TABLE_BOOKINGS.'.vat_fee,
		'.TABLE_BOOKINGS.'.vat_percent,
		'.TABLE_BOOKINGS.'.payment_sum,
		'.TABLE_BOOKINGS.'.currency,
		'.TABLE_BOOKINGS.'.customer_id,
		'.TABLE_BOOKINGS.'.transaction_number,
		DATE_FORMAT(checkin, "'.$sqlFieldDateFormatFrom.'") as mod_checkin,
		DATE_FORMAT(checkout, "'.$sqlFieldDateFormatFrom.'") as mod_checkout,
		IF('.TABLE_BOOKINGS.'.status > 6, -1, '.TABLE_BOOKINGS.'.status) as status,
		CASE
			WHEN '.TABLE_CURRENCIES.'.symbol_placement = \'before\' THEN
				CONCAT('.TABLE_CURRENCIES.'.symbol, '.TABLE_BOOKINGS.'.payment_sum + '.TABLE_BOOKINGS.'.additional_payment) 
			ELSE
				CONCAT('.TABLE_BOOKINGS.'.payment_sum + '.TABLE_BOOKINGS.'.additional_payment, '.TABLE_CURRENCIES.'.symbol)
		END as tp_w_currency,
		('.TABLE_BOOKINGS.'.payment_sum + '.TABLE_BOOKINGS.'.additional_payment) as tp_wo_currency,
		IF(('.TABLE_BOOKINGS.'.status = 2 OR '.TABLE_BOOKINGS.'.status = 3), CONCAT("<a href=\"javascript:void(\'invoice\')\" class=\"button\" title=\"'._INVOICE.'\" onclick=\"javascript:__mgDoPostBack(\''.TABLE_BOOKINGS.'\', \'invoice\', \'", '.TABLE_BOOKINGS.'.id, "\')\">", "'._INVOICE.'", "</a></nobr>"), "") as link_order_invoice,
		CONCAT("<a href=\"javascript:void(\'description\')\" class=\"button\" title=\"'._DESCRIPTION.'\" onclick=\"javascript:__mgDoPostBack(\''.TABLE_BOOKINGS.'\', \'description\', \'", '.TABLE_BOOKINGS.'.id, "\')\">", "'._DESCRIPTION.'", "</a>") as link_order_description,
		IF(
			(('.TABLE_BOOKINGS.'.status IN(2,3) AND DATEDIFF('.TABLE_BOOKINGS.'.cancel_payment_date, \''.@date('Y-m-d').'\') >= 0) OR '.TABLE_BOOKINGS.'.status = 1),
			CONCAT("<a href=\"javascript:void(0);\" class=\"button\" title=\"'._BUTTON_CANCEL.'\" onclick=\"javascript:__mgMyDoPostBack(\''.TABLE_BOOKINGS.'\', \'cancel\', \'", '.TABLE_BOOKINGS.'.id, "\');\">'._BUTTON_CANCEL.'</a>"),
			""
		) as link_cust_order_cancel,
		br.hotel_id,
		(SELECT GROUP_CONCAT(IF(hd.name != "", hd.name, "") SEPARATOR "; ") FROM '.TABLE_BOOKINGS_ROOMS.' br LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON hd.hotel_id = br.hotel_id AND hd.language_id = \''.$lang.'\' WHERE br.booking_number = '.TABLE_BOOKINGS.'.booking_number) as mod_hotel_names,
		CONCAT("images/hotels/", 
			IF('.TABLE_HOTELS.'.hotel_image != "",
				'.TABLE_HOTELS.'.hotel_image,
				"images/hotels/no_image.png"
			)
		) as hotel_image
	FROM '.TABLE_BOOKINGS.'
		INNER JOIN (SELECT '.TABLE_BOOKINGS_ROOMS.'.booking_number, '.TABLE_BOOKINGS_ROOMS.'.hotel_id, '.TABLE_BOOKINGS_ROOMS.'.room_id, '.TABLE_BOOKINGS_ROOMS.'.checkin, '.TABLE_BOOKINGS_ROOMS.'.checkout, '.TABLE_BOOKINGS_ROOMS.'.hotel_id as b_r_hotel_id FROM '.TABLE_BOOKINGS_ROOMS.' GROUP BY '.TABLE_BOOKINGS_ROOMS.'.booking_number) br ON '.TABLE_BOOKINGS.'.booking_number = br.booking_number
		LEFT OUTER JOIN '.TABLE_HOTELS.' ON '.TABLE_HOTELS.'.id = br.hotel_id
		LEFT OUTER JOIN '.TABLE_CURRENCIES.' ON '.TABLE_BOOKINGS.'.currency = '.TABLE_CURRENCIES.'.code
	';		

$objBookings->SetViewModeSql($sql);

$content = '
<div class="itemlabel3" style="margin-bottom:20px;padding-bottom:15px;">
    <div class="col-md-12 offset-0">
		<div class="col-md-12 title" style="padding-top:5px;padding-bottom:5px;">
			<h3>{mod_hotel_names}</h3>
		</div>
		<div class="clearfix"></div>
		<div class="col-md-3 offset-0 image">
			<a href="index.php?page=flats&fid={hotel_id}"><img src="{hotel_image}" alt="{mod_hotel_names}" title="{mod_hotel_names}" style="width:100%;min-height:130px;padding-left:15px;" /></a>
		</div>
        <div class="col-md-9 offset-0">
			<div class="col-md-6" style="margin-bottom:10px;">
				<b>'._CHECK_IN.'</b><br>
				{mod_checkin}
			</div>
			<div class="col-md-6" style="margin-bottom:10px;">
				<b>'._CHECK_OUT.'</b><br>
				{mod_checkout}
			</div>
			<div class="clearfix"></div>
			<div class="col-md-12 offset-0">
				<div class="col-md-6">
					<b>'._BOOKING_NUMBER.':</b>
				</div>
				<div class="col-md-6">
					{booking_number}
				</div>
			</div>
			<div class="clearfix"></div>
			<div class="col-md-12 offset-0">
				<div class="col-md-6">
					<b>'._PAYMENT_SUM.':</b>
				</div>
				<div class="col-md-6">
					{tp_w_currency}
				</div>
			</div>
			<div class="clearfix"></div>
			<div class="col-md-12 buttons-box" style="padding-top:10px;">
				{link_order_description}
				{link_order_invoice}
				{link_cust_order_cancel}
			</div>
		</div>
    </div>
	<div class="clearfix"></div>
</div>
';

return $content;
