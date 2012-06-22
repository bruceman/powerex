<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>test page</title>
    </head>
    <body>
        <?php include ('nav.php') ?>
        <hr/>
        <h2>name : {$name}</h2>
        <h2>age: {$age}</h2>
        <h2>age * 2 = {= $age * 2}</h2>
        <hr/>
        <h2>{$books[0]} - {$books[1]}</h2>
        <hr/>
        <h2>{$songs["a"]} - {$songs["zj"][0]} - {$songs["zj"][1]}</h2>
        <p:if test="$books">
            <h3>books count : {= count($books) }</h3>
            <h3> echo something : {: echo "hello"}</h3>
        <p:elseif test="$name">
            <h3>else if statment</h3>
        <p:else>
            <h3>else statment</h3>
        </p:if>
        <hr/>
        <p:set var="$i" value="1" />
        <p:while test="$i++<3">
            <h4>{$i}</h4>
        </p:while>
        <p:unset var="$i"/>
        <h5>{= isset($i) ? "true" : "false"}</h5>
        <hr/>
        <p:foreach value="$book" in="$books">
            <h4>{$book}</h4>
        </p:foreach>
        <hr/>
        <p:foreach key="$ind" value="$name" in="$books">
            <h4>{$ind} -- {$name}</h4>
        </p:foreach>
    </body>
</html>
