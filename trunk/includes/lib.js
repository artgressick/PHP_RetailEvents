/* ************************************************************************** **
**      $(String)                                                             **
**      Description: Extension of getElementById method                       **
**      Arguments: String(required) - eg. 'myId'                              **
** ************************************************************************** */
function $() {
	var aElems = [];
	for (var i=0; i<arguments.length; i++) {
		var soElem = arguments[i];
		if (typeof soElem == 'string') soElem = document.getElementById(soElem);
		if (arguments.length == 1) return soElem;
		aElems.push(soElem);
	}
	return aElems;
}

/* ************************************************************************** **
**      $t(String,Object)                                                     **
**      Description: Reduction of getElementByTagName method                  **
**      Arguments: sTag(required) - String - eg. 'myTagName'                  **
**                 oObj(optional) - Object - eg. myHTMLElement                **
** ************************************************************************** */
function $t(sTag,oObj) {
	oObj = oObj || document;
	return oObj.getElementsByTagName(sTag);
}

/* ************************************************************************** **
**      $c(String,Object,String)                                              **
**      Description: GetElementsByClassName                                   **
**      Arguments: sClass(required) - String - eg. 'myClassName'              **
**                 oObj(optional) - Object - eg. myHTMLElement                **
**                 sTag(optional) - String - eg. 'myTagName'                  **
** ************************************************************************** */
function $c(sClass,oObj,sTag) {
	oObj = oObj || document;
	if (!oObj.length) { oObj = [oObj]; }
	var aElements = [];
	for(var i = 0; i<oObj.length; i++) {
		oEl = oObj[i];
		if(oEl.getElementsByTagName) {
			oObj.children = oEl.getElementsByTagName(sTag || '*');
			for (var j = 0; j<oObj.children.length; j++) {
				oObj.child = oObj.children[j];
				if(oObj.child.className&&(new RegExp('\\b'+sClass+'\\b').test(oObj.child.className))) {
					aElements.push(oObj.child);
				}
			}
		}
	}
	return aElements;
}

/* ************************************************************************** **
**      new HTMLObject(Object)                                                **
**      Description: Creates an HTMLObject object                             **
**      Arguments: oEl(required) - Object - eg. myHTMLElement                 **
** ************************************************************************** */
HTMLObject = function(oEl) {
	//if(!window.attachEvent) {return oEl;}
	for(property in HTMLObject) { oEl[property] = HTMLObject[property]; }
	return oEl;
}

/* ************************************************************************** **
**      Object.extend(HTMLObject,WhizBang.prototype)                          **
**      Description: Extends an HTMLObject with WhizBang properties           **
**      Arguments: none                                                       **
** ************************************************************************** */
WhizBang = function() { };

/* ************************************************************************** **
**      Object.extend(Object,Object)                                          **
**      Description: Extends objects with properties from other objects       **
**      Arguments: oDestination(required) - Object - eg. myHTMLObject         **
**                 oSource(required) - Object - eg. WhizBang.prototype        **
** ************************************************************************** */
Object.extend = function(oDestination,oSource) {
	for(property in oSource) { oDestination[property] = oSource[property]; }
	return oDestination;
}

/* ************************************************************************** **
**      new Class(String)                                                     **
**      Description: Creates a Class object                                   **
**      Arguments: String(required) - eg. 'myClassName'                       **
** ************************************************************************** */
var Class = function() {
	return function() { this.initialize.apply(this,arguments); }
}

/* ************************************************************************** **
**      addEventToObject(Object,String,Function)                              **
**      Description: Event addition                                           **
**      Arguments: oObj(required) - Object - eg. myHTMLObject                 **
**                 sEvt(required) - String - eg. 'onclick'                    **
**                 fFunc(required) - Function - eg. functionName              **
**                                                                            **
**      removeEventFromObject(Object,String)                                  **
**      Description: Event removal                                            **
**      Arguments: oObj(required) - Object - eg. myHTMLObject                 **
**                 sEvt(required) - String - eg. 'onclick'                    **
** ************************************************************************** */
function addEventToObject(oObj,sEvt,fFunc) {
	var oldhandler = oObj[sEvt];
	oObj[sEvt] = (typeof oObj[sEvt] != 'function') ? fFunc : function(){ oldhandler(); fFunc(); };
}

function removeEventFromObject(oObj,sEvt) {
	var oldhandler = oObj[sEvt];
	oObj[sEvt] = null;
}

