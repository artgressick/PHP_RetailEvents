function describe(x) {
	var firstCell = x.parentNode.parentNode.childNodes[0];
	var secondCell = x.parentNode.parentNode.childNodes[1];
	if (!x.parentNode.parentNode.childNodes[0].className) {
		firstCell = x.parentNode.parentNode.childNodes[1];
		secondCell = x.parentNode.parentNode.childNodes[3];
	}
	var description;
	for (i = 0; i < x.parentNode.childNodes.length; i++) {
		if (x.parentNode.childNodes[i].className == "sessiondescription") {
			description = x.parentNode.childNodes[i];
			break;
		}
	}
	if (description.style.display == 'block') {
		description.style.display = 'none';
		x.parentNode.parentNode.style.backgroundColor = "#fff";
		firstCell.className = 'start';
		secondCell.className = 'end';

	} else {
		description.style.display = 'block';
		x.parentNode.parentNode.style.backgroundColor = "#f9f9f9";
		categoryColor = description.childNodes[description.childNodes.length - 1].className;
		firstCell.className = categoryColor;
		firstCell.style.color = '#000';
		secondCell.className = categoryColor;
		secondCell.style.color = '#000';
	}
}

function highlightToday() {
	var today = new Date();
	var year = new String(today.getFullYear());
	var month = today.getMonth();
	if (++month < 10) {
		month = '0' + month.toString();
	}
	var day = today.getDate();
	if (day < 10) {
		day = '0' + day.toString();
	}
	var idName = new String('a' + year + month + day);
	document.getElementById(idName).getElementsByTagName("th")[0].style.backgroundColor = '#E7F4FF';
}
