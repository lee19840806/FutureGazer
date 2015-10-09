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
                        <div class="panel-heading"><?= $file['name'] ?></div>
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
var fields = $.parseJSON('<?= $fields ?>')

var container = document.getElementById('data');
var handsonTable = new Handsontable(container, {
    data: content,
    minSpareRows: 0,
    rowHeaders: true,
    colHeaders: _.pluck(fields, 'name'),
    contextMenu: false
    });

$("#loading").remove();
</script>