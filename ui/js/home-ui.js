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
    $("ul#collection-list").append($(returnedIds.join("")).hide().fadeIn(1000));
    $("#artcounter span.count").text($("ul#collection-list li").size())
    return true;
}

$(document).ready(function(){
    
    // report page stuff
    $('ul.metrics li').tooltip();
    $('a#copy-permalink').zclip({
        path:'ui/jquery/ZeroClipboard.swf',
        copy:$('#permalink a.copyable').text(),
        afterCopy:function(){
            $('a#copy-permalink').text('copied.');
        }
    });
    $('#about-metrics').hide();

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

    // use importers to add objects to the edit pane
    $("button.import-button").click(function(){
        var $thisDiv = $(this).parent();
        var sourceName = $(this).attr("id");
        var souceQuery = $(this).siblings("input").val();
        if ($thisDiv.attr("id") == "manual-add") {
            addIdsToEditPane($thisDiv.find("textarea").val());
        }
        else {
            $(this).replaceWith("<span class='loading'><img src='./ui/img/ajax-loader.gif'> Loading...<span>");
            $.get("./seed.php?type="+sourceName+"&name="+souceQuery, function(response,status,xhr){
                if ($thisDiv.parent().hasClass("quick-collection")) { // it's a quick collection
                    displayStr = (typeof response.contacts == "undefined") ? response.groups : response.contacts;
                    $thisDiv.find("span.loading").empty();
                    $thisDiv.append(
                        // suggest we reformat api respose; presentation should be client's job
                        $("<div class='response'>"+displayStr+"</div>").hide().slideDown()
                    );
                }
                else {
                    $thisDiv.find("span.loading")
                        .empty()
                        .append( 
                            $("<span class='response'><span class='count'>"+response.artifactCount+"</span> added</span>").hide().fadeIn(1000)
                        );
                    addIdsToEditPane(response.artifactIds);
                }
            }, "json");
        }
    });
    
    // submitting the object IDs
    $("#id-form").submit(function(){
        var ids = [];
        $("ul#collection-list span.object-id").each(function(){
           ids.push($(this).text()); 
        });
        if (ids.length == 0) {
            alert("Looks like you haven't added any research objects to the collection yet.")
            return false;
        } else {
            $("form#id-form input#artifacts-list").val(ids.join("\n"));
            return true;
        }
    });

});