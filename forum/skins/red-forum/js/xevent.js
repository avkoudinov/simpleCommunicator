Forum.addXEvent = function(oEmt, sEvt, act)
{
	if (!oEmt) return;
	if (oEmt.addEventListener)
		oEmt.addEventListener (sEvt, act, false);
	else
		if (oEmt.attachEvent)
			oEmt.attachEvent ('on'+sEvt, act);
		else
			oEmt['on'+sEvt] = act;
};

Forum.removeXEvent = function(oEmt, sEvt, act)
{
	if (!oEmt) return;
	if (oEmt.removeEventListener)
		oEmt.removeEventListener (sEvt, act, false);
	else
		if (oEmt.detachEvent)
			oEmt.detachEvent ('on'+sEvt, act);
		else
			oEmt['on'+sEvt] = null;
};

Forum.fireEvent = function(oEmt, sEvt)
{
  if ("createEvent" in document) {
      var evt = document.createEvent("HTMLEvents");
      evt.initEvent(sEvt, false, true);
      oEmt.dispatchEvent(evt);
  }
  else
      oEmt.fireEvent('on' + sEvt);
};
