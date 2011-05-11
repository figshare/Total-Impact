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
        require_once './input.php';
       
        if (isset($_POST['submitted'])){
            // do stuff with the submitted form
        }
        else {
            ?>
        
        <form method="POST" name="main" action="./index.php">
            <label for="name">What's your name?</label>
            <input name="ids" id="name"
            <label for="ids">Put your IDs here.</label>
            <textarea name="ids"></textarea>
        </form>
        
        <?php
            
            
            
        }

        
        
        
        
        ?>
    </body>
</html>
