<link href="/css/handsontable.full.min.css" rel="stylesheet">
<script src="/js/handsontable.full.min.js"></script>
<script src="/js/lodash.min.js"></script>
<script src="/js/Sortable.min.js"></script>
<script src="/js/moment.min.js"></script>
<script src="/js/ht.js"></script>
<div class="container-fluid" style="padding-left: 40px; padding-right: 40px;">
    <div class="row">
        <div class="col-lg-2">
            <?= $this->element('left_menu') ?>
        </div>
        <div class="col-lg-10">
            <div class="row">
                <div class="col-lg-12">
                    <h4><strong>Summarize data</strong></h4>
                    <hr>
                    <div class="row" id="divConfig" style="display: none;">
                    	<div class="col-lg-3">
							<div class="panel panel-primary">
                                <div class="panel-heading">Fields (drag from here)</div>
                                <div class="panel-body">
	                                <div class="row">
	                                    <?= $this->Html->nestedList($fields, ['id' => 'fields', 'style' => 'min-height: 10px; padding-bottom: 10px;']); ?>
	                                </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                        	<div class="panel panel-primary">
                                <div class="panel-heading">Group by (drop here)</div>
                                <div class="panel-body">
                                	<div class="row">
                                    	<ul id="groupBy" style="min-height: 10px; padding-bottom: 10px;"></ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                        	<div class="panel panel-primary">
                                <div class="panel-heading">Sum values (drop here)</div>
                                <div class="panel-body">
                                	<div class="row">
                                    	<ul id="summary" style="min-height: 10px; padding-bottom: 10px;"></ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                        	<div class="panel panel-primary">
                                <div class="panel-heading">
                                	Calculated fields&nbsp;&nbsp;
                                	<button style="line-height: 0.7;" id="addCalculatedField" type="button" class="btn btn-xs btn-info" data-toggle="modal" data-target="#modalAddCalcField">
                                    	<strong> Add + </strong>
                                	</button></div>
                                <div class="panel-body">
                                	<div class="row">
	                                    <ul id="calculated" style="min-height: 10px; padding-bottom: 10px;"></ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row" id="divMaturationCurves" style="display: none;">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <button id="saveCurves" type="button" class="btn btn-sm btn-info">
                                    <strong>Save summary data</strong>
                                </button>
                            </div>
                            <div class="form-group">
                                <input type="text" id="curveName" class="form-control" placeholder="Name for summary data" required>
                            </div>
                            <div class="panel panel-info">
                            	<div class="panel-heading">Summary result</div>
                                <div class="panel-body">
                                    <div id="dataMaturationCurves" style="height: 400px; width: auto; overflow: hidden;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-lg-12">
                            <div id="loading" class="alert alert-success" role="alert">
                                <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
                                <span class="sr-only">Loading:</span>
                                Loading data, please wait...
                            </div>
                            <div class="panel panel-primary">
                                <div class="panel-heading">Raw data - <?= $file['name'] ?></div>
                                <div class="panel-body">
                                    <div id="data" style="height: 400px; width: auto; overflow: hidden;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <br>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAddCalcField" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Add a calculated field</h4>
      </div>
      <div class="modal-body" id="modalBody">
      	<div class="row">
	      	<div class="col-lg-12">
		      	<form class="form-inline">
			      	<div class="form-group">
			        	<input class="form-control input-sm" id="calcFieldName" type="text" placeholder="Field name">
			        	<label for="exampleInputName2">=</label>
					</div>
					<div class="form-group">
						<select class="form-control input-sm" id="operand1">
							<option>1</option>
							<option>2</option>
						</select>
					</div>
					<div class="form-group">
						<select class="form-control input-sm" id="operator">
							<option value="add">+</option>
							<option value="subtract">-</option>
							<option value="multiply">&times;</option>
							<option value="divide">&divide;</option>
						</select>
					</div>
					<div class="form-group">
						<select class="form-control input-sm" id="operand2">
							<option>1</option>
							<option>2</option>
						</select>
					</div>
				</form>
			</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-primary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-sm btn-info" id="modalButtonSave">Save</button>
      </div>
    </div>
  </div>
