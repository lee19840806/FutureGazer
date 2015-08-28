<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <meta name="description" content="The online portfolio forecast platform">
    <meta name="Keywords" content="Future Gazer,Forecast">
    <meta name="author" content="Shicheng Li">
    
    <link rel="icon" href="/favicon.ico">
    <title>Future Gazer</title>

    <link href="/css/bootstrap.css" rel="stylesheet">
    <link href="/css/fileinput.css" rel="stylesheet">
    <script src="/js/jquery-2.1.4.js"></script>
    <script src="/js/bootstrap.js"></script>
    <script src="/js/fileinput.js"></script>
    <script src="/js/papaparse.js"></script>
</head>

<body>
    <?= $this->element('header') ?>
    <?= $this->fetch('content') ?>
</body>
</html>
