<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240501130151 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD favorite_city_id INT DEFAULT NULL, ADD firstname VARCHAR(255) NOT NULL, ADD lastname VARCHAR(255) NOT NULL, ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649D6F8CCC1 FOREIGN KEY (favorite_city_id) REFERENCES city (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649D6F8CCC1 ON user (favorite_city_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649D6F8CCC1');
        $this->addSql('DROP INDEX IDX_8D93D649D6F8CCC1 ON user');
        $this->addSql('ALTER TABLE user DROP favorite_city_id, DROP firstname, DROP lastname, DROP created_at, DROP updated_at');
    }
}
