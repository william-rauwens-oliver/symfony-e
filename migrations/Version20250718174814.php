<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250718174814 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comment_like (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, commentaire_id INTEGER NOT NULL, CONSTRAINT FK_8A55E25FA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_8A55E25FBA9CD190 FOREIGN KEY (commentaire_id) REFERENCES commentaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_8A55E25FA76ED395 ON comment_like (user_id)');
        $this->addSql('CREATE INDEX IDX_8A55E25FBA9CD190 ON comment_like (commentaire_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_comment_like ON comment_like (user_id, commentaire_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE comment_like');
    }
}
