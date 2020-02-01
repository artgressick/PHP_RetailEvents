/* GENERAL -------------------------------------------------------------------*/

function addEventToObject(obj, evt, func) {
	var oldhandler = obj[evt];
	obj[evt] = (typeof obj[evt] != 'function') ? func : function(){oldhandler();func();};
}

function ajaxRequest(url,func,obj) {
	if (window.XMLHttpRequest) {var req = new XMLHttpRequest();}
	else if (window.ActiveXObject) { try {req = new ActiveXObject("Msxml2.XMLHTTP") || new ActiveXObject("Microsoft.XMLHTTP");}catch(e) {}} //moved catch to ||
	else { //if no xhr, show standard include
		document.getElementById('locBlock').style.display = "none";
		document.getElementById('findastore').style.display = "block"; 
	}
	if (req) {
		if (func) { req.onreadystatechange = function() {func(req,obj);}}
		req.open('GET',url,true);
		req.setRequestHeader('X-Requested-With','XMLHttpRequest');
		req.setRequestHeader('If-Modified-Since','Wed, 15 Nov 1995 00:00:00 GMT');
		if (req.overrideMimeType) req.overrideMimeType("text/xml");
		req.send(null);
	}
	return false;
}

function getElementTextNS(prefix, local, parentElem, index) {
    var result = "";
    if (prefix && isIE) {result = parentElem.getElementsByTagName(prefix + ":" + local)[index];}
    else {result = parentElem.getElementsByTagName(local)[index];}
    if (result) {
        if (result.childNodes.length > 1) {return result.childNodes[1].nodeValue;} 
        else {return result.firstChild.nodeValue;}
    } else {
        return "n/a";
    }
}

/* MECHANICS -----------------------------------------------------------------*/

