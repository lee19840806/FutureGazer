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
                        <input type="hidden" name="fileID" value="<?= $fields['fileID'] ?>">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="panel panel-primary">
                                            <div class="panel-heading">Segmentation variables</div>
                                            <div class="panel-body">
                                                <p>Define segments by selecting one or more variables</p>
                                                <?= $this->Form->select('segmentVariables', $fields['Fields'], ['multiple' => 'checkbox']) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="panel panel-primary">
                                            <div class="panel-heading">Maturation curve variables</div>
                                            <div class="panel-body">
                                                <p>Select the variable which represents <strong>origination amount</strong></p>
                                                <?= $this->Form->select('origination', $fields['Fields']) ?>
                                                <hr>
                                                <p>Select the variable which represents <strong>charge off amount</strong></p>
                                                <?= $this->Form->select('chargeOff', $fields['Fields']) ?>
                                                <hr>
                                                <p>Select the variable which represents <strong>months on book</strong></p>
                                                <?= $this->Form->select('MoB', $fields['Fields']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-lg-12">
                                <p><strong>Click "Build maturation curves" if you are ready</strong></p>
                                <button id="buildCurves" type="submit" class="btn btn-sm btn-success">
                                    <strong>Build maturation curves</strong>
                                </button>
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