<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250720001248 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__commentaire AS SELECT id, user_id, publication_id, parent_id, content, created_at, updated_at FROM commentaire');
        $this->addSql('DROP TABLE commentaire');
        $this->addSql('CREATE TABLE commentaire (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, publication_id INTEGER NOT NULL, parent_id INTEGER DEFAULT NULL, content CLOB NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_67F068BCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_67F068BC38B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_67F068BC727ACA70 FOREIGN KEY (parent_id) REFERENCES commentaire (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO commentaire (id, user_id, publication_id, parent_id, content, created_at, updated_at) SELECT id, user_id, publication_id, parent_id, content, created_at, updated_at FROM __temp__commentaire');
        $this->addSql('DROP TABLE __temp__commentaire');
        $this->addSql('CREATE INDEX IDX_67F068BC727ACA70 ON commentaire (parent_id)');
        $this->addSql('CREATE INDEX IDX_67F068BCA76ED395 ON commentaire (user_id)');
        $this->addSql('CREATE INDEX IDX_67F068BC38B217A7 ON commentaire (publication_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__like AS SELECT id, user_id, publication_id FROM "like"');
        $this->addSql('DROP TABLE "like"');
        $this->addSql('CREATE TABLE "like" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, publication_id INTEGER NOT NULL, CONSTRAINT FK_AC6340B3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AC6340B338B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO "like" (id, user_id, publication_id) SELECT id, user_id, publication_id FROM __temp__like');
        $this->addSql('DROP TABLE __temp__like');
        $this->addSql('CREATE UNIQUE INDEX unique_like ON "like" (user_id, publication_id)');
        $this->addSql('CREATE INDEX IDX_AC6340B3A76ED395 ON "like" (user_id)');
        $this->addSql('CREATE INDEX IDX_AC6340B338B217A7 ON "like" (publication_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__repost AS SELECT id, publication_id, user_id, created_at, updated_at FROM repost');
        $this->addSql('DROP TABLE repost');
        $this->addSql('CREATE TABLE repost (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, publication_id INTEGER NOT NULL, user_id INTEGER NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_DD3446C538B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_DD3446C5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO repost (id, publication_id, user_id, created_at, updated_at) SELECT id, publication_id, user_id, created_at, updated_at FROM __temp__repost');
        $this->addSql('DROP TABLE __temp__repost');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_REPOST_USER_PUBLICATION ON repost (user_id, publication_id)');
        $this->addSql('CREATE INDEX IDX_DD3446C538B217A7 ON repost (publication_id)');
        $this->addSql('CREATE INDEX IDX_DD3446C5A76ED395 ON repost (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__commentaire AS SELECT id, user_id, publication_id, parent_id, content, created_at, updated_at FROM commentaire');
        $this->addSql('DROP TABLE commentaire');
        $this->addSql('CREATE TABLE commentaire (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, publication_id INTEGER NOT NULL, parent_id INTEGER DEFAULT NULL, content CLOB NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_67F068BCA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_67F068BC38B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_67F068BC727ACA70 FOREIGN KEY (parent_id) REFERENCES commentaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO commentaire (id, user_id, publication_id, parent_id, content, created_at, updated_at) SELECT id, user_id, publication_id, parent_id, content, created_at, updated_at FROM __temp__commentaire');
        $this->addSql('DROP TABLE __temp__commentaire');
        $this->addSql('CREATE INDEX IDX_67F068BCA76ED395 ON commentaire (user_id)');
        $this->addSql('CREATE INDEX IDX_67F068BC38B217A7 ON commentaire (publication_id)');
        $this->addSql('CREATE INDEX IDX_67F068BC727ACA70 ON commentaire (parent_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__like AS SELECT id, user_id, publication_id FROM "like"');
        $this->addSql('DROP TABLE "like"');
        $this->addSql('CREATE TABLE "like" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, publication_id INTEGER NOT NULL, CONSTRAINT FK_AC6340B3A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AC6340B338B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO "like" (id, user_id, publication_id) SELECT id, user_id, publication_id FROM __temp__like');
        $this->addSql('DROP TABLE __temp__like');
        $this->addSql('CREATE INDEX IDX_AC6340B3A76ED395 ON "like" (user_id)');
        $this->addSql('CREATE INDEX IDX_AC6340B338B217A7 ON "like" (publication_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_like ON "like" (user_id, publication_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__repost AS SELECT id, publication_id, user_id, created_at, updated_at FROM repost');
        $this->addSql('DROP TABLE repost');
        $this->addSql('CREATE TABLE repost (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, publication_id INTEGER NOT NULL, user_id INTEGER NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_DD3446C538B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_DD3446C5A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO repost (id, publication_id, user_id, created_at, updated_at) SELECT id, publication_id, user_id, created_at, updated_at FROM __temp__repost');
        $this->addSql('DROP TABLE __temp__repost');
        $this->addSql('CREATE INDEX IDX_DD3446C538B217A7 ON repost (publication_id)');
        $this->addSql('CREATE INDEX IDX_DD3446C5A76ED395 ON repost (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_REPOST_USER_PUBLICATION ON repost (user_id, publication_id)');
    }
}
