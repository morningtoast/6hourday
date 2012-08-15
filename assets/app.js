var app = {
	"active" : {
		"project" : {},
		"task"    : {},
		"time"    : {},
		"log"     : {}
	},
	
	"data" : {
		"projects" : [],
		"tasks"    : []
	},	
	
	"views"      : {},
	"controller" : {},
	"helpers"    : {},
	"model"      : {},
	"setup"      : {},
	"device" : "desktop"
}





// Test data -------------
var testProjects = [
	{ "id":1,"name" : "Fantasy League" },
	{ "id":2,"name" : "Redline Events" },
	{ "id":3,"name" : "TMA" },
	{ "id":4,"name" : "Side Projects" }
];

var testTasks = [
	{ "id":1,"name" : "Design" },
	{ "id":2,"name" : "Coding"},
	{ "id":3,"name" : "Docs" },
	{ "id":4,"name" : "Meetings" },
	{ "id":4,"name" : "Wireframes" }	
];
// ----- end test data


app.helpers = {
	"clearAll" : function() {
		this.clearProjects();
		this.clearTasks();
		this.clearHistory();
	},
	
	"clearProjects" : function() {
		$("#project-list").empty();
		$("#newproject").remove();
	},
	
	"clearTasks" : function() {
		$("#task-list").empty();
		$("#newtask").remove();
	},
	
	"clearHistory" : function() {
		$("#history-list").empty();
	},
	
	"clearAllData" : function() {
		app.helpers.clearActiveProject();
		app.helpers.clearActiveTask();
		app.helpers.clearActiveTime();
	},
	
	"clearActiveProject" : function() {
		app.active.project = {}
	},
	
	"clearActiveTask" : function() {
		app.active.task = {}
	},
	
	"clearActiveTime" : function() {
		app.active.time = {}
	}
	
	
}


app.setup = function(message) {
	console.log("reset");
	app.helpers.clearAll();
	app.helpers.clearAllData();
	
	if (app.views.listProjects()) {
		app.views.addProject();
	}
	console.log(message);
	app.views.landingMessage(message);
}

app.model = {
	"getProjectsByUser" : function(callback) {
		$.ajax({
			"url"      : "response.php",
			"data"     : {"lookup":"projects"},
			"dataType" : "json",
			"success"  : function(response) {
				app.data.projects = response;
				callback(true);
			}
		});
		
		return(false);
	},
	"getTasksByProject" : function(callback) {

		$.ajax({
			"url"      : "response.php",
			"data"     : {"lookup":"tasks","ProjectID":app.active.project.ProjectID},
			"dataType" : "json",
			"success"  : function(response) {
				app.data.tasks = response;
				callback(true);
			}
		});
		
		return(false);
	},
	"saveNewProject" : function(element) {
		var pillObj        = $(element).parent();
		var newProjectName = pillObj.find("input").val();
		
		$.ajax({
			"url"      : "response.php",
			"data"     : {"lookup":"newproject","Name":newProjectName},
			"dataType" : "json",
			"success"  : function(response) {
				pillObj.remove();
				app.views.insertProject(response);
				app.views.addProject();
				
			}
		});		
	
	},
	"cancelNewProject" : function(element) {
		$(element).parent().remove();
		app.views.addProject();
	},
	"saveNewTask" : function(element) {
		var pillObj     = $(element).parent();
		var newTaskName = pillObj.find("input").val();
		
		$.ajax({
			"url"      : "response.php",
			"data"     : {"lookup":"newtask","ProjectID":app.active.project.ProjectID,"Name":newTaskName},
			"dataType" : "json",
			"success"  : function(response) {
				pillObj.remove();
				app.views.insertTask(response);
				app.views.addTask();
				
			}
		});		
	
	},
	"cancelNewTask" : function(element) {
		$(element).parent().remove();
		app.views.addTask();
	},
	
	"finish" : function() {
		var logDate    = $("#log-date").val();
		var logSummary = $("#log-summary").val();
		
		app.active.log = {"Summary":logSummary,"Date":logDate}
		
		console.log(app.active);
		
		var data = {"lookup":"save","data":app.active}
		
		
		$.ajax({
			"url"      : "response.php",
			"type" : "POST",
			"data"     : {"save":data},
			"success"  : function(response) {
				app.setup("Entry saved");
			}
		});
		
		
		
		
	}


}

