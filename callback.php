<?php
// callback.php - Handle OAuth callbacks and generate tokens

session_start();

$client_id = $_SESSION['client_id'] ?? null;
$client_secret = $_SESSION['client_secret'] ?? null;
$redirect_uri = $_SESSION['redirect_uri'] ?? null;

if (!$client_id || !$client_secret || !$redirect_uri) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error - OAuth Helper</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="alert alert-danger">
                        <h4 class="alert-heading">No Credentials Found</h4>
                        <p>Please set up your OAuth credentials first.</p>
                        <hr>
                        <a href="setup.php" class="btn btn-danger">Go to Setup</a>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Handle initial authorization (exchange code for tokens)
if (isset($_GET['code'])) {
    $returned_state = $_GET['state'] ?? '';
    $stored_state = $_SESSION['oauth_state'] ?? '';
    
    if (empty($returned_state) || $returned_state !== $stored_state) {
        die('Invalid state parameter. Possible CSRF attack.');
    }
    
    unset($_SESSION['oauth_state']);

    $auth_code = $_GET['code'];
    
    $data = [
        'code' => $auth_code,
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code'
    ];
    
    $token_endpoint = $_SESSION['token_endpoint'] ?? null;

    if (!$token_endpoint) {
      die('Error: Token endpoint not found in session. Please start from setup.php');
    }

    if (!filter_var($token_endpoint, FILTER_VALIDATE_URL)) {
      die('Invalid token endpoint URL');
    }

    if (parse_url($token_endpoint, PHP_URL_SCHEME) !== 'https') {
      die('Token endpoint must use HTTPS');
    }

    $ch = curl_init($token_endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $tokens = json_decode($response, true);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Authorization Complete - OAuth Helper</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <?php if (isset($tokens['refresh_token'])): ?>
                        <div class="card shadow-sm mb-3">
                            <div class="card-body p-4">
                                <h1 class="card-title text-success mb-4">✓ Authorization Complete</h1>
                                
                                <div class="alert alert-warning">
                                    <strong>⚠️ Save your refresh token!</strong> You won't see it again.
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Refresh Token</label>
                                    <textarea class="form-control font-monospace" rows="3" onclick="this.select()" readonly><?= htmlspecialchars($tokens['refresh_token']) ?></textarea>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Access Token (valid 1 hour)</label>
                                    <textarea class="form-control font-monospace" rows="3" onclick="this.select()" readonly><?= htmlspecialchars($tokens['access_token']) ?></textarea>
                                </div>
                                
                                <a href="callback.php" class="btn btn-primary">Generate New Access Token</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card shadow-sm">
                            <div class="card-body p-4">
                                <h1 class="card-title text-danger mb-4">Error</h1>
                                <pre class="bg-light p-3 rounded"><?= htmlspecialchars(json_encode($tokens, JSON_PRETTY_PRINT)) ?></pre>
                                <a href="setup.php" class="btn btn-secondary mt-3">Back to Setup</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Generate new access token from refresh token
if (isset($_POST['refresh_token'])) {
    $refresh_token = trim($_POST['refresh_token']);
    
    $data = [
        'refresh_token' => $refresh_token,
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'grant_type' => 'refresh_token'
    ];
    
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $tokens = json_decode($response, true);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Token Generated - OAuth Helper</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <?php if (isset($tokens['access_token'])): ?>
                        <div class="card shadow-sm">
                            <div class="card-body p-4">
                                <h1 class="card-title text-success mb-4">✓ Access Token Generated</h1>
                                <p class="text-muted">Valid for 1 hour</p>
                                
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Access Token</label>
                                    <textarea class="form-control font-monospace" rows="3" onclick="this.select()" readonly><?= htmlspecialchars($tokens['access_token']) ?></textarea>
                                </div>
                                
                                <div class="alert alert-info">
                                    <strong>Use in API requests:</strong><br>
                                    <code>Authorization: Bearer <?= htmlspecialchars(substr($tokens['access_token'], 0, 30)) ?>...</code>
                                </div>
                                
                                <a href="callback.php" class="btn btn-primary">Generate Another</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card shadow-sm">
                            <div class="card-body p-4">
                                <h1 class="card-title text-danger mb-4">Error</h1>
                                <pre class="bg-light p-3 rounded"><?= htmlspecialchars(json_encode($tokens, JSON_PRETTY_PRINT)) ?></pre>
                                <a href="callback.php" class="btn btn-secondary mt-3">Try Again</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Default form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Access Token - OAuth Helper</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="card-title mb-3">Generate Access Token</h1>
                        <p class="text-muted mb-4">Using credentials from session. <a href="setup.php">Change credentials</a></p>
                        
                        <form method="post">
                            <div class="mb-3">
                                <label for="refresh_token" class="form-label">Refresh Token</label>
                                <textarea class="form-control font-monospace" id="refresh_token" name="refresh_token" rows="3" required></textarea>
                                <div class="form-text">Paste your saved refresh token here.</div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Generate Access Token</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
