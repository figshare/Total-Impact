$.ajaxSetup ({
    cache: false
});
var ajax_load = "<img src='./ui/img/ajax-loader.gif' alt='loading...' />";

addIdsToEditPane = function(str){
    returnedIds = str.split("\n");
    var len = returnedIds.length
    for (i=0; i<len; i++) {
      returnedIds[i] = "<li><a class='remove' href='#'>remove</a><span class='object-id'>"+returnedIds[i]+"</span></li>";
    }
    $("ul#collection-list").append(returnedIds.join("\n"));
    $("#artcounter span.count").text($("ul#collection-list li").size())
    return true;
}

$(document).ready(function(){
    // show/hide stuff
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

    // click to remove object IDs in the edit pane
    $("ul#collection-list li a.remove").live("click", function(){
        $(this).parent().remove();
        $("#artcounter span.count").text($("ul#collection-list li").size())
        return false;
    })
    $("a#clear-artifacts").click(function(){
        $("ul#collection-list").empty();
        $("#artcounter span.count").text("0")
        return false;
    });

    var fulllist = $("textarea.artifactList").val();
    var numberartifacts = fulllist.split("\n").length - 1;
    $("#number-artifacts").html(numberartifacts+"");

    // use one-click importers to add objects to the edit pane
    $("#importers button.import-button").click(function(){
        var $thisButton = $(this);
        var sourceName = $(this).attr("id");
        var souceQuery = $(this).siblings("input").val();
        if ($(this).parent().attr("id") == "manual-add") {
            addIdsToEditPane($thisButton.siblings("textarea").val());
        }
        else {
            $(this).hide().parent().append("<span class='loading'><img src='./ui/img/ajax-loader.gif'> Loading...<span>");
            $.get("./seed.php?type="+sourceName+"&name="+souceQuery, function(response,status,xhr){
                $thisButton.siblings("span.loading").empty()
                    .append( 
                        $("<span class='response'><span class='count'>"+response.artifactCount+"</span> added</span>").hide().fadeIn()
                    );
                    addIdsToEditPane(response.artifactIds);
            }, "json");
        }
    });

    // use the manual add box to add objects to the edit pane

});