<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223225901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande ADD CONSTRAINT FK_2694D7A519EB6921 FOREIGN KEY (client_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_2694D7A519EB6921 ON demande (client_id)');
        $this->addSql('ALTER TABLE entite_collecte CHANGE telephone telephone VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande DROP FOREIGN KEY FK_2694D7A519EB6921');
        $this->addSql('DROP INDEX IDX_2694D7A519EB6921 ON demande');
        $this->addSql('ALTER TABLE demande DROP client_id');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE entite_collecte CHANGE telephone telephone VARCHAR(20) NOT NULL');
    }
}
