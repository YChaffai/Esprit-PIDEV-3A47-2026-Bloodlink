<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209194400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE compagne_entitecollecte (compagne_id INT NOT NULL, entitecollecte_id INT NOT NULL, INDEX IDX_A13C4DE08EB43C7 (compagne_id), INDEX IDX_A13C4DE029FDD895 (entitecollecte_id), PRIMARY KEY (compagne_id, entitecollecte_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE compagne_entitecollecte ADD CONSTRAINT FK_A13C4DE08EB43C7 FOREIGN KEY (compagne_id) REFERENCES compagne (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE compagne_entitecollecte ADD CONSTRAINT FK_A13C4DE029FDD895 FOREIGN KEY (entitecollecte_id) REFERENCES entitecollecte (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE compagne DROP FOREIGN KEY `FK_3A4264B3C1EADCA`');
        $this->addSql('DROP INDEX IDX_3A4264B3C1EADCA ON compagne');
        $this->addSql('ALTER TABLE compagne DROP id_entite');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE compagne_entitecollecte DROP FOREIGN KEY FK_A13C4DE08EB43C7');
        $this->addSql('ALTER TABLE compagne_entitecollecte DROP FOREIGN KEY FK_A13C4DE029FDD895');
        $this->addSql('DROP TABLE compagne_entitecollecte');
        $this->addSql('ALTER TABLE compagne ADD id_entite INT DEFAULT NULL');
        $this->addSql('ALTER TABLE compagne ADD CONSTRAINT `FK_3A4264B3C1EADCA` FOREIGN KEY (id_entite) REFERENCES entitecollecte (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_3A4264B3C1EADCA ON compagne (id_entite)');
    }
}
