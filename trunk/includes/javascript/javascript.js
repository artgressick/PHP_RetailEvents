function RowHighlight(row)
{
	lastClass = getStyleClass(row);
	changeStyleClass(row, lastClass + ' Highlight');
}
function RowUnHighlight(row)
{
	changeStyleClass(row, lastClass);
}

function getStyleClass(item)
{
	return(item.className);
}

function changeStyleClass(item, className)
{
	item.className=className;
}

function defaultOnLoad()
{
	var field = document.getElementById('DocLoadFocus');
	if(field) field.focus();
}

//
// Association list entry
//  Manipulates DOM to add and remove associated items which are saved in a separate association table.
//

function list_add(table_id, field_id, field_chr, item_id, item_chr)
{
	var idlist = document.getElementById(field_id);
	if(!idlist) {
		alert("Couldn't find the id field.");
		return;
	}

	// if the item is already in the field, don't re-add it.
	if(csv_exists(idlist.value, item_id)) {
		alert('This item is already in the list.');
		return;
	}

	idlist.value = csv_add(idlist.value, item_id);

	var chrlist = document.getElementById(field_chr);
	if(!chrlist) {
		alert("Couldn't find the chr field.");
		return;
	}

	chrlist.value = csv_add(chrlist.value, item_chr.replace(',', '&#44;'));

	var table = document.getElementById(table_id);
	if(!table) {
		alert("Couldn't find the table.");
		return;
	}

	var tbody = table.getElementsByTagName("TBODY")[0];
	var row = document.createElement("TR");

	var td = document.createElement("TD");
	td.appendChild(document.createTextNode(item_chr));
	row.appendChild(td);

	var td = document.createElement("TD");
	td.className='alignright';
	td.innerHTML= "<input type='button' value='Remove' onclick=\"list_remove('" + table_id + "', '" + field_id + "', '" + field_chr + "', " + item_id + ", this);\" />";
	row.appendChild(td);

	tbody.appendChild(row);

	table_set_alternating(table_id);

	return(row);
}

function table_set_alternating(table_id)
{
	var table = document.getElementById(table_id);
	var tbody = table.getElementsByTagName("TBODY")[0];

	// as stupid as this looks (I could have used for and getElementsByTagName) it's because Safari doesn't work properly (apparently)
	var count = 1;
	var row = tbody.firstChild;
	do {
		if(row.nodeName == "TR") {
			row.className = (++count%2?'even':'odd');
		}

		row = row.nextSibling;
	} while(row);

}

function list_remove(table_id, field_id, field_chr, item_id, button)
{
	var idlist = document.getElementById(field_id);
	if(!idlist) {
		alert("Couldn't find the id field.");
		return;
	}

	offset = csv_search(idlist.value, item_id);

	idlist.value = csv_remove(idlist.value, item_id);

	var chrlist = document.getElementById(field_chr);
	if(!chrlist) {
		alert("Couldn't find the chr field.");
		return;
	}

	chrlist.value = csv_removeat(chrlist.value, offset);

	tablerow = button.parentNode.parentNode;
	tablerow.parentNode.removeChild(tablerow);

	table_set_alternating(table_id);
}

function csv_add(string, item)
{
	if(string.length == 0) {
		string = item;
	} else {
		string = string + "," + item;
	}
	return(string);
}

function csv_remove(string, item)
{
	var items = string.split(",");
	items.splice(array_search(item, items), 1);
	return(items.toString());
}

function csv_removeat(string, offset)
{
	var items = string.split(",");
	items.splice(offset, 1);
	return(items.toString());
}

function csv_exists(string, item)
{
	var items = string.split(",");

	return(array_search(item, items) != -1);
}

function csv_search(string, item)
{
	var items = string.split(",");
	return(array_search(item, items));
}

function array_search(needle, haystack)
{
	for(i in haystack) {
		if(haystack[i] == needle) return(i);
	}
	return(-1);
}
