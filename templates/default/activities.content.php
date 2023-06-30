<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site https://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

?>

<!-- CONTENT -->
<div class="container">
    <div class="container pagecontainer offset-0">
		
	<?php if(TRUE){
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
            </div>
            <!-- End of padding -->
            
            <br/><br/>
            <div class="clearfix"></div>

            <?php if(Application::Get('lang') == 'en'){ ?>
          
            <div class="itemscontainer offset-1">
        
                <div class="hpadding20 center">
                    <span class="opensans normal dark size24 textcenter">Special Interests</span>
                </div>
                
                <br/>
                <br/>

                <div class="offset-2">
                    <div class="col-md-4 offset-0">
                        <div class="listitem2">
                            <a href="<?= 'templates/'.Application::Get('template').'/'; ?>updates/update1/img/activities/act01.jpg" data-footer="A custom footer text" data-title="A random title" data-gallery="multiimages" data-toggle="lightbox"><img src="<?= 'templates/'.Application::Get('template').'/'; ?>updates/update1/img/activities/act01.jpg" alt=""/></a>
                            <div class="liover"></div>
                            <a class="fav-icon" href="javascript:void(0);"></a>
                            <a class="book-icon" href="javascript:void(0);"></a>
                        </div>
                    </div>
                    <div class="col-md-8 offset-2">
                        <div class="itemlabel4">
                            <div class="labelright">
                                <br/><span class="green size18"><b>$36.00</b></span><br/>
                                <span class="size11 grey">/person</span><br/><br/><br/>
                                <img src="<?= 'templates/'.Application::Get('template').'/'; ?>images/filter-rating-5.png" width="60" alt=""/><br/>
                                <img src="<?= 'templates/'.Application::Get('template').'/'; ?>images/user-rating-5.png" width="60" alt=""/><br/>
                                <span class="size11 grey">18 Reviews</span><br/><br/><br/>
                                <form action="#">
                                 <button class="bookbtn mt1" type="submit">Book</button>	
                                </form>			
                            </div>
                            <div class="labelleft2">			
                                <span class="size16"><b>4x4 Sunset Desert Safari and Dhow Cruise Dinner</b></span><br/>
                                <div class="line4 wh80percent"></div>
                                
                                <p class="grey size14 lh6">
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum nec semper lectus. Suspendisse placerat enim mauris, eget lobortis nisi egestas et.
                                Donec elementum metus et mi aliquam eleifend. Suspendisse volutpat egestas rhoncus.</p><br/>
                            
                            </div>
                        </div>
                    </div>
                </div>

                <div class="clearfix"></div>
                <div class="offset-2"><hr class="featurette-divider3"></div>
                
                <div class="offset-2">
                    <div class="col-md-4 offset-0">
                        <div class="listitem2">
                            <a href="<?= 'templates/'.Application::Get('template').'/'; ?>updates/update1/img/activities/act02.jpg" data-footer="A custom footer text" data-title="A random title" data-gallery="multiimages" data-toggle="lightbox"><img src="<?= 'templates/'.Application::Get('template').'/'; ?>updates/update1/img/activities/act02.jpg" alt=""/></a>
                            <div class="liover"></div>
                            <a class="fav-icon" href="javascript:void(0);"></a>
                            <a class="book-icon" href="javascript:void(0);"></a>
                        </div>
                    </div>
                    <div class="col-md-8 offset-2">
                        <div class="itemlabel4">
                            <div class="labelright">
                                <br/><span class="green size18"><b>$39.00</b></span><br/>
                                <span class="size11 grey">/person</span><br/><br/><br/>
                                <img src="<?= 'templates/'.Application::Get('template').'/'; ?>images/filter-rating-5.png" width="60" alt=""/><br/>
                                <img src="<?= 'templates/'.Application::Get('template').'/'; ?>images/user-rating-5.png" width="60" alt=""/><br/>
                                <span class="size11 grey">18 Reviews</span><br/><br/><br/>
                                <form action="#">
                                 <button class="bookbtn mt1" type="submit">Book</button>	
                                </form>			
                            </div>
                            <div class="labelleft2">			
                                <span class="size16"><b>Aquaventure Waterpark and The Lost Chambers Aquarium</b></span><br/>
                                <div class="line4 wh80percent"></div>									
                                <p class="grey size14 lh6">
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum nec semper lectus. Suspendisse placerat enim mauris, eget lobortis nisi egestas et.
                                Donec elementum metus et mi aliquam eleifend. Suspendisse volutpat egestas rhoncus.</p><br/>
                            
                            </div>
                        </div>
                    </div>
                </div>

                <div class="clearfix"></div>
                <div class="offset-2"><hr class="featurette-divider3"></div>
                
                <div class="offset-2">
                    <div class="col-md-4 offset-0">
                        <div class="listitem2">
                            <a href="<?= 'templates/'.Application::Get('template').'/'; ?>updates/update1/img/activities/act03.jpg" data-footer="A custom footer text" data-title="A random title" data-gallery="multiimages" data-toggle="lightbox"><img src="<?= 'templates/'.Application::Get('template').'/'; ?>updates/update1/img/activities/act03.jpg" alt=""/></a>
                            <div class="liover"></div>
                            <a class="fav-icon" href="javascript:void(0);"></a>
                            <a class="book-icon" href="javascript:void(0);"></a>
                        </div>
                    </div>
                    <div class="col-md-8 offset-2">
                        <div class="itemlabel4">
                            <div class="labelright">
                                <br/><span class="green size18"><b>$45.00</b></span><br/>
                                <span class="size11 grey">/person</span><br/><br/><br/>
                                <img src="<?= 'templates/'.Application::Get('template').'/'; ?>images/filter-rating-5.png" width="60" alt=""/><br/>
                                <img src="<?= 'templates/'.Application::Get('template').'/'; ?>images/user-rating-5.png" width="60" alt=""/><br/>
                                <span class="size11 grey">18 Reviews</span><br/><br/><br/>
                                <form action="#">
                                 <button class="bookbtn mt1" type="submit">Book</button>	
                                </form>			
                            </div>
                            <div class="labelleft2">			
                                <span class="size16"><b>Yas Waterworld and Ferrari World Abu Dhabi</b></span><br/>
                                <div class="line4 wh80percent"></div>									
                                <p class="grey size14 lh6">
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum nec semper lectus. Suspendisse placerat enim mauris, eget lobortis nisi egestas et.
                                Donec elementum metus et mi aliquam eleifend. Suspendisse volutpat egestas rhoncus.</p><br/>
                            
                            </div>
                        </div>
                    </div>
                </div>

                <div class="clearfix"></div>
                <div class="offset-2"><hr class="featurette-divider3"></div>
                
                <div class="offset-2">
                    <div class="col-md-4 offset-0">
                        <div class="listitem2">
                            <a href="<?= 'templates/'.Application::Get('template').'/'; ?>updates/update1/img/activities/act04.jpg" data-footer="A custom footer text" data-title="A random title" data-gallery="multiimages" data-toggle="lightbox"><img src="<?= 'templates/'.Application::Get('template').'/'; ?>updates/update1/img/activities/act04.jpg" alt=""/></a>
                            <div class="liover"></div>
                            <a class="fav-icon" href="javascript:void(0);"></a>
                            <a class="book-icon" href="javascript:void(0);"></a>
                        </div>
                    </div>
                    <div class="col-md-8 offset-2">
                        <div class="itemlabel4">
                            <div class="labelright">
                                <br/><span class="green size18"><b>$49.00</b></span><br/>
                                <span class="size11 grey">/person</span><br/><br/><br/>
                                <img src="<?= 'templates/'.Application::Get('template').'/'; ?>images/filter-rating-5.png" width="60" alt=""/><br/>
                                <img src="<?= 'templates/'.Application::Get('template').'/'; ?>images/user-rating-5.png" width="60" alt=""/><br/>
                                <span class="size11 grey">18 Reviews</span><br/><br/><br/>
                                <form action="#">
                                 <button class="bookbtn mt1" type="submit">Book</button>	
                                </form>			
                            </div>
                            <div class="labelleft2">			
                                <span class="size16"><b>Dubai Half-Day City Tour</b></span><br/>
                                <div class="line4 wh80percent"></div>									
                                <p class="grey size14 lh6">
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum nec semper lectus. Suspendisse placerat enim mauris, eget lobortis nisi egestas et.
                                Donec elementum metus et mi aliquam eleifend. Suspendisse volutpat egestas rhoncus.</p><br/>
                            
                            </div>
                        </div>
                    </div>
                </div>

                <div class="clearfix"></div>
                <div class="offset-2"><hr class="featurette-divider3"></div>
                
                <div class="offset-2">
                    <div class="col-md-4 offset-0">
                        <div class="listitem2">
                            <a href="<?= 'templates/'.Application::Get('template').'/'; ?>updates/update1/img/activities/act05.jpg" data-footer="A custom footer text" data-title="A random title" data-gallery="multiimages" data-toggle="lightbox"><img src="<?= 'templates/'.Application::Get('template').'/'; ?>updates/update1/img/activities/act05.jpg" alt=""/></a>
                            <div class="liover"></div>
                            <a class="fav-icon" href="javascript:void(0);"></a>
                            <a class="book-icon" href="javascript:void(0);"></a>
                        </div>
                    </div>
                    <div class="col-md-8 offset-2">
                        <div class="itemlabel4">
                            <div class="labelright">
                                <br/><span class="green size18"><b>$49.00</b></span><br/>
                                <span class="size11 grey">/person</span><br/><br/><br/>
                                <img src="<?= 'templates/'.Application::Get('template').'/'; ?>images/filter-rating-5.png" width="60" alt=""/><br/>
                                <img src="<?= 'templates/'.Application::Get('template').'/'; ?>images/user-rating-5.png" width="60" alt=""/><br/>
                                <span class="size11 grey">18 Reviews</span><br/><br/><br/>
                                <form action="#">
                                 <button class="bookbtn mt1" type="submit">Book</button>	
                                </form>									
                            </div>
                            <div class="labelleft2">			
                                <span class="size16"><b>Ski Dubai Ski Slope Access</b></span><br/>
                                <div class="line4 wh80percent"></div>									
                                <p class="grey size14 lh6">
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum nec semper lectus. Suspendisse placerat enim mauris, eget lobortis nisi egestas et.
                                Donec elementum metus et mi aliquam eleifend. Suspendisse volutpat egestas rhoncus.</p><br/>
                            
                            </div>
                        </div>
                    </div>
                </div>

                <div class="clearfix"></div>
                <div class="offset-2"><hr class="featurette-divider3"></div>
                

                <div class="offset-2">
                    <div class="col-md-4 offset-0">
                        <div class="listitem2">
                            <a href="<?= 'templates/'.Application::Get('template').'/'; ?>updates/update1/img/activities/act06.jpg" data-footer="A custom footer text" data-title="A random title" data-gallery="multiimages" data-toggle="lightbox"><img src="<?= 'templates/'.Application::Get('template').'/'; ?>updates/update1/img/activities/act06.jpg" alt=""/></a>
                            <div class="liover"></div>
                            <a class="fav-icon" href="javascript:void(0);"></a>
                            <a class="book-icon" href="javascript:void(0);"></a>
                        </div>
                    </div>
                    <div class="col-md-8 offset-2">
                        <div class="itemlabel4">
                            <div class="labelright">
                                <br/><span class="green size18"><b>$49.00</b></span><br/>
                                <span class="size11 grey">/person</span><br/><br/><br/>
                                <img src="<?= 'templates/'.Application::Get('template').'/'; ?>images/filter-rating-5.png" width="60" alt=""/><br/>
                                <img src="<?= 'templates/'.Application::Get('template').'/'; ?>images/user-rating-5.png" width="60" alt=""/><br/>
                                <span class="size11 grey">18 Reviews</span><br/><br/><br/>
                                <form action="#">
                                 <button class="bookbtn mt1" type="submit">Book</button>	
                                </form>									
                            </div>
                            <div class="labelleft2">			
                                <span class="size16"><b>Dubai Skydiving</b></span><br/>
                                <div class="line4 wh80percent"></div>									
                                <p class="grey size14 lh6">
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum nec semper lectus. Suspendisse placerat enim mauris, eget lobortis nisi egestas et.
                                Donec elementum metus et mi aliquam eleifend. Suspendisse volutpat egestas rhoncus.</p><br/>
                            
                            </div>
                        </div>
                    </div>
                </div>

                <div class="clearfix"></div>
                <div class="offset-2"><hr class="featurette-divider3"></div>
                
                
                
            </div>	
            <!-- End of offset1-->		

            <div class="hpadding20">
            
                <ul class="pagination right paddingbtm20">
                  <li class="disabled"><a href="javascript:void(0);">&laquo;</a></li>
                  <li><a href="javascript:void(0);">1</a></li>
                  <li><a href="javascript:void(0);">2</a></li>
                  <li><a href="javascript:void(0);">3</a></li>
                  <li><a href="javascript:void(0);">4</a></li>
                  <li><a href="javascript:void(0);">5</a></li>
                  <li><a href="javascript:void(0);">&raquo;</a></li>
                </ul>

            </div>

                        </div>

            <?php } ?>
        <!-- END OF LIST CONTENT-->
        
	<?php } ?>    

    </div>
    <!-- END OF container-->
    
</div>
<!-- END OF CONTENT -->

