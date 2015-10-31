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
                                    <?= $this->Html->nestedList($fields, ['id' => 'fields', 'style' => 'min-height: 20px;']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                        	<div class="panel panel-primary">
                                <div class="panel-heading">Group by (drop here)</div>
                                <div class="panel-body">
                                    <ul id="groupBy" style="min-height: 20px;"></ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                        	<div class="panel panel-primary">
                                <div class="panel-heading">Sum values (drop here)</div>
                                <div class="panel-body">
                                    <ul id="summary" style="min-height: 20px;"></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row" id="divMaturationCurves" style="display: yes;">
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
	
	var mCurves = aggregate(content, fields, groupBy, aggrObj, calcFields);

	_.forEach(mCurves.fields, function(obj) {
        obj['file_id'] = fileID;
        });

	handsonTableMaturationCurves.updateSettings({data: mCurves.content, colHeaders: _.pluck(mCurves.fields, 'name')});
}




var handsonTable = new Handsontable(document.getElementById('data'), {
    data: content,
    minSpareRows: 0,
    rowHeaders: true,
    colHeaders: _.pluck(fields, 'name'),
    contextMenu: false
    });

$("#loading").remove();
$("#divConfig").slideDown();
$("#divButtonBuild").slideDown();

var handsonTableMaturationCurves = new Handsontable(document.getElementById('dataMaturationCurves'), {
    data: [],
    minSpareRows: 0,
    rowHeaders: true,
    colHeaders: [],
    contextMenu: false
    });

function aggregate(collection, originalFields, groupVariables, aggregationFields, calculatedFields)
{
    var fields = [];
    var result = {};
    
    var groupByResult = _.groupBy(collection, function(obj) {
        return _.map(groupVariables, function(variable) { return obj[variable]; });
        });

    _.forEach(groupVariables, function(obj) {
        fields.push({'indx': fields.length + 1, 'name': obj, 'type': _.find(originalFields, {'name': obj})['type']});
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
        fields.push({'indx': fields.length + 1, 'name': n, 'type': _.find(originalFields, {'name': n})['type']});
        });

    _.forEach(calculatedFields.divide, function(obj) {
        _.forEach(aggregateResult, function(n) {
            n[obj['fieldName']] = n[obj['numerator']] / n[obj['denominator']];
            });

        fields.push({'indx': fields.length + 1, 'name': obj['fieldName'], 'type': 'number'});
        });

    var sortedResult = _.sortByAll(aggregateResult, groupVariables);

    result.fields = fields;
    result.content = sortedResult;

    return result;
}

$("#buildCurves").click(function() {
    var fileID = <?= $file['id'] ?>;
    
    var segmentVariableIndexes = $.map($("[name='segmentVariables[]']"), function(element, index) { return (element.checked == true) ? index : undefined; });
    
    var originationIndex = parseInt($("[name='origination']").val());
    var chargeOffIndex = parseInt($("[name='chargeOff']").val());
    var mobIndex = parseInt($("[name='MoB']").val());
    
    var originationVariable = fields[originationIndex]['name'];
    var chargeOffVariable = fields[chargeOffIndex]['name'];
    var mobVariable = fields[mobIndex]['name'];

    var groupVariables = _.map(segmentVariableIndexes, function(value) { return fields[value]['name']; });
    groupVariables.push(fields[mobIndex]['name']);

    var aggrFields = {count: [], sum: [], average: []};
    aggrFields.sum.push(chargeOffVariable);
    aggrFields.sum.push(originationVariable);

    var calcFields = {add: [], subtract: [], multiply: [], divide: []};
    calcFields.divide.push({'fieldName': 'charge_off_rate', 'numerator': chargeOffVariable, 'denominator': originationVariable});

    mCurves = aggregate(content, fields, groupVariables, aggrFields, calcFields);

    _.forEach(mCurves.fields, function(obj) {
        obj['file_id'] = fileID;
        });

    $("#divMaturationCurves").slideUp();
    handsonTableMaturationCurves.updateSettings({data: mCurves.content, colHeaders: _.pluck(mCurves.fields, 'name')});
    $("#divMaturationCurves").slideDown();
});

$("#saveCurves").click(function() {
    var fileName = $("#curveName").val();
    
    if (fileName == "")
    {
        alert("Please enter a name for this maturation curve data");
        return;
    }
    
    $.ajax({
        method: "GET",
        url: "/Files/name_available",
        data: {'fileName': fileName}
    })
        .done(function(data) {
            if (data == "0")
            {
                alert("File name '" + fileName + "' existed, please use another name.");
            }
            else
            {
                var m = {"fileName": fileName, "fileFields": mCurves.fields, "fileContent": mCurves.content};

                $.ajax({
                    method: "POST",
                    url: "/Files/client_save_data",
                    data: {"fileName": fileName, "fileFields": JSON.stringify(mCurves.fields), "fileContent": JSON.stringify(mCurves.content)}
                })
                    .done(function(result) {
                        if (result == "0")
                        {
                            alert("An error has occured when trying to save maturation curve data. Please try again.");
                        }
                        else
                        {
                            alert("Maturation curve data has been saved. Go to 'Manage my data' -> 'List my data' to view the data.");
                        }
                        })
                    .fail(function(jqXHR, textStatus, errorThrown) {
                        var a = 1;
                        });
            }
            });
});
</script>










