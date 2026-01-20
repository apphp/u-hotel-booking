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

$all_conference_types = ConferenceTypes::GetAllConferences();

$all_conferences = Conferences::GetAllConferences('', 'start_date');

$sort_conferences = array();
if(!empty($all_conferences) && $all_conferences[1] > 0){
	foreach($all_conferences[0] as $conference){
		$conference_type_id = $conference['conference_type_id'];
		$start_year = date('Y', strtotime($conference['start_date']));
		if(!isset($sort_conferences[$conference_type_id])){
			$sort_conferences[$conference_type_id] = array();
			$sort_conferences[$conference_type_id][$start_year] = array($conference);
		}else{
			if(!isset($sort_conferences[$conference_type_id][$start_year])){
				$sort_conferences[$conference_type_id][$start_year] = array($conference);
			}else{
				$sort_conferences[$conference_type_id][$start_year][] = $conference;
			}
		}
	}
}

?>

<!-- CONTENT -->
<div class="container">
    <div class="container pagecontainer offset-0">	
	<?php if(!Modules::IsModuleInstalled('conferences')){
			draw_important_message(_NOT_AUTHORIZED);
		}else{
	?>

        <!-- FILTERS -->
        <div class="col-md-3 filters offset-0">
            <div class="hpadding20">
                <?php
                    // Draw menu tree
                    Menu::DrawMenu('left');						
                ?>                         
            </div>    
            
        </div>
        <!-- END OF FILTERS -->
        
		<!-- LIST CONTENT-->
        <div class="rightcontent col-md-9 offset-0">
        
            <div class="hpadding20">
				<?php if(0){ ?>
                <!-- Top filters -->
                <div class="topsortby">
                    <div class="col-md-4 offset-0">
                            
                            <div class="left mt7"><b>Sort by:</b></div>
                            
                            <div class="right wh70percent">
                                <select class="form-control mySelectBoxClass ">
                                  <option selected>Guest rating</option>
                                  <option>5 stars</option>
                                  <option>4 stars</option>
                                  <option>3 stars</option>
                                  <option>2 stars</option>
                                  <option>1 stars</option>
                                </select>
                            </div>

                    </div>			
                    <div class="col-md-4">
                        <div class="w50percent">
                            <div class="wh90percent">
                                <select class="form-control mySelectBoxClass ">
                                  <option selected>Name</option>
                                  <option>A to Z</option>
                                  <option>Z to A</option>
                                </select>
                            </div>
                        </div>
                        <div class="w50percentlast">
                            <div class="wh90percent">
                                <select class="form-control mySelectBoxClass ">
                                  <option selected>Price</option>
                                  <option>Ascending</option>
                                  <option>Descending</option>
                                </select>
                            </div>
                        </div>					
                    </div>
                    <div class="col-md-4 offset-0">
                        <button class="popularbtn left">Most Popular</button>
                        <div class="right">
                            <button class="gridbtn">&nbsp;</button>
                            <button class="listbtn active">&nbsp;</button>
                            <button class="grid2btn">&nbsp;</button>
                        </div>
                    </div>
                </div>
                <!-- End of topfilters-->
				<?php } ?>
            </div>
            <!-- End of padding -->
            
            <br/><br/>
            <div class="clearfix"></div>
                <div class="itemscontainer offset-1">
            
                    <div class="hpadding20 center">
					<h2 class="textcenter"><?= _CONFERENCES; ?></span>
                    </div>
    
				<?php
					if(!empty($all_conference_types) && $all_conference_types[1] > 0){
						foreach($all_conference_types[0] as $conference){
							$arr_conference_years = !empty($sort_conferences[$conference['id']]) ? array_keys($sort_conferences[$conference['id']]) : array();
				?>
                    <div class="offset-2">
<!--
						<div class="col-md-4 offset-0">
                            <div class="listitem2">
                                <a href="<?//= 'templates/'.Application::Get('template').'/'; ?>updates/update1/img/activities/act01.jpg" data-footer="A custom footer text" data-title="A random title" data-gallery="multiimages" data-toggle="lightbox">
								<img src="<?//= 'templates/'.Application::Get('template').'/'; ?>updates/update1/img/activities/act01.jpg" alt=""/></a>
                                <div class="liover"></div>
                                <a class="fav-icon" href="javascript:void(0);"></a>
                                <a class="book-icon" href="javascript:void(0);"></a>
                            </div>
						</div>
-->
						<div class="col-md-12 offset-2">
                            <div class="itemlabel4">
<!--
								<div class="labelright">
                                    <br/><span class="green size18"><b>$36.00</b></span><br/>
                                    <span class="size11 grey">/person</span><br/><br/><br/>
                                    <img src="<?//= 'templates/'.Application::Get('template').'/'; ?>images/filter-rating-5.png" width="60" alt=""/><br/>
                                    <img src="<?//= 'templates/'.Application::Get('template').'/'; ?>images/user-rating-5.png" width="60" alt=""/><br/>
                                    <span class="size11 grey">18 Reviews</span><br/><br/><br/>
                                    <form action="#">
                                     <button class="bookbtn mt1" type="submit">Book</button>	
                                    </form>			
                                </div>
-->
								<div class="labelleft2">			
									<h3><?= $conference['name']; ?></h3>
                                    <div class="line4 wh80percent"></div>
                                    
									<p class="grey size14 lh6">
										<?= $conference['short_description']; ?>
                                    </p>
                                </div>
                            </div>
						<?php if(!empty($arr_conference_years)){ ?>
							<p class="size14 lh6">
								<table class="conference conference-id-<?= $conference['id']; ?>">
									<thead>
										<tr>
											<th><?= _WORKSHOP_DATES; ?></th>
											<th><?= _LOCATION; ?></th>
										</tr>
									</thead>
									<tbody>
								<?php
									foreach($arr_conference_years as $conference_year){
										$arr_conferences = $sort_conferences[$conference['id']][$conference_year];
								?>
										<tr><td colspan=2 class="conference-year"><?= $conference_year; ?></td></tr>
									<?php foreach($arr_conferences as $conference){ ?>
										<tr>
											<td class="conference-date"><a href="index.php?page=conference_general&cid=<?= (int)$conference['id']; ?>"><?= date(get_date_format(), strtotime($conference['start_date'])).' - '.date(get_date_format(), strtotime($conference['finish_date'])); ?></a></td>
											<td class="conference-location"><a href="index.php?page=conference_general&cid=<?= (int)$conference['id']; ?>"><?= $conference['hotel_location_name']; ?></a></td>
										</tr>
									<?php } ?>
								<?php } ?>
									</tbody>
								</table>
							</p>
						<?php } ?>
                        </div>
					</div>
					<br>
					<?php } ?>
				<?php } ?>
                </div>	
                <!-- End of offset1-->		
                <!--div class="hpadding20">            
                    <ul class="pagination right paddingbtm20">
                      <li class="disabled"><a href="javascript:void(0);">&laquo;</a></li>
                      <li><a href="javascript:void(0);">1</a></li>
                      <li><a href="javascript:void(0);">2</a></li>
                      <li><a href="javascript:void(0);">3</a></li>
                      <li><a href="javascript:void(0);">4</a></li>
                      <li><a href="javascript:void(0);">5</a></li>
                      <li><a href="javascript:void(0);">&raquo;</a></li>
                    </ul>
                </div-->
        </div>
        <!-- END OF LIST CONTENT-->
        
	<?php } ?>    

    </div>
    <!-- END OF container-->
    
</div>
<!-- END OF CONTENT -->

