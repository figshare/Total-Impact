<?php
    function getPageTitle(){
        if ( !defined(PAGE_SUBTITLE) ){
            define('PAGE_SUBTITLE', 'uncover the invisible impact of reseach');
        }
        return "total-impact: " . PAGE_SUBTITLE;

    }
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <!-- this makes way too many calls to external files right now, in order to
        facilitate development. These need to be condensed, for performance reasons,
        in a production build. -->
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <title><?php echo getPageTitle(); ?></title>
        <link href='http://fonts.googleapis.com/css?family=Lobster+Two:400italic' rel='stylesheet' type='text/css' />
        <link rel="stylesheet" type="text/css" href="./ui/main.css" />
        <link rel="stylesheet" type="text/css" href="./ui/homepage.css" />
        <link rel="stylesheet" type="text/css" href="./ui/report.css" />
        <link rel="icon" type="image/ico" href="./ui/favicon.ico" />

        <script type="text/javascript">
            //Google Analytics code
            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', 'UA-23384030-1']);
            _gaq.push(['_trackPageview']);

            (function() {
                var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
                ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
            })();
        </script>

        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
        <script type="text/javascript" src="./ui/js/json2.min.js"></script>
        <script type="text/javascript" src="./ui/js/tinybox.js"></script>
        <script type="text/javascript" src="./ui/js/jquery.headerlinks.js"></script>
        <script type="text/javascript" src="ui/js/jquery.tooltip.js"></script>
        <script type="text/javascript" src="ui/js/jquery.zclip.js"></script>
        <script type="text/javascript" src="ui/js/jquery.color.js"></script>
        <script type="text/javascript" src="./ui/js/home-ui.js"></script>

    </head>
    <body>
        <div id="header">
            <div class="wrapper">
                <h1><span class="loathsome-hack"></span><a href="/">total-impact</a><span class="loathsome-hack"></span></h1>
                <ul id="nav">
                    <li><a href="/about">about</a></li>
                    <li><a href="http://twitter.com/#!/totalImpactdev">twitter</a></li>
                </ul>
            </div>
        </div><!-- end header -->

