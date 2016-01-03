<link href="/css/handsontable.full.min.css" rel="stylesheet">
<script src="/js/handsontable.full.min.js"></script>
<script src="/js/lodash.min.js"></script>
<script src="/js/moment.min.js"></script>
<div class="container-fluid" style="padding-left: 40px; padding-right: 40px;">
    <div class="row">
        <div class="col-lg-2">
            <?= $this->element('left_menu') ?>
        </div>
        <div class="col-lg-10">
            <div class="row">
                <div class="col-lg-12">
                    <h4><strong>View file</strong></h4>
                    <hr>
                    <?= $this->Flash->render() ?>
                    <div id="loading" class="alert alert-success" role="alert">
                        <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
                        <span class="sr-only">Loading:</span>
                        Loading data, please wait...
                    </div>
                    <div class="panel panel-primary">
                        <div class="panel-heading"><?= $fileName ?></div>
                        <div class="panel-body">
                            <div id="data" style="height: 700px; width: auto; overflow: hidden;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<br>
<br>
<br>
<br>
<script>
var content = $.parseJSON('<?= $file['file_content']['content'] ?>');
var fileFields = $.parseJSON('<?= $fileFields ?>');
$("#loading").remove();

var handsonNew = new Handsontable(document.getElementById('data'), {
	data: [],
    minSpareRows: 0,
    rowHeaders: true,
    colHeaders: true,
    readOnly: true,
    contextMenu: false
    });

var autoColumnSizePlugin = handsonNew.getPlugin('autoColumnSize');
autoColumnSizePlugin.enablePlugin();

updateHandsonTable(content, fileFields);

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
</script>






















