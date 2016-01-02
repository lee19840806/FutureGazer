<link href="/css/fileinput.min.css" rel="stylesheet">
<link href="/css/handsontable.full.min.css" rel="stylesheet">
<script src="/js/fileinput.min.js"></script>
<script src="/js/lodash.min.js"></script>
<script src="/js/papaparse.min.js"></script>
<script src="/js/moment.min.js"></script>
<script src="/js/handsontable.full.min.js"></script>
<script src="/js/jszip.min.js"></script>
<div class="container-fluid" style="padding-left: 40px; padding-right: 40px;">
    <div class="row">
        <div class="col-lg-2">
            <?= $this->element('left_menu') ?>
        </div>
        <div class="col-lg-10">
            <div class="row">
                <div class="col-lg-12">
                    <h4><strong>Upload data from a file</strong></h4>
                    <hr>
                    <?= $this->Flash->render() ?>
                    <form id="fileUploadForm" enctype="multipart/form-data" action="/Files/submitFile" method="POST">
                        <div class="form-group">
                            <input id="csvMeta" type="hidden" name="csvMeta" />
                            <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
                        </div>
                        <div class="form-group">
                            <label for="userfile">Step 1. Click "Browse" button and select a CSV file: </label>
                            <input id="userFileUpload" name="userfile" type="file" class="file-loading" accept=".csv" required="required">
                        </div>
                        <br>
                        <hr>
                        <div id="step2" class="form-group" style="display: none;">
                            <p><label>Step 2. Select data type for each column: </label></p>
                            <table class="table table-condensed table-striped table-hover">
                                <thead>
                                    <tr class="success">
                                        <th>Column Name</th>
                                        <th>Data Type</th>
                                        <th>Display Format</th>
                                    </tr>
                                </thead>
                                <tbody id="dataTypeSelection">
                                </tbody>
                            </table>
                        </div>
                    </form>
                    <hr>
                    <div class="row">
                    	<div class="col-lg-12">
                    		<form class="form-inline">
	                        	<div class="form-group">
	                                <button id="btnSave" type="button" class="btn btn-sm btn-info">
	                                    <strong>Save data</strong>
	                                </button>
	                                <input type="text" id="dataSetName" class="form-control input-sm" placeholder="Name for this data" required>
	                            </div>
                            </form>
                            <br>
	                    	<div class="panel panel-info">
	                            <div class="panel-heading">Read CSV file</div>
	                            <div class="panel-body">
	                                <div id="handsonNew" style="height: 400px; width: auto; overflow: hidden;"></div>
	                            </div>
	                        </div>
                        </div>
                    </div>
                    <br>
                    <br>
                    <br>
                    <br>
                </div>
            </div>
        </div>
    </div>
</div>
<div style="display: none;">
<form class="form-inline" id="selectType">
	<div class="form-group">
		<select class="form-control input-sm" name="">
			<option value="text">Text</option>
			<option value="numeric">Numeric</option>
			<option value="date">Date</option>
		</select>
	</div>
</form>
<form class="form-inline" id="selectFormat_date">
	<div class="form-group">
		<select class="form-control input-sm" name="">
			<option value="YYYY-MM-DD">YYYY-MM-DD</option>
		</select>
	</div>
</form>
<form class="form-inline" id="selectFormat_numeric">
	<div class="form-group">
		<select class="form-control input-sm" name="">
			<option value="0">1234567</option>
			<option value="0,0">1,234,567</option>
			<option value="0,0.00">1,234,567.89</option>
			<option value="0%">12%</option>
			<option value="0.00%">12.34%</option>
			<option value="$0,0">$1,234,567</option>
			<option value="$0,0.00">$1,234,567.89</option>
		</select>
	</div>
</form>
<form class="form-inline" id="selectFormat_text">
	<div class="form-group">
		<select class="form-control input-sm" name="">
			<option value="general">General</option>
		</select>
	</div>
</form>
</div>
<script>
var resultData = {originalData: null, convertedData: null, fields: []};

var handsonNew = new Handsontable(document.getElementById('handsonNew'), {
	data: [],
    minSpareRows: 0,
    rowHeaders: true,
    colHeaders: true,
    readOnly: true,
    contextMenu: false
    });

var autoColumnSizePlugin = handsonNew.getPlugin('autoColumnSize');
autoColumnSizePlugin.enablePlugin();

