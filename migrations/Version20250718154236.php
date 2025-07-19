<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250718154236 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__like AS SELECT id, user_id, publication_id FROM "like"');
        $this->addSql('DROP TABLE "like"');
        $this->addSql('CREATE TABLE "like" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, publication_id INTEGER NOT NULL, CONSTRAINT FK_AC6340B3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AC6340B338B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO "like" (id, user_id, publication_id) SELECT id, user_id, publication_id FROM __temp__like');
        $this->addSql('DROP TABLE __temp__like');
        $this->addSql('CREATE INDEX IDX_AC6340B338B217A7 ON "like" (publication_id)');
        $this->addSql('CREATE INDEX IDX_AC6340B3A76ED395 ON "like" (user_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_like ON "like" (user_id, publication_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__like AS SELECT id, user_id, publication_id FROM "like"');
        $this->addSql('DROP TABLE "like"');
        $this->addSql('CREATE TABLE "like" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, publication_id INTEGER NOT NULL, CONSTRAINT FK_AC6340B3A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AC6340B338B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO "like" (id, user_id, publication_id) SELECT id, user_id, publication_id FROM __temp__like');
        $this->addSql('DROP TABLE __temp__like');
        $this->addSql('CREATE INDEX IDX_AC6340B3A76ED395 ON "like" (user_id)');
        $this->addSql('CREATE INDEX IDX_AC6340B338B217A7 ON "like" (publication_id)');
    }
}
