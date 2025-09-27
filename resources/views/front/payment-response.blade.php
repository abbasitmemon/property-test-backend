<!doctype html>
<html lang="en">

    <head>
        <title>Product Purchased</title>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!-- Bootstrap CSS v5.2.1 -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>

    <body>

        <div class="container-fluid bg-dark">
            <div class="row vh-100">
                <div class="col-lg-6 m-auto">
                    <div class="alert alert-{{ $status }}" role="alert">
                        <h4 class="alert-heading">{{ $title }}</h4>
                        <hr>
                        <p>{{ $message }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bootstrap JavaScript Libraries -->
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js"></script>
    </body>

</html>
