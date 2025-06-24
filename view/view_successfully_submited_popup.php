<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Success</title>
    <base href="<?= $web_root ?>" >
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <script src="css/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="container d-flex flex-column align-items-center py-5 bg-secondary-subtle" data-bs-theme="dark" style="max-width:700px" >
    <div class="card text-center  w-100">
        <div class="card-header">
            <h2 class=""><?= $msg ?></h2>
        </div>
        <div class="card-body">
            <a  href="forms/index/<?=$encoded_search?>" >
                <span class="btn btn-primary" style="width:150px">Ok</span>
            </a>
        </div>
    </div>
</body>
</html>