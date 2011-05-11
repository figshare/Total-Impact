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
       
        if (isset($_POST['submitted'])){
            $collection = new Collection(
                    $couch,
                    $_POST['name'],
                    $_POST['ids']);
            $id = $collection->make();
        }
        else {
            ?>
        
        <form method="POST" name="main" action="./index.php">
            <label for="name">What's your name?</label>
            <input name="name" id="name" />
            
            <label for="ids">Put your IDs here.</label>
            <textarea name="ids" id="ids"></textarea>
            
            <input type="hidden" name="submitted" value="true" />
            <input type="submit" id="submit" value="submit" />
        </form>
        
        <?php
            
            
            
        }

        
        
        
        
        ?>
    </body>
</html>
