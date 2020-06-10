<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200609203629 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'sqlite',
            'Migration can only be executed safely on \'sqlite\'.'
        );

        $this->addSql(
            'CREATE TABLE movies (
          id VARCHAR(36) NOT NULL, 
          genre VARCHAR(30) NOT NULL, 
          title VARCHAR(255) NOT NULL, 
          released_at DATE NOT NULL, 
          PRIMARY KEY(id)
        )'
        );
        $this->addSql('CREATE UNIQUE INDEX movies_lower_title_key ON movies (lower(title))');
        $this->addSql('CREATE INDEX movies_lower_genre_idx ON movies (lower(genre))');
        $this->addSql('CREATE INDEX movies_lower_genre_lower_title_idx ON movies (lower(genre), lower(title))');
        $this->addSql('CREATE INDEX movies_released_at_week_year_idx ON movies (strftime(\'%W-%Y\', released_at))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'sqlite',
            'Migration can only be executed safely on \'sqlite\'.'
        );

        $this->addSql('DROP TABLE movies');
    }
}
