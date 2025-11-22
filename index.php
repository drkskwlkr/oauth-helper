<?php
// index.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OAuth Helper - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body p-5 text-center">
                        <h1 class="card-title mb-4">OAuth Helper</h1>
                        <p class="lead mb-4">Generate OAuth 2.0 tokens for any API provider.</p>
                        <p class="text-muted mb-4">Pre-configured to support Google, GitHub, Microsoft, Facebook, LinkedIn, Spotify out of the box. Data for other OAuth providers can be used ad hoc or added permanently to the included <strong>providers.json</strong> file.</p>
                        
                        <div class="d-grid gap-2">
                            <a href="setup.php" class="btn btn-primary btn-lg">Get Started (New Setup)</a>
                            <a href="generate.php" class="btn btn-outline-secondary">Generate Token (Have Refresh Token)</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
