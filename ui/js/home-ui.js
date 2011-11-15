

$.ajaxSetup ({
    cache: false
});
var ajax_load = "<img src='./ui/img/ajax-loader.gif' alt='loading...' />";

$(document).ready(function(){
  // tooltips
    $('#importers ul li')
        .prepend('<span class="pointer">▶</span>')
        .children("div")
        .hide();
    $('#importers ul li').children("a").click(function(){
        var arrow = $(this).siblings("span").text();
        arrow = (arrow == "▶") ? "▼" : "▶";
        $(this).siblings("span").text(arrow);
        $(this).siblings("div").slideToggle();
    });

    var fulllist = $("textarea.artifactList").val();
    var numberartifacts = fulllist.split("\n").length - 1;
    $("#number-artifacts").html(numberartifacts+"");

  $("button").click(function(){
	var myId = this.id;
	if ((myId.substring(0, 12) === "quick_report")) {
		var textId = "mendeley_profile_input";
		var textVal = $('#'+textId).val();
		var divId = "quick_report_div";
	} else {
		var textId = this.id + "_input";
		var textVal = $('#'+textId).val();
		var divId = this.id + "_div";
	}
	if (myId === "manual") {
		var fulllist = $("textarea.artifactList").val();
		var numberartifacts = fulllist.split("\n").length - 1;
    	$("#"+divId).html(numberartifacts + " IDs.");
	   	$("#number-artifacts").html(numberartifacts+"");
		$("#artifactListHidden").val($("textarea.artifactList").val());
	} else {
	    $("#"+divId).html("<img width='14' src='./ui/img/ajax-loader.gif'> Loading...");
		$.get("./seed.php?type="+myId+"&name="+textVal, function(response,status,xhr){
			if (myId=="quick_report_contacts") {
				$("#"+divId).html("Click to go directly to report:<p/>" + response["contacts"]);
			} else if (myId=="quick_report_groups") {
				$("#"+divId).html("Click to go directly to report:<p/>" + response["groups"]);
			} else {
				/* var value = response["artifactIds"]+""; */
				/* value = value.replace(/\s+/gmi, "<br/>"); */
				/* $("div.artifactList").prepend(value + "<br/>"); */
				$("textarea.artifactList").val(response["artifactIds"] + "\n" + $("textarea.artifactList").val());
		    	$("#"+divId).html("Added " + response["artifactCount"] + " IDs.");
				var fulllist = $("textarea.artifactList").val();
				var numberartifacts = fulllist.split("\n").length - 1;
			   	$("#number-artifacts").html(numberartifacts+"");
				$("#artifactListHidden").val($("textarea.artifactList").val());
			}
		}, "json");
	}
	}).error(function(){ alert("error!");});
  });