var readFile = function(fileHandler)
{
	if (fileHandler != undefined)
    {
        Papa.parse(fileHandler, {
            header: true,
            skipEmptyLines: true,
        	complete: function(results) {
        		var tbody = $('#dataTypeSelection');
        	    tbody.empty();
        	    $("#step2").slideDown('fast');
        	    $("#step3").slideDown('fast');
        	    $("#csvMeta").val(JSON.stringify(results.meta));

        	    resultData = processCsvData(results);

        	    _.forEach(resultData.fields, function(field) {
        	    	var selectType = $('#selectType').clone().find('select').attr('name', field.name).change(changeType)
        	    		.find('option[value="' + field.type + '"]').attr('selected', 'selected').end().end();
        	    	var selectFormat = $('#selectFormat_' + field.type).clone().find('select').change(changeFormat).attr('name', field.name).end();
        	    	tbody.append($('<tr>').append($('<td>').html(field.name)).append($('<td>').append(selectType)).append($('<td>').append(selectFormat)));
            	});

        	    updateHandsonTable(resultData.convertedData, resultData.fields);
        	}
        });
    }
}

function changeFormat(e)
{
	var fieldName = $(e.target).attr('name');
	_.find(resultData.fields, {name: fieldName}).format = e.target.value;

	updateHandsonTable(resultData.convertedData, resultData.fields);
}

function changeType(e)
{
	var fieldName = $(e.target).attr('name');
	var isDate = true;
	var isNumeric = true;

	_.forEach(resultData.originalData, function(row, key) {
		if (e.target.value == 'numeric')
		{
			if (!$.isNumeric(row[fieldName]) && (row[fieldName] != ""))
			{
				isNumeric = false;
				$(e.target).find('option').removeAttr('selected');
				$(e.target).find('option[value="' + _.result(_.find(resultData.fields, {name: fieldName}), 'type') + '"]').attr('selected', 'selected');
				alert('Value "' + row[fieldName] + '" at row ' + (key + 1) + ' cannot be converted to a number.');
				return false;
			}
		}
		
		if (e.target.value == 'date')
		{
			if ((moment(row[fieldName], "YYYY-MM-DD", true).isValid() == false) && (row[fieldName] != ""))
			{
				isDate = false;
				$(e.target).find('option').removeAttr('selected');
				$(e.target).find('option[value="' + _.result(_.find(resultData.fields, {name: fieldName}), 'type') + '"]').attr('selected', 'selected');
				alert('Value "' + row[fieldName] + '" at row ' + (key + 1) + ' cannot be converted to a date.');
				return false;
			}
		}
	});

	if (isDate == false || isNumeric == false)
	{
		return;
	}
	
	var selectFormat = $('#selectFormat_' + e.target.value).clone().find('select').change(changeFormat).attr('name', fieldName).end();
	var td = $($(e.target).parent().parent().parent()).next();
	td.children('form').remove();
	td.append(selectFormat);

	_.forEach(resultData.convertedData, function(row, key) {
		if (e.target.value == 'text')
		{
			row[fieldName] = resultData.originalData[key][fieldName];
		}
		else if (e.target.value == 'numeric')
		{
			(resultData.originalData[key][fieldName] == '') ? (row[fieldName] = null) : (row[fieldName] = parseFloat(resultData.originalData[key][fieldName]));
		}
		else if (e.target.value == 'date')
		{
			var theDate = moment(resultData.originalData[key][fieldName], "YYYY-MM-DD", true);
			theDate.isValid() ? row[fieldName] = theDate.format() : row[fieldName] = "";
		}
	});

	_.find(resultData.fields, {name: fieldName}).type = e.target.value;

	if (e.target.value == 'text')
	{
		_.find(resultData.fields, {name: fieldName}).format = 'general';
	}
	else if (e.target.value == 'numeric')
	{
		_.find(resultData.fields, {name: fieldName}).format = '0';
	}
	else if (e.target.value == 'date')
	{
		_.find(resultData.fields, {name: fieldName}).format = 'YYYY-MM-DD';
	}
	
	updateHandsonTable(resultData.convertedData, resultData.fields);
}

