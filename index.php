<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>total-impact.org</title>
    </head>
    <body>
        <h1><em>Total</em> impact, bitches!</h1>
        <?php
        require_once './bootstrap.php';
        print_r($configs);
       
        $couch = new Couch_Client($configs['db']['dsn'], $configs['db']['db_name']);

        
        
        
        
        ?>
    </body>
</html>
