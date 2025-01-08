<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Welcome</title>
    <style>
        body {
            background: linear-gradient(120deg, #fdfbfb, #ebedee);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            text-align: center;
            padding: 50px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-custom {
            margin: 10px;
            width: 200px;
            font-size: 18px;
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }
        .btn-secondary:hover {
            background-color: #495057;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to User Management</h1>
        <p class="lead">Manage your account securely and easily.</p>
        <a href="register.php" class="btn btn-primary btn-custom">Register</a>
        <a href="login.php" class="btn btn-secondary btn-custom">Login</a>
    </div>
</body>
</html>
