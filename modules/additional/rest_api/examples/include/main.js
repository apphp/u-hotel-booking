var keyID = null;
var editKey = null;
var createSelectKey = 0;
var table_info = null;

function addField(type, exclusion_fields)
{
	type = type === null ? 'edit' : type;
	myselect = createSelectFields(exclusion_fields);
	$('#' + type + '-table tbody').append('<tr class="' + type + '-new-field" data-number="' + createSelectKey + '"><td></td></tr>');
	$('#' + type + '-table tr td').last().append(myselect);
	$('#' + type + '-table tr').last().append('<td><input class="form-control" data-type="' + type + '-textbox-field" data-number="' + createSelectKey + '" type="text" value=""/></td><td style="width:60px;"><button type="button" class="btn btn-sm btn-warning" data-type="' + type + '-btn-delete" data-number="' + createSelectKey + '">Delete Field</button></td>');
}

function createSelectFields(exclusion_fields)
{
	if(exclusion_fields === null){
		console.log('Required parameter exclusion_fields is not passed');
		return false;
	}

	createSelectKey++;

	var myselect = $('<select/>', { class: 'all-fields form-control', 'data-type': 'select-field', 'data-number': createSelectKey });
	var items = table_info.columns;

	$('<option/>', {
		val:  '',
		text: '-- field --'
	}).appendTo(myselect);

	$.each(items,function() {
		if(exclusion_fields.indexOf(this.toString()) != -1){
			return;
		}
		$('<option/>', {
			val:  this,
			text: this.replace(/_/g, ' ').replace(/(\w+)/g,function(c){ return c.charAt(0).toUpperCase()+c.substr(1); })
		}).appendTo(myselect);
	});
	myselect.val('');

	return myselect;
}

function init(info, exclusion_fields)
{
	if(info === null){
		console.log('Required parameter info is not passed');
		return false;
	}else if(exclusion_fields === null){
		console.log('Required parameter exclusion_fields is not passed');
		return false;
	}

	keyID = info !== null ? info.columns.indexOf('id') : null;

	table_info = info;

	/*** EDIT ***/
	$('[data-type=edit-btn-set-id]').click(function(){
		var idExists = false, tmpID = '', editID = $('#edit_input_id').val();
		
		for(i = 0, maxCount = table_info.records.length; i < maxCount; i++){
			tmpID = table_info.records[i][keyID];
			if(tmpID == editID){
				idExists = true;
				editKey = i;
				addField('edit', exclusion_fields);
				$('#edit_input_id').attr('disabled', 'disabled');
				$('#edit-table input[name=api_id]').val(editID);
				$('[data-type=edit-btn-set-id]').hide();
				$('[data-type=edit-btn-submit]').show();
				$('[data-type=edit-btn-reset]').show();
				$('[data-type=edit-btn-add-field]').show();
			}
		}

		if(idExists == false){
			alert('This ID does not exist');
		}
	});

	$('[data-type=edit-btn-reset]').click(function(){
		$('tr.edit-new-field').remove();

		$('#edit_input_id').removeAttr('disabled');

		$('[data-type=edit-btn-set-id]').show();
		$('[data-type=edit-btn-submit]').hide();
		$('[data-type=edit-btn-reset]').hide();
		$('[data-type=edit-btn-add-field]').hide();
	});

	$('[data-type=edit-btn-add-field]').click(function(){
		addField('edit', exclusion_fields);
	});


	$("body").on("change", "#edit-table [data-type=select-field]", function () {
		var editID = $('#edit_input_id').val();
		var num = $(this).data('number');
		var val = $(this).val();
		var activeTextbox = $('[data-type="edit-textbox-field"][data-number="' + num + '"]');
		var lastTextbox = $('[data-type="edit-textbox-field"]').last();

		if(val != ''){
			activeTextbox.attr('name', val);
		}else{
			activeTextbox.removeAttr('name');
		}
		if(editKey !== null){
			if(val != ''){
				var key = table_info.columns.indexOf(val);
				activeTextbox.val(table_info.records[editKey][key]);
			}else{
				activeTextbox.val('');
			}
		}

		if(activeTextbox[0] == lastTextbox[0]){
			addField('edit', exclusion_fields);
		}
	});

	$("body").on("click", "#edit-table [data-type=edit-btn-delete]", function () {
		var num = $(this).data('number');
		$('tr.edit-new-field[data-number="' + num + '"]').remove();
	});

	/*** ADD ***/
	$('[data-type=add-btn-reset]').click(function(){
		$('tr.add-new-field').remove();
	});

	$('[data-type=add-btn-add-field]').click(function(){
		addField('add', exclusion_fields);
	});


	$("body").on("change", "#add-table [data-type=select-field]", function () {
		var num = $(this).data('number');
		var val = $(this).val();
		var activeTextbox = $('[data-type="add-textbox-field"][data-number="' + num + '"]');
		var lastTextbox = $('[data-type="add-textbox-field"]').last();

		if(val != ''){
			activeTextbox.attr('name', val);
		}else{
			activeTextbox.removeAttr('name');
		}

		activeTextbox.val('');

		if(activeTextbox[0] == lastTextbox[0]){
			addField('add', exclusion_fields);
		}
	});

	$("body").on("click", "#add-table [data-type=add-btn-delete]", function () {
		var num = $(this).data('number');
		$('tr.add-new-field[data-number="' + num + '"]').remove();
	});

	/*** DELETE ***/
	$('[data-type=delete-btn-submit]').click(function(){
		var idExists = false, tmpID = '', editID = $('#delete_input_id').val();
		
		for(i = 0, maxCount = table_info.records.length; i < maxCount; i++){
			tmpID = table_info.records[i][keyID];
			if(tmpID == editID){
				idExists = true;
				if(confirm('Are you sure you want to delete this record?')){
					$('#delete-table input[name=api_id]').val(editID);
					$('#delete-form').submit();
				}
			}
		}

		if(idExists == false){
			alert('This ID does not exist');
		}
	});
}
