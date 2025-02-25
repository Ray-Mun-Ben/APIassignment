<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feel Fresh Resort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Background Styling */
        body {
            background: url("fabio-fistarol-qai_Clhyq0s-unsplash.jpg") no-repeat center center/cover;
            color: white;
            text-align: center;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Welcome Box */
        .hero-container {
            background: rgba(0, 0, 0, 0.6);
            padding: 60px;
            border-radius: 15px;
            margin: 30px auto;
            max-width: 800px;
        }

        /* Image Cards */
        .feature-card {
            transition: transform 0.3s ease-in-out;
        }
        .feature-card:hover {
            transform: scale(1.05);
        }

        /* Footer */
        .footer {
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 20px;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Hero Section -->
    <div class="hero-container">
        <h1 class="fw-bold">Welcome to Feel Fresh Resort</h1>
        <p class="lead">Your dream vacation awaits – experience paradise with us.</p>
        <a href="register.php" class="btn btn-primary btn-lg mx-2">Register</a>
        <a href="login.php" class="btn btn-secondary btn-lg mx-2">Login</a>
    </div>

    <!-- Features Section -->
    <div class="container mt-5">
        <h2 class="text-center mb-4">Why Choose Feel Fresh Resort?</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="card feature-card">
                    <img src="images/resort-pool.jpeg" class="card-img-top" alt="Luxury Pool">
                    <div class="card-body">
                        <h5 class="card-title">Luxury Pool & Spa</h5>
                        <p class="card-text">Relax in our world-class infinity pool and rejuvenating spa.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card">
                    <img src="images/resort-dining.jpeg" class="card-img-top" alt="Fine Dining">
                    <div class="card-body">
                        <h5 class="card-title">Gourmet Dining</h5>
                        <p class="card-text">Enjoy 5-star dining experiences with exotic cuisines.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card">
                    <img src="images/resort-activities.jpeg" class="card-img-top" alt="Adventure Activities">
                    <div class="card-body">
                        <h5 class="card-title">Exciting Activities</h5>
                        <p class="card-text">From scuba diving to sunset cruises, adventure awaits!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Section -->
    <div class="container mt-5">
        <h2 class="text-center">Get in Touch</h2>
        <p class="text-center">For inquiries, bookings, or special requests, reach out to us.</p>
        <div class="row">
            <div class="col-md-6">
                <h5>Email</h5>
                <p>contact@feelfreshresort.com</p>
            </div>
            <div class="col-md-6">
                <h5>Phone</h5>
                <p>+1 (800) 555-1234</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        &copy; <?= date("Y") ?> Feel Fresh Resort | Designed for luxury and relaxation.
    </div>

</body>
</html>