<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260210154337 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE don (id INT AUTO_INCREMENT NOT NULL, id_client INT NOT NULL, date DATETIME NOT NULL, quantite DOUBLE PRECISION NOT NULL, type_don VARCHAR(255) NOT NULL, id_entite INT NOT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL, INDEX IDX_F8F081D9E173B1B8 (id_client), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dossier_med (id INT AUTO_INCREMENT NOT NULL, id_client INT NOT NULL, id_don INT NOT NULL, taille DOUBLE PRECISION NOT NULL, poid DOUBLE PRECISION NOT NULL, temperature DOUBLE PRECISION NOT NULL, sexe VARCHAR(255) NOT NULL, contact_urgence INT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, age INT NOT NULL, type_sang VARCHAR(255) NOT NULL, INDEX IDX_4A63B9CE173B1B8 (id_client), INDEX IDX_4A63B9C66546983 (id_don), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE don ADD CONSTRAINT FK_F8F081D9E173B1B8 FOREIGN KEY (id_client) REFERENCES client (id)');
        $this->addSql('ALTER TABLE dossier_med ADD CONSTRAINT FK_4A63B9CE173B1B8 FOREIGN KEY (id_client) REFERENCES client (id)');
        $this->addSql('ALTER TABLE dossier_med ADD CONSTRAINT FK_4A63B9C66546983 FOREIGN KEY (id_don) REFERENCES don (id)');
        $this->addSql('ALTER TABLE password_reset_token CHANGE expires_at expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE stock CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE available_at available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE don DROP FOREIGN KEY FK_F8F081D9E173B1B8');
        $this->addSql('ALTER TABLE dossier_med DROP FOREIGN KEY FK_4A63B9CE173B1B8');
        $this->addSql('ALTER TABLE dossier_med DROP FOREIGN KEY FK_4A63B9C66546983');
        $this->addSql('DROP TABLE don');
        $this->addSql('DROP TABLE dossier_med');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL, CHANGE available_at available_at DATETIME NOT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE password_reset_token CHANGE expires_at expires_at DATETIME NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE stock CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
    }
}