/* ************************************************************************** **
**      isQTInstalled()                                                       **
**      Description: Checks to see if Quicktime is installed; returns true    **
**                   or false.                                                **
**      Arguments: none                                                       **
** ************************************************************************** */
function isQTInstalled() {
	var qtInstalled = false;
	qtObj = false;
	if(navigator.plugins && navigator.plugins.length) {
		for(var i=0; i < navigator.plugins.length; i++ ) {
         var plugin = navigator.plugins[i];
         if(plugin.name.indexOf("QuickTime") > -1) { qtInstalled = true; }
        }
	} else {
		execScript('on error resume next: qtObj = IsObject(CreateObject("QuickTimeCheckObject.QuickTimeCheck.1"))','VBScript');
		qtInstalled = qtObj;
	}
	return qtInstalled;
}

/* ************************************************************************** **
**      Array.push(Object)                                                    **
**      Description: Adds method for older unsupported browsers               **
**      Arguments: oElem(required) - Object - eg. 'myString'                  **
**                                                                            **
**      Array.shift()                                                         **
**      Description: Adds method for older unsupported browsers               **
**      Arguments: none                                                       **
** ************************************************************************** */
if (!Array.prototype.push) {
	Array.prototype.push = function(oElem) {
		this[this.length] = oElem;
		return this.length;
	}
}

if (!Array.prototype.shift) {
	Array.prototype.shift = function() {
		var response = this[0];
		for (var i=0; i < this.length-1; i++) { this[i] = this[i + 1]; }
		this.length--;
		return response;
	}
}

/* ************************************************************************** **
**      ajaxRequest(String,Function)                                          **
**      Description: Ajax                                                     **
**      Arguments: sUrl(required) - String - eg. '/myXMLFile.xml'             **
**                 fFunc(optional) - Function - eg. function() { myFunctions }**
** ************************************************************************** */
function ajaxRequest(sUrl,fFunc) {
	if (window.XMLHttpRequest) {
		var req = new XMLHttpRequest();
		if (fFunc) { req.onreadystatechange = function() { fFunc(req); } }
		req.open("GET", sUrl, true);
		if (req.setRequestHeader) {req.setRequestHeader('If-Modified-Since','Wed, 15 Nov 1995 00:00:00 GMT');}
		req.send(null);
	} else if (window.ActiveXObject) {
		isIE = true;
		try {var req = new ActiveXObject("Msxml2.XMLHTTP");}
		catch(e) {req = new ActiveXObject("Microsoft.XMLHTTP");}
		if (req) {
			if (fFunc) { req.onreadystatechange = function() { fFunc(req); } }
			req.open("GET", sUrl, true);
			req.send();
		}
	}
}

/* ************************************************************************** **
**      ahah(String,String,Function)                                          **
**      Description: Puts an XHTML page into an HTML page                     **
**      Arguments: sUrl(required) - String - eg. '/myXHTMLpage.html'          **
**                 sTarget(required) - String - eg. 'myElementId'             **
**                 fFunc(optional) - Function - eg. function() { myFunctions }**
**                                                                            **
**      ahahDone(String,Function)                                             **
**      Description: See above                                                **
**      Arguments: sTarget(required) - String - eg. 'myElementId'             **
**                 fFunc(optional) - Function - eg. function() { myFunctions }**
** ************************************************************************** */
function ahah(sUrl,sTarget,fFunc) {
	document.getElementById(sTarget).innerHTML = '<img src="http://images.apple.com/ilife/images/waitanimation.gif" alt="wait" width="20" height="20" border="0" id="wait">';
	if (window.XMLHttpRequest) {
		req = new XMLHttpRequest();
		req.onreadystatechange = 
			(fFunc) ? function(){ ahahDone(sTarget,fFunc); } : function() { ahahDone(sTarget); };
		req.open("GET", sUrl, true);
		req.send(null);
	} else if (window.ActiveXObject) {
		req = new ActiveXObject("Microsoft.XMLHTTP");
		if (req) {
			req.onreadystatechange = 
				(fFunc) ? function(){ ahahDone(sTarget,fFunc); } : function() { ahahDone(sTarget); };
			req.open("GET", sUrl, true);
			req.send();
		}
	}
}

