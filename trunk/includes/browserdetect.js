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
