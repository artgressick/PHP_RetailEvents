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
	this[0].parentNode.style.visibility = 'visible';
	
	this.initOpacity = function (div, startOpacity, endOpacity) {
		if (startOpacity <= endOpacity) {
			this.setOpacity(div, endOpacity);
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
	}else {
		this.changeOpacity(this.opacity);
	}
	
}
WhizBang.prototype.CrossFade = CrossFade;