function ahahDone(sTarget,fFunc) {
	if (req.readyState == 4) {
		if (req.status == 200 || req.status == 304) {
			results = req.responseText;
			document.getElementById(sTarget).innerHTML = results;
			if (fFunc) { fFunc(); }
		} else {
			document.getElementById(sTarget).innerHTML="ahah error:\n" + req.statusText;
		}
	}
}

/* ************************************************************************** **
**      bind(Object)                                                          **
**      Description: binds a function to a function or object                 **
**                   support for legacy bindWithArguments                     **
**      Arguments: oObj(required) - Object - eg. myObject                     **
** ************************************************************************** */
Function.prototype.bind = Function.prototype.bindWithArguments = function(oObj) {
	var __method = this;
	var args = [];
	for (var i=0,len=arguments.length-1; i<len; i++) { args[i] = arguments[i+1]; }
	return function() { __method.apply(oObj,args); };
};

/* ************************************************************************** **
**      ensureElementFromObject(Object,Boolean)                               **
**      Description: A simple IE fix                                          **
**      Arguments: oEl(required) - Object - eg. myHTMLElement                 **
**                 bAgain(optional) - Boolean - eg. true                      **
** ************************************************************************** */
function ensureElementFromObject(oEl,bAgain){
	if(!window.attachEvent) { return; } // if not IE6, return
	if(oEl.getAttribute('elementFromObject') != 'true' || bAgain === true) {
		for(property in Object) { oEl[property] = Object[property]; }
		oEl.setAttribute('elementFromObject','true');
	}
}

/* ************************************************************************** **
**      stopDefaultAction(Event)                                              **
**      Description: Stops default action of an event                         **
**      Arguments: ev(required) - Event - eg. ev                              **
** ************************************************************************** */
function stopDefaultAction(ev) {
	if(!ev) { ev = window.event; }
	(ev.stopPropagation) ? ev.stopPropagation() : ev.cancelBubble = true;
	(ev.preventDefault) ? ev.preventDefault() : ev.returnValue = false;
	return false;
}

/* ************************************************************************** **
**      getClickedLink(Event)                                                 **
**      Description: Gets the clicked anchor tag                              **
**      Arguments: ev(required) - Event - eg. ev                              **
** ************************************************************************** */
function getClickedLink(ev) {
	if(!ev) { ev = window.event; }
	var clickedLink = (window.event) ? window.event.srcElement : ev.target;
	while (!clickedLink.tagName || clickedLink.tagName.toLowerCase() != "a") clickedLink = clickedLink.parentNode;
	return clickedLink;
}

