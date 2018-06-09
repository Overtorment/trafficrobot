<!doctype html>
<html class="no-js" lang="">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>TrafficRobot</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

</head>
<body>
<!--[if lt IE 8]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->



<h2>You are sending message to uniq connector <?php echo $args['key']; ?></h2>

<form method="POST" action="<?php echo $args['action']; ?>">
    <textarea cols="50" rows="10" name="data"></textarea>
    <br/>
    <input type="submit">
</form>

<br/><br/>
You can also send data programmatically via
<pre>curl -X POST -d "Wheres the money Lebowski?" <?php echo $args['baseUrl']; ?>/<?php echo $args['key']; ?></pre> <br/>
or <br/>
<pre>echo "Now is `date`" | curl -d @- <?php echo $args['baseUrl']; ?>/<?php echo $args['key']; ?></pre>




</body>
</html>

