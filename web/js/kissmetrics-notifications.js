var _kmq = _kmq || [];
if (window.webkitNotifications && window.jQuery)
{
	jQuery('<div style="position:fixed; z-index:100000; bottom: 5px; left: 5px; width: 40px; height: 25px;"><button class="cl-button small default">KM '+(sessionStorage.getItem("_km_notifications") || 'off')+'</button>')
		.appendTo('body')
		.find('button')
			.click(function(){
				if (sessionStorage.getItem("_km_notifications") === 'on')
				{
					sessionStorage.setItem("_km_notifications", 'off');
				}
				else
				{
					window.webkitNotifications.requestPermission();
					sessionStorage.setItem("_km_notifications", 'on');
				}
				jQuery(this).text('KM '+sessionStorage.getItem("_km_notifications"));
			});

	var kissmetrics_notification = function(text) {
		if (sessionStorage.getItem("_km_notifications") === 'on')
		{
			var notification = window.webkitNotifications.createNotification('http://www.quickonlinetips.com/archives/wp-content/uploads/kissmetrics-logo.png', 'Kissmetrics Event', text);
			notification.show();
		}
	}

	_kmq.push = function(item) {
		if (item[0] === 'trackClick' || item[0] === 'trackClickOnOutboundLink') {
			$('body').on('click', item[1].charAt(0) === '.' ? item[1] : '#' + item[1], function(event){
				kissmetrics_notification(item[2])
			});
		}
		else if (item[0] === 'trackSubmit') {
			$('body').on('submit', item[1].charAt(0) === '.' ? item[1] : '#' + item[1], function(event){
				kissmetrics_notification(item[2])
			});
		}
		else if(item[0] === 'record')
		{
			kissmetrics_notification(item[1])
		}
		return Array.prototype.push.apply(this, arguments);
	}
}