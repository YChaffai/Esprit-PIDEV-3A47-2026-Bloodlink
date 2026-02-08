<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260205225457 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE demande (id INT AUTO_INCREMENT NOT NULL, id_banque INT NOT NULL, type_sang VARCHAR(10) NOT NULL, quantite INT NOT NULL, urgence VARCHAR(50) NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE transfert (id INT AUTO_INCREMENT NOT NULL, from_org_id INT NOT NULL, from_org VARCHAR(255) NOT NULL, to_org_id INT NOT NULL, to_org VARCHAR(255) NOT NULL, date_envoie DATE NOT NULL, date_reception DATE NOT NULL, quantite INT NOT NULL, status VARCHAR(50) NOT NULL, id_stock INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, demande_id INT NOT NULL, INDEX IDX_1E4EACBB80E95E18 (demande_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE transfert ADD CONSTRAINT FK_1E4EACBB80E95E18 FOREIGN KEY (demande_id) REFERENCES demande (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transfert DROP FOREIGN KEY FK_1E4EACBB80E95E18');
        $this->addSql('DROP TABLE demande');
        $this->addSql('DROP TABLE transfert');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
