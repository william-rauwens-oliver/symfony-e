<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250719161610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__repost AS SELECT id, publication_id, user_id, created_at, updated_at FROM repost');
        $this->addSql('DROP TABLE repost');
        $this->addSql('CREATE TABLE repost (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, publication_id INTEGER NOT NULL, user_id INTEGER NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_DD3446C538B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_DD3446C5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO repost (id, publication_id, user_id, created_at, updated_at) SELECT id, publication_id, user_id, created_at, updated_at FROM __temp__repost');
        $this->addSql('DROP TABLE __temp__repost');
        $this->addSql('CREATE INDEX IDX_DD3446C5A76ED395 ON repost (user_id)');
        $this->addSql('CREATE INDEX IDX_DD3446C538B217A7 ON repost (publication_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_REPOST_USER_PUBLICATION ON repost (user_id, publication_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__repost AS SELECT id, publication_id, user_id, created_at, updated_at FROM repost');
        $this->addSql('DROP TABLE repost');
        $this->addSql('CREATE TABLE repost (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, publication_id INTEGER NOT NULL, user_id INTEGER NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_DD3446C538B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_DD3446C5A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO repost (id, publication_id, user_id, created_at, updated_at) SELECT id, publication_id, user_id, created_at, updated_at FROM __temp__repost');
        $this->addSql('DROP TABLE __temp__repost');
        $this->addSql('CREATE INDEX IDX_DD3446C538B217A7 ON repost (publication_id)');
        $this->addSql('CREATE INDEX IDX_DD3446C5A76ED395 ON repost (user_id)');
    }
}
