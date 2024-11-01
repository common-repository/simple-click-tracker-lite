function Sct_mask(obj)
{
	obj.append('<div class="sct_mask" style="position: absolute; top: 0; background-color: #EEE; border: 1px solid #CCC; height: ' + obj.height() + 'px; width: ' + obj.width() + 'px; text-align: center;"><img src="' + sct_plugin_url + 'includes/icons/loader-ball.gif" width="32" height="32" alt="" style="margin-top: ' + Math.floor((obj.height() - 32) / 2) + 'px;" /></div>');
}

function Sct_unmask(obj)
{
	obj.find('.sct_mask').remove();
}

var TimeToFade = 2000.0;

function sct_fade(eid) {
	var element = document.getElementById(eid);

	if (element == null)
		return;

	element.FadeTimeLeft = TimeToFade;
	setTimeout("sct_animateFade(" + new Date().getTime() + ",'" + eid + "')", 33);
}

function sct_animateFade(lastTick, eid) {
	var curTick = new Date().getTime();
	var elapsedTicks = curTick - lastTick;

	var element = document.getElementById(eid);

	if (element.FadeTimeLeft <= elapsedTicks) {
		element.style.opacity = element.FadeState == 1 ? '1' : '0';
		element.style.filter = 'alpha(opacity = '
				+ (element.FadeState == 1 ? '100' : '0') + ')';
		element.FadeState = element.FadeState == 1 ? 2 : -2;
		element.style.display = "none";
		return;
	}

	element.FadeTimeLeft -= elapsedTicks;
	var newOpVal = element.FadeTimeLeft / TimeToFade;
	if (element.FadeState == 1) {
		newOpVal = 1 - newOpVal;
	}

	element.style.opacity = newOpVal;
	element.style.filter = 'alpha(opacity = ' + (newOpVal * 100) + ')';

	setTimeout("sct_animateFade(" + curTick + ",'" + eid + "')", 33);
}