function processCsvData(csvParseResult)
{
	var returnData = {'originalData': null, 'convertedData': null, 'fields': []};
	returnData.originalData = csvParseResult.data;
	returnData.convertedData = _.cloneDeep(csvParseResult.data);
	
	_.forEach(csvParseResult.meta.fields, function(fieldName, index) {
	    var isDateTime = true;
	    var isNumber = true;
	    
        _.forEach(csvParseResult.data, function(dataValue, dataIndex) {
        	if ((isDateTime == true) && (moment(dataValue[fieldName], "YYYY-MM-DD", true).isValid() == false) && (dataValue[fieldName] != ""))
	        {
	        	isDateTime = false;

	        	if ((isNumber == true) && (!$.isNumeric(dataValue[fieldName])) && (dataValue[fieldName] != ""))
	            {
	                isNumber = false;
	                return false;
	            }
	        }
        });

        if (isDateTime == true)
        {
        	returnData.fields.push({'indx': index + 1, 'name': fieldName, 'type': 'date', 'format': 'YYYY-MM-DD'});
        }
        else if (isNumber == true)
        {
        	returnData.fields.push({'indx': index + 1, 'name': fieldName, 'type': 'numeric', 'format': '0'});
        }
        else
        {
        	returnData.fields.push({'indx': index + 1, 'name': fieldName, 'type': 'text', 'format': 'general'});
        }
    });

	_.forEach(returnData.fields, function(field, index) {
		_.forEach(returnData.convertedData, function(row, key) {
			if (field.type == 'date')
			{
				var theDate = moment(row[field.name], "YYYY-MM-DD", true);
				theDate.isValid() ? row[field.name] = theDate.format() : row[field.name] = "";
			}
			else if (field.type == 'numeric')
			{
				(row[field.name] == '') ? (row[field.name] = null) : (row[field.name] = parseFloat(row[field.name]));
			}
		});
	});

	return returnData;
}

function updateHandsonTable(data, fields, clearTable)
{
	if (clearTable == true)
	{
		handsonNew.updateSettings({data: [], columns: []});
	}
	else
	{
		columnSetting = _.map(fields, function(field) {
			if (field.type == 'date')
			{
				return {data: field.name, type: field.type, renderer: dateRenderer};
			}
			else
			{
				return {data: field.name, type: field.type, format: field.format};
			}
		});
		
		handsonNew.updateSettings({data: data, colHeaders: _.pluck(fields, 'name'), columns: columnSetting});
		autoColumnSizePlugin.recalculateAllColumnsWidth();
		handsonNew.render();
	}
}

function dateRenderer(instance, td, row, col, prop, value, cellProperties)
{
	if (value == '')
	{
		td.innerHTML = '';
		return td;
	}
	else
	{
		td.innerHTML = moment(value).format('YYYY-MM-DD');
		return td;
	}
}

function saveData(e)
{
	var thisButton = $(this);
	thisButton.attr('disabled', 'disabled');
	
    var fileName = $("#dataSetName").val();
    
    if (fileName == "")
    {
        alert("Please enter a name for this data.");
        thisButton.removeAttr('disabled');
        return;
    }

    if (resultData.originalData == null)
    {
    	alert("Data is empty, please read data from a file.");
        thisButton.removeAttr('disabled');
        return;
    }
    
    var jsonData = JSON.stringify({"fileName": fileName, "fileFields": resultData.fields, "fileContent": JSON.stringify(resultData.convertedData)});
    var zip = new JSZip();
    zip.file("z.txt", jsonData);
    var zippedBinary = zip.generate({type: "blob", compression: "DEFLATE", compressionOptions: {level: 9}});

    var formData = new FormData();
    formData.append('zippedBinary', zippedBinary);

    $.ajax({
        method: "POST",
        url: "/Files/name_available",
        data: {'fileName': fileName}
    })
    	.done(function(data) {
            if (data == "0")
            {
                alert("File name '" + fileName + "' existed, please use another name.");
                thisButton.removeAttr('disabled');
            }
            else
            {
                $.ajax({
                    method: "POST",
                    url: "/Files/save_compressed_data",
                    timeout: 20000,
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false
                })
                    .done(function(result) {
                        if (result == 0)
                        {
                            alert("An error has occured when trying to save data. Please try again.");
                            thisButton.removeAttr('disabled');
                        }
                        else
                        {
                            alert("Data has been saved. Go to 'Manage my data' -> 'List my data' to view the data.");
                            thisButton.removeAttr('disabled');
                        }
                        })
                    .fail(function(jqXHR, textStatus, errorThrown) {
                    	thisButton.removeAttr('disabled');
                        });
            }
            });
}

$(document).on('ready', function() {
    var tbody = $('#dataTypeSelection');
    tbody.empty();
    
    $("#btnSave").click(saveData);
    
    $("#userFileUpload").fileinput({
        browseClass: "btn btn-primary",
        showPreview: false,
        showUpload: false
    });

    $("#userFileUpload").change(function() {
        readFile(this.files[0]);
    });

    $('#userFileUpload').on('fileclear', function(event) {
        $("#step2").slideUp('fast');
        $("#step3").slideUp('fast');
        $("#csvMeta").val("");
        tbody.empty();
	    resultData = {originalData: null, convertedData: null, fields: []};
	    updateHandsonTable(null, null, true);
    });
});
</script>
















