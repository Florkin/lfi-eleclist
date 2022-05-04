<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220501214817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE elector DROP FOREIGN KEY FK_4C0374397C9ABEC9');
        $this->addSql('ALTER TABLE elector ADD CONSTRAINT FK_4C0374397C9ABEC9 FOREIGN KEY (grouped_address_id) REFERENCES grouped_address (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE elector DROP FOREIGN KEY FK_4C0374397C9ABEC9');
        $this->addSql('ALTER TABLE elector ADD CONSTRAINT FK_4C0374397C9ABEC9 FOREIGN KEY (grouped_address_id) REFERENCES grouped_address (id)');
    }
}
