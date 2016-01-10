<link href="/css/handsontable.full.min.css" rel="stylesheet">
<link href="/css/bootstrap-datepicker.standalone.min.css" rel="stylesheet">
<script src="/js/handsontable.full.min.js"></script>
<script src="/js/lodash.min.js"></script>
<script src="/js/bootstrap-datepicker.min.js"></script>
<script src="/js/moment.min.js"></script>
<script src="/js/ht.js"></script>
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
										<option value="days">Daily</option>
										<option value="weeks">Weekly</option>
										<option value="months" selected="selected">Monthly</option>
										<option value="quarters">Quarterly</option>
										<option value="years">Annually</option>
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
						<div class="col-lg-2">
	                    	<form>
	                    		<div class="form-group">
	                    			<label for="seriesName">Name of series</label>
				                    <input class="form-control input-sm" id="seriesName" type="text" value="month_end" maxlength="32" required />
								</div>
							</form>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<button class="btn btn-sm btn-info" id="generate"><strong>Generate date series</strong></button>
						</div>
					</div>
					<hr>
					<div class="row">
                        <div class="col-lg-12">
                        	<div class="form-group">
	                        	<button id="save" type="button" class="btn btn-sm btn-info">
	                                <strong>Save date series</strong>
	                            </button>
                            </div>
                            <div class="form-group">
                            	<input type="text" id="dataSetName" class="form-control" placeholder="Name for this data set" required>
                            </div>
                            <div class="panel panel-primary" style="z-index: -2;">
                                <div class="panel-heading" style="z-index: -2;">Date series</div>
                                <div class="panel-body" style="z-index: -2;">
                                    <div id="data" style="height: 400px; width: auto; overflow: hidden; z-index: 0;"></div>
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
var dates = [];
var fields = [];

var myTable = new MyHandsonTable('data');
/*
var handsonTable = new Handsontable(document.getElementById('data'), {
    data: [],
    minSpareRows: 0,
    rowHeaders: true,
    colHeaders: [],
    contextMenu: false
    });
*/
$('#datepicker').datepicker({
	keyboardNavigation: false,
    autoclose: true
});

$('#generate').click(function() {
	dates = [];
	
	var startDate = moment($('#startDate').datepicker('getDate'));
	var endDate = moment($('#endDate').datepicker('getDate'));
	var frequency = $('#frequency').val();
	var alignment = $('#alignment').val();
	var seriesName = $('#seriesName').val();
	
	if (!startDate.isValid() || !endDate.isValid())
	{
		alert('Please pick a start date and an end date.');
		return;
	}

	if (seriesName == '')
	{
		alert('Please enter the name of series.');
		return;
	}

	var alignmentOption = '';

	switch (frequency)
	{
		case 'days': alignmentOption = 'day'; break;
		case 'weeks': alignmentOption = 'week'; break;
		case 'months': alignmentOption = 'month'; break;
		case 'quarters': alignmentOption = 'quarter'; break;
		case 'years': alignmentOption = 'year'; break;
	}

	var currentDate = startDate;

	while (currentDate <= endDate)
	{
		var d = currentDate;

		if (alignment == 'start')
		{
			d = d.startOf(alignmentOption);
		}
		else
		{
			d = d.endOf(alignmentOption);
		}

		var dateObj = {};
		dateObj[seriesName] = d.format();
		dates.push(dateObj);
		currentDate.add(1, frequency);
	}

	fields.push({'indx': 1, 'name': seriesName, 'type': 'date', 'format': 'YYYY-MM-DD'});

	myTable.updateTable(dates, fields);
});

$("#save").click(function() {
	var thisButton = $(this);
	thisButton.attr('disabled', 'disabled');
	
    var fileName = $("#dataSetName").val();
    
    if (fileName == "")
    {
        alert("Please enter a name for this data");
        thisButton.removeAttr('disabled');
        return;
    }
    
    $.ajax({
        method: "POST",
        url: "/Files/name_available",
        data: {'fileName': fileName}
    })
        .done(function(data) {
            if (data == "0")
            {
                alert("File name '" + fileName + "' existed, please use another name.");
                thisButton.removeAttr('disabled');
            }
            else
            {
                $.ajax({
                    method: "POST",
                    url: "/Files/client_save_data",
                    data: {"fileName": fileName, "fileFields": JSON.stringify(fields), "fileContent": JSON.stringify(dates)}
                })
                    .done(function(result) {
                        if (result == "0")
                        {
                            alert("An error has occured when trying to save this data. Please try again.");
                            thisButton.removeAttr('disabled');
                        }
                        else
                        {
                            alert("Data has been saved. Go to 'Manage my data' -> 'List my data' to view the data.");
                            thisButton.removeAttr('disabled');
                        }
                        })
                    .fail(function(jqXHR, textStatus, errorThrown) {
                    	thisButton.removeAttr('disabled');
                        });
            }
            });
});
</script>
















