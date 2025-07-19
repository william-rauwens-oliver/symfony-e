<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250719215101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__follow AS SELECT id, follower_id, followed_id, created_at FROM follow');
        $this->addSql('DROP TABLE follow');
        $this->addSql('CREATE TABLE follow (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, follower_id INTEGER NOT NULL, followed_id INTEGER NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_68344470AC24F853 FOREIGN KEY (follower_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_68344470D956F010 FOREIGN KEY (followed_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO follow (id, follower_id, followed_id, created_at) SELECT id, follower_id, followed_id, created_at FROM __temp__follow');
        $this->addSql('DROP TABLE __temp__follow');
        $this->addSql('CREATE INDEX IDX_68344470D956F010 ON follow (followed_id)');
        $this->addSql('CREATE INDEX IDX_68344470AC24F853 ON follow (follower_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_follow ON follow (follower_id, followed_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__follow AS SELECT id, follower_id, followed_id, created_at FROM follow');
        $this->addSql('DROP TABLE follow');
        $this->addSql('CREATE TABLE follow (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, follower_id INTEGER NOT NULL, followed_id INTEGER NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_68344470AC24F853 FOREIGN KEY (follower_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_68344470D956F010 FOREIGN KEY (followed_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO follow (id, follower_id, followed_id, created_at) SELECT id, follower_id, followed_id, created_at FROM __temp__follow');
        $this->addSql('DROP TABLE __temp__follow');
        $this->addSql('CREATE INDEX IDX_68344470AC24F853 ON follow (follower_id)');
        $this->addSql('CREATE INDEX IDX_68344470D956F010 ON follow (followed_id)');
    }
}