/* ************************************************************************** **
**      Object.getHeight()                                                    **
**      Description: Gets the height of an HTMLObject                         **
**      Arguments: none                                                       **
**                                                                            **
**      Object.getWidth()                                                     **
**      Description: Gets the width of an HTMLObject                          **
**      Arguments: none                                                       **
**                                                                            **
**      Object.getTop()                                                       **
**      Description: Gets the top value of an HTMLObject                      **
**      Arguments: none                                                       **
**                                                                            **
**      Object.getLeft()                                                      **
**      Description: Gets the left value of an HTMLObject                     **
**      Arguments: none                                                       **
**                                                                            **
**      Object.getInfo(String)                                                **
**      Description: Gets the style value of the passed string from the       **
**                   HTMLObject                                               **
**      Arguments: selector(required) - String - eg. 'top'                    **
**                                                                            **
**      Object.addClass(String)                                               **
**      Description: Adds a class to an HTMLObject                            **
**      Arguments: class_name(required) - String - eg. 'myClassName'          **
**                                                                            **
**      Object.removeClass(String)                                            **
**      Description: Removes a class from an HTMLObject                       **
**      Arguments: class_name(required) - String - eg. 'myClassName'          **
**                                                                            **
**      Object.hasClass(String)                                               **
**      Description: Checks to see if an HTMLObject has a certain class       **
**      Arguments: class_name(required) - String - eg. 'myClassName'          **
**                                                                            **
**      Object.addEvent(String,Function)                                      **
**      Description: Adds an event to an HTMLObject                           **
**      Arguments: evt(required) - String - eg. 'onclick'                     **
**                 func(required) - Function - eg. myFunctionName             **
**                                                                            **
**      Object.removeEvent(String)                                            **
**      Description: Removes an event from an HTMLObject                      **
**      Arguments: evt(required) - String - eg. 'onclick'                     **
** ************************************************************************** */
Object.extend(HTMLObject, {
	getHeight: function() { return this.offsetHeight; },
	getWidth: function() { return this.offsetWidth; },
	getTop: function() {
		var styleValue = 0;
		var obj = this;
		if(obj.offsetParent) {
			while(obj.offsetParent) {
				styleValue += obj.offsetTop;
				obj = obj.offsetParent;
			}
		} else if(obj.x) { styleValue += obj.y; }
		return styleValue;
	},
	getLeft: function() {
		var styleValue = 0;
		var obj = this;
		if(obj.offsetParent) {
			while(obj.offsetParent) {
				styleValue += obj.offsetLeft;
				obj = obj.offsetParent;
			}
		} else if(obj.x) { styleValue += obj.x; }
		return styleValue;
	},
	getInfo: function(selector) {
		var viewCSS = (typeof document.defaultView == 'function') ? document.defaultView() : document.defaultView;
		if(viewCSS && viewCSS.getComputedStyle) {
			var s = viewCSS.getComputedStyle(this,null);
			return s && s.getPropertyValue(selector);
		}
		return this.currentStyle && (this.currentStyle[selector] || null) || null;
	},
	addClass: function(class_name) {
		if(this.className !== '') { this.className += ' ' + class_name; }
		else { this.className = class_name; }
	},
	removeClass: function(class_name) {
		var oldClass = this.className;
		var re = new RegExp('\\s?'+class_name+'\\b');
		if(oldClass.indexOf(class_name) != -1) { this.className = oldClass.replace(re,''); }
	},
	hasClass: function(class_name) {
		var re = new RegExp('(^|\\s+)'+class_name+'(\\s+|$)');
		if(this.getAttributeNode("class") !== null) { return re.test(this.getAttributeNode("class").value); }
		else if(this.className) { return re.test(this.className); }
		else { return false; }
	},
	addEvent: function(evt,func) { addEventToObject(this,evt,func); },
	removeEvent: function(evt) { removeEventFromObject(this,evt); }
});

/* ************************************************************************** **
**      new AppleJAX()                                                        **
**                                                                            **
**      AppleJAX.ahah(String,String,Function)                                 **
**      Description: Puts XHTML page into an XHTML page                       **
**      Arguments: url(required) - String - eg. '/myXHTMLpage.html'           **
**                 target(required) - String - eg. 'myHTMLElementId'          **
**                 func(optional) - Function - eg. function() { myFunctions } **
**                                                                            **
**      AppleJAX.ahahdone(String,Function)                                    **
**      Description: Used with ahah. See above.                               **
**      Arguments: target(required) - String - eg. 'myHTMLElementId'          **
**                 func(optional) - Function - eg. function() { myFunctions } **
**                                                                            **
**      AppleJAX.getXML(String,Function)                                      **
**                                                                            **
**      AppleJAX.getXML2(String,Function)                                     **
**                                                                            **
**      AppleJAX.getXMLdone(Function)                                         **
**                                                                            **
**      AppleJAX.getXMLwithResults(String,Function)                           **
**                                                                            **
**      AppleJAX.getXMLdoneWithResults(XMLHttpRequest,Function)               **
**                                                                            **
** ************************************************************************** */
var AppleJAX = new Object();

