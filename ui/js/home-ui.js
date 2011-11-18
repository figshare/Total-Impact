$.ajaxSetup ({
    cache: false
});
var ajax_load = "<img src='./ui/img/ajax-loader.gif' alt='loading...' />";

addIdsToEditPane = function(str){
    if ($("#importers").width() > 340){
        $("#pullers")
            .animate({
                "margin-top": 0,
                left: 0
            }, 1000)
            .parent().siblings(" #edit-collection")
            .animate({
                width: "340px",
                "padding-right": "40px"
            }, 1000)
            .siblings("#importers")
            .animate({
                width: "340px"
            }, 1000, function(){
                return addIdsToEditPane(str);
            })
    }
    else {
        returnedIds = str.split("\n");
        var len = returnedIds.length
        for (i=0; i<len; i++) {
          returnedIds[i] = "<li><a class='remove' href='#'>remove</a><span class='object-id'>"+returnedIds[i]+"</span></li>";
        }
        $("ul#collection-list").append($(returnedIds.join("")).hide().fadeIn(1000));
        $("#artcounter span.count")
            .text($("ul#collection-list li").size())
            .css("color", "#933")
            .animate({"color": "#333"}, 1000)
        return true;
    }

}

$(document).ready(function(){
    
    // report page stuff
    $('ul.metrics li').tooltip();
    $('a#copy-permalink').zclip({
        path:'ui/js/ZeroClipboard.swf',
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
    $("ul#collection-list li").live("click", function(){
        $(this).slideUp(250, function(){$(this).remove();} );
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
        if ($thisDiv.find("textarea")[0]) { // there's a sibling textarea
            addIdsToEditPane($thisDiv.find("textarea").val());
        }
        else {
            $(this).hide().after("<span class='loading'><img src='./ui/img/ajax-loader.gif'> Loading...<span>");
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
                    addIdsToEditPane(response.artifactIds);
                    $thisDiv.find("span.loading")
                        .empty()
                        .append( 
                            $("<span class='response'><span class='count'>"+response.artifactCount+"</span> added</span>")
                            .hide()
                            .fadeIn(500, function(){
                                $(this).delay(2000).fadeOut(500, function(){
                                    $(this)
                                    .parent()
                                    .siblings("button")
                                    .fadeIn(500)
                                    .siblings("span.loading")
                                    .remove()

                                })
                            })
                        )
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

    // remove prepoluated values in form inputs
    $("textarea").add("input").focus(function(){
        if (this.defaultValue == this.value) {
            this.value = "";
        }
    }).blur(function(){
        if ($(this).val() == "") {
            $(this).val(this.defaultValue);
        }
    })

    // dialog for supported IDs
    $("div#manual-add p.prompt a").click(function(){
        TINY.box.show({url:'supported-ids.php'})
        return false;
    });

    // scroll down to recently shared reports
    $("#link-to-recently-shared").click(function(){
        $("html, body").animate({ scrollTop: $(document).height() }, 1000)
            .find("#twitterfeed h4")
            .css("cssText", "background: transparent !important")
            .parent()
            .css("background", "#933")
            .animate({"background-color": "#eee"}, 1500, "linear")
        return false;
    });

    // table of contents
    $('#toc').tocBuilder({ type: 'headings', startLevel: 2, endLevel: 2, backLinkText: 'back to contents' });


});