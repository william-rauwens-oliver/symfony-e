<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250707144447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE publication (id SERIAL NOT NULL, user_id INT NOT NULL, content TEXT NOT NULL, image VARCHAR(256) DEFAULT NULL, video VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AF3C6779A76ED395 ON publication (user_id)');
        $this->addSql('COMMENT ON COLUMN publication.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE publication ADD CONSTRAINT FK_AF3C6779A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "like" ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE "like" ADD publication_id INT NOT NULL');
        $this->addSql('ALTER TABLE "like" ADD CONSTRAINT FK_AC6340B3A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "like" ADD CONSTRAINT FK_AC6340B338B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_AC6340B3A76ED395 ON "like" (user_id)');
        $this->addSql('CREATE INDEX IDX_AC6340B338B217A7 ON "like" (publication_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "like" DROP CONSTRAINT FK_AC6340B338B217A7');
        $this->addSql('ALTER TABLE publication DROP CONSTRAINT FK_AF3C6779A76ED395');
        $this->addSql('DROP TABLE publication');
        $this->addSql('ALTER TABLE "like" DROP CONSTRAINT FK_AC6340B3A76ED395');
        $this->addSql('DROP INDEX IDX_AC6340B3A76ED395');
        $this->addSql('DROP INDEX IDX_AC6340B338B217A7');
        $this->addSql('ALTER TABLE "like" DROP user_id');
        $this->addSql('ALTER TABLE "like" DROP publication_id');
    }
}
