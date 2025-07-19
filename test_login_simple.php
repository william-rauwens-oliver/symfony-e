<?php

$baseUrl = 'http://localhost:8000';
$cookieFile = '/tmp/test_cookies.txt';

// Test 1: Vérifier le statut avant connexion
echo "=== Test 1: Statut avant connexion ===\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/auth/status');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$response = curl_exec($ch);
curl_close($ch);
echo $response . "\n\n";

// Test 2: Récupérer le CSRF token
echo "=== Test 2: Récupération du CSRF token ===\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$loginPage = curl_exec($ch);
curl_close($ch);

if (preg_match('/name="_csrf_token" value="([^"]+)"/', $loginPage, $matches)) {
    $csrfToken = $matches[1];
    echo "CSRF Token trouvé: " . $csrfToken . "\n";
} else {
    echo "CSRF Token non trouvé\n";
    exit(1);
}

// Test 3: Tentative de connexion
echo "\n=== Test 3: Tentative de connexion ===\n";
$postData = http_build_query([
    '_username' => 'test@example.com',
    '_password' => 'password',
    '_csrf_token' => $csrfToken
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Code HTTP: " . $httpCode . "\n";
echo "Réponse de connexion:\n";
echo $response . "\n\n";

// Test 4: Vérifier le statut après connexion
echo "=== Test 4: Statut après connexion ===\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/auth/status');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$response = curl_exec($ch);
curl_close($ch);
echo $response . "\n";

// Nettoyer le fichier de cookies
unlink($cookieFile); 