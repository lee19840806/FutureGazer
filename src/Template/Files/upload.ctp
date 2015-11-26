<link href="/css/fileinput.min.css" rel="stylesheet">
<script src="/js/fileinput.min.js"></script>
<script src="/js/lodash.min.js"></script>
<script src="/js/papaparse.min.js"></script>
<script src="/js/moment.min.js"></script>
<div class="container-fluid" style="padding-left: 40px; padding-right: 40px;">
    <div class="row">
        <div class="col-lg-2">
            <?= $this->element('left_menu') ?>
        </div>
        <div class="col-lg-10">
            <div class="row">
                <div class="col-lg-12">
                    <h4><strong>Upload data from a file</strong></h4>
                    <hr>
                    <?= $this->Flash->render() ?>
                    <form id="fileUploadForm" enctype="multipart/form-data" action="/Files/submitFile" method="POST">
                        <div class="form-group">
                            <input id="csvMeta" type="hidden" name="csvMeta" />
                            <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
                        </div>
                        <div class="form-group">
                            <label for="userfile">Step 1. Click "Browse" button and select a CSV file: </label>
                            <input id="userFileUpload" name="userfile" type="file" class="file-loading" accept=".csv" required="required">
                        </div>
                        <br>
                        <hr>
                        <br>
                        <div id="step2" class="form-group" style="display: none;">
                            <p><label>Step 2. Select data type for each column: </label></p>
                            <table class="table table-condensed table-striped table-hover">
                                <thead>
                                    <tr class="success">
                                        <th>Column Name</th>
                                        <th>Data Type</th>
                                    </tr>
                                </thead>
                                <tbody id="dataTypeSelection">
                                    <tr>
                                        <td>id</td>
                                        <td>
                                            <select name="dataType[id]">
                                                <option value="string" selected="selected">String</option>
                                                <option value="number">Number</option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <br>
                        <hr>
                        <br>
                        <div id="step3"  class="form-group" style="display: none;">
                            <p><label>Step 3. Click "Upload data" to upload the data to the platform</label></p>
                            <button id="submitBtn" type="submit" class="btn btn-sm btn-success" style="width: 150px;">
                                <strong>Upload Data</strong>
                            </button>
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
$(document).on('ready', function(){
    var tbody = $('#dataTypeSelection');
    tbody.empty();
    
    $("#submitBtn").click(function() {
        $("#fileUploadForm").submit();
        $(this).attr('disabled', 'disabled');
        $(this).html('Uploading...');
    });
    
    $("#userFileUpload").fileinput({
        browseClass: "btn btn-primary",
        showPreview: false,
        showUpload: false
    });

    $("#userFileUpload").change(function(){
        if (this.files[0] != undefined)
        {
            Papa.parse(this.files[0], {
                header: true,
                skipEmptyLines: true,
            	complete: function(results) {
            	    tbody.empty();
            	    $("#step2").slideDown('fast');
            	    $("#step3").slideDown('fast');
            	    $("#csvMeta").val(JSON.stringify(results.meta));
            	    
            	    _.forEach(results.meta.fields, function(fieldName, index) {
                	    var isDateTime = true;
                	    var isNumber = true;
                	    
            	        _.forEach(results.data, function(dataValue, dataIndex) {
                	        if ((isDateTime == true) && (moment(dataValue[fieldName], "YYYY-MM-DD", true).isValid() == false) && (dataValue[fieldName] != ""))
                	        {
                	        	isDateTime = false;
                	        }
                	        
            	        	if ((isNumber == true) && (!$.isNumeric(dataValue[fieldName])) && (dataValue[fieldName] != ""))
            	            {
            	                isNumber = false;
            	            }

            	            if (isDateTime == false && isNumber == false)
            	            {
								return false;
            	            }
            	        });
                	    
                	    var columnName = $('<td>').html(fieldName);
                	    var dataType;

                	    if (isDateTime)
                	    {
                	    	dataType = $('<td>').append($('<select class="form-control input-sm">').attr('name', "dataType[" + fieldName + "]")).children()
            	        		.append($('<option>').attr('value', 'string').attr('selected', 'selected').html('Date'))
                            	.append($('<option>').attr('value', 'string').html('String'))
                            	.append($('<option>').attr('value', 'number').html('Number'));
                	    }
                	    else if (isNumber)
                	    {
                	        dataType = $('<td>').append($('<select class="form-control input-sm">').attr('name', "dataType[" + fieldName + "]")).children()
                	        	.append($('<option>').attr('value', 'string').html('Date'))
                                .append($('<option>').attr('value', 'string').html('String'))
                                .append($('<option>').attr('value', 'number').attr('selected', 'selected').html('Number'));
                	    }
                	    else
                	    {
                	        dataType = $('<td>').append($('<select class="form-control input-sm">').attr('name', "dataType[" + fieldName + "]")).children()
                	        	.append($('<option>').attr('value', 'string').html('Date'))
                                .append($('<option>').attr('value', 'string').attr('selected', 'selected').html('String'))
                                .append($('<option>').attr('value', 'number').html('Number'));
                	    }
             	       
            	        tbody.append($('<tr>').append(columnName).append(dataType));
            	    });
            	}
            });
        }
    });

    $('#userFileUpload').on('fileclear', function(event) {
        $("#step2").slideUp('fast');
        $("#step3").slideUp('fast');
        $("#csvMeta").val("");
        tbody.empty();
    });
});
</script>
















