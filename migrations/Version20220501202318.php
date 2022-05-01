<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220501202318 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE grouped_address (id INT AUTO_INCREMENT NOT NULL, number VARCHAR(50) DEFAULT NULL, street VARCHAR(255) DEFAULT NULL, postcode VARCHAR(5) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE elector ADD grouped_address_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE elector ADD CONSTRAINT FK_4C0374397C9ABEC9 FOREIGN KEY (grouped_address_id) REFERENCES grouped_address (id)');
        $this->addSql('CREATE INDEX IDX_4C0374397C9ABEC9 ON elector (grouped_address_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE elector DROP FOREIGN KEY FK_4C0374397C9ABEC9');
        $this->addSql('DROP TABLE grouped_address');
        $this->addSql('DROP INDEX IDX_4C0374397C9ABEC9 ON elector');
        $this->addSql('ALTER TABLE elector DROP grouped_address_id');
    }
}
