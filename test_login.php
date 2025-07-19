<?php

// Script de test pour simuler une connexion complète
$baseUrl = 'http://localhost:8000';

echo "=== Test de connexion complète ===\n";

// 1. Vérifier l'état initial
echo "\n1. État initial:\n";
$response = file_get_contents($baseUrl . '/auth/status');
$status = json_decode($response, true);
echo "Authentifié: " . ($status['authenticated'] ? 'OUI' : 'NON') . "\n";
echo "Session ID: " . $status['session_id'] . "\n";

// 2. Récupérer le token CSRF depuis la page de login
echo "\n2. Récupération du token CSRF:\n";
$loginPage = file_get_contents($baseUrl . '/login');
if (preg_match('/Debug CSRF Token:\s*([a-zA-Z0-9\.\-_]+)/', $loginPage, $matches)) {
    $csrfToken = $matches[1];
    echo "Token CSRF trouvé: " . $csrfToken . "\n";
} else {
    echo "Token CSRF non trouvé dans la page de login\n";
    echo "Contenu de la page: " . substr($loginPage, 0, 500) . "...\n";
    exit(1);
}

// 3. Simuler une connexion avec gestion des cookies
echo "\n3. Tentative de connexion:\n";
$postData = http_build_query([
    '_username' => 'test@example.com',
    '_password' => 'password123',
    '_csrf_token' => $csrfToken
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . strlen($postData),
            'Cookie: PHPSESSID=' . $status['session_id']
        ],
        'content' => $postData,
        'follow_location' => false
    ]
]);

$response = file_get_contents($baseUrl . '/login', false, $context);
echo "Réponse de connexion: " . substr($response, 0, 200) . "...\n";

// 4. Vérifier l'état après connexion
echo "\n4. État après connexion:\n";
$response = file_get_contents($baseUrl . '/auth/status');
$status = json_decode($response, true);
echo "Authentifié: " . ($status['authenticated'] ? 'OUI' : 'NON') . "\n";
echo "Session ID: " . $status['session_id'] . "\n";

if ($status['user']) {
    echo "Utilisateur connecté: " . $status['user']['email'] . "\n";
}

// 5. Tester l'accès à une page protégée
echo "\n5. Test d'accès à une page protégée:\n";
$profileResponse = file_get_contents($baseUrl . '/profile');
if (strpos($profileResponse, 'Access Denied') !== false) {
    echo "❌ Accès refusé à /profile\n";
} else {
    echo "✅ Accès autorisé à /profile\n";
}

echo "\n=== Fin du test ===\n"; 