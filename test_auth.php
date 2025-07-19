<?php

// Script de test pour vérifier l'authentification
$baseUrl = 'http://localhost:8000';

echo "=== Test d'authentification ===\n";

// 1. Vérifier l'état initial
echo "\n1. État initial:\n";
$response = file_get_contents($baseUrl . '/auth/status');
$status = json_decode($response, true);
echo "Authentifié: " . ($status['authenticated'] ? 'OUI' : 'NON') . "\n";
echo "Session ID: " . $status['session_id'] . "\n";

// 2. Simuler une connexion
echo "\n2. Tentative de connexion:\n";
$postData = http_build_query([
    '_username' => 'test@example.com',
    '_password' => 'password123',
    '_csrf_token' => 'test_token'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . strlen($postData)
        ],
        'content' => $postData,
        'follow_location' => false
    ]
]);

$response = file_get_contents($baseUrl . '/login', false, $context);
echo "Réponse de connexion: " . substr($response, 0, 200) . "...\n";

// 3. Vérifier l'état après connexion
echo "\n3. État après connexion:\n";
$response = file_get_contents($baseUrl . '/auth/status');
$status = json_decode($response, true);
echo "Authentifié: " . ($status['authenticated'] ? 'OUI' : 'NON') . "\n";
echo "Session ID: " . $status['session_id'] . "\n";

echo "\n=== Fin du test ===\n"; 