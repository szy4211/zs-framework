<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>我是标题</title>

</head>

<body>

<div>
    <ul>
        <?php foreach ($data as $datum) { ?>
            <li><?php echo $datum['areaname'] ?></li>
        <?php } ?>
    </ul>

</div>

</body>

</html>