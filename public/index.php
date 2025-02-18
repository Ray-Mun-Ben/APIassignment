<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Welcome</title>
    <style>
        body {
            background-image: url("fabio-fistarol-qai_Clhyq0s-unsplash.jpg"); 
            background-size: cover; /* or contain, auto, etc. - see below */
            background-repeat: no-repeat; /* Optional: Prevent image from repeating */
            background-position: center; /* Optional: Position the image */
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0; /* Important: Remove default body margin */
        }

        .container {
            text-align: center;
            padding: 50px;
            background: rgba(255, 255, 255, 0.8); /* Semi-transparent white background */
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* ... rest of your CSS ... */
    </style>
</head>
<body>
    <div class="container">
        <h1>Feel Fresh Resort Booking & Management System</h1>
        <p class="lead">ver.1.3.0 <i>still in development</i></p>
        <a href="../views/auth/register.php" class="btn btn-primary btn-custom">Register</a>
<a href="../views/auth/login.php" class="btn btn-secondary btn-custom">Login</a>


    </div>
</body>
</html>