Object.extend(AppleJAX, {
	ahah: function(url,target,func) {
		if(window.XMLHttpRequest) {
			req = new XMLHttpRequest();
			req.onreadystatechange =
				(func) ? function() { AppleJAX.ahahdone(target,func); } : function() { ahahDone(target); };
			req.open("GET", url, true);
			req.send(null);
		} else if(window.ActiveXObject) {
			req = new ActiveXObject("Microsoft.XMLHTTP");
			if(req) {
				req.onreadystatechange =
					(func) ? function() { AppleJAX.ahahdone(target,func) ;} : function() { ahahDone(target); };
				req.open("GET", url, true);
				req.send();
			}
		}
	},
	ahahdone: function(target,func) {
		if(req.readyState == 4) {
			if(req.status == 200 || req.status == 304) {
				results = req.responseText;
				$(target).innerHTML = results;
				if(func) { func(); }
			} else { $(target).innerHTML = 'ahah error:\n' + req.statusText; }
		}
	},
	getXML: function(url,func) {
		if(window.XMLHttpRequest) {
			req = new XMLHttpRequest();
			req.onreadystatechange = function() { AppleJAX.getXMLdone(func); };
			req.open("GET", url, true);
			req.send(null);
		} else if (window.ActiveXObject) {
			req = new ActiveXObject("Microsoft.XMLHTTP");
			if(req) {
				req.onreadystatechange = function() { AppleJAX.getXMLdone(func); };
				req.open("GET", url, true);
				req.send();
			}
		}
	},
	getXML2: function(url,func) {
		if(window.XMLHttpRequest) {
			req = new XMLHttpRequest();
			if(func) { req.onreadystatechange = function() { AppleJAX.getXMLdone(func); }; };
			req.open("GET", url, true);
			req.send(null);
		} else if(window.ActiveXObject) {
			req = new ActiveXObject("Microsoft.XMLHTTP");
			if(req) {
				if(func) { req.onreadystatechange = function() { AppleJAX.getXMLdone(func); }; };
				req.open("GET", url, true);
				req.send();
			}
		}
	},
	getXMLdone: function(func) {
		if(req.readyState == 4) {
			results = req.responseXML;
			if(func) { func(); }
		}
	},
	getXMLwithResults: function(url,func) {
		if(window.XMLHttpRequest) {
			req = new XMLHttpRequest();
			req.onreadystatechange = function() { AppleJAX.getXMLdoneWithResults(req,func); };
			req.open("GET", url, true);
			req.send(null);
		} else if(window.ActiveXObject) {
			var req = new ActiveXObject("Microsoft.XMLHTTP");
			if(req) {
				req.onreadystatechange = function() { AppleJAX.getXMLdoneWithResults(req,func); };
				req.open("GET", url, true);
				req.send();
			}
		}
	},
	getXMLdoneWithResults: function(req,func) {
		if(req.readyState == 4) {
			var results = req.responseXML;
			if(func) { func(results); }
		}
	}
});

/* ************************************************************************** **
**      Fade                                                                  **
** ************************************************************************** */
WhizBang.prototype.Fade = function(duration,startOpacity,endOpacity,interval,step,curve,func) {
	this.interval = interval;
	this.duration = duration;
	this.step = step;

	this.setOpacity = function(o) {
		this.style.opacity = (o / 101);
		this.style.MozOpacity = (o / 100);
		this.style.KhtmlOpacity = (o / 100);
		this.style.filter = "alpha(opacity=" + o + ")";
	};
	this.setOpacity(startOpacity);

	if(startOpacity == 0) { this.style.visibility = 'visible'; }

	if(Browser.isIE()) {
		if(this.id == 'newfeatureinfo') {
			this.style.zIndex = '100';
			var movieframe = document.getElementById('movieframe');
			movieframe.style.height = this.offsetHeight + 'px';
		} else if(!(this.id == 'sharebug')&&!(this.id == 'newfeatureinfo')&&!(this.id == 'gallery')) {
			this.style.height = this.offsetHeight+'px';
		}
		this.style.zoom = '1';
	}
	var delta = startOpacity;
	var newOpacity = startOpacity;
	this.changeOpacity = function(newOpacity,endOpacity) {
		var changeOpacityST = null;

		var stepDuration = Math.round(this.duration/this.step);
		this.duration -= stepDuration;
		this.step--;
		if(newOpacity < endOpacity) {
			if(newOpacity <= endOpacity) {
				delta = (this.step > 0) ? (endOpacity - newOpacity)/this.step : 0;
				newOpacity = Math.max(startOpacity, Math.min(newOpacity+delta, endOpacity));
				this.setOpacity(newOpacity);
				changeOpacityST = window.setTimeout(this.changeOpacity.bindWithArguments(this,newOpacity,endOpacity),stepDuration);
			}
		}else if(newOpacity > endOpacity) {
			if(newOpacity >= endOpacity) {
				delta = (this.step > 0) ? (newOpacity - endOpacity)/this.step : 0;
				newOpacity = Math.min(startOpacity, Math.max(newOpacity-delta, endOpacity));
				this.setOpacity(newOpacity);
				changeOpacityST = window.setTimeout(this.changeOpacity.bindWithArguments(this,newOpacity,endOpacity),stepDuration);
			}
		}else if (newOpacity == endOpacity) {
			this.setOpacity(newOpacity);
			clearTimeout(changeOpacityST);
			if(func) { func(); }
		}
	};
	this.changeOpacity(newOpacity,endOpacity);
	return true;
};

