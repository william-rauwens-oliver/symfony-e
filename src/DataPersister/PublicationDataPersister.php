<?php
namespace App\DataPersister;

// Constantes pour les erreurs d'upload PHP
if (!defined('UPLOAD_ERR_OK')) {
    define('UPLOAD_ERR_OK', 0);
}
if (!defined('UPLOAD_ERR_INI_SIZE')) {
    define('UPLOAD_ERR_INI_SIZE', 1);
}
if (!defined('UPLOAD_ERR_FORM_SIZE')) {
    define('UPLOAD_ERR_FORM_SIZE', 2);
}
if (!defined('UPLOAD_ERR_PARTIAL')) {
    define('UPLOAD_ERR_PARTIAL', 3);
}
if (!defined('UPLOAD_ERR_NO_FILE')) {
    define('UPLOAD_ERR_NO_FILE', 4);
}
if (!defined('UPLOAD_ERR_NO_TMP_DIR')) {
    define('UPLOAD_ERR_NO_TMP_DIR', 6);
}
if (!defined('UPLOAD_ERR_CANT_WRITE')) {
    define('UPLOAD_ERR_CANT_WRITE', 7);
}
if (!defined('UPLOAD_ERR_EXTENSION')) {
    define('UPLOAD_ERR_EXTENSION', 8);
}

use ApiPlatform\Metadata\Operation;
use App\Entity\Publication;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\SecurityBundle\Security;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\PublicationInput;

class PublicationDataPersister implements ProcessorInterface
{
    private $entityManager;
    private $security;
    private $uploadDir;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->uploadDir = __DIR__ . '/../../public/uploads/images';
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    private function getUploadErrorMessage(int $errorCode): string
    {
        return match($errorCode) {
            UPLOAD_ERR_OK => 'Aucune erreur',
            UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille maximale autorisée par PHP',
            UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille maximale autorisée par le formulaire',
            UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement uploadé',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été uploadé',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
            UPLOAD_ERR_CANT_WRITE => 'Impossible d\'écrire le fichier sur le disque',
            UPLOAD_ERR_EXTENSION => 'Une extension PHP a arrêté l\'upload',
            default => 'Erreur d\'upload inconnue'
        };
    }

    private function getMaxUploadSize(): int
    {
        $uploadMaxFilesize = $this->parseSize(ini_get('upload_max_filesize'));
        $postMaxSize = $this->parseSize(ini_get('post_max_size'));
        $memoryLimit = $this->parseSize(ini_get('memory_limit'));
        
        // Retourner la plus petite valeur
        return min($uploadMaxFilesize, $postMaxSize, $memoryLimit);
    }

