jQuery.fn.countdown = function(options) {
	/**
	 * app init
	*/	
	if(!options) options = '()';
	if(jQuery(this).length == 0) return false;
	var obj = this;	

	/**
	 * break out and execute callback (if any)
	 */
	if(options.seconds < 0 || options.seconds == 'undefined')
	{
		if(options.callback) eval(options.callback);
		return null;
	}

	/**
	 * recursive countdown
	 */
	window.setTimeout(
		function() {
			var time = options.seconds;
			var dd=Math.floor(time/(60*60*24));
			var hh=Math.floor(time/(60*60))%24;
			var mm=Math.floor(time/60)%60;
			var ss=Math.floor(time%60);
			var timeStr = '';
			timeStr+=dd+' 天 ';
			timeStr+=hh+' 小时 ';
			timeStr+=mm+' 分 ';
			timeStr+=ss+' 秒';
			
			jQuery(obj).html(timeStr);
			--options.seconds;
			jQuery(obj).countdown(options);
		}
		, 1000
	);	

	/**
     * return null
     */
    return this;
}