<link href="/css/handsontable.full.min.css" rel="stylesheet">
<script src="/js/handsontable.full.min.js"></script>
<script src="/js/lodash.min.js"></script>
<div class="container-fluid" style="padding-left: 40px; padding-right: 40px;">
    <div class="row">
        <div class="col-lg-2">
            <?= $this->element('left_menu') ?>
        </div>
        <div class="col-lg-10">
            <div class="row">
                <div class="col-lg-12">
                    <h4><strong>Connect data</strong></h4>
                    <hr>
                    <div class="row">
                    	<div class="col-lg-12">
                    		<form class="form-inline">
	                    		<div class="form-group">
	                    			<label for="joinType">Connection Type&nbsp;&nbsp;</label>
				                    <select class="form-control input-sm" id="joinType">
										<option value="cartesian">Cartesian product (cross join)</option>
										<option value="left">Left join</option>
										<option value="inner">Inner join</option>
									</select>
								</div>
							</form>
							<br>
							<button class="btn btn-sm btn-info" id="btnConnect" type="button">Connect data</button>
                    	</div>
                    </div>
                    <hr>
                    <div class="row">
                    	<div class="col-lg-12">
	                    	<ul class="nav nav-tabs" role="tablist">
			                    <li role="presentation" class="active"><a href="#rawData" aria-controls="rawData" role="tab" data-toggle="tab">Raw data sets</a></li>
			                    <li role="presentation"><a href="#newData" id="tabNewData" aria-controls="newData" role="tab" data-toggle="tab">New data set</a></li>
			                </ul>
			                
			                <div class="tab-content">
			                    <div role="tabpanel" class="tab-pane active" id="rawData">
			                    	<div class="row">
			                    		<br>
				                        <div class="col-lg-6">
				                    		<form class="form-inline">
				                    			<div class="form-group">
						                    		<label for="selectFiles1">Select data set as A&nbsp;&nbsp;</label>
						                    		<?= $this->Form->select('selectFiles1', $fileNames, ['id' => 'selectFiles1', 'class' => 'form-control input-sm']); ?>
					                    		</div>
					                    		<button class="btn btn-sm btn-primary" id="btnLoad1" type="button">Load data</button>
				                    		</form>
				                    		<br>
				                    		<div class="panel panel-primary">
				                                <div class="panel-heading">Data set A</div>
				                                <div class="panel-body">
				                                    <div id="handson1" style="height: 400px; width: auto; overflow: hidden;"></div>
				                                </div>
				                            </div>
				                    	</div>
				                    	<div class="col-lg-6">
				                    		<form class="form-inline">
				                    			<div class="form-group">
						                    		<label for="selectFiles2">Select data set as B&nbsp;&nbsp;</label>
						                    		<?= $this->Form->select('selectFiles2', $fileNames, ['id' => 'selectFiles2', 'class' => 'form-control input-sm']); ?>
					                    		</div>
					                    		<button class="btn btn-sm btn-primary" id="btnLoad2" type="button">Load data</button>
				                    		</form>
				                    		<br>
				                    		<div class="panel panel-primary">
				                                <div class="panel-heading">Data set B</div>
				                                <div class="panel-body">
				                                    <div id="handson2" style="height: 400px; width: auto; overflow: hidden;"></div>
				                                </div>
				                            </div>
				                    	</div>
			                    	</div>
			                    </div>
			                    <div role="tabpanel" class="tab-pane" id="newData">
			                    	<div class="row">
			                    		<div class="col-lg-12">
				                        	<br>
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
				                            	<div class="panel-heading">New connected data</div>
				                                <div class="panel-body">
				                                    <div id="handsonNew" style="height: 400px; width: auto; overflow: hidden;"></div>
				                                </div>
				                            </div>
				                        </div>
			                        </div>
			                    </div>
			                </div>
		                </div>
		                
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
var dataSets = {'dataSet1': {data: [], fields: []}, 'dataSet2': {data: [], fields: []}, 'resultData': {data: [], fields: []}};

