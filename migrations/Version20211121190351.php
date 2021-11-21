<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211121190351 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавляем таблицы авторов и их треков. Устанавливаем связи между ними';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE author (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(60) NOT NULL, alias VARCHAR(60) DEFAULT NULL, city VARCHAR(60) DEFAULT NULL, followers_count INT DEFAULT 0, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE track (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, title VARCHAR(255) NOT NULL, duration INT NOT NULL, playback_count INT DEFAULT 0, comments_count INT DEFAULT 0, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_D6E3F8A669CCBE9A (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE track ADD CONSTRAINT FK_D6E3F8A669CCBE9A FOREIGN KEY (author_id) REFERENCES author (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE track DROP FOREIGN KEY FK_D6E3F8A669CCBE9A');
        $this->addSql('DROP TABLE author');
        $this->addSql('DROP TABLE track');
    }
}