var storeLocator = {
	// INITIALIZE --------------------------------------------------------------
	indexURL: 'http://www.apple.com/main/rss/retail/index_mini.xml',
	storeURL: "",
	storeName: "",
	locationName: "",
	stores: [],
	loaded: false,
	Init: function() {
		var r = storeLocator;
		document.getElementById('locBlock').style.display = "block";
		document.getElementById('findastore').style.display = "none"; 
		ajaxRequest(r.indexURL,r.XMLFetchResponse);

	},
	// BUILDERS ----------------------------------------------------------------
	BuildStoreMenu: function(chosenOption) {
		var r = storeLocator;
		var storeMenu = document.getElementById("storeMenu");
		if (r.storeName) storeMenu.style.opacity = 1.0;
		storeMenu.options.length = 0;
		for(i=0;i<r.stores.length;i++) {
			if(r.stores[i].state == chosenOption) {
				storeMenu.options[storeMenu.options.length] = new Option(r.stores[i].name,r.stores[i].link,false,false);
			}
		}
		function compareText (option1, option2) { return option1.text < option2.text ? -1 :	option1.text > option2.text ? 1 : 0; }
		function sortSelect (select, compareFunction) {
			if (!compareFunction) compareFunction = compareText;
			var options = new Array (select.options.length);
			for (var i = 0; i < options.length; i++) options[i] = new Option (select.options[i].text, select.options[i].value, select.options[i].defaultSelected, select.options[i].selected);
			options.sort(compareFunction);
			select.options.length = 0;
			select.options[0] = new Option("Select a Store","",false,false);
			for (var i = 0; i < options.length; i++) select.options[select.options.length] = options[i];
		}
		sortSelect(storeMenu);
		storeMenu.selectedIndex = 0;
	},
	BuildBack: function(states) {
		var r = storeLocator;
		var locMenu = document.getElementById('locationMenu'); 
		//clear the location menu out
		locMenu.options.length = 0;
		locMenu.options[0] = new Option("Select a State","",false,false);
		locMenu.options[0].disabled;
		for (var j=0; j<states.length;j++) {
			var s = states[j].getAttribute('name');
			if(s.length>0) locMenu.options[j+1] = new Option(r.ConvertState(s),s,false,false);
		}
		addEventToObject(locMenu,'onchange',storeLocator.ProcessPopup);
		//if(!r.storeName) addEventToObject(locMenu,'onclick', function() {if(!storeLocator.storeName);storeLocator.ProcessPopup(this)});
		var storeMenu = document.getElementById('storeMenu');
		addEventToObject(storeMenu,'onchange',storeLocator.ProcessPopup);
		r.BuildStoreMenu(r.locationName);
	},
	// EVENT HANDLERS ----------------------------------------------------------
	ProcessPopup: function(ev) { 
		if (!ev) ev = window.event;
		var elem = (window.event) ? window.event.srcElement : ev.target;
		if (!elem.id || elem.id == "undefined") {elem = elem.parentNode;}
		//if (elem.nodeType == 3 || elem.nodeType == 1) {elem = elem.parentNode;}
		var r = storeLocator;
		if (elem.id == "locationMenu") {
			var chosenOption = elem.options[elem.selectedIndex].value; 
			r.locationName = chosenOption;
			var storeMenu = document.getElementById("storeMenu"); 
			if (r.locationName.length>0) storeMenu.style.opacity = 1.0;
			else storeMenu.style.opacity = 0.3;
			storeLocator.BuildStoreMenu(chosenOption);
		} else if (elem.id == "storeMenu" && r.locationName.length>0) {
			var chosenOption = elem.options[elem.selectedIndex].value; 
		//	if (chosenOption.length>0) window.location.href = 'http://genius.apple.com/customer/?store='+chosenOption;
			if (chosenOption.length>0) window.location.href = chosenOption;
		}
		(ev.stopPropagation) ? ev.stopPropagation() : ev.cancelBubble = true;
		(ev.preventDefault) ? ev.preventDefault() : ev.returnValue = false;
	},
	// PROCESSORS --------------------------------------------------------------
	XMLFetchResponse: function(req)	{
		var r = storeLocator;
		if (req.readyState == 4) {
			if (req.status == 200) {
				r.ParseBackXML(req);
			} else {
				//disable menus
				document.getElementById('locationMenu').style.opacity = 0.3;
				document.getElementById('storeMenu').style.opacity = 0.3;
			}
		}
	},
	ParseBackXML: function(req) {
		var r = storeLocator;
		var storesTemp = req.responseXML.documentElement.getElementsByTagName('country')[0].getElementsByTagName('store');
		//clear out r.stores;
		r.stores = [];
		for (var i=0;i<storesTemp.length;i++) {
			r.stores[r.stores.length] = {
				name: storesTemp[i].getElementsByTagName('name')[0].firstChild.nodeValue,
				id: storesTemp[i].getElementsByTagName('appleid')[0].firstChild.nodeValue,
				link: storesTemp[i].getElementsByTagName('link')[0].firstChild.nodeValue,
				state: storesTemp[i].parentNode.getAttribute('name')}
		}
		function charOrdA(a, b) {
			a = parseInt(a.name.toLowerCase()); b =  parseInt(b.name.toLowerCase());
			if (a>b) return 1;
			if (a<b) return -1;
			return 0;
		}
		r.stores.sort(charOrdA);
		for (var i=0;i<r.stores.length;i++) {r.stores[r.stores[i].name] = r.stores[i];}
		var states = req.responseXML.documentElement.getElementsByTagName('state');
		r.BuildBack(states);
	},
	ConvertState: function(state) {
		state = (state=="AK") ? "Alaska" : (state=="AL") ? "Alabama" : (state=="AZ") ? "Arizona" : (state=="AR") ? "Arkansas" : (state=="CA") ? "California" : (state=="CO") ? "Colorado" : (state=="CT") ? "Connecticut" : (state=="DE") ? "Delaware" : (state=="DE") ? "District of Columbia" : (state=="FL") ? "Florida" : (state=="GA") ? "Georgia" : (state=="HI") ? "Hawaii" : (state=="ID") ? "Idaho" : (state=="IL") ? "Illinois" : (state=="IN") ? "Indiana" : (state=="IA") ? "Iowa" : (state=="KS") ? "Kansas" : (state=="KY") ? "Kentucky" : (state=="LA") ? "Louisiana" : (state=="ME") ? "Maine" : (state=="MD") ? "Maryland" : (state=="MA") ? "Massachusetts" : (state=="MI") ? "Michigan" : (state=="MN") ? "Minnesota" : (state=="MS") ? "Mississippi" : (state=="MO") ? "Missouri" : (state=="MT") ? "Montana" : (state=="NE") ? "Nebraska" : (state=="NV") ? "Nevada" : (state=="NH") ? "New Hampshire" : (state=="NJ") ? "New Jersey" : (state=="NM") ? "New Mexico" : (state=="NY") ? "New York" : (state=="NC") ? "North Carolina" : (state=="ND") ? "North Dakota" : (state=="OH") ? "Ohio" : (state=="OK") ? "Oklahoma" : (state=="OR") ? "Oregon" : (state=="PA") ? "Pennsylvania" : (state=="RI") ? "Rhode Island" : (state=="SC") ? "South Carolina" : (state=="SD") ? "South Dakota" : (state=="TN") ? "Tennessee" : (state=="TX") ? "Texas" : (state=="UT") ? "Utah" : (state=="VT") ? "Vermont" : (state=="VA") ? "Virginia" : (state=="WA") ? "Washington" : (state=="WV") ? "West Virginia" : (state=="WI") ? "Wisconsin" : (state=="WY") ? "Wyoming" : "";
		return state;
	}
}
