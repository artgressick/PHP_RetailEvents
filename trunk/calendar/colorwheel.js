//
// Section for the "Color Wheel" used in Session Type add/edit pages:
//

hue = 60;
adeg = 60;
sat = 1;
val = 1;
squarecolor = "#ffffff";
pickindex = 0;

threec = new Array("#ffffff", "#ffffff", "#ffffff"); // the three colors

function InitializeColorWheel()
{
  if (!((browser.isIE55 || browser.isIE6up) && browser.isWin32)) {
     //fix for IE's png stupidity
     // demoronize div#wheel image if browser is non-moronic
     thesmartversion = "<a href='javascript://' onclick='javascript:pickColor(); return false;'><img src='hsvwheel.png' alt='color wheel' width='553' height='257' border='0'></a>";
  document.getElementById("wheel").innerHTML = thesmartversion;
  }

  // keypress stuff
  // NOTE: Commented out because keypress changes are not desired behavior for any page this is used in
/*  if (browser.isSafari) {
    document.onkeypress = rotate; //fix for safari repeat
  } else {
    document.onkeydown = rotate;
  }
*/
  // start capturing the mouse
  capture();

  // get the current color
  squarecolor = document.getElementById("chrColor").value;
}

// HSV conversion algorithm adapted from easyrgb.com
function hsv2rgb(Hdeg,S,V) {
  H = Hdeg/360;     // convert from degrees to 0 to 1
  if (S==0) {       // HSV values = From 0 to 1
    R = V*255;     // RGB results = From 0 to 255
    G = V*255;
    B = V*255;}
  else {
    var_h = H*6;
    var_i = Math.floor( var_h );     //Or ... var_i = floor( var_h )
    var_1 = V*(1-S);
    var_2 = V*(1-S*(var_h-var_i));
    var_3 = V*(1-S*(1-(var_h-var_i)));
    if (var_i==0)      {var_r=V ;    var_g=var_3; var_b=var_1}
    else if (var_i==1) {var_r=var_2; var_g=V;     var_b=var_1}
    else if (var_i==2) {var_r=var_1; var_g=V;     var_b=var_3}
    else if (var_i==3) {var_r=var_1; var_g=var_2; var_b=V}
    else if (var_i==4) {var_r=var_3; var_g=var_1; var_b=V}
    else               {var_r=V;     var_g=var_1; var_b=var_2}
    R = Math.round(var_r*255);   //RGB results = From 0 to 255
    G = Math.round(var_g*255);
    B = Math.round(var_b*255);
  }
  return new Array(R,G,B);
}

function rgb2hex(rgbary) {
  cary = new Array; 
  cary[3] = "#";
  for (i=0; i < 3; i++) {
    cary[i] = parseInt(rgbary[i]).toString(16);
    if (cary[i].length < 2) cary[i] = "0"+ cary[i];
    cary[3] = cary[3] + cary[i];
    cary[i+4] = rgbary[i]; //save dec values for later
  }
  // function returns hex color as an array of three two-digit strings
  // plus the full hex color and original decimal values
  return cary;
}

function webRounder(c,d) {//d is the divisor
  //safe divisor is 51, smart divisor is 17 
  thec = "#";
  for (i=0; i<3; i++) {
      num = Math.round(c[i+4]/d) * d; //use saved rgb value
      numc = num.toString(16);
      if (String(numc).length < 2) numc = "0" + numc;
      thec += numc;
  }
  return thec;
}

function hexColorArray(c) { //now takes string hex value with #
    threec[2] = c[3];
    threec[1] = webRounder(c,17);
    threec[0] = webRounder(c,51);
    return false;
}

function capture() {
 window.document.getElementById("wheel").onmousemove = mouseMoved;
 window.document.getElementById("wheel").onmouseout = mouseOut;
}

function greyMoved(x,y) {
    adeg = hue;
    xside = (x<=553)?x - 296:256;
    yside = (y<=256)?y:256;
    sat = xside/256;
    val = 1 - (yside/256);
    c = rgb2hex(hsv2rgb(hue,sat,val));
    hexColorArray(c);
    hoverColor();
    return false;
}

function mouseMoved(e) {
 if (document.layers) {
  x = e.layerX;
  y = e.layerY;
 }
 else if (document.all) {
  x = event.offsetX;
  y = event.offsetY;
 }
 else if (document.getElementById) {
  x = (e.pageX - document.getElementById("wheel").offsetLeft);
  y = (e.pageY - document.getElementById("wheel").offsetTop);
 }
 if (x >= 296) {greyMoved(x,y);
   return false;}
 if (y > 256) {return false;}

    cartx = x - 128;
    carty = 128 - y;
    cartx2 = cartx * cartx;
    carty2 = carty * carty;
    cartxs = (cartx < 0)?-1:1;
    cartys = (carty < 0)?-1:1;
    cartxn = cartx/128;                      //normalize x
    rraw = Math.sqrt(cartx2 + carty2);       //raw radius
    rnorm = rraw/128;                        //normalized radius
    if (rraw == 0) {
      sat = 0;
      val = 0;
      rgb = new Array(0,0,0);
      }
    else {
      arad = Math.acos(cartx/rraw);            //angle in radians 
      aradc = (carty>=0)?arad:2*Math.PI - arad;  //correct below axis
      adeg = 360 * aradc/(2*Math.PI);  //convert to degrees
      if (rnorm > 1) {    // outside circle
            rgb = new Array(255,255,255);
            sat = 1;
            val = 1;            
            }
      //else rgb = hsv2rgb(adeg,1,1);
            else if (rnorm >= .5) {
	      sat = 1 - ((rnorm - .5) *2);
              val = 1;
	      rgb = hsv2rgb(adeg,sat,val);
	      }
              else {
                   sat = 1;
	      	   val = rnorm * 2;
	      	   rgb = hsv2rgb(adeg,sat,val);}
   }
   c = rgb2hex(rgb);
   hexColorArray(c);
   hoverColor();
   return false;
}

function mouseOut() {
  document.getElementById("ColorDisplay").style.backgroundColor = document.getElementById("chrColor").value;
}

function hoverColor() {
  document.getElementById("ColorDisplay").style.backgroundColor = threec[2];
  return false;
}

function pickColor() {
  document.getElementById("ColorDisplay").style.backgroundColor = threec[1];
  document.getElementById("wheel").style.backgroundColor = threec[3];
  document.getElementById("chrColor").value = threec[1];
  setSquare(adeg);
  return false;
}

function setColor(color) {
}

function setSquare(deg) {
  hue = deg;
  adeg = deg;
  c = rgb2hex(hsv2rgb(hue,1,1));
  squarecolor = c[3];
  if (document.layers) {
     document.layers["wheel"].bgColor = squarecolor;
  } else if (document.all) {
     document.all["wheel"].style.backgroundColor = squarecolor;
  } else if (document.getElementById) {
     document.getElementById("wheel").style.backgroundColor = squarecolor;
  }
}

function initColor() {
}

function theToggle(divid,disp) {
	var elements = document.getElementsByTagName("div");
	for(var i = 0; i < elements.length; i++) {
	if (elements.item(i).id == divid) {
	   elements.item(i).style.display = disp;}}}