app.views = {
	"listProjects" : function() {
		function render() {
			$.each(app.data.projects, function(index, pair) {
				app.views.insertProject(pair);
			});
		}
	
		app.model.getProjectsByUser(render);
	
		return(true);	
	},
	
	"insertProject" : function(data) {
		$("#project-pill-tmpl").tmpl(data).appendTo("#project-list");
	},	
	
	"addProject" : function() {
		$("#addproject-tmpl").tmpl().insertAfter("#project-list");
	},
	
	"newProject" : function() {
		$("#project-input-tmpl").tmpl().appendTo("#project-list");
		$("#newproject").remove();
	},
	
	
	"listTasks" : function() {
		function render() {
			$.each(app.data.tasks, function(index, pair) {
				app.views.insertTask(pair);
			});
		}
		
		app.helpers.clearTasks();
		app.model.getTasksByProject(render);
	
		return(true);	
	},
	
	"insertTask" : function(data) {
		$("#task-pill-tmpl").tmpl(data).appendTo("#task-list");
	},
	
	"addTask" : function() {
		$("#addtask-tmpl").tmpl().insertAfter("#task-list");
	},
	
	"newTask" : function() {
		$("#task-input-tmpl").tmpl().appendTo("#task-list");
		$("#newtask").remove();
	},
	
	"listHistory" : function() {
		app.helpers.clearHistory(); 
		
		if (app.active.project.ProjectID > 0) {
			$("#project-pill-tmpl").tmpl(app.active.project).appendTo("#history-list");
		}
		
		if (app.active.task.TaskID > 0) {
			$("#task-pill-tmpl").tmpl(app.active.task).appendTo("#history-list");
		}
		
		if (app.active.time.Length) {
			$("#clock-pill-tmpl").tmpl(app.active.time).appendTo("#history-list");
		}
	},
	
	"clock" : function() {
		$("#clock-tmpl").tmpl().appendTo("#task-list");
	},
	
	"timeLog" : function() {
		$("#timelog-tmpl").tmpl().appendTo("#task-list");
	},
	
	"landingMessage" : function(s) {
		$("#landing-message-tmpl").tmpl({"message":s}).appendTo("#task-list");
	}

}




$(function() {
	sizeit.configure(
		{ max: 600, name: "phone" },
		{ max: 1024, name: "tablet" },
		{ name: "desktop" }
	);
	app.device = sizeit.size();
	
	app.setup("Welcome");
	
	
	//$("html").addClass(sizeit.size());
	
});	


/* Listeners */

// Forward
$("#project-list li.project.pill a").live("click", function(event){
	var clicked = $(this);
	
	app.active.project = {"ProjectID":clicked.attr("data-record"), "Name":clicked.attr("data-name")}
	
	app.helpers.clearProjects();
	
	app.views.listHistory();
	app.views.listTasks();
	app.views.addTask();
});

$("#task-list li.task.pill a").live("click", function(event){
	var clicked = $(this);
	
	app.active.task = {"TaskID":clicked.attr("data-record"), "Name":clicked.attr("data-name")}
	
	app.helpers.clearTasks();
	
	app.views.listHistory();
	app.views.clock();
});

$("#clock a").live("click", function(event){
	var clicked = $(this);
	
	app.active.time = {"Length":clicked.attr("data-length"), "Name":clicked.attr("data-name")}

	app.views.listHistory();
	app.helpers.clearTasks();
	app.views.timeLog();
});

// Backward
$("#history-list li.project.pill a").live("click", function(event){
	app.setup();
});

$("#history-list li.task.pill a").live("click", function(event){
	app.helpers.clearActiveTask();
	app.helpers.clearActiveTime();
	app.helpers.clearTasks();
	
	app.views.listHistory();
	
	app.views.listTasks();
	app.views.addTask();
});

