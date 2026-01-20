jQuery(document).ready(function(){
    var $ = jQuery;

    $('#hotel_id').on("change", function(){
        var hotelId = $('#hotel_id').val();
        if(hotelId !== '' || hotelId !== null || hotelId !== undefined){
            var ajax = $.ajax({
                url: 'ajax/pms_change_room_and_inventory.ajax.php',
                global: false,
                type: 'POST',
                data: ({
                    hotelId: hotelId
                }),
                dataType: 'html',
                async: true,
                error: function(html){
                    console.error("AJAX: cannot connect to the server or server response error!");
                },
                success: function(html){
                    try{
                        var obj = $.parseJSON(html);
                        var objRooms = obj.rooms;
                        var objInventory = obj.inventory;
                        console.log(objRooms);
                        console.log(objInventory);
                        var rooms = $('#room_id');
                        var inventory = $('#inventory_id');
                        if(hotelId !== '' || hotelId !== null || hotelId !== undefined){
                            rooms.find('option').not(':first').remove();
                            for (var key in objRooms) {
                                $(rooms).append($('<option>', {
                                    value: key,
                                    text: objRooms[key]
                                }));
                            }
                            inventory.find('option').not(':first').remove();
                            for (var key in objInventory) {
                                $(inventory).append($('<option>', {
                                    value: key,
                                    text: objInventory[key]
                                }));
                            }
                        }else{
                            rooms.find('option').not(':first').remove();
                            inventory.find('option').not(':first').remove();
                        }
                    }catch(err){
                        console.error(err);
                    }
                }
            });

            return ajax;
        }
    });
});