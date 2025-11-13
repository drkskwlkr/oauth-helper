<?php
// setup.php - Store OAuth credentials in session

session_start();

// Dynamically generate redirect URI based on current domain
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$default_redirect_uri = $protocol . '://' . $host . '/callback.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['client_id'] = trim($_POST['client_id']);
    $_SESSION['client_secret'] = trim($_POST['client_secret']);
    $_SESSION['redirect_uri'] = trim($_POST['redirect_uri']);
    $_SESSION['scope'] = trim($_POST['scope']);
    
    // Generate authorization URL
    $auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
        'client_id' => $_SESSION['client_id'],
        'redirect_uri' => $_SESSION['redirect_uri'],
        'response_type' => 'code',
        'scope' => $_SESSION['scope'],
        'access_type' => 'offline',
        'prompt' => 'consent'
    ]);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Credentials Saved - OAuth Helper</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <h1 class="card-title text-success mb-4">✓ Credentials Saved</h1>
                            <p class="lead">Click the button below to authorize with your OAuth provider:</p>
                            <a href="<?= htmlspecialchars($auth_url) ?>" class="btn btn-primary btn-lg mb-3">Authorize</a>
                            <div class="alert alert-info mt-4">
                                <strong>Or copy this URL:</strong>
                                <textarea class="form-control mt-2 font-monospace" rows="3" onclick="this.select()" readonly><?= htmlspecialchars($auth_url) ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OAuth Helper - Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="card-title mb-4">OAuth Helper</h1>
                        <p class="text-muted mb-4">Enter your OAuth credentials to get started.</p>
                        
                        <form method="post">
                            <div class="mb-3">
                                <label for="client_id" class="form-label">Client ID</label>
                                <input type="text" class="form-control" id="client_id" name="client_id" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="client_secret" class="form-label">Client Secret</label>
                                <input type="text" class="form-control" id="client_secret" name="client_secret" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="redirect_uri" class="form-label">Redirect URI</label>
                                <input type="text" class="form-control" id="redirect_uri" name="redirect_uri" value="<?= htmlspecialchars($default_redirect_uri) ?>" required>
				<div class="form-text small">
					<span class="badge bg-success">Auto-detected</span> Must match exactly what's configured in your OAuth client.
				</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="scope" class="form-label">Scope</label>
                                <input type="text" class="form-control" id="scope" name="scope" value="https://www.googleapis.com/auth/business.manage" required>
				<div class="form-text small">
					<span class="badge bg-warning text-dark">Example</span> Google Business Profile API scope provided. Replace with the appropriate scope(s) for your OAuth provider. Multiple scopes should be space-separated.
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Save & Generate Authorization URL</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