var handson1 = new Handsontable(document.getElementById('handson1'), {
    data: [],
    minSpareRows: 0,
    rowHeaders: true,
    colHeaders: [],
    contextMenu: false
    });

var handson2 = new Handsontable(document.getElementById('handson2'), {
    data: [],
    minSpareRows: 0,
    rowHeaders: true,
    colHeaders: [],
    contextMenu: false
    });

var handsonNew = new Handsontable(document.getElementById('handsonNew'), {
    data: [],
    minSpareRows: 0,
    rowHeaders: true,
    colHeaders: [],
    contextMenu: false
    });

var handsonTables = {'handson1': handson1, 'handson2': handson2, 'handsonNew': handsonNew};

$('#btnLoad1').click(loadData);
$('#btnLoad2').click(loadData);

$('#btnConnect').click(connectData);

function loadData(event)
{
	var target = $(event.target);
	var targetID = target.attr('id');
	var targetNum = targetID.substr(-1, 1);
	
	var fileID = $('#selectFiles' + targetNum).val();

	target.attr('disabled', 'disabled');
	target.html('Loading...');
	
	$.ajax({
        method: "POST",
        url: "/Files/ajax_get_file",
        data: {'file_id': fileID}
    })
    .done(function(result) {
        var resultObj = $.parseJSON(result);
        dataSets['dataSet' + targetNum]['data'] = $.parseJSON(resultObj.file_content.content);
        dataSets['dataSet' + targetNum]['fields'] = resultObj.file_fields;
    	handsonTables['handson' + targetNum].updateSettings({data: $.parseJSON(resultObj.file_content.content), colHeaders: _.pluck(resultObj.file_fields, 'name')});

    	target.removeAttr('disabled');
    	target.html('Load data');
    });
}

function connectData(event)
{
	var left = dataSets['dataSet1'];
	var right = dataSets['dataSet2'];

	if (left.data.length == 0)
	{
		alert('Data set A is empty, please load data first.');
		return;
	}

	if (right.data.length == 0)
	{
		alert('Data set B is empty, please load data first.');
		return;
	}

	$('#tabNewData').tab('show');
	var connectType = $('#joinType').val();
	var resultData = {};

	switch (connectType)
	{
		case 'cartesian': resultData = cartesianProduct(left, right); break;
		case 'left': resultData = leftJoin(left, right); break;
		case 'inner': resultData = innerJoin(left, right); break;
		default: alert('Unknown connection type.');
	}
	
	dataSets['resultData']['data'] = resultData.data;
    dataSets['resultData']['fields'] = resultData.fields;
	handsonTables['handsonNew'].updateSettings({data: resultData.data, colHeaders: _.pluck(resultData.fields, 'name')});
}

function cartesianProduct(left, right)
{
	var result = {'data': [], 'fields': []};

	_.forEach(left.data, function(leftDataRow) {
		_.forEach(right.data, function(rightDataRow) {
			var mergedRow = _.merge(leftDataRow, rightDataRow);
			result.data.push(_.clone(mergedRow, true));
		});
	});

	var combinedFields = _.union(left.fields, right.fields);

	_.forEach(combinedFields, function(field, key) {
		var f = {'indx': key + 1, 'name': field['name'], 'type': field['type']};
		result.fields.push(f);
	});

	return result;
}

function leftJoin(leftData, rightData)
{
	alert('This function is under development.');
}

function innerJoin(leftData, rightData)
{
	alert('This function is under development.');
}

$("#btnSave").click(function() {
	var thisButton = $(this);
	thisButton.attr('disabled', 'disabled');
	
    var fileName = $("#dataSetName").val();
    
    if (fileName == "")
    {
        alert("Please enter a name for this summary data");
        thisButton.removeAttr('disabled');
        return;
    }
    
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
                    url: "/Files/client_save_data",
                    timeout: 30000,
                    data: {"fileName": fileName, "fileFields": JSON.stringify(dataSets['resultData']['fields']), "fileContent": JSON.stringify(dataSets['resultData']['data'])}
                })
                    .done(function(result) {
                        if (result == "0")
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
});
</script>
















