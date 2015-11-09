<link href="/css/handsontable.full.min.css" rel="stylesheet">
<link href="/css/bootstrap-datepicker.standalone.min.css" rel="stylesheet">
<script src="/js/handsontable.full.min.js"></script>
<script src="/js/lodash.min.js"></script>
<script src="/js/bootstrap-datepicker.min.js"></script>
<div class="container-fluid" style="padding-left: 40px; padding-right: 40px;">
    <div class="row">
        <div class="col-lg-2">
            <?= $this->element('left_menu') ?>
        </div>
        <div class="col-lg-10">
            <div class="row">
                <div class="col-lg-12">
                    <h4><strong>Generate date series</strong></h4>
                    <hr>
                    <div class="row">
	                    <div class="col-lg-4">
	                    	<form>
	                    		<div class="form-group">
	                    			<label for="startDate">Start and end date</label>
				                    <div class="input-daterange input-group" id="datepicker">
									    <input type="text" class="input-sm form-control" id="startDate" name="start" />
									    <span class="input-group-addon">to</span>
									    <input type="text" class="input-sm form-control" id="endDate" name="end" />
									</div>
								</div>
							</form>
						</div>
						<div class="col-lg-2">
	                    	<form>
	                    		<div class="form-group">
	                    			<label for="frequency">Series frequency</label>
				                    <select class="form-control input-sm" id="frequency">
										<option value="daily">Daily</option>
										<option value="monthly" selected="selected">Monthly</option>
										<option value="quarterly">Quarterly</option>
										<option value="annually">Annually</option>
									</select>
								</div>
							</form>
						</div>
						<div class="col-lg-2">
	                    	<form>
	                    		<div class="form-group">
	                    			<label for="alignment">Date alignment</label>
				                    <select class="form-control input-sm" id="alignment">
										<option value="start">Start</option>
										<option value="end" selected="selected">End</option>
									</select>
								</div>
							</form>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<button class="btn btn-sm btn-info" id="generate"><strong>Generate date series</strong></button>
						</div>
					</div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$('#datepicker').datepicker({
	keyboardNavigation: false,
    autoclose: true
});

$('#generate').click(function() {
	alert('generating date series');
});
</script>
















