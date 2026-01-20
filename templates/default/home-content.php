<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site http://www.hotel-booking-script.com
* @license http://www.gnu.org/licenses/
*/

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------


$total_hotels = Hotels::GetAllActive();

if($total_hotels[1] > 1){
?>

<!-- WRAP -->
<div class="wrap cstyle03">		
    <div class="container mt-200 z-index100">		
      <div class="row">

        <?php
            // Count number of tabs
            $tabs_count = 0;

            // Prepare properties array
            $property_types = Application::Get('property_types');
            if (GROUP_PROPERTY_DROPDOWNBOX && !empty($property_types) && is_array($property_types)) {
                $tabs_count++;
            } elseif (!empty($property_types) && is_array($property_types)) {
                foreach($property_types as $key => $val){
                    if (!$val['show_on_menu']) continue;
                    $tabs_count++;
                }
            }
            if(Modules::IsModuleInstalled('car_rental') && ModulesSettings::Get('car_rental', 'is_active') == 'yes'){
                $tabs_count++;
            }
            if(Modules::IsModuleInstalled('tours_and_attractions') && ModulesSettings::Get('tours_and_attractions', 'is_active') == 'yes' && ModulesSettings::Get('tours_and_attractions', 'show_tab_on_homepage') == 'yes'){
                $tabs_count++;
            }

            // Prepare tabs CSS
			if ($tabs_count < 5) {
				$left_css = 'col-md-4';
                $right_css = 'col-md-4';
                $max_blocks = 2;
			} elseif ($tabs_count == 5) {
                $left_css = 'col-lg-offset-1 col-md-5';
                $right_css = 'col-md-5';
                $max_blocks = 1;
            } elseif ($tabs_count == 6) {
                $left_css = 'col-md-6';
                $right_css = 'col-md-5';
                $max_blocks = 1;
            } elseif ($tabs_count == 7) {
                $left_css = 'col-md-8';
                $right_css = 'col-md-4';
                $max_blocks = 1;
            } else {
                $left_css = 'col-md-12';
                $right_css = 'col-md-4';
                $max_blocks = 0;
                ///echo '<style>table tr { float: left}</style>';
            }
        ?>

        <div class="<?=$left_css;?>">
            <div class="bs-example bs-example-tabs cstyle04">
            
                <ul class="nav nav-tabs<?= GROUP_PROPERTY_DROPDOWNBOX ? ' group-dropbox' : '' ?>" id="myTab">
					<?php
                    $count = 0;
                    $properties_count = 0;
                    $first_property = 1;
                    ?>
                    <?php if(ALLOW_TAB_SEARCH_ALL){ ?>
                        <li onclick="mySelectUpdate(0)" class=""><a data-toggle="tab" href="#all"><?= _ALL; ?></a></li>
                        <?php
                            $count++;
                            $first_property = 0;
                        ?>
                    <?php } ?>

                    <?php if (GROUP_PROPERTY_DROPDOWNBOX && !empty($property_types) && is_array($property_types)) { ?>
                        <li onclick="mySelectUpdate(1)" class="<?= !$count ? 'active' : ''; ?>"><a data-toggle="tab" href="#property"><span class="hotels"></span> &nbsp;<?= _PROPERTIES; ?></a></li>
                    <?php
                        $count++;
                    } elseif (!empty($property_types) && is_array($property_types)) {
                        foreach($property_types as $key => $val){
                            if (!$val['show_on_menu']) continue;
                            if(!$properties_count++) $first_property = $val['id'];
                            $arr_property_types[$val['id']] = $val['name'];
                            echo '<li onclick="mySelectUpdate('.$val['id'].')" class="'.(!$count ? 'active' : '').'"><a data-toggle="tab" href="#property"><span class="'.$val['property_code'].'"></span> &nbsp;'.$val['name'].'</a></li>';
                            $count++;
                        }
                    }
                    ?>

                    <?php if(Modules::IsModuleInstalled('car_rental') && ModulesSettings::Get('car_rental', 'is_active') == 'yes'){	?>
                        <li onclick="mySelectUpdate(3)" class="cars-tab"><a data-toggle="tab" href="#car"><span class="car"></span> &nbsp;<?= _CARS; ?></a></li>
                    <?php } ?>

                    <?php if(Modules::IsModuleInstalled('tours_and_attractions') && ModulesSettings::Get('tours_and_attractions', 'is_active') == 'yes' && ModulesSettings::Get('tours_and_attractions', 'show_tab_on_homepage') == 'yes'){	?>
						<li onclick="mySelectUpdate(4)" class="tours-tab"><a data-toggle="tab" href="#tour"><span class="suitcase"></span> &nbsp;<?= _TOURS; ?></a></li>
                    <?php } ?>
                </ul>

                <div class="tab-content<?= GROUP_PROPERTY_DROPDOWNBOX ? ' group-dropbox' : ''; ?>" id="myTabContent">
					<div id="property" class="tab-pane fade active in">
                        <?php if (GROUP_PROPERTY_DROPDOWNBOX && !empty($property_types) && is_array($property_types)){ ?>
                            <label><?= _PROPERTY_TYPE; ?></label><br>
                            <select  class="my-form-control" name="select-tab" id="select-tab" style="width:91%">
                                <?php
                                    foreach($property_types as $key => $val){
                                        if (!$val['show_on_menu']) continue;
                                        if(!$properties_count++) $first_property = $val['id'];
                                        $arr_property_types[$val['id']] = $val['name'];
                                        echo '<option data-toggle="tab" href="#property" value="'.$val['id'].'">'.$val['name'].'</option>';
                                        $count++;
                                    }
                                ?>
                            </select>
                        <?php } ?>

                    <?php
                        if(Modules::IsModuleInstalled('booking')){
                            if(ModulesSettings::Get('booking', 'show_reservation_form') == 'yes'){
                                if(Application::Get('page') != 'rooms'){
                                    echo Rooms::DrawSearchAvailabilityBlock(true, '', '', 8, 3, 'main-vertical', '', '', false, true, true, $first_property);
                                }                    
                            }
                        }
					?>
					</div>
					
                    <?php
                        if(Modules::IsModuleInstalled('car_rental') && ModulesSettings::Get('car_rental', 'is_active') == 'yes'){
                            echo CarAgencies::DrawSearchAvailabilityBlock();
                        }
                    ?>
                    <?php
                    if(Modules::IsModuleInstalled('tours_and_attractions') && ModulesSettings::Get('tours_and_attractions', 'is_active') == 'yes'){
                        echo Tours::DrawSearchAvailabilityBlock();
                    }
                    ?>
				</div>
            </div>
        </div>
        <?php
            // 1 'top'
            Hotels::DrawHotelsByGroup(1, false, true, ['col-md'=>$right_css, 'max'=>$max_blocks]);
        ?>
      </div>
    </div>

	<?php
		// 2 'last-minute'
		$last_minute_hotels = Hotels::DrawHotelsByGroup(2, false, false);
		// 3 'early booking'
		$early_booking_hotels = Hotels::DrawHotelsByGroup(3, false, false);
		// 4 'hot deals'
		$hot_deals_hotels = Hotels::DrawHotelsByGroup(4, false, false);
	?>
	
	<?php if(!empty($last_minute_hotels) || !empty($early_booking_hotels) || !empty($hot_deals_hotels)){ ?>
    <div class="deals3 m-t-0-imp">
        <div class="container">
            <div class="row">
                <?= $last_minute_hotels; ?>
                
                <?= $early_booking_hotels; ?>
                
                <?= $hot_deals_hotels; ?>
            </div>
        </div>
    </div>
	<?php } ?>
	
	<?php
		// 'best rated' hotels 
        $best_rated_hotels = Hotels::DrawHotelsBestRated(false);
		if(!empty($best_rated_hotels)){
            $hotels_total_number = $total_hotels[1];
            if($hotels_total_number > 1){
	?>
		<div class="deal-best-hotels">
			<div class="container">				
				<div class="row">
					<div class="best-hotels-title dtitle"><?= _MOST_POPULAR_HOTELS; ?>: </div>
					<?= $best_rated_hotels; ?>
				</div>
			</div>
		</div>
    <?php
            }
        }
    ?>		

	<?php
		// Home page text
		$mg_language_id = isset($_REQUEST['mg_language_id']) ? prepare_input($_REQUEST['mg_language_id']) : Application::Get('lang');
		$objPage = new Pages('home', false, $mg_language_id);
		$home_text = $objPage->GetText(true);
		if(!empty($home_text)){
	?>
		<div class="deals5">
			<div class="container">
				<div class="row"><?= $home_text; ?></div>
			</div>
		</div>
	<?php } ?>		

	
	<?php
		$last_booking_block = Bookings::DrawLastBookingBlock(2, 3, false);
		if(!empty($last_booking_block)){
	?>		
		<div class="deals4">
			<div class="container">	
				<div class="row"><?= $last_booking_block; ?></div>
			</div>
		</div>	
	<?php } ?>
	

    <div class="lastminute3 last-minute">		    
        <?php include('templates/'.Application::Get('template').'/last_minute.php'); ?>
    </div>

    <div class="container cstyle06 today-top-deals">
        <?php include('templates/'.Application::Get('template').'/today_top_deals.php'); ?>
        <hr class="featurette-divider2 today-featured-offers">
        <?php include('templates/'.Application::Get('template').'/today_featured_offers.php'); ?>
		<br>
    </div>
	
    <?php include('templates/'.Application::Get('template').'/footer.php');?>
</div>
<!-- END OF WRAP -->

<?php }else{ ?>

<!-- WRAP -->
<div class="wrap cstyle03">

    <div class="container mt-200 z-index100">
      <div class="row">
        <div class="col-md-4">
            <div class="bs-example bs-example-tabs cstyle04">

                <ul class="nav nav-tabs" id="myTab">
					<?php
						$count = 0;
						$properties_count = 0;
						$first_property = 1;
					?>

					<?php if(ALLOW_TAB_SEARCH_ALL){ ?>
						<li onclick="mySelectUpdate(0)" class=""><a data-toggle="tab" href="#all"><!--<span class="car"></span>--> &nbsp;<?= _ALL; ?></a></li>
						<?php
							$count++;
							$first_property = 0;
						?>
					<?php } ?>

					<?php
						// Prepare properties array
						$property_types = Application::Get('property_types');
						foreach($property_types as $key => $val){
                            if (!$val['show_on_menu']) continue;
							if(!$properties_count++) $first_property = $val['id'];
							$arr_property_types[$val['id']] = $val['name'];
							echo '<li onclick="mySelectUpdate('.$val['id'].')" class="'.(!$count ? 'active' : '').'"><a data-toggle="tab" href="#property"><span class="'.$val['property_code'].'"></span> &nbsp;'.$val['name'].'</a></li>';
							$count++;
						}
					?>
					<?php if(Modules::IsModuleInstalled('car_rental') && ModulesSettings::Get('car_rental', 'is_active') == 'yes'){	?>
						<li onclick="mySelectUpdate(3)" class=""><a data-toggle="tab" href="#car"><span class="car"></span> &nbsp;<?= _CARS; ?></a></li>
					<?php } ?>
                </ul>

                <div class="tab-content" id="myTabContent">
					<div id="property" class="tab-pane fade active in">
                    <?php
                        if(Modules::IsModuleInstalled('booking')){
                            if(ModulesSettings::Get('booking', 'show_reservation_form') == 'yes'){
                                if(Application::Get('page') != 'rooms'){
                                    echo Rooms::DrawSearchAvailabilityBlock(true, '', '', 8, 3, 'main-vertical', '', '', false, true, true, $first_property);
                                }
                            }
                        }
					?>
					</div>

                    <?php
                        if(Modules::IsModuleInstalled('car_rental') && ModulesSettings::Get('car_rental', 'is_active') == 'yes'){
                            echo CarAgencies::DrawSearchAvailabilityBlock();
                        }
                    ?>
                </div>
            </div>
        </div>
        <?php
            // 1 'top'
            Hotels::DrawHotelsByGroup(1, false, true, array('col-md'=>'col-md-5'));
        ?>
      </div>
    </div>

	<?php
		// Home page text
		$mg_language_id = isset($_REQUEST['mg_language_id']) ? prepare_input($_REQUEST['mg_language_id']) : Application::Get('lang');
		$objPage = new Pages('home', false, $mg_language_id);
		$home_text = $objPage->GetText(true);
		if(!empty($home_text)){
	?>
		<br>
		<div class="mt-200 mb-200 pside10">
			<div class="container">
				<div class="row"><?= $home_text; ?></div>
			</div>
		</div>
	<?php } ?>

	<div class="lastminute3 last-minute">
        <?php include('templates/'.Application::Get('template').'/last_minute.php'); ?>
    </div>

    <?php include('templates/'.Application::Get('template').'/footer.php');?>

</div>
<?php } ?>
<script>
    jQuery(document).ready(function(){
        jQuery('#select-tab').on('change', function(e) {
            console.log(e);
            var valueOption =  jQuery('#select-tab option:selected').val();
            mySelectUpdate(valueOption);
            e.preventDefault();
        });
    });
</script>
