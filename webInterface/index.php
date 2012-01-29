<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL ^ E_NOTICE);

require_once ("inc/config.inc.php");
require_once("inc/connection.inc.php");
require_once("inc/functions.inc.php");
require_once("inc/functions.php");
require_once("inc/PageManager.class.php");

$pMgr = new PageManager("pages", "page");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="keywords" content="siri,port,siri port,full,full siri port,jimmykane9,free full siri port,free,free port,siri iphone 4,iphone 4,iphone 3gs" />
        <meta name="description" content="Free and opensource Siri Proxy server by @JimmyKane9. Website by @WouterDS." />
        <title>The Three Little Pigs :: Siri Proxy | <?php echo $pMgr->getTitle(); ?></title>
        <link rel="stylesheet" type="text/css" media="screen" href="design/css/style.css" />
        <link rel="shortcut icon" href="design/img/favicon.ico" />
        <script src="http://code.jquery.com/jquery-latest.js"></script>
    </head>
    <body>
        <div id="top">
            <div class="centerContainer">
                <?php echo $pMgr->navigation(); ?>

                <div id="header">
                    <h1>The Three Little Pigs - A Siri Server That Needs To Be Fed</h1>
                </div>
            </div>
        </div>
        <div id="middle">
            <div class="centerContainer">
                <?php echo $pMgr->getPageContent(); ?>
            </div>
            <div id="fixFooter"></div>
        </div>
        <div id="bottom">
            <div class="centerContainer">
                <div class="left">
                    <div class="left">
                        <h3>Follow us for news &amp; updates!</h3>
                        <ul>
                            <li><a href="https://twitter.com/WouterDS" class="twitter-follow-button" data-show-count="true" style="width: 250px;">Follow @WouterDS</a></li>
                            <li><a href="https://twitter.com/JimmyKane9" class="twitter-follow-button" data-show-count="true" style="width: 250px;">Follow @JimmyKane9</a></li>
                            <li><a href="https://twitter.com/thpryrchn" class="twitter-follow-button" data-show-count="true" style="width: 250px;">Follow @thpryrchn </a></li>
                            <li><a href="https://twitter.com/HisyamNasir" class="twitter-follow-button" data-show-count="true" style="width: 250px;">Follow @HisyamNasir </a></li>
                        </ul>
                    </div>
                    <div class="right">
                        <h3>Like this on Facebook!</h3>
                        <fb:like-box href="http://www.facebook.com/pages/The-Three-Little-Pigs-Siri-Proxy/163734087063210" width="320" height="120" show_faces="false" border_color="#000" stream="true" header="true" style="background: #EEE;"></fb:like-box>
                    </div>
                </div>
                <div class="right">
                    <h3>Contact Us</h3>
                    <div>
                        <ul>
                            <li>T: <a href="http://twitter.com/wouterds">@WouterDS</a></li>
                            <li>T: <a href="https://twitter.com/thpryrchn">@thpryrchn</a></li>
                            <li>T: <a href="https://twitter.com/HisyamNasir">@HisyamNasir</a></li>
                            <li>T: <a href="http://twitter.com/jimmykane9">@JimmyKane9</a></li>
                            <li>G+: <a href="http://gplus.to/jimmykane">http://gplus.to/jimmykane</a></li>
                        </ul>         
                    </div>
                </div>
                <div class="clear"></div>
                <div id="footerBar">
                    Siri Proxy Server brought to you by <a href="http://twitter.com/jimmykane9">@JimmyKane9</a> | Website by <a href="http://twitter.com/wouterds">@WouterDS</a>
                </div>
            </div>
            <div id="fb-root"></div>    
            <script>(function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s); js.id = id;
                js.src = "//connect.facebook.net/en_GB/all.js#xfbml=1&appId=323947750954563";
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));</script>
            <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
            <script type="text/javascript">
            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', 'UA-28164962-1']);
            _gaq.push(['_trackPageview']);

            (function() {
                var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
                ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
            })();
            </script>            
            <script src="js/cufon-yui.js" type="text/javascript"></script>
            <script src="js/League_Gothic_400.font.js" type="text/javascript"></script>
            <script src="js/Tisa_400-Tisa_700-Tisa_italic_400-Tisa_italic_700.font.js" type="text/javascript"></script>
            <script type="text/javascript">
            Cufon.replace('#middle p, #middle ul, #middle ol, #middle label, #middle table, #bottom', { hover: true, fontFamily: 'Tisa' });
            Cufon.replace('#top, h1, h2, h3, h4, h5, h6', { hover: true, fontFamily: 'League Gothic' });
            </script>
           
    </body>
</html>