</div>

<style>
#calculated {}

#calculated li {
	position: relative;
}

#calculated i {
	-webkit-transition: opacity .2s;
	transition: opacity .2s;
	opacity: 0;
	cursor: pointer;
	color: #c00;
	top: 0px;
	left: 10px;
 	position: relative;
	font-style: normal;
}

#calculated li:hover i {
	opacity: 1;
}
</style>

<script>
var content = $.parseJSON('<?= $file['file_content']['content'] ?>');
var fields = $.parseJSON('<?= $fieldsJSON ?>');
var fileID = <?= $file['id'] ?>;
var mCurves = {};

var sortableFields = Sortable.create(document.getElementById('fields'), {
	group: 'fields',
	sort: false,
	ghostClass: 'bg-primary'});

var sortableGroupBy = Sortable.create(document.getElementById('groupBy'), {
	group: 'fields',
	ghostClass: 'bg-primary',
	onSort: function (event) {
		updatePivotTable();
		}});

var sortableSummary = Sortable.create(document.getElementById('summary'), {
	group: 'fields',
	ghostClass: 'bg-primary',
	onSort: function (event) {
		updatePivotTable();
		}});

var sortableCalculated = Sortable.create(document.getElementById('calculated'), {
	group: 'calc',
	ghostClass: 'bg-primary',
	filter: '.item-remove',
	onFilter: function (event) {
		event.item.parentNode.removeChild(event.item);
		updatePivotTable();
		},
	onSort: function (event) {
		updatePivotTable();
		}});

function updatePivotTable()
{
	var groupBy = [];
	var aggrObj = {count: [], sum: [], average: []};
	var calcFields = {add: [], subtract: [], multiply: [], divide: []};
	
	$.each($('#groupBy > li'), function (index, value) {
		groupBy.push($(value).html());
	});

	$.each($('#summary > li'), function (index, value) {
		aggrObj.sum.push($(value).html());
	});
	
	$.each($('#calculated > li'), function (index, value) {
		var operation = $.parseJSON($(value).children('input').val());

		switch (operation.operator)
		{
			case 'add':
				calcFields.add.push({'fieldName': operation.fieldName, 'operand1': operation.operand1, 'operand2': operation.operand2});
				break;
			case 'subtract':
				calcFields.subtract.push({'fieldName': operation.fieldName, 'operand1': operation.operand1, 'operand2': operation.operand2});
				break;
			case 'multiply':
				calcFields.multiply.push({'fieldName': operation.fieldName, 'operand1': operation.operand1, 'operand2': operation.operand2});
				break;
			case 'divide':
				calcFields.divide.push({'fieldName': operation.fieldName, 'operand1': operation.operand1, 'operand2': operation.operand2});
				break;
		}
	});
	
	mCurves = aggregate(content, fields, groupBy, aggrObj, calcFields);

	_.forEach(mCurves.fields, function(obj) {
        obj['file_id'] = fileID;
        });

	handsonTableMaturationCurves.updateTable(mCurves.content, mCurves.fields);
}

$('#modalAddCalcField').on('show.bs.modal', function(event) {
	$('#operand1').html('');
	$('#operand2').html('');

	$.each($('#groupBy > li'), function (index, value) {
		$('#operand1').append('<option value="' + $(value).html() + '">' + $(value).html() + '</option>');
		$('#operand2').append('<option value="' + $(value).html() + '">' + $(value).html() + '</option>');
	});

	$.each($('#summary > li'), function (index, value) {
		$('#operand1').append('<option value="' + $(value).html() + '">' + $(value).html() + '</option>');
		$('#operand2').append('<option value="' + $(value).html() + '">' + $(value).html() + '</option>');
	});
});

