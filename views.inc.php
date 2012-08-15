<?
	include("cfw.inc.php");
	$date_today = date("Y-m-d");
	
	$a_week = array_reverse(date_getweek($date_today, "Monday", 10));
	//print_r($a_week);
	foreach ($a_week as $d) {
		if ($d < $date_today) {
			$name = date("l",strtotime($d));
			
			if ($name != "Sunday" and $name != "Saturday") {
				$date_prev = $d;
				break;
			}
		}
	}
	
	//echo "***".$date_prev;
	
	
?>
<!-- Projects -->
<script id="project-pill-tmpl" type="text/x-jquery-tmpl">
	<li class="project pill"><a href="#" data-record="${ProjectID}" data-name="${Name}">${Name}</a></li>
</script>

<script id="addproject-tmpl" type="text/x-jquery-tmpl">	
	<p id="newproject" class="addnew">
		<a class="btn" href="#" onclick="return app.views.newProject();"><i class="icon-plus-sign"></i> New list</a>
	</p>	
</script>

<script id="project-input-tmpl" type="text/x-jquery-tmpl">
	<li class="edit project">
		<div class="input"><input type="text" size="30" value="" style="width:75%;" /></div>
		<a class="btn btn-small" href="#" onclick="return app.model.saveNewProject(this)"><i class="icon-ok"></i> Add</a>
		<a class="btn btn-small" href="#" onclick="return app.model.cancelNewProject(this)"><i class="icon-remove"></i> Cancel</a>
	</li>
</script>


<!-- Tasks -->
<script id="task-pill-tmpl" type="text/x-jquery-tmpl">
	<li class="task pill"><a href="#" data-record="${TaskID}" data-name="${Name}">${Name}</a></li>
</script>


<script id="addtask-tmpl" type="text/x-jquery-tmpl">	
	<p id="newtask" class="addnew">
		<a class="btn" href="#" onclick="return app.views.newTask()"><i class="icon-plus-sign"></i> New task</a>
	</p>	
</script>

<script id="task-input-tmpl" type="text/x-jquery-tmpl">
	<li class="edit task">
		<input type="text" size="20" value="" style="width:70%;" />
		<a class="btn btn-small" href="#" onclick="return app.model.saveNewTask(this)"><i class="icon-ok"></i> Add</a>
		<a class="btn btn-small" href="#" onclick="return app.model.cancelNewTask(this)"><i class="icon-remove"></i> Cancel</a>
	</li>
</script>

<script id="landing-message-tmpl" type="text/x-jquery-tmpl">
	<li class="message">${message}</li>
</script>


<!-- Clock -->

<script id="clock-pill-tmpl" type="text/x-jquery-tmpl">
	<li class="time pill"><a href="#" data-length="${Length}" data-name="${Name}">${Name}</li>
</script>

<script id="clock-tmpl" type="text/x-jquery-tmpl">
	<li id="clock">
		<a href="#" class="full" data-length="1" data-name="Full day"><span>Full day</span></a>
		<div class="clear">

			<div class="container-6">
				<a href="#" class="half" data-length=".5" data-name="1/2 Day"><span>1/2</span></a>
			</div>
			<div class="container-6 hide-phone">
				<a href="#" class="half" data-length=".5" data-name="1/2 Day"><span>1/2</span></a>
			</div>
			<div class="clear">
			
			<div class="container-8">
				<a href="#" class="third" data-length=".66" data-name="1/3 Day"><span>2/3</span></a>
			</div>
			<div class="container-4 hide-phone">
				<a href="#" class="third" data-length=".33" data-name="1/3 Day"><span>1/3</span></a>
			</div>
			<div class="clear">
		
			<div class="container-9">
				<a href="#" class="quarter" data-length=".75" data-name="3/4 Day"><span>3/4</span></a>
			</div>
			<div class="container-3">
				<a href="#" class="quarter" data-length=".25" data-name="1/4 Day"><span>1/4</span></a>
			</div>
			<div class="clear">
	</li>
</script>


<script id="timelog-tmpl" type="text/x-jquery-tmpl">
	<li id="time-log">
		<div class="well">
			<h4>When:</h4>
			<p>
				<select id="log-date">
					<option value="<?= $date_today; ?>">Today</option>
					<option value="<?= date_calc($date_today, -1); ?>">Yesterday</option>
					<option value="<?= $date_prev; ?>">Previous weekday</option>
					<option value="<?= date_calc($date_today, -2); ?>">Two days ago</option>
				</select>
			</p>
			<h4>What'd you do?</h4>
			<p>
				
				<textarea cols="40" rows="4" id="log-summary" style="width:75%;"></textarea>
			</p>
			<p>
				<input type="button" value="Finish" class="btn btn-primary btn-large" onclick="return app.model.finish();" />
			</p>
		</div>
		<div class="clear">

	</li>
</script>

















	




<script id="task-lineitem-tmpl" type="text/x-jquery-tmpl">
	<li class="task"><a href="#" class="item" data-record="${id}" data-name="${name}" onclick="actionTask(this);">${name}</a></li>
</script>



<script id="time-lineitem-tmpl" type="text/x-jquery-tmpl">
	<li class="time" data-record="${id}" data-name="${name}" onclick="actionTime(this);">${name}</li>
</script>


