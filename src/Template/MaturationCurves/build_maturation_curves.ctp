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
                                <button id="buildCurves" type="button" class="btn btn-sm btn-success">
                                    <strong>Build maturation curves</strong>
                                </button>
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

var container = document.getElementById('data');
var handsonTable = new Handsontable(container, {
    data: content,
    minSpareRows: 0,
    rowHeaders: true,
    colHeaders: _.pluck(fields, 'name'),
    contextMenu: false
    });

$("#loading").remove();

var list = [ { user_id: 301, alert_id: 199, deal_id: 32243 },
             { user_id: 301, alert_id: 200, deal_id: 32243 },
             { user_id: 301, alert_id: 200, deal_id: 107293 },
             { user_id: 301, alert_id: 200, deal_id: 277470 } ];

           var groups = _.groupBy(list, function(value){
               return value.user_id + '#' + value.alert_id;
           });

           var data = _.map(groups, function(group){
               return {
                   user_id: group[0].user_id,
                   alert_id: group[0].alert_id,
                   deals: _.pluck(group, 'deal_id')
               }
           });

$("#buildCurves").click(function() {
    var segmentVariableIndexes = $.map($("[name='segmentVariables[]']"), function(element, index) { return (element.checked == true) ? index : undefined; });
    
    var originationIndex = parseInt($("[name='origination']").val());
    var chargeOffIndex = parseInt($("[name='chargeOff']").val());
    var mobIndex = parseInt($("[name='MoB']").val());
    
    var originationVariable = fields[originationIndex]['name'];
    var chargeOffVariable = fields[chargeOffIndex]['name'];
    var mobVariable = fields[mobIndex]['name'];

    var groupVariables = _.map(segmentVariableIndexes, function(value) { return fields[value]['name']; });
    groupVariables.push(fields[mobIndex]['name']);

    var groupByResult = _.groupBy(content, function(obj) {
        return _.map(groupVariables, function(variable) { return obj[variable]; });
        });

    var aggregateResult = _.map(groupByResult, function(obj) {
        var p = {};
        
        _.forEach(groupVariables, function(variable) {
            p[variable] = obj[0][variable];
            });
        
        p[originationVariable] = _.reduce(obj, function(total, n) {
            var converted = (n[originationVariable] == "") ? 0 : n[originationVariable];
            return total + converted;
            }, 0);

        p[chargeOffVariable] = _.reduce(obj, function(total, n) {
            var converted = (n[chargeOffVariable] == "") ? 0 : n[chargeOffVariable];
            return total + converted;
            }, 0);

        p['charge_off_rate'] = p[chargeOffVariable] / p[originationVariable];

        return p;
        });

    var sortedResult = _.sortByAll(aggregateResult, groupVariables);
    
    var a = 1;
});
</script>










