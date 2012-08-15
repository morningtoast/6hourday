<!doctype html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!-- Consider adding a manifest.appcache: h5bp.com/d/Offline -->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
  <meta charset="utf-8">

  <!-- Use the .htaccess and remove these lines to avoid edge case issues.
       More info: h5bp.com/i/378 -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <title></title>
  <meta name="description" content="">

  <!-- Mobile viewport optimized: h5bp.com/viewport -->
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

  <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

	<link rel="stylesheet" href="assets/bootstrap.min.css">  
	<link rel="stylesheet" href="assets/flex-grid.css">
	<link rel="stylesheet" href="assets/app.css">
  
  
  <!-- More ideas for your <head> here: h5bp.com/d/head-Tips -->

  <!-- All JavaScript at the bottom, except this Modernizr build.
       Modernizr enables HTML5 elements & feature detects for optimal performance.
       Create your own custom Modernizr build: www.modernizr.com/download/ -->
	<script src="assets/jquery.min.js"></script>
	<script src="assets/modernizr-2.5.3.min.js"></script>
	<script src="assets/jquery.tmpl.min.js"></script>
	<script src="assets/sizeit.min.js"></script>
	<script src="assets/app.js"></script>

    <style type="text/css">
		body { background-color:#333; }
		ul, ol { margin:0; }
		
	.stack li {
		-moz-border-radius: 6px;
		-webkit-border-radius: 6px;
		-o-border-radius: 6px;	
		margin-bottom:4px;
		padding:12px;
		font-size:135%;
	}
		
	  .stack li.project { background-color:#BEDF4A;   }
	  .stack li.task { background-color:#7D9EEC;  color:#fff; }
	  .stack li.time { background-color:#f00;   color:#fff; }
	  .stack span { display:block; }
	  .stack .pill a { color:#fff; display:block; }
	  .stack .pill a:hover { text-decoration:none; }
	  
	  .stack #clock { padding:0; }
	  
	  #clock a.full { background-color:#FF3300; color:#fff; }
	  #clock a.third { background-color:#FFBB44; color:#fff; }
	  #clock a.half { background-color:#FF7722; color:#fff; }
	  #clock a.quarter { background-color:#FFFF66; color:#FF3300; }
	  
	  #clock span {  }
	  #clock a:hover { text-decoration:none; }
		#clock a { 
			padding:16px; margin:0 4px 8px 4px; display:block; text-align:center;
			
			font-size:150%;
			
			
			-moz-border-radius: 6px;
			-webkit-border-radius: 6px;
			-o-border-radius: 6px;	
		}
		
		.icon-add { width:30px; height:30px; background:url(assets/img/plus.png) center center; }
		
		.icon.small { width:21px; }
		
		.edit .icon { margin-left:6px; }
		.edit .input { display:block; margin-bottom:8px; }
		.well h4 { padding-bottom:6px;  }
		
		input,textarea,input[type="text"],select { margin-bottom:0; width:auto; }
		
		#task-list .message { color:#ccc; font-size:200%; font-style:italic; text-align:center; }
		
		
	header { background-color:#000; color:#fff; padding:10px; margin-bottom:5px; }
		
/* Phone - When less than 600 px*/
	@media only screen and (max-width:600px){
		body { font-size:120%; }
		.container-4, .column-8, .container-6, .container-9, .container-3, .container-4 { float:none; width:100%; }
		.hide-phone { display:none; }
	}
	
		
		
    </style>
</head>
<body>
<div class="navbar">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="#">SixHour Day</a>
          <div class="nav-collapse">
            <ul class="nav">
              <li class="active"><a href="#">Settings</a></li>
              <li><a href="#about">History</a></li>
              <li><a href="#contact">Reporting</a></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>


	<div class="container">
		<div id="rail" class="container-4">
			<ul id="project-list" class="stack unstyled"></ul>
			<ul id="history-list" class="stack unstyled"></ul>
		</div>
		<div id="work" class="column-8">
			<ul id="task-list" class="stack unstyled"><li class="default">Select a task list</ul></ul>
		</div>
	</div>
	<? include_once("views.inc.php"); ?>
	<script src="assets/bootstrap.min.js"></script>
</body>
</html>