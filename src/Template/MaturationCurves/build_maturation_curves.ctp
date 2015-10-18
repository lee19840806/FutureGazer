<div class="container-fluid" style="padding-left: 40px; padding-right: 40px;">
    <div class="row">
        <div class="col-lg-2">
            <?= $this->element('left_menu') ?>
        </div>
        <div class="col-lg-10">
            <div class="row">
                <div class="col-lg-12">
                    <h4><strong>Build maturation curves</strong></h4>
                    <hr>
                    <?= $this->Flash->render() ?>
                    <form id="buildMaturationForm" action="/MaturationCurves/calculate" method="POST">
                        <input type="hidden" name="fileID" value="<?= $file['id'] ?>">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="panel panel-primary">
                                            <div class="panel-heading">Segmentation variables</div>
                                            <div class="panel-body">
                                                <p>Define segments by selecting one or more variables</p>
                                                <?= $this->Form->select('segmentVariables', $fields, ['multiple' => 'checkbox']) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="panel panel-primary">
                                            <div class="panel-heading">Maturation curve variables</div>
                                            <div class="panel-body">
                                                <p>Select the variable which represents <strong>origination amount</strong></p>
                                                <?= $this->Form->select('origination', $fields, ['disabled' => $disabledItems]) ?>
                                                <hr>
                                                <p>Select the variable which represents <strong>charge off amount</strong></p>
                                                <?= $this->Form->select('chargeOff', $fields, ['disabled' => $disabledItems]) ?>
                                                <hr>
                                                <p>Select the variable which represents <strong>months on book</strong></p>
                                                <?= $this->Form->select('MoB', $fields, ['disabled' => $disabledItems]) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <p><strong>Click "Build maturation curves" if you are ready</strong></p>
                                <button id="buildCurves" type="button" class="btn btn-sm btn-primary">
                                    <strong>Build maturation curves</strong>
                                </button>
                            </div>
                        </div>
                        <hr>
                        <div id="divMaturationCurves" class="row" style="display: none;">
                            <div class="col-lg-12">
                                <button id="saveCurves" type="button" class="btn btn-sm btn-success">
                                    <strong>Save maturation curves for later use</strong>
                                </button>
                                <br>
                                <br>
                                <div class="panel panel-success">
                                <div class="panel-heading">Maturation curves</div>
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
                                    <div class="panel-heading"><?= $file['name'] ?></div>
                                    <div class="panel-body">
                                        <div id="data" style="height: 400px; width: auto; overflow: hidden;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <br>
                    <br>
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

var container = document.getElementById('data');
var handsonTable = new Handsontable(container, {
    data: content,
    minSpareRows: 0,
    rowHeaders: true,
    colHeaders: _.pluck(fields, 'name'),
    contextMenu: false
    });

$("#loading").remove();

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

    var mCurves = aggregate(content, fields, groupVariables, aggrFields, calcFields);

    _.forEach(mCurves.fields, function(obj) {
        obj['file_id'] = fileID;
        });

    $("#divMaturationCurves").slideUp();
    
    var containerMaturationCurves = document.getElementById('dataMaturationCurves');
    var handsonTableMaturationCurves = new Handsontable(containerMaturationCurves, {
        data: mCurves.content,
        minSpareRows: 0,
        rowHeaders: true,
        colHeaders: _.pluck(mCurves.fields, 'name'),
        contextMenu: false
        });

    $("#divMaturationCurves").slideDown();
    
    var a = 1;
});
</script>