    private function parseSize(string $size): int
    {
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        $size = (int) $size;
        
        return match($last) {
            'g' => $size * 1024 * 1024 * 1024,
            'm' => $size * 1024 * 1024,
            'k' => $size * 1024,
            default => $size
        };
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024 * 1024) {
            return round($bytes / (1024 * 1024 * 1024), 2) . ' GB';
        } elseif ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] Entrée process, type: " . (is_object($data) ? get_class($data) : gettype($data)) . "\n", FILE_APPEND);
        if ($data instanceof PublicationInput) {
            file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] Mapping PublicationInput vers Publication\n", FILE_APPEND);
            $publication = new Publication();
            $publication->setTexte($data->texte);
            
            // Gestion de l'upload d'image
            if ($data->image) {
                try {
                    file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] Traitement image: " . $data->image->getClientOriginalName() . "\n", FILE_APPEND);
                    file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] Taille image: " . $data->image->getSize() . " bytes\n", FILE_APPEND);
                    file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] Type MIME: " . $data->image->getMimeType() . "\n", FILE_APPEND);
                    file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] Erreur upload: " . $data->image->getError() . "\n", FILE_APPEND);
                    file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] isValid(): " . ($data->image->isValid() ? 'true' : 'false') . "\n", FILE_APPEND);
                    
                    // Vérifier que le fichier est valide
                    if (!$data->image->isValid()) {
                        $errorMessage = $this->getUploadErrorMessage($data->image->getError());
                        file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] ERREUR: Fichier image invalide - Code: " . $data->image->getError() . " - Message: " . $errorMessage . "\n", FILE_APPEND);
                        
                        // Vérifier la taille du fichier
                        $maxSize = $this->getMaxUploadSize();
                        if ($data->image->getSize() > $maxSize) {
                            $errorMessage = "Le fichier est trop volumineux. Taille maximale autorisée: " . $this->formatBytes($maxSize);
                            file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] ERREUR: Fichier trop volumineux - Taille: " . $data->image->getSize() . " bytes, Max: $maxSize bytes\n", FILE_APPEND);
                            throw new \Exception($errorMessage);
                        }
                        
                        // Essayer de traiter le fichier même s'il n'est pas considéré comme "valide"
                        // Certains navigateurs peuvent envoyer des fichiers valides mais avec des erreurs mineures
                        if ($data->image->getError() === UPLOAD_ERR_OK && $data->image->getSize() > 0) {
                            file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] Tentative de traitement malgré isValid()=false\n", FILE_APPEND);
                        } else {
                            throw new \Exception('Le fichier image est invalide: ' . $errorMessage);
                        }
                    }
                    
                    // Vérifier que le dossier existe
                    if (!is_dir($this->uploadDir)) {
                        file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] Création du dossier: " . $this->uploadDir . "\n", FILE_APPEND);
                        mkdir($this->uploadDir, 0777, true);
                    }
                    
                    // Vérifier les permissions
                    if (!is_writable($this->uploadDir)) {
                        file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] ERREUR: Dossier non accessible en écriture: " . $this->uploadDir . "\n", FILE_APPEND);
                        throw new \Exception('Le dossier d\'upload n\'est pas accessible en écriture');
                    }
                    
                    $file = $data->image;
                    $extension = $file->guessExtension() ?: 'jpg';
                    $filename = uniqid('img_') . '.' . $extension;
                    $filepath = $this->uploadDir . '/' . $filename;
                    
                    file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] Upload vers: " . $filepath . "\n", FILE_APPEND);
                    
                    $file->move($this->uploadDir, $filename);
                    
                    // Vérifier que le fichier a bien été créé
                    if (!file_exists($filepath)) {
                        file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] ERREUR: Fichier non créé après upload\n", FILE_APPEND);
                        throw new \Exception('Erreur lors de l\'upload du fichier');
                    }
                    
                    $publication->setImage('/uploads/images/' . $filename);
                    file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] Image uploadée avec succès: " . $filename . "\n", FILE_APPEND);
                } catch (\Exception $e) {
                    file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] ERREUR upload image: " . $e->getMessage() . "\n", FILE_APPEND);
                    throw $e;
                }
            }
            
            // Gestion de l'upload de vidéo
            if ($data->video) {
                try {
                    file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] Traitement vidéo: " . $data->video->getClientOriginalName() . "\n", FILE_APPEND);
                    
                    if (!$data->video->isValid()) {
                        file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] ERREUR: Fichier vidéo invalide\n", FILE_APPEND);
                        throw new \Exception('Le fichier vidéo est invalide');
                    }
                    
                    $file = $data->video;
                    $extension = $file->guessExtension() ?: 'mp4';
                    $filename = uniqid('vid_') . '.' . $extension;
                    $filepath = $this->uploadDir . '/' . $filename;
                    
                    file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] Upload vidéo vers: " . $filepath . "\n", FILE_APPEND);
                    
                    $file->move($this->uploadDir, $filename);
                    
                    if (!file_exists($filepath)) {
                        file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] ERREUR: Fichier vidéo non créé après upload\n", FILE_APPEND);
                        throw new \Exception('Erreur lors de l\'upload de la vidéo');
                    }
                    
                    $publication->setVideo('/uploads/images/' . $filename);
                    file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] Vidéo uploadée avec succès: " . $filename . "\n", FILE_APPEND);
                } catch (\Exception $e) {
                    file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] ERREUR upload vidéo: " . $e->getMessage() . "\n", FILE_APPEND);
                    throw $e;
                }
            }
            
            if ($this->security->getUser()) {
                $publication->setUser($this->security->getUser());
            }
            $publication->setCreatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($publication);
            $this->entityManager->flush();
            file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] Publication persistée avec ID: " . $publication->getId() . ", texte: " . $publication->getTexte() . "\n", FILE_APPEND);
            return $publication;
        }
        if ($data instanceof Publication) {
            file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] Update Publication existante\n", FILE_APPEND);
            // fallback pour update
            if ($this->security->getUser() && !$data->getUser()) {
                $data->setUser($this->security->getUser());
            }
            if (!$data->getCreatedAt()) {
                $data->setCreatedAt(new \DateTimeImmutable());
            }
            $this->entityManager->persist($data);
            $this->entityManager->flush();
            file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] Publication mise à jour ID: " . $data->getId() . "\n", FILE_APPEND);
            return $data;
        }
        file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] Donnée non prise en charge\n", FILE_APPEND);
        return $data;
    }
} 