
<!-- This script is based on the javascript code of Roman Feldblum (web.developer@programmer.net) -->
<!-- Original script : http://javascript.internet.com/forms/format-phone-number.html -->
<!-- Original script is revised by Eralper Yilmaz (http://www.eralper.com) -->
<!-- Revised script : http://www.kodyaz.com -->

var zChar = new Array(' ', '(', ')', '-', '.');
var maxphonelength = 14;
var phonevalue1;
var phonevalue2;
var cursorposition;

function ParseForNumber1(object){
phonevalue1 = ParseChar(object.value, zChar);
}
function ParseForNumber2(object){
phonevalue2 = ParseChar(object.value, zChar);
}

function backspacerUP(object,e) {
if(e){
e = e
} else {
e = window.event
}
if(e.which){
var keycode = e.which
} else {
var keycode = e.keyCode
}

ParseForNumber1(object)

if(keycode >= 48){
ValidatePhone(object)
}
}

function backspacerDOWN(object,e) {
if(e){
e = e
} else {
e = window.event
}
if(e.which){
var keycode = e.which
} else {
var keycode = e.keyCode
}
ParseForNumber2(object)
}

function GetCursorPosition(){

var t1 = phonevalue1;
var t2 = phonevalue2;
var bool = false
for (i=0; i<t1.length; i++)
{
if (t1.substring(i,1) != t2.substring(i,1)) {
if(!bool) {
cursorposition=i
bool=true
}
}
}
}

function ValidatePhone(object){

var p = phonevalue1

p = p.replace(/[^\d]*/gi,"")

if (p.length < 3) {
object.value=p
} else if(p.length==3){
pp=p;
d4=p.indexOf('(')
d5=p.indexOf(')')
if(d4==-1){
pp="("+pp;
}
if(d5==-1){
pp=pp+") ";
}
object.value = pp;
} else if(p.length>3 && p.length < 7){
p ="(" + p;
l30=p.length;
p30=p.substring(0,4);
p30=p30+") "

p31=p.substring(4,l30);
pp=p30+p31;

object.value = pp;

} else if(p.length >= 7){
p ="(" + p;
l30=p.length;
p30=p.substring(0,4);
p30=p30+") "

p31=p.substring(4,l30);
pp=p30+p31;

l40 = pp.length;
p40 = pp.substring(0,9);
p40 = p40 + "-"

p41 = pp.substring(9,l40);
ppp = p40 + p41;

object.value = ppp.substring(0, maxphonelength);
}

GetCursorPosition()

if(cursorposition >= 0){
if (cursorposition == 0) {
cursorposition = 2
} else if (cursorposition <= 2) {
cursorposition = cursorposition + 1
} else if (cursorposition <= 5) {
cursorposition = cursorposition + 2
} else if (cursorposition == 6) {
cursorposition = cursorposition + 2
} else if (cursorposition == 7) {
cursorposition = cursorposition + 4
e1=object.value.indexOf(')')
e2=object.value.indexOf('-')
if (e1>-1 && e2>-1){
if (e2-e1 == 4) {
cursorposition = cursorposition - 1
}
}
} else if (cursorposition < 11) {
cursorposition = cursorposition + 3
} else if (cursorposition == 11) {
cursorposition = cursorposition + 1
} else if (cursorposition >= 12) {
cursorposition = cursorposition
}

//var txtRange = object.createTextRange();
//txtRange.moveStart( "character", cursorposition);
//txtRange.moveEnd( "character", cursorposition - object.value.length);
//txtRange.select();
}

}

function ParseChar(sStr, sChar)
{
if (sChar.length == null)
{
zChar = new Array(sChar);
}
else zChar = sChar;

for (i=0; i<zChar.length; i++)
{
sNewStr = "";

var iStart = 0;
var iEnd = sStr.indexOf(sChar[i]);

while (iEnd != -1)
{
sNewStr += sStr.substring(iStart, iEnd);
iStart = iEnd + 1;
iEnd = sStr.indexOf(sChar[i], iStart);
}
sNewStr += sStr.substring(sStr.lastIndexOf(sChar[i]) + 1, sStr.length);

sStr = sNewStr;
}

return sNewStr;
}

 /*function jsformatssn(asSSNControl)
  {
      // Remove any characters that are not numbers
     
	      var re = /\D/g;
      var lvCurrentSSNControlID = asSSNControl.id;
	      var lvSSNControl = lvCurrentSSNControlID.substring(lvCurrentSSNControlID.lastIndexOf("_") + 1);
	      var lvParent;
	      var lvCompare;
	      var lvRequired;
	      // Get which control sent the request
	      if (lvSSNControl == "txtCreditCheckSSN")
	      {
	          lvParent = $get('<%=txtCreditCheckSSN.ClientID %>');
	      }
	      var lvNumber = lvParent.value.replace(re, "");
	      var lvLength = lvNumber.length;
	      // Fill the first dash for the user
	      if(lvLength > 3 && lvLength < 6)
	      {
	          var lvSegmentA = lvNumber.slice(0,3);
	          var lvSegmentB = lvNumber.slice(3,5);
	          lvParent.value = lvSegmentA + "-" + lvSegmentB;
	      }
	      else
	      {
	          // Fill the dashes for the user
	          if(lvLength > 5)
	          {
	              var lvSegmentA = lvNumber.slice(0,3);
	              var lvSegmentB = lvNumber.slice(3,5);
	              var lvSegmentC = lvNumber.slice(5,9);
	              lvParent.value = lvSegmentA + "-" + lvSegmentB + "-" + lvSegmentC;
	          }
	          else
	          {
	              // If nothing is entered, leave the field blank
	              if (lvLength < 1)
	              {
	                  lvParent.value = "";
	              }
	              lvParent.value = lvNumber;
	          }
	      }
	   }*/
           function formatSSN(FIELDNAME)
{
     var theCount = 0;
     var theString = document.getElementById(FIELDNAME).value;
     var newString = "";
     var myString = theString;
     var theLen = myString.length;
     for ( var i = 0 ; i < theLen ; i++ )
     {
     // Character codes for ints 1 - 9 are 48 - 57
          if ( (myString.charCodeAt(i) >= 48 ) && (myString.charCodeAt(i) <= 57) )
          newString = newString + myString.charAt(i);
     }
// Now the validation to determine that the remaining string is 9 characters.
     if (newString.length == 9 )
     {
// Now the string has been stripped of other chars it can be reformatted to ###-##-####
          var newLen = newString.length;
          var newSSN = "";
          for ( var i = 0 ; i < newLen ; i++ )
          {
               if ( ( i == 2 ) || ( i == 4 ) )
               {
                    newSSN = newSSN + newString.charAt(i) + "-";
               }else{
                    newSSN = newSSN + newString.charAt(i);
               }
          }
          document.getElementById(FIELDNAME).value = newSSN;
          return true;
     }else{
        //  alert("The Social Security Number you entered "+newString+" does not contian the correct number of digits");
          document.getElementById(FIELDNAME).focus();
          return false;
     }
}
// [dFilter] - A Numerical Input Mask for JavaScript
// Written By Dwayne Forehand - March 27th, 2003
// Please reuse & redistribute while keeping this notice.

var dFilterStep

function dFilterStrip (dFilterTemp, dFilterMask)
{
    dFilterMask = replace(dFilterMask,'#','');
    for (dFilterStep = 0; dFilterStep < dFilterMask.length++; dFilterStep++)
		{
		    dFilterTemp = replace(dFilterTemp,dFilterMask.substring(dFilterStep,dFilterStep+1),'');
		}
		return dFilterTemp;
}

function dFilterMax (dFilterMask)
{
 		dFilterTemp = dFilterMask;
    for (dFilterStep = 0; dFilterStep < (dFilterMask.length+1); dFilterStep++)
		{
		 		if (dFilterMask.charAt(dFilterStep)!='#')
				{
		        dFilterTemp = replace(dFilterTemp,dFilterMask.charAt(dFilterStep),'');
				}
		}
		return dFilterTemp.length;
}

function dFilter (key, textbox, dFilterMask)
{
		
                dFilterNum = dFilterStrip(textbox.value, dFilterMask);
 
		if (key==9/*||key==37||key==38||key==39*/)
		{
		    return true;
		}
		else if (key==8&&dFilterNum.length!=0)
		{
		 	 	dFilterNum = dFilterNum.substring(0,dFilterNum.length-1);
		}
 	  else if ( ((key>47&&key<58)||(key>95&&key<106)) && dFilterNum.length<dFilterMax(dFilterMask) )
		{
                   
                 if(key>95&&key<106){
                     key=key-48;
                 }
        dFilterNum=dFilterNum+String.fromCharCode(key);
       // dFilterNum=key;
		}

		var dFilterFinal='';
    for (dFilterStep = 0; dFilterStep < dFilterMask.length; dFilterStep++)
		{
        if (dFilterMask.charAt(dFilterStep)=='#')
				{
					  if (dFilterNum.length!=0)
					  {
				        dFilterFinal = dFilterFinal + dFilterNum.charAt(0);
					      dFilterNum = dFilterNum.substring(1,dFilterNum.length);
					  }
				    else
				    {
				        dFilterFinal = dFilterFinal + "";
				    }
				}
		 		else if (dFilterMask.charAt(dFilterStep)!='#')
				{
				    dFilterFinal = dFilterFinal + dFilterMask.charAt(dFilterStep);
				}
//		    dFilterTemp = replace(dFilterTemp,dFilterMask.substring(dFilterStep,dFilterStep+1),'');
		}


		textbox.value = dFilterFinal;
    return false;
}

function replace(fullString,text,by) {
// Replaces text with by in string
    var strLength = fullString.length, txtLength = text.length;
    if ((strLength == 0) || (txtLength == 0)) return fullString;

    var i = fullString.indexOf(text);
    if ((!i) && (text != fullString.substring(0,txtLength))) return fullString;
    if (i == -1) return fullString;

    var newstr = fullString.substring(0,i) + by;

    if (i+txtLength < strLength)
        newstr += replace(fullString.substring(i+txtLength,strLength),text,by);

    return newstr;
}