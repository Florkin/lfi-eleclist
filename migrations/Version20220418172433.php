<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220418172433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE address (id INT AUTO_INCREMENT NOT NULL, number VARCHAR(50) NOT NULL, street VARCHAR(255) NOT NULL, add1 VARCHAR(255) DEFAULT NULL, add2 VARCHAR(255) DEFAULT NULL, postcode VARCHAR(5) NOT NULL, city VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE elector (id INT AUTO_INCREMENT NOT NULL, address_id INT DEFAULT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, birthname VARCHAR(255) DEFAULT NULL, vote_office VARCHAR(3) NOT NULL, UNIQUE INDEX UNIQ_4C037439F5B7AF75 (address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE elector ADD CONSTRAINT FK_4C037439F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE elector DROP FOREIGN KEY FK_4C037439F5B7AF75');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE elector');
    }
}
