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
                    <form enctype="multipart/form-data" action="/Files/submit" method="POST">
                        <div class="form-group">
                            <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
                        </div>
                        <div class="form-group">
                            <label for="userfile">Step 1. Click "Browse" button and select a CSV file: </label>
                            <input id="userFileUpload" name="userfile" type="file" class="file-loading" accept=".csv" required="true">
                        </div>
                        <br>
                        <hr>
                        <br>
                        <div id="step2" class="form-group">
                            <p><label>Step 2. Config the data types of each column: </label></p>
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
                                            <select name="dataType['id']">
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
                        <div id="step3"  class="form-group">
                            <p><label for="userfile">Step 3. Click "Upload data" to upload the data to the platform</label></p>
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
    $("#step2").hide();
    $("#step3").hide();
    
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
            	    console.log(results);

            	    $("#step2").slideDown('fast');
            	    $("#step3").slideDown('fast');
            	    tbody.empty();
            	    
            	    $.each(results.meta.fields, function(index, value) {
                	    var columnName = $('<td>').html(value);
                	    var dataType = $('<td>').append($('<select>').attr('name', "dataType['" + value + "']")).children()
                	       .append($('<option>').attr('value', 'string').attr('selected', 'selected').html('String'))
                	       .append($('<option>').attr('value', 'number').html('Number'));
             	       
            	        tbody.append($('<tr>').append(columnName).append(dataType));
            	    });
            	}
            });
        }
    });

    $('#userFileUpload').on('fileclear', function(event) {
        $("#step2").slideUp('fast');
        $("#step3").slideUp('fast');
        tbody.empty();
    });
});
</script>
















