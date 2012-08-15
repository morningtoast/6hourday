var device = {
	clientSize: function() {
		var winW = 630, winH = 460;
		
		if (document.body && document.body.offsetWidth) {
			winW = document.body.offsetWidth;
			winH = document.body.offsetHeight;
		}
		
		if (document.compatMode=='CSS1Compat' &&
		document.documentElement &&
		document.documentElement.offsetWidth) {
			 winW = document.documentElement.offsetWidth;
			 winH = document.documentElement.offsetHeight;
		}
		
		if (window.innerWidth && window.innerHeight) {
			 winW = window.innerWidth;
			 winH = window.innerHeight;
		}
		  
		var clientSize = {"x":winW,"y":winH}
		return(clientSize);
	},
	
	
	profile: function() {
		this.size  = this.clientSize();
		this.touch = $("html").hasClass("touch");
		
		this.attachClass();
		this.attachOrientation();
	},
	
	attachOrientation: function() {
		$("html").removeClass("landscape").removeClass("portrait");
	
		var height = $(window).height();
		var width  = $(window).width();
		
		if(width > height) {
			$("html").addClass("landscape");
			this.orientation = "landscape";
		} else {
			$("html").addClass("portrait");
			this.orientation = "portrait";
		}	
	},
	
	attachCss: function() {
		var deviceCss   = "<cms:link>/digital/css/desktop.css</cms:link>";
		var deviceClass = "desktop";
		
		if (this.size.x <= 1024 && this.touch) {
			// tablet
			deviceCss   = "<cms:link>/digital/css/tablet.css</cms:link>";
			deviceClass = "tablet";
		}
		
		if (this.size.x < 600) {
			// phone
			deviceCss   = "<cms:link>/digital/css/phone.css</cms:link>";
			deviceClass = "phone";
		}
		
		if (this.size.x >= 1280 && !this.touch) {
			// widescreen
			deviceCss   = "<cms:link>/digital/css/wide.css</cms:link>";
			deviceClass = "widescreen";
		}
		
		if (document.createStyleSheet){
                	document.createStyleSheet(deviceCss);
            	} else {
                	$("head").append($('<link rel="stylesheet" href="'+deviceCss+'" type="text/css" />'));
		}	
	
	},
	
	attachClass: function() {
		$("html").removeClass("desktop").removeClass("tablet").removeClass("phone");
	
		var deviceClass = "desktop";
	
		if (this.size.x <= 1024 && this.touch) {
			// tablet
			deviceClass = "tablet";
		}
		
		if (this.size.x < 600) {
			// phone
			deviceClass = "phone";
		}
		
		if (this.size.x >= 1280 && !this.touch) {
			// widescreen
			deviceClass = "desktop";
		}	
		
		$("html").addClass(deviceClass);
	},
	
	ini: function() {
		this.profile();
	}
	
} // END deviceclass




// Instant load
