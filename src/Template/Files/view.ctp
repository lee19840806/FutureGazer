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
                    <div class="panel panel-primary">
                        <div class="panel-heading"><?= json_decode($file)->FileName ?></div>
                        <div class="panel-body">
                            <div id="data"></div>
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
var file = $.parseJSON('<?= $file ?>');

var container = document.getElementById('data');
var handsonTable = new Handsontable(container, {
    data: file.Content,
    minSpareRows: 0,
    rowHeaders: true,
    colHeaders: file.Fields,
    contextMenu: false
    });
</script>