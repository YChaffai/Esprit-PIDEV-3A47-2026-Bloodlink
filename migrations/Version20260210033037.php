<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260210033037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE compagne_entitecollecte DROP FOREIGN KEY `FK_A13C4DE029FDD895`');
        $this->addSql('ALTER TABLE compagne_entitecollecte DROP FOREIGN KEY `FK_A13C4DE08EB43C7`');
        $this->addSql('DROP TABLE compagne_entitecollecte');
        $this->addSql('ALTER TABLE entitecollecte ADD compagne_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE entitecollecte ADD CONSTRAINT FK_F8DF28068EB43C7 FOREIGN KEY (compagne_id) REFERENCES compagne (id)');
        $this->addSql('CREATE INDEX IDX_F8DF28068EB43C7 ON entitecollecte (compagne_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE compagne_entitecollecte (compagne_id INT NOT NULL, entitecollecte_id INT NOT NULL, INDEX IDX_A13C4DE08EB43C7 (compagne_id), INDEX IDX_A13C4DE029FDD895 (entitecollecte_id), PRIMARY KEY (compagne_id, entitecollecte_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE compagne_entitecollecte ADD CONSTRAINT `FK_A13C4DE029FDD895` FOREIGN KEY (entitecollecte_id) REFERENCES entitecollecte (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE compagne_entitecollecte ADD CONSTRAINT `FK_A13C4DE08EB43C7` FOREIGN KEY (compagne_id) REFERENCES compagne (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE entitecollecte DROP FOREIGN KEY FK_F8DF28068EB43C7');
        $this->addSql('DROP INDEX IDX_F8DF28068EB43C7 ON entitecollecte');
        $this->addSql('ALTER TABLE entitecollecte DROP compagne_id');
    }
}
