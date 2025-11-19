<?php
// setup.php - Store OAuth credentials in session

session_start();

// Load providers
$providers = json_decode(file_get_contents(__DIR__ . '/providers.json'), true);

// Handle provider selection via AJAX or form change
$selected_provider = $_GET['provider'] ?? 'google';
$provider_data = $providers[$selected_provider] ?? $providers['google'];

// Dynamically generate redirect URI based on current domain
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$default_redirect_uri = $protocol . '://' . $host . '/callback.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate endpoints before storing
    $auth_endpoint = trim($_POST['auth_endpoint']);
    $token_endpoint = trim($_POST['token_endpoint']);
    $redirect_uri = trim($_POST['redirect_uri']);
    
    if (!filter_var($auth_endpoint, FILTER_VALIDATE_URL) || parse_url($auth_endpoint, PHP_URL_SCHEME) !== 'https') {
        die('Invalid authorization endpoint. Must be a valid HTTPS URL.');
    }
    
    if (!filter_var($token_endpoint, FILTER_VALIDATE_URL) || parse_url($token_endpoint, PHP_URL_SCHEME) !== 'https') {
        die('Invalid token endpoint. Must be a valid HTTPS URL.');
    }
    
    if (!filter_var($redirect_uri, FILTER_VALIDATE_URL) || parse_url($redirect_uri, PHP_URL_SCHEME) !== 'https') {
        die('Invalid redirect URI. Must be a valid HTTPS URL.');
    }
    
    // Store validated values
    $_SESSION['client_id'] = trim($_POST['client_id']);
    $_SESSION['client_secret'] = trim($_POST['client_secret']);
    $_SESSION['redirect_uri'] = $redirect_uri;
    $_SESSION['scope'] = trim($_POST['scope']);
    $_SESSION['auth_endpoint'] = $auth_endpoint;
    $_SESSION['token_endpoint'] = $token_endpoint;
    
    // Generate authorization URL
    $auth_url = $_SESSION['auth_endpoint'] . '?' . http_build_query([
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
    <script>
        function updateProvider() {
            const select = document.getElementById('provider_select');
            const provider = select.value;
            window.location.href = '?provider=' + provider;
        }
    </script>
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
                                <label for="provider_select" class="form-label">OAuth Provider</label>
                                <select class="form-select" id="provider_select" onchange="updateProvider()">
                                    <?php foreach ($providers as $key => $provider): ?>
                                        <option value="<?= $key ?>" <?= $key === $selected_provider ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($provider['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (!empty($provider_data['docs_url'])): ?>
                                    <div class="form-text small">
                                        <a href="<?= htmlspecialchars($provider_data['docs_url']) ?>" target="_blank">View <?= htmlspecialchars($provider_data['name']) ?> OAuth documentation →</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
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
                            
                            <div class="mb-3">
                                <label for="scope" class="form-label">Scope</label>
                                <input type="text" class="form-control" id="scope" name="scope" value="<?= htmlspecialchars($provider_data['scope_example']) ?>" required>
                                <div class="form-text small">
                                    <span class="badge bg-warning text-dark">Example</span> <?= htmlspecialchars($provider_data['name']) ?> scope provided. Replace with the appropriate scope(s) for your OAuth provider. Multiple scopes should be space-separated.
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="auth_endpoint" class="form-label">Authorization Endpoint</label>
                                <input type="text" class="form-control font-monospace" id="auth_endpoint" name="auth_endpoint" value="<?= htmlspecialchars($provider_data['auth_endpoint']) ?>" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="token_endpoint" class="form-label">Token Endpoint</label>
                                <input type="text" class="form-control font-monospace" id="token_endpoint" name="token_endpoint" value="<?= htmlspecialchars($provider_data['token_endpoint']) ?>" required>
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
