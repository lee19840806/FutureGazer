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
                    		Variables selection
                    	</div>
                    </div>
                    <hr>
                    <div class="row">
                    	<div class="col-lg-6">
                    		<form class="form-inline">
                    			<div class="form-group">
		                    		<label for="files1">Select data set as A&nbsp;&nbsp;</label>
		                    		<?= $this->Form->select('selectFiles1', $fileNames, ['id' => 'selectFiles1', 'class' => 'form-control input-sm']); ?>
	                    		</div>
	                    		<button class="btn btn-sm btn-primary" id="btnLoad1" type="button">Load data A</button>
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
		                    		<label for="files2">Select data set as B&nbsp;&nbsp;</label>
		                    		<?= $this->Form->select('selectFiles2', $fileNames, ['id' => 'selectFiles2', 'class' => 'form-control input-sm']); ?>
	                    		</div>
	                    		<button class="btn btn-sm btn-primary" id="btnLoad2" type="button">Load data B</button>
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
            </div>
        </div>
    </div>
</div>
<script>
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

$('#btnLoad1').click(loadData);
$('#btnLoad2').click(loadData);

function loadData(event)
{
	targetID = $(event.target).attr('id');
	targetNum = targetID.substr(-1, 1);
	
	fileID = $('#selectFiles' + targetNum).val();
	
	$.ajax({
        method: "POST",
        url: "/Files/ajax_get_file",
        data: {'file_id': fileID}
    })
    .done(function(result) {
		var a = 1;
    });
}
</script>
















