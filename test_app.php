<?php

require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

// Charger les variables d'environnement
$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__.'/.env');

echo "Test de l'application Symfony\n";
echo "=============================\n\n";

// Test de la base de données
try {
    $databaseUrl = $_ENV['DATABASE_URL'];
    $pdo = new PDO($databaseUrl);
    echo "✅ Connexion à la base de données réussie\n";
    
    // Test des tables
    $tables = ['user', 'publication', 'commentaire', 'like'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "✅ Table '$table' existe avec $count enregistrement(s)\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur de connexion à la base de données: " . $e->getMessage() . "\n";
}

echo "\nTest terminé.\n"; 