$('#modalButtonSave').click(function(event) {
	var calcFieldName = $('#calcFieldName').val();

	if (calcFieldName == "")
	{
		alert('Please fill in the field name.');
		return;
	}

	var operand1 = $('#operand1').val();
	var operator = $('#operator').val();
	var operand2 = $('#operand2').val();
	
	if (operand1 == undefined || operand2 == undefined)
	{
		alert('Please at least add one field into "Sum values".');
		return;
	}
	
	var operation = {'fieldName': calcFieldName,
		'operand1': operand1,
		'operator': operator,
		'operand2': operand2
		};
	
	var input = $('<input>').attr('type', 'hidden').val(JSON.stringify(operation));
	var li = $('<li>').html(calcFieldName).append('<i class="item-remove">✖</i>').append(input);
	$('#calculated').append(li);
	
	$('#modalAddCalcField').modal('hide');
	updatePivotTable();

	
	$('#calcFieldName').val('');
	$('#operand1').html('');
	$('#operator').val('add');
	$('#operand2').html('');
});

var myTable = new MyHandsonTable('data');
myTable.updateTable(content, fields);

$("#loading").remove();
$("#divConfig").slideDown();
$("#divMaturationCurves").slideDown();
$("#divButtonBuild").slideDown();

var handsonTableMaturationCurves = new MyHandsonTable('dataMaturationCurves');

function aggregate(collection, originalFields, groupVariables, aggregationFields, calculatedFields)
{
    var fields = [];
    var result = {};
    
    var groupByResult = _.groupBy(collection, function(obj) {
        return _.map(groupVariables, function(variable) { return obj[variable]; });
        });

    _.forEach(groupVariables, function(obj) {
        fields.push({
            'indx': fields.length + 1,
            'name': obj,
            'type': _.find(originalFields, {'name': obj})['type'],
            'format': _.find(originalFields, {'name': obj})['format']});
        });

    var aggregateResult = _.map(groupByResult, function(obj) {
        var row = {};
        
        _.forEach(groupVariables, function(variable) {
            row[variable] = obj[0][variable];
            });

        _.forEach(aggregationFields.sum, function(n) {
            row[n] = _.reduce(obj, function(total, single) {
                var converted = (single[n] == "") ? 0 : single[n];
                return total + converted;
                }, 0);
            });
        
        return row;
        });

    _.forEach(aggregationFields.sum, function(n) {
        fields.push({
            'indx': fields.length + 1,
			'name': n,
			'type': _.find(originalFields, {'name': n})['type'],
			'format': _.find(originalFields, {'name': n})['format']});
        });

    _.forEach(calculatedFields.add, function(obj) {
        _.forEach(aggregateResult, function(n) {
            n[obj['fieldName']] = n[obj['operand1']] + n[obj['operand2']];
            });

        fields.push({'indx': fields.length + 1, 'name': obj['fieldName'], 'type': 'numeric', 'format': '0.00'});
        });
    
    _.forEach(calculatedFields.divide, function(obj) {
        _.forEach(aggregateResult, function(n) {
            n[obj['fieldName']] = n[obj['operand1']] / n[obj['operand2']];
            });

        fields.push({'indx': fields.length + 1, 'name': obj['fieldName'], 'type': 'numeric', 'format': '0.00%'});
        });

    var sortedResult = _.sortByAll(aggregateResult, groupVariables);

    result.fields = fields;
    result.content = sortedResult;

    return result;
}

$("#saveCurves").click(function() {
	var thisButton = $(this);
	thisButton.attr('disabled', 'disabled');
	
    var fileName = $("#curveName").val();
    
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
                    data: {"fileName": fileName, "fileFields": JSON.stringify(mCurves.fields), "fileContent": JSON.stringify(mCurves.content)}
                })
                    .done(function(result) {
                        if (result == "0")
                        {
                            alert("An error has occured when trying to save maturation curve data. Please try again.");
                            thisButton.removeAttr('disabled');
                        }
                        else
                        {
                            alert("Summary data has been saved. Go to 'Manage my data' -> 'List my data' to view the data.");
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










