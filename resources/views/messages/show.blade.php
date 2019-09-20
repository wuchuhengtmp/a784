<html>
    <head>

    </head>
    <body >
        <h2>{{ $title }}</h2>
        <h5>{{ $created_at }}</h5>
        <div>
            <?php 
                echo  htmlspecialchars_decode($content);
            ?>
        </div>
    </body>
<html>

