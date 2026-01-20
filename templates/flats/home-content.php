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

?>
<!-- WRAP -->
<div class="wrap cstyle03">		
    <div class="container z-index100">		
      <div class="row">
        <div class="col-md-4 col-sm-4">
            <div class="bs-example bs-example-tabs cstyle04">
            
             <!--   <ul class="nav nav-tabs" id="myTab">
					<?php
						// prepare properties array		
						$property_types = Application::Get('property_types');
						$count = 0;
						foreach($property_types as $key => $val){
							$arr_property_types[$val['id']] = $val['name'];
							echo '<li onclick="mySelectUpdate('.$val['id'].')" class="'.(!$count ? 'active' : '').'"><a data-toggle="tab" href="#property"><span class="'.$val['property_code'].'"></span> &nbsp;'.$val['name'].'</a></li>';
							$count++;
						}
					?>
					<?php if(Modules::IsModuleInstalled('car_rental') && ModulesSettings::Get('car_rental', 'is_active') == 'yes'){	?>
						<li onclick="mySelectUpdate(3)" class=""><a data-toggle="tab" href="#car"><span class="car"></span> &nbsp;<?= _CARS; ?></a></li>
					<?php } ?>					
                </ul> -->
                
                <div class="tab-content" id="myTabContent">
					<div id="property" class="tab-pane fade active in">
                    <?php
                        if(Modules::IsModuleInstalled('booking')){
                            if(ModulesSettings::Get('booking', 'show_reservation_form') == 'yes'){
                                if(Application::Get('page') != 'rooms'){
                                    echo Rooms::DrawSearchAvailabilityBlock(true, '', '', 8, 3, 'main-vertical', '', '', false, true, true, 1);
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
        
      </div>
    </div>

	<?php
		// 'best rated' hotels 
        $best_rated_hotels = Hotels::DrawHotelsBestRated(false);
		if(!empty($best_rated_hotels)){
	?>
		<div class="deal-best-hotels">
			<div class="container">				
				<div class="row">
					<div class="best-hotels-title dtitle"><?= _MOST_POPULAR_FLATS; ?>: </div>
					<?= $best_rated_hotels; ?>
				</div>
			</div>
		</div>
	<?php } ?>		

	<?php
		// Home page text
		$mg_language_id = isset($_REQUEST['mg_language_id']) ? prepare_input($_REQUEST['mg_language_id']) : Application::Get('lang');
		$objPage = new Pages('home', false, $mg_language_id);
		$home_text = $objPage->GetText(true);
		if(!empty($home_text)){
	?>
		<div class="deals5">
			<div class="container">
				<?= $home_text; ?>
			</div>
		</div>
	<?php } ?>		

    <div class="deals3">
        <div class="container">	
            <div class="row">
                <?php
                    // 2 'last-minute'
                    Hotels::DrawHotelsByGroup(2);
                ?>
                
                <?php
                    // 3 'early booking'
                    Hotels::DrawHotelsByGroup(3);
                ?>
                
                <?php
                    // 4 'hot deals'
                    Hotels::DrawHotelsByGroup(4);
                ?>
            </div>
        </div>
    </div>
	

    <!-- <div class="container cstyle06 today-top-deals">
        <?php include('templates/'.Application::Get('template').'/today_top_deals.php'); ?>
        <hr class="featurette-divider2 today-featured-offers">
        <?php include('templates/'.Application::Get('template').'/today_featured_offers.php'); ?>
		<br>
    </div> -->
	
    <?php include('templates/'.Application::Get('template').'/footer.php');?>
</div>
<!-- END OF WRAP -->
