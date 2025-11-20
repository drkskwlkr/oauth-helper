<?php
// generate.php - Standalone access token generator

session_start();

// Load providers
$providers = json_decode(file_get_contents(__DIR__ . '/providers.json'), true);

// Handle provider selection
$selected_provider = $_GET['provider'] ?? 'google';
$provider_data = $providers[$selected_provider] ?? $providers['google'];

if (isset($_POST['refresh_token'])) {
    $refresh_token = trim($_POST['refresh_token']);
    $client_id = trim($_POST['client_id']);
    $client_secret = trim($_POST['client_secret']);
    $token_endpoint = trim($_POST['token_endpoint']);
    
    // Validate token endpoint
    if (!filter_var($token_endpoint, FILTER_VALIDATE_URL) || parse_url($token_endpoint, PHP_URL_SCHEME) !== 'https') {
        die('Invalid token endpoint. Must be a valid HTTPS URL.');
    }
    
    $data = [
        'refresh_token' => $refresh_token,
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'grant_type' => 'refresh_token'
    ];
    
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
                                
                                <a href="generate.php" class="btn btn-primary">Generate Another</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card shadow-sm">
                            <div class="card-body p-4">
                                <h1 class="card-title text-danger mb-4">Error</h1>
                                <pre class="bg-light p-3 rounded"><?= htmlspecialchars(json_encode($tokens, JSON_PRETTY_PRINT)) ?></pre>
                                <a href="generate.php" class="btn btn-secondary mt-3">Try Again</a>
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Access Token - OAuth Helper</title>
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
                        <h1 class="card-title mb-3">Generate Access Token</h1>
                        <p class="text-muted mb-4">Use your saved refresh token to generate a new access token.</p>
                        
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
                                <label for="token_endpoint" class="form-label">Token Endpoint</label>
                                <input type="text" class="form-control font-monospace" id="token_endpoint" name="token_endpoint" value="<?= htmlspecialchars($provider_data['token_endpoint']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="client_id" class="form-label">Client ID</label>
                                <input type="text" class="form-control" id="client_id" name="client_id" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="client_secret" class="form-label">Client Secret</label>
                                <input type="text" class="form-control" id="client_secret" name="client_secret" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="refresh_token" class="form-label">Refresh Token</label>
                                <textarea class="form-control font-monospace" id="refresh_token" name="refresh_token" rows="3" required></textarea>
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
