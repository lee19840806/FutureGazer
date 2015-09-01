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
                    <form>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="panel panel-primary">
                                            <div class="panel-heading">Segmentation variables</div>
                                            <div class="panel-body">
                                                <p>Define segments by selecting one or more variables</p>
                                                <?= $this->Form->select('groupVariables', $fields['Fields'], ['multiple' => 'checkbox']) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="panel panel-primary">
                                            <div class="panel-heading">Origination, charge off amount and date variables</div>
                                            <div class="panel-body">
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-lg-12">
                                <p><label for="buildCurves">Click "Build maturation curves" if you are ready</label></p>
                                <button id="buildCurves" type="submit" class="btn btn-sm btn-success">
                                    <strong>Build maturation curves</strong>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>