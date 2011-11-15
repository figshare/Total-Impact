$.ajaxSetup ({
    cache: false
});
var ajax_load = "<img src='./ui/img/ajax-loader.gif' alt='loading...' />";

writeToList = function(str){
    returnedIds = str.split("\n");
    var len = returnedIds.length
    for (i=0; i<len; i++) {
      returnedIds[i] = "<li><a href='#'>remove</a><span class='object-id'>"+returnedIds[i]+"</span></li>";
    }
    return returnedIds.join("\n");
}

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
    $("div.quick-collection a").click(function(){
        $(this).parent().next().slideToggle();
    }).parent().next().hide();

    var fulllist = $("textarea.artifactList").val();
    var numberartifacts = fulllist.split("\n").length - 1;
    $("#number-artifacts").html(numberartifacts+"");

    $("#importers button.import-button").click(function(){
        $(this).hide().parent().append("<span class='loading'><img src='./ui/img/ajax-loader.gif'> Loading...<span>");
        var $thisButton = $(this);
        var sourceName = $(this).attr("id");
        var souceQuery = $(this).siblings("input").val();
        $.get("./seed.php?type="+sourceName+"&name="+souceQuery, function(response,status,xhr){
            $thisButton.siblings("span.loading").empty()
                .append("<span class='response'><span class='count'>"+response.artifactCount+"</span> added</span>");
            $("ul#collection-list").append(writeToList(response.artifactIds));

            
        }, "json");
    });
});