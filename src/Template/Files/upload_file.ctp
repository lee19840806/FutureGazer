<link href="/css/fileinput.min.css" rel="stylesheet">
<link href="/css/handsontable.full.min.css" rel="stylesheet">
<script src="/js/fileinput.min.js"></script>
<script src="/js/lodash.min.js"></script>
<script src="/js/papaparse.min.js"></script>
<script src="/js/moment.min.js"></script>
<script src="/js/handsontable.full.min.js"></script>
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
var resultData = {'isValid': false, 'data': null, 'fields': []};

var handsonNew = new Handsontable(document.getElementById('handsonNew'), {
    data: [],
    minSpareRows: 0,
    rowHeaders: true,
    colHeaders: [],
    contextMenu: false
    });

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

        	    var resultData = processCsvData(results);

        	    _.forEach(resultData.fields, function(field) {
        	    	var selectType = $('#selectType').clone().find('select').attr('name', field.name)
        	    		.find('option[value="' + field.type + '"]').attr('selected', 'selected').end().end();
        	    	var selectFormat = $('#selectFormat_' + field.type).clone();
        	    	tbody.append($('<tr>').append($('<td>').html(field.name)).append($('<td>').append(selectType)).append($('<td>').append(selectFormat)));
            	});
        	}
        });
    }
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
				row[field.name] = parseFloat(row[field.name]);
			}
		});
	});

	return returnData;
}

function updateHandsonTable()
{
	var rows = $('#dataTypeSelection > tr');
	_.forEach(rows, function(n, key) {
		resultData.fields.push({
			'indx': key + 1,
			'name': $($(n).children()[0]).html(),
			'type': $($(n).children()[1]).find('select').val(),
			'format': $($(n).children()[2]).find('select').val()});
	});

	handsonNew.updateSettings({data: resultData.data, colHeaders: true,
		columns: [{'data': 'term', 'type': 'text'}, {'data': 'grade', 'type': 'text'}]
	});
}

$(document).on('ready', function() {
    var tbody = $('#dataTypeSelection');
    tbody.empty();
    
    $("#btnSave").click(function() {
    	var thisButton = $(this);
    	thisButton.attr('disabled', 'disabled');
    	
        var fileName = $("#dataSetName").val();
        
        if (fileName == "")
        {
            alert("Please enter a name for this data");
            thisButton.removeAttr('disabled');
            return;
        }

        var columnTypes = $('#dataTypeSelection').find('select');
        var a = 1;
    });
    
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
        resultData.isValid = false;
	    resultData.data = null;
	    resultData.fields = [];
    });
});
</script>
