/* ************************************************************************** **
**      ScaleToHeight                                                         **
** ************************************************************************** */
WhizBang.prototype.ScaleToHeight = function(duration,startHeight,endHeight,step,func) {
	this.interval = 1;
	this.duration = duration;
	this.step = step;
	this.setHeight = function(h) {this.style.height = h+"px"; };

	var delta = startHeight;
	var newHeight = startHeight;
	var time = 0;

	this.changeHeight = function (newHeight,endHeight) {
		var changeHeightST = null;
		var stepDuration = Math.round(this.duration/this.step);
		this.duration -= stepDuration;
		if (newHeight < endHeight) {
			if(Browser.isSafari1()) {
				this.setHeight(endHeight);
			} else {
				delta = (this.step > 0) ? (endHeight - newHeight)/this.step : 0;
				newHeight = Math.max(startHeight, Math.min(newHeight+delta, endHeight));
				if(newHeight > (endHeight-1)) {newHeight = endHeight;}
				this.setHeight(newHeight);
				changeHeightST = window.setTimeout(this.changeHeight.bindWithArguments(this,newHeight,endHeight),stepDuration);
			}
		}
		else if (newHeight > endHeight) {
			if(Browser.isSafari1()) {
				this.setHeight(endHeight);
			} else {
				delta = (this.step > 0) ? (newHeight - endHeight)/this.step : 0;
				newHeight = Math.min(startHeight, Math.max(newHeight-delta, endHeight));
				if(newHeight < (endHeight+1)) {newHeight = endHeight;}
				this.setHeight(newHeight);
				changeHeightST = window.setTimeout(this.changeHeight.bindWithArguments(this,newHeight,endHeight),stepDuration);
			}
		} else if (newHeight == endHeight) {
			this.setHeight(endHeight);
			clearTimeout(changeHeightST);
			if (func) {func();}
		}
	};
	this.changeHeight(newHeight,endHeight);
};

/* ************************************************************************************** **
**      Scale Attribute                                                                   **
**        Parameters: attribute,unit,startValue,endValue,duration,framerate,percent,func  **
**          sAttribute(required): String - eg. 'height'                                   **
**          sUnit(required): String - eg. 'px'                                            **
**          iStartValue(required): Integer - eg. 0                                        **
**          iEndValue(required): Integer - eg. 0                                          **
**          iDuration(required): Integer - eg. 500                                        **
**          iFramerate(required): Integer - eg. 30                                        **
**          iPercent(optional): Integer - eg. 50                                          **
**          fFunc(optional): Function - eg. function() { blah = blah; }                   **
** ************************************************************************************** */
WhizBang.prototype.scaleAttrib = function(sAttribute,sUnit,iStartValue,iEndValue,iDuration,iFramerate,iPercent,fFunc) {
	this.attribute = sAttribute;
	this.unit = sUnit;
	this.startValue = iStartValue;
	this.endValue = iEndValue;
	this.duration = iDuration;
	this.framerate = iFramerate;
	this.percent = iPercent;
	this.delta = this.startValue;
	this.step = (this.duration/1000)*this.framerate;
	this.oncomplete = false;
	this.funcdone = false;
	if(this.percent == 100) { this.oncomplete = true; }
	this.funcpercent = Math.round((this.duration/this.step)*(this.percent/100));
	this.percentdone = 0;

	this.setAttrib = function(iVal) {this.style[this.attribute] = iVal+this.unit;};

	this.changeAttrib = function (iNewValue,iEndValue) {
		var me = this;
		var iStepDuration = Math.round(this.duration/this.step);
		this.duration -= iStepDuration;
		this.percentdone++;
		if((this.percentdone == this.funcpercent) && fFunc && this.oncomplete == false) {
			fFunc();
			this.funcdone = true;
		}
		if (iNewValue < iEndValue) {
			this.delta = (this.step > 0) ? (iEndValue - iNewValue)/this.step : 0;
			iNewValue = Math.max(this.startValue, Math.min(iNewValue+this.delta, iEndValue));
			if(iNewValue > (iEndValue-1)) {iNewValue = iEndValue;}
			this.setAttrib(iNewValue);
			me.changeAttribST = setTimeout(function(){me.changeAttrib(iNewValue,iEndValue);},iStepDuration);
		} else if (iNewValue > iEndValue) {
			this.delta = (this.step > 0) ? (iNewValue - iEndValue)/this.step : 0;
			iNewValue = Math.min(this.startValue, Math.max(iNewValue-this.delta, iEndValue));
			if(iNewValue < (iEndValue+1)) {iNewValue = iEndValue;}
			this.setAttrib(iNewValue);
			me.changeAttribST = setTimeout(function(){me.changeAttrib(iNewValue,iEndValue)},iStepDuration);
		} else if (iNewValue == iEndValue) {
			this.setAttrib(iEndValue);
			clearTimeout(me.changeAttribST);
			if(fFunc && (this.funcdone == false)) {fFunc();}
		}
	};

	this.changeAttrib(this.startValue,this.endValue);
	return true;
};

