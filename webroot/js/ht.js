function MyHandsonTable(target)
{
	this.table = new Handsontable(document.getElementById(target), {
		data: this.data,
	    minSpareRows: this.minSpareRows,
	    rowHeaders: this.rowHeaders,
	    colHeaders: this.colHeaders,
	    readOnly: this.readOnly,
	    contextMenu: this.contextMenu
	    });
	
	this.autoColumnSizePlugin = this.table.getPlugin('autoColumnSize');
	this.autoColumnSizePlugin.enablePlugin();
}

MyHandsonTable.prototype.table = null;

MyHandsonTable.prototype.data = [];
MyHandsonTable.prototype.minSpareRows = 0;
MyHandsonTable.prototype.rowHeaders = true;
MyHandsonTable.prototype.colHeaders = true;
MyHandsonTable.prototype.readOnly = true;
MyHandsonTable.prototype.contextMenu = false;

MyHandsonTable.prototype.autoColumnSizePlugin = null;

MyHandsonTable.prototype.recalculateAllColumnsWidth = function()
{
	this.autoColumnSizePlugin.recalculateAllColumnsWidth();
}

MyHandsonTable.prototype.updateTable = function(data, fields)
{
	columnSetting = _.map(fields, function(field) {
		if (field.type == 'date')
		{
			return {data: field.name, type: field.type, renderer: function(instance, td, row, col, prop, value, cellProperties)
				{
					if (value == '')
					{
						td.innerHTML = '';
						return td;
					}
					else
					{
						td.innerHTML = moment(value).format('YYYY-MM-DD');
						return td;
					}
				}
			};
		}
		else
		{
			return {data: field.name, type: field.type, format: field.format};
		}
	});
	
	this.table.updateSettings({data: data, colHeaders: _.pluck(fields, 'name'), columns: columnSetting});
	this.recalculateAllColumnsWidth();
	this.table.render();
}

MyHandsonTable.prototype.clearTable = function()
{
	this.table.updateSettings({data: [], columns: []});
}