$("#history-list li.time.pill a").live("click", function(event){
	app.helpers.clearActiveTime();
	app.helpers.clearActiveTime();
	
	app.helpers.clearTasks();
	app.views.listHistory();
	
	app.views.clock();
});

/*
function newProject() {
	$("#rail .addnew").hide();
	$("#newproject-lineitem-tmpl").tmpl().appendTo("#project-list");
}

function addProject(trigger) {
	var newProjectName = $("#project-name").val();
	
	// Save and get back ID
	
	var newProject = {"id":99,"name":newProjectName}
	
	$("#project-lineitem-tmpl").tmpl(newProject).appendTo("#project-list");
	cancelEdit("#rail");
}

function newTask() {
	$("#work .addnew").hide();
	$("#newtask-lineitem-tmpl").tmpl().appendTo("#task-list");
}

function addTask(trigger) {
	var newProjectName = $("#task-name").val();
	
	// Save and get back ID
	
	var newTask = {"id":99,"name":newProjectName}
	
	$("#task-lineitem-tmpl").tmpl(newTask).appendTo("#task-list");
	cancelEdit("#work");
}

function cancelEdit(element) {
	$(element).closest(".edit").remove();
}

function reset() {
	$("#task-list").empty();
	$("#history-list").empty();
	$("#work .addnew").hide();
	getProjects();
	
}

function getProjects() {
	$.each(stuff, function(index, pair) {
		$("#project-lineitem-tmpl").tmpl(pair).appendTo("#project-list");
	});
	
	return(true);
}

function getProjectTasks() {
	$("#project-list").empty();
	working.task = {}
	
	$("#project-lineitem-tmpl").tmpl(working.project).appendTo("#history-list");
	showProjectTasks();
}

function showProjectTasks() {
	$.each(tasks, function(index, pair) {
		$("#task-lineitem-tmpl").tmpl(pair).appendTo("#task-list");
	});
	
	$("#addtask-tmpl").tmpl().appendTo("#work");
}

function getTimer() {
	working.time = {}
	$("#task-list").empty();
	$("#task-lineitem-tmpl").tmpl(working.task).appendTo("#history-list");
	
	$("#timestamp-tmpl").tmpl().appendTo("#task-list");
}

function getTimeLog() {
	$("#task-list").empty();
	$("#time-lineitem-tmpl").tmpl(working.time).appendTo("#history-list");
	
	$("#task-list").html($("#timelog-tmpl").tmpl());
}


function actionTask(element) {
	var clicked = $(element);
	working.task = {"id":clicked.attr("data-record"), "name":clicked.attr("data-name")}
	getTimer();
}

function actionTime(element) {
	var clicked = $(element);
	working.task = {"id":clicked.attr("data-record"), "name":clicked.attr("data-name")}
	getTimer();
}




// Project pill, landing/reset view
$("#project-list li.project a").live("click", function(event){
	$("#task-list").empty();
	
	var clicked = $(this);
	
	working.project = {"id":clicked.attr("data-record"), "name":clicked.attr("data-name")}
	if (getProjectTasks()) {
		$("#addproject-tmpl").tmpl().appendTo("#rail");
	}
});






$("#clock a").live("click", function(event){
	var clicked = $(this);
	working.time = {"length":clicked.attr("data-length"), "name":clicked.attr("data-name")}
	
	getTimeLog();
	return false;
});

$("#time-log input").live("click", function(event){
	var clicked = $(this);
	var date    = $("#date").val();
	var summary = $("#summary").val();
	
	working.log = {"date":date, "summary":summary}
	console.log(working);
	reset();
});

$("#history-list .project").live("click", function(event){
	working.project = {}
	$("#task-list").empty();
	$("#history-list").empty();
	getProjectTasks();
});

$("#history-list .task").live("click", function(event){
	$("#task-list").empty();
	$("#history-list").empty();
	getProjectTasks();
});

$("#history-list .time").live("click", function(event){
	$("#task-list").empty();
	$("#history-list li.time").remove();
	$("#history-list li.task").remove();
	getTimer();
});

*/