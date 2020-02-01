// Functionality for the mini clickable toolbars on the left side.
var boxHeight = 0;
function miniMenuDisplay(val) {
	boxHeight = 0;
	if(document.getElementById(val+"box").style.display == "") {
		closeBox(val+"box");
		setcookie(val+"box",'hide');
	} else {

		var elems = new Array();
		var tmptags = new Array();
		tmptags = document.getElementById('calmenu').getElementsByTagName('div');
		var i=0;
		var j=0;
		var boxState = 'closed';
		
		while(i < tmptags.length) {
			if(tmptags[i].id) {
				if(/\w+box$/.test(tmptags[i].id)) {
					elems[j] = tmptags[i].id;
					if(document.getElementById(elems[j]).style.display == "") {
						boxState = "open";
						document.getElementById(val+"box").style.height = "0px";
						document.getElementById(val+"box").style.display = "";
						switchBox(elems[j],val+"box");
						break;
					}
					j++;
				}
			}
			i++;
		}

		var i=0;
		while(i < elems.length) {
			if(boxState == "closed") { 
				if(elems[i] == val+"box") { document.getElementById(elems[i]).style.display = ""; }
				openBox(val+"box");
			}
			setcookie(elems[i],'hide');
			i++;
		}

		setcookie(val+"box",'show');

	}
}

function openBox(val) {
	if(boxHeight < 400) {
		boxHeight += 20;
		document.getElementById(val).style.height = boxHeight + "px";
		setTimeout("openBox('"+val+"')",20);	
	}
}

function closeBox(val) {
	if(boxHeight < 400) {
		boxHeight += 20;
		document.getElementById(val).style.height = parseInt(400 - boxHeight) + "px";
		setTimeout("closeBox('"+val+"')",10);	
	} else if(boxHeight >= 400) {
		document.getElementById(val).style.display = "none";
	}
}

function switchBox(val1,val2) {
	if(boxHeight < 400) {
//		alert(val1 +" - "+ val2 +" - "+ trans +" - "+ boxHeight);
		boxHeight += 20;

		document.getElementById(val1).style.height = parseInt(400 - boxHeight) + "px";
		document.getElementById(val2).style.height = boxHeight + "px";

		setTimeout("switchBox('"+val1+"','"+val2+"')",10);	
	} else if(boxHeight >= 400) {
			document.getElementById(val1).style.display = "none";
	}
}


// Setting / updating cookie variables in JS
function setcookie(name, value, expires, path, domain, secure) { 
	document.cookie= name + "=" + escape(value) + 
		((expires)? "; expires=" + expires.toGMTString() : "") + 
		((path)? "; path=" + path : "") + 
		((domain)? "; domain=" + domain : "") + 
		((secure)? "; secure" : ""); 
}


