<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250709175031 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE commentaire ADD publication_id INT NOT NULL');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC38B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_67F068BCA76ED395 ON commentaire (user_id)');
        $this->addSql('CREATE INDEX IDX_67F068BC38B217A7 ON commentaire (publication_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE commentaire DROP CONSTRAINT FK_67F068BCA76ED395');
        $this->addSql('ALTER TABLE commentaire DROP CONSTRAINT FK_67F068BC38B217A7');
        $this->addSql('DROP INDEX IDX_67F068BCA76ED395');
        $this->addSql('DROP INDEX IDX_67F068BC38B217A7');
        $this->addSql('ALTER TABLE commentaire DROP user_id');
        $this->addSql('ALTER TABLE commentaire DROP publication_id');
    }
}
