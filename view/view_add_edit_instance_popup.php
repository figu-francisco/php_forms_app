<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm</title>
    <base href="<?= $web_root ?>" >
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <script src="css/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="//fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@30..48,100..700,0..1,-50..200&icon_names=contact_support" >
</head>

<body class="container d-flex flex-column align-items-center py-5 bg-secondary-subtle" data-bs-theme="dark" style="max-width:700px" >
    <div class="card text-center  w-100">
        <div class="card-header">
            <h3 >You have already answered this form.</h3>
            <p class="text-secondary">You can view your submission or submit again.</p>
        </div>
        <div class="card-body">
            
            <h2 class="card-title">What would you like to do?</h2>
        </div>
        <div class="d-flex justify-content-center flex-wrap gap-3 m-3 ">
            <a href="instance/readonly/<?= $instance->get_id() ?>/1/<?= $encoded_search ?>">
                <span class="btn btn-outline-success" style="width:150px">View submission</span>
            </a>
            <form action="instance/submit_new/<?= $encoded_search ?>" method="post">
                    <input type="number" name="form_id" value=<?= $form->get_id() ?> hidden>    
                    <button type="submit" class="btn btn-outline-primary" style="width:150px">Submit again</button>
            </form>
            <a href="forms/index/<?= $encoded_search ?>">
                <span class="btn btn-outline-secondary" style="width:150px">Cancel</span>
            </a>
        </div>
    </div>
</body>

</html>