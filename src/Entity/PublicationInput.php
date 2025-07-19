<?php
namespace App\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

class PublicationInput
{
    /**
     * @Assert\NotBlank(message="Le texte ne doit pas être vide.")
     * @Groups(["publication:write"])
     */
    public ?string $texte = null;
    /**
     * @Groups(["publication:write"])
     */
    public ?UploadedFile $image = null;
    /**
     * @Groups(["publication:write"])
     */
    public ?UploadedFile $video = null;
} 