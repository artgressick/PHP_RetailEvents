
function CrossFade() {
	this.interval = 50; // initial interval
	this.setOpacity = function (obj, o) {
	    obj.style.opacity = (o / 101);
	    obj.style.MozOpacity = (o / 100);
	    obj.style.KhtmlOpacity = (o / 100);
	    obj.style.filter = "alpha(opacity=" + o + ")";
	}
	for(j=0;j<this.length;j++) {
		this[j].style.position='absolute';
		this[j].style.top=0;
		this[j].style.zIndex=0;
		this.setOpacity(this[j],0);
	}
	this.opacity = 100;
	this.setOpacity(this[0].parentNode,this.opacity);
	this.currentDiv = 0;
	this.loadingCF = true;
	this.previousDiv = this.length-1;
	//this[0].parentNode.style.display = 'block';
	this[0].parentNode.style.visibility = 'visible';
	this.initOpacity = function (div, startOpacity, endOpacity) {
		if (startOpacity <= endOpacity) {
			this.setOpacity(div, startOpacity);
			startOpacity += 10;
				window.setTimeout(this.initOpacity.bindWithArguments(this,div,startOpacity,endOpacity),this.interval);
		}
	}
	this.initOpacity(this[this.currentDiv],0,100);
	this.changeOpacity = function (opac) {
		if(opac < 100) {
			this.setOpacity(this[this.currentDiv],opac);
			this.setOpacity(this[this.previousDiv],100-opac);
			opac += 10;
			window.setTimeout(this.changeOpacity.bindWithArguments(this,opac),80);
		} else {
			if(this.loadingCF != true) {this.setOpacity(this[this.currentDiv],100);}
			else {this.loadingCF = false;}
			this.setOpacity(this[this.previousDiv],0);
			this.previousDiv = this.currentDiv;
			this.currentDiv = (this.currentDiv>=this.length-1) ? 0 : this.currentDiv + 1;
			this[this.previousDiv].style.zIndex = 100;
			this[this.currentDiv].style.zIndex = 0;
			opac = 0;
			window.setTimeout(this.changeOpacity.bindWithArguments(this,opac),(this.interval*100));
		}
	}
	this.olen = this[0].className.length;
	this.onum = this[0].className.charAt(this.olen-1);
	if(!isNaN(this.onum) && this.onum > 1) {window.setTimeout(this.changeOpacity.bindWithArguments(this,this.opacity),(80*this.interval)/this.onum);
	//} else if(this.onum == 3) { 
	//	window.setTimeout(this.changeOpacity.bindWithArguments(this,this.opacity),300*this.interval);
	}else {
		this.changeOpacity(this.opacity);
	}
}
WhizBang.prototype.CrossFade = CrossFade;

var currentImg;
var previousImg;
var fadedivs = [];

function isA(o,klass){ if(!o.className) return false; return new RegExp('\\b'+klass+'\\b').test(o.className) }

// get elements by class name, eg $c('post', document, 'li')
function $c(c,o,t) { o=o||document;
	if (!o.length) o = [o]
	var elements = []
	for(var i = 0, e; e = o[i]; i++) {
		if(e.getElementsByTagName) {
			var children = e.getElementsByTagName(t || '*');
			for (var j = 0, child; child = children[j]; j++) {
				if(isA(child,c)) { elements.push(child); }
			}
		}
	}
	return elements
}

function InitCrossFade() {
	if(InitCrossFade.arguments) {
		var fadecontainer = document.getElementById(InitCrossFade.arguments[0]);
		for(i=1;i<InitCrossFade.arguments.length;i++) {
			fadecontainer.innerHTML+=InitCrossFade.arguments[i];
		}
		var fade = fadecontainer.lastChild;
		var fadeclass = fade.className;
		fadedivs = new HTMLObject($c(fadeclass,'','*'));
		Object.extend(fadedivs,WhizBang.prototype);
		fadedivs.CrossFade();
	}
}


function rand_unique(low,high,total,exclude) {
	var nums = [];
	if(exclude) { total - 1; }
	var randNum;
	var stopGo;
	while(nums.length < total) {
		randNum = 1 + Math.round(Math.random()*(high-1));
		if(randNum == exclude) { continue; }
		stopGo = 1;
		var j=0;
		while(j < nums.length) {
			if(nums[j] == randNum) { stopGo = 0; break; }
			j++;
		}
		if(stopGo == 1) {
			nums[nums.length] = randNum;
		}
	}
	nums[nums.length] = exclude;
	return nums;
}




//addEventToObject(window,'onload',InitCrossFade);