WhizBang.prototype.scaleNegAttrib = function(sAttribute,sUnit,iStartValue,iEndValue,iDuration,iFramerate,iPercent,fFunc) {
	this.attribute = sAttribute;
	this.unit = sUnit;
	this.startValue = Number(iStartValue);
	this.endValue = iEndValue;
	this.duration = iDuration;
	this.framerate = iFramerate;
	this.percent = iPercent;
	this.delta = this.startValue;
	this.step = (this.duration/1000)*this.framerate;
	this.oncomplete = false;
	this.funcdone = false;
	if(this.percent == 100) { this.oncomplete = true; }
	this.funcpercent = Math.round((this.duration/this.step)*(this.percent/100));
	this.percentdone = 0;

	this.setAttrib = function(iVal) {alert('iVal: '+iVal);this.style[this.attribute] = iVal+this.unit;};

	this.changeAttrib = function (iNewValue,iEndValue) {
		//var me = this;
		var iStepDuration = Math.round(this.duration/this.step);
		this.duration -= iStepDuration;
		this.percentdone++;
		alert('iNewValue: '+iNewValue+'\niEndValue: '+iEndValue+'\ndelta: '+this.delta);
		if((this.percentdone == this.funcpercent) && fFunc && this.oncomplete == false) {
			fFunc();
			this.funcdone = true;
		}
		if (iNewValue < iEndValue) {
			this.delta = (this.step > 0) ? (iEndValue - iNewValue)/this.step : 0;
			alert('delta1: '+this.delta);
			iNewValue = Math.max(iStartValue, Math.min(iNewValue+this.delta, iEndValue));
			if(iNewValue > (iEndValue-1)) {iNewValue = iEndValue;}
			this.setAttrib(iNewValue);
			this.changeAttribST = window.setTimeout(this.changeAttrib.bind(this,iNewValue,iEndValue),iStepDuration);
		} else if (iNewValue > iEndValue) {
			this.delta = (this.step > 0) ? (iNewValue - iEndValue)/this.step : 0;
			var iNextValue = (iNewValue-this.delta);
			iNewValue = Math.min(iStartValue, Math.max(iNextValue, iEndValue));
			alert('delta2: '+this.delta+'\niNewValue: '+iNewValue+'\nstartValue: '+iStartValue+'\niNewValue-this.delta: '+iNextValue+'\niEndValue: '+iEndValue);
			if(iNewValue < (iEndValue+1)) {iNewValue = iEndValue;}
			this.setAttrib(iNewValue);
			this.changeAttribST = window.setTimeout(this.changeAttrib.bind(this,iNewValue,iEndValue),iStepDuration);
		} else if (iNewValue == iEndValue) {
			this.setAttrib(iEndValue);
			clearTimeout(me.changeAttribST);
			if(fFunc && (this.funcdone == false)) {fFunc();}
		}
	};

	this.changeAttrib(this.startValue,this.endValue);
	return true;
};
/* ************************************************************************** **
**      Searchbox                                                             **
** ************************************************************************** */

