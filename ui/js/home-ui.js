

$.ajaxSetup ({
    cache: false
});
var ajax_load = "<img src='./ui/img/ajax-loader.gif' alt='loading...' />";

$(document).ready(function(){
  // tooltips
    $('#importers ul li')
        .prepend('<span class="pointer">▶</span>') // hack; these arrows should be entities, but that causes probs when replacing...
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

    $("#importers button.import-button").click(function(){
        $(this).hide().parent().append("<span class='loading'><img width='14' src='./ui/img/ajax-loader.gif'> Loading...<span>");
    });

  });