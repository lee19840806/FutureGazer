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
                    <div id="success" class="alert alert-success" role="alert" style="display: none;">
                        <span class="glyphicon glyphicon-ok-circle" aria-hidden="true"></span>
                        <span class="sr-only">Success:</span>
                        The maturation curves have been saved. You can view it from "Manage my data" in the left menu bar.
                    </div>
                    <div id="error" class="alert alert-warning" role="alert" style="display: none;">
                        <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                        <span class="sr-only">Error:</span>
                        An error has occured while trying to save the curves. Please try again later.
                    </div>
                    <div id="loading" class="alert alert-success" role="alert">
                        <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
                        <span class="sr-only">Loading:</span>
                        Loading data, please wait...
                    </div>
                    <div>
                        <form class="form-inline">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control input-sm" id="inputName" placeholder="Data set name">
                            </div>
                            <div class="form-group">&nbsp;&nbsp;&nbsp;&nbsp;</div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <input type="email" class="form-control input-sm" id="inputDescription" placeholder="Data set description">
                            </div>
                            <div class="form-group">&nbsp;&nbsp;&nbsp;&nbsp;</div>
                            <button id="btnSaveData" class="btn btn-sm btn-success"><strong>Save for later use</strong></button>
                        </form>
                    </div>
                    <hr>
                    <div id="legend"></div>
                    <div style="width:100%"><canvas id="myChart" width="900" height="350"></canvas></div>
                    <hr>
                    <div class="panel panel-primary">
                        <div class="panel-heading">Maturation Curves Data</div>
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
var m = $.parseJSON('<?= $maturation ?>');
var headers = $.parseJSON('<?= $columnHeaders ?>');

var numberOfMaturationPoints = m.curves.length;
var numberOfSegmentVariables = m.segment.length;

function generateRGBA()
{
    return "rgba(" + parseInt(Math.random()*255) + ","  + parseInt(Math.random()*255) + "," + parseInt(Math.random()*255) + ",1)";
}

var chartRawData = new Array();

for (var i = 0; i < numberOfMaturationPoints; i++)
{
    var segmentString = "";
    
    for (var j = 0; j < numberOfSegmentVariables; j++)
    {
        (j == numberOfSegmentVariables - 1) ? segmentString += m.curves[i][m.segment[j]] : segmentString += m.curves[i][m.segment[j]] + ", ";
    }

    chartRawData.push({segment: segmentString, MoB: m.curves[i][m["mob"]], charge_off_rate: m.curves[i]["charge_off_rate"]});
}

var uniqueSegments = _.uniq(_.pluck(chartRawData, "segment"), true);
var chartDataSets = new Array();
var xAxisLabels;

_.each(uniqueSegments, function(element) {
    xAxisLabels = _.pluck(_.where(chartRawData, {segment: element}), "MoB");
    var color = generateRGBA();
    
    chartDataSets.push({
        label: element,
        fillColor: "rgba(0,0,0,0)",
        strokeColor: color,
        pointColor: color,
        pointStrokeColor: "#fff",
        pointHighlightFill: "#fff",
        pointHighlightStroke: color,
        data: _.pluck(_.where(chartRawData, {segment: element}), "charge_off_rate")
    });
});

Chart.defaults.global.animation = false;
var ctx = $("#myChart").get(0).getContext("2d");

var data = {
    labels: xAxisLabels,
    datasets: chartDataSets
};

var myLineChart = new Chart(ctx).Line(data, {bezierCurve: false, responsive: true,
    legendTemplate : "<ul><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].strokeColor%>\">"
        + "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> <%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>"});
$("#legend").html(myLineChart.generateLegend());

var container = document.getElementById('data');
var handsonTable = new Handsontable(container, {
    data: m.curves,
    minSpareRows: 0,
    rowHeaders: true,
    colHeaders: headers,
    contextMenu: false
    });

$("#loading").remove();

$("#btnSaveData").click(function(event) {
    event.preventDefault();

    if ($("#inputName").val().length == 0)
    {
        alert("Please enter a name for the maturation curves data set.");
        return;
    }

    if ($("#inputDescription").val().length == 0)
    {
        alert("Please enter a description for the maturation curves data set.");
        return;
    }

    $("#btnSaveData").attr("disabled", "disabled");

    $.ajax({
        method: "POST",
        url: "/MaturationCurves/saveMaturation",
        data: {Fields: <?= $columnHeaders ?>, Content: <?= $maturation ?>}
    })
    .done(function() {
        $("#success").hide();
        $("#error").hide();
        $("#success").show();
    })
    .error(function() {
        $("#success").hide();
        $("#error").hide();
        $("#error").show();
    })
    .always(function() {
        $("#btnSaveData").removeAttr("disabled");
    });
});
</script>