var Searchbox = {
	init: function() {
		var boxes = document.getElementsByClassName('searchbox','','input');
		for(var i = 0, box; box = boxes[i]; i++) {
			if(!Browser.isSafari2()) {
				var str = Searchbox.getAttrValue(box,'placeholder');
				//Searchbox.setStyles(box);
				Searchbox.setBoxHeightWidth(box);
				Searchbox.setValue(box, str);
			}
		}
	},
	
	getAttrValue: function(b, a) {
		return b.getAttribute(a);
	},
	
	setValue: function(b, str) {
		b.value = str;
		b.onfocus = function(){b.value="";b.style.color="#000";};
	},
	
	setBoxHeightWidth: function(b) {
		var div = b.parentNode;
		var img1 = document.createElement('img');
		img1.setAttribute('border','0');
		img1.setAttribute('align','middle');
		img1.setAttribute('alt','');
		img1.style.height = '18px';
		img1.style.verticalAlign = 'top';
		img1.style.zIndex = '100';
		if(Browser.isIE7) { img1.setAttribute('class','srchimgs'); }
		else { img1.className = 'srchimgs'; }
		var img2 = img1.cloneNode(true);
		img1.style.width = '16px';
		img2.style.width = '9px';
		img1.setAttribute('src','http://images.apple.com/ilife/images/searchleftcap20060111.gif');
		img2.setAttribute('src','http://images.apple.com/ilife/images/searchrightcap20060111.gif');
		//alert('img1: '+img1.outerHTML);
		//alert('img2: '+img2.outerHTML);
		div.insertBefore(img1,b);
		div.appendChild(img2);
		
		if(Browser.isIE()) {
			b.style.width = ((div.id == 'search') ? 180 : div.offsetWidth) - (img1.width + img2.width) - 28 + 'px';
			b.style.height = ((img1.height >= img2.height) ? img1.height : img2.height) - 6 + 'px';
			b.style.marginTop = '-1px';
			//(div.id != 'search') ? b.style.marginTop = '2px' : b.style.marginTop = '-1px';
		} else {
		
			// 6px to offset padding
			b.style.width = ((div.id == 'search') ? 180 : div.offsetWidth) - (img1.width + img2.width) - 6 + 'px';
			b.style.height = ((img1.height >= img2.height) ? img1.height : img2.height) - 6 + 'px';
		
		}
		
	}
}

/* ************************************************************************** **
**      Browser                                                               **
** ************************************************************************** */

var Browser = {
	isSafari1: function() {
		var agent = navigator.userAgent.toLowerCase();
		if(agent.indexOf('safari') != -1 && !Browser.isSafari2()) {return true;}
	},
	isSafari2: function() {
		var agent = navigator.userAgent.toLowerCase();
		if(agent.indexOf('safari') != -1 && (parseFloat(agent.substring(agent.indexOf("applewebkit/")+"applewebkit/".length,agent.length).substring(0,agent.substring(agent.indexOf("applewebkit/")+"applewebkit/".length,agent.length).indexOf(' '))) >=  300)) {return true;}
	},
	isIE7: function() {
		var agent = navigator.userAgent.toLowerCase();
		if(agent.indexOf('msie 7.0') != -1) {return true;}
	},
	isIE: function() {
		var agent = navigator.userAgent.toLowerCase();
		if(agent.indexOf('msie') != -1) {return true;}
	}
};

/* ************************************************************************** **
**      Browser stuff                                                         **
** ************************************************************************** */
function detect() {
	// simplify things
	var agent 	= navigator.userAgent.toLowerCase();
	
	// detect platform
	this.isMac		= (agent.indexOf('mac') != -1);
	this.isWin		= (agent.indexOf('win') != -1);
	this.isWin2k	= (this.isWin && (
			agent.indexOf('nt 5') != -1));
	this.isWinSP2	= (this.isWin && (
			agent.indexOf('xp') != -1 || 
			agent.indexOf('sv1') != -1));
	this.isOther	= (
			agent.indexOf('unix') != -1 || 
			agent.indexOf('sunos') != -1 || 
			agent.indexOf('bsd') != -1 ||
			agent.indexOf('x11') != -1 || 
			agent.indexOf('linux') != -1);
	
	// detect browser
	this.isSafari	= (agent.indexOf('safari') != -1);
	this.isSafari2 = (this.isSafari && (parseFloat(agent.substring(agent.indexOf("applewebkit/")+"applewebkit/".length,agent.length).substring(0,agent.substring(agent.indexOf("applewebkit/")+"applewebkit/".length,agent.length).indexOf(' '))) >=  300));
	this.isOpera	= (agent.indexOf('opera') != -1);
	this.isNN		= (agent.indexOf('netscape') != -1);
	this.isIE		= (agent.indexOf('msie') != -1);
	
	// itunes compabibility
	this.isiTunesOK	= this.isMac || this.isWin2k;
}
var browser = new detect();