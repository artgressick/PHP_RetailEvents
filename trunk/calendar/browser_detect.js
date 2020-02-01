// code from Browser Detect Lite  v2.1
// http://www.dithered.com/javascript/browser_detect/index.html
// modified by Chris Nott (chris@NOSPAMdithered.com - remove NOSPAM)
// modified by Michael Lovitt to include OmniWeb and Dreamcast
// modified by Jemima Pereira to detect only relevant browsers
// modified by Robin Daugherty to detect more browsers - fsck "relevant"

function BrowserDetectXLite() {
	var ua = navigator.userAgent.toLowerCase(); 
	this.ua = ua;

	// browser name
	this.isIE     = ( (ua.indexOf("msie") != -1) && (ua.indexOf("opera") == -1) && (ua.indexOf("webtv") == -1) ); 
   this.isSafari = (ua.indexOf('applewebkit') != -1);
	this.isGecko  = (ua.indexOf('gecko') != -1) && !this.isIE && !this.isSafari;

	if(this.isSafari) {
		var regex = /applewebkit\/([0-9]*)/;
		var matches = regex.exec(ua);
		if(matches) {
			this.WebKitVersion = matches[1];
		}
	}

	// browser version
	this.versionMinor = parseFloat(navigator.appVersion); 
	
	// correct version number for IE4+ 
	if (this.isIE && this.versionMinor >= 4) {
		this.versionMinor = parseFloat( ua.substring( ua.indexOf('msie ') + 5 ) );
	}
	
	this.versionMajor = parseInt(this.versionMinor);
	
	// platform
	this.isWin   = (ua.indexOf('win') != -1);
	this.isWin32 = (this.isWin && ( ua.indexOf('95') != -1 || ua.indexOf('98') != -1 || ua.indexOf('nt') != -1 || ua.indexOf('win32') != -1 || ua.indexOf('32bit') != -1) );
	this.isMac   = (ua.indexOf('mac') != -1);

	this.isIE4x = (this.isIE && this.versionMajor == 4);
	this.isIE4up = (this.isIE && this.versionMajor >= 4);
	this.isIE5x = (this.isIE && this.versionMajor == 5);
	this.isIE55 = (this.isIE && this.versionMinor == 5.5);
	this.isIE5up = (this.isIE && this.versionMajor >= 5);
	this.isIE6x = (this.isIE && this.versionMajor == 6);
	this.isIE6up = (this.isIE && this.versionMajor >= 6);
	
	this.isIE4xMac = (this.isIE4x && this.isMac);

}
var browser = new BrowserDetectXLite();
