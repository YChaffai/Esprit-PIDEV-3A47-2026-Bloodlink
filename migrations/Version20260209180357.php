<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209180357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE banque DROP FOREIGN KEY `fk_usr`');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY `commande_ibfk_1`');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY `fk_client3`');
        $this->addSql('ALTER TABLE compagne DROP FOREIGN KEY `fk_entite`');
        $this->addSql('ALTER TABLE demande DROP FOREIGN KEY `fk_banque1`');
        $this->addSql('ALTER TABLE questionnaire DROP FOREIGN KEY `fk_client2`');
        $this->addSql('ALTER TABLE rendezvous DROP FOREIGN KEY `fk_client1`');
        $this->addSql('ALTER TABLE rendezvous DROP FOREIGN KEY `fk_entite1`');
        $this->addSql('ALTER TABLE rendezvous DROP FOREIGN KEY `rendezvous_ibfk_1`');
        $this->addSql('ALTER TABLE transfert DROP FOREIGN KEY `fk_demande`');
        $this->addSql('ALTER TABLE transfert DROP FOREIGN KEY `fk_stock2`');
        $this->addSql('DROP TABLE banque');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE compagne');
        $this->addSql('DROP TABLE demande');
        $this->addSql('DROP TABLE entitecollecte');
        $this->addSql('DROP TABLE questionnaire');
        $this->addSql('DROP TABLE rendezvous');
        $this->addSql('DROP TABLE stock');
        $this->addSql('DROP TABLE transfert');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY `fk_user`');
        $this->addSql('ALTER TABLE client ADD telephone VARCHAR(20) DEFAULT NULL, CHANGE type_sang type_sang VARCHAR(10) NOT NULL, CHANGE dernier_don dernier_don DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455BF396750 FOREIGN KEY (id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE don DROP FOREIGN KEY `fk_client4`');
        $this->addSql('ALTER TABLE don DROP FOREIGN KEY `fk_entite2`');
        $this->addSql('DROP INDEX fk_entite2 ON don');
        $this->addSql('ALTER TABLE don CHANGE date date DATETIME NOT NULL, CHANGE quantite quantite DOUBLE PRECISION NOT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE don ADD CONSTRAINT FK_F8F081D9E173B1B8 FOREIGN KEY (id_client) REFERENCES client (id)');
        $this->addSql('ALTER TABLE don RENAME INDEX fk_client4 TO IDX_F8F081D9E173B1B8');
        $this->addSql('ALTER TABLE dossier_med DROP FOREIGN KEY `fk_client5`');
        $this->addSql('ALTER TABLE dossier_med DROP FOREIGN KEY `fk_don`');
        $this->addSql('ALTER TABLE dossier_med CHANGE taille taille DOUBLE PRECISION NOT NULL, CHANGE poid poid DOUBLE PRECISION NOT NULL, CHANGE temperature temperature DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE dossier_med ADD CONSTRAINT FK_4A63B9CE173B1B8 FOREIGN KEY (id_client) REFERENCES client (id)');
        $this->addSql('ALTER TABLE dossier_med ADD CONSTRAINT FK_4A63B9C66546983 FOREIGN KEY (id_don) REFERENCES don (id)');
        $this->addSql('ALTER TABLE dossier_med RENAME INDEX fk_client5 TO IDX_4A63B9CE173B1B8');
        $this->addSql('ALTER TABLE dossier_med RENAME INDEX fk_don TO IDX_4A63B9C66546983');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE banque (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, addresse VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, telephone INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, reference INT NOT NULL, quantite INT NOT NULL, priorite VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, type_sang VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, id_banque INT NOT NULL, id_client INT NOT NULL, id_stock INT NOT NULL, INDEX fk_client3 (id_client), INDEX fk_banque (id_banque), INDEX fk_stock (id_stock), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE compagne (id INT AUTO_INCREMENT NOT NULL, id_entite INT NOT NULL, titre VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, date_debut DATE NOT NULL, date_fin DATE NOT NULL, created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, updated_at DATETIME DEFAULT \'NULL\', INDEX fk_entite (id_entite), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE demande (id INT AUTO_INCREMENT NOT NULL, id_banque INT NOT NULL, type_sang VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, quantite VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, urgence VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, updated_at DATETIME DEFAULT \'NULL\', INDEX fk_banque1 (id_banque), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE entitecollecte (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, localisation VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, telephone INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE questionnaire (id INT AUTO_INCREMENT NOT NULL, id_client INT NOT NULL, nom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, prenom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, age INT NOT NULL, sexe VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, poid INT NOT NULL, autres VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, INDEX fk_client2 (id_client), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE rendezvous (id INT AUTO_INCREMENT NOT NULL, id_client INT NOT NULL, id_entite INT NOT NULL, id_campagne INT NOT NULL, date_don DATETIME NOT NULL, status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, id_questionnaire INT NOT NULL, INDEX fk_questionnaire (id_questionnaire), INDEX fk_client1 (id_client), INDEX fk_entite1 (id_entite), INDEX fk_compagne (id_campagne), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE stock (id INT AUTO_INCREMENT NOT NULL, type_orgid INT NOT NULL, type_org VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, type_sang VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, quantite INT NOT NULL, created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, updated_at DATETIME DEFAULT \'NULL\', PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE transfert (id INT AUTO_INCREMENT NOT NULL, id_demande INT NOT NULL, from_orgid INT NOT NULL, from_org VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, to_orgid INT NOT NULL, to_org VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, date_envoie DATE NOT NULL, date_reception DATE NOT NULL, status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, updated_at DATETIME DEFAULT \'NULL\', quantite INT NOT NULL, id_stock INT NOT NULL, INDEX fk_demande (id_demande), INDEX fk_stock2 (id_stock), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE banque ADD CONSTRAINT `fk_usr` FOREIGN KEY (id) REFERENCES user (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT `commande_ibfk_1` FOREIGN KEY (id_stock) REFERENCES stock (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT `fk_client3` FOREIGN KEY (id_client) REFERENCES client (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE compagne ADD CONSTRAINT `fk_entite` FOREIGN KEY (id_entite) REFERENCES entitecollecte (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE demande ADD CONSTRAINT `fk_banque1` FOREIGN KEY (id_banque) REFERENCES banque (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE questionnaire ADD CONSTRAINT `fk_client2` FOREIGN KEY (id_client) REFERENCES client (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rendezvous ADD CONSTRAINT `fk_client1` FOREIGN KEY (id_client) REFERENCES client (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rendezvous ADD CONSTRAINT `fk_entite1` FOREIGN KEY (id_entite) REFERENCES entitecollecte (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rendezvous ADD CONSTRAINT `rendezvous_ibfk_1` FOREIGN KEY (id_questionnaire) REFERENCES questionnaire (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE transfert ADD CONSTRAINT `fk_demande` FOREIGN KEY (id_demande) REFERENCES demande (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE transfert ADD CONSTRAINT `fk_stock2` FOREIGN KEY (id_stock) REFERENCES stock (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455BF396750');
        $this->addSql('ALTER TABLE client DROP telephone, CHANGE type_sang type_sang VARCHAR(255) NOT NULL, CHANGE dernier_don dernier_don DATE NOT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT `fk_user` FOREIGN KEY (id) REFERENCES user (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE don DROP FOREIGN KEY FK_F8F081D9E173B1B8');
        $this->addSql('ALTER TABLE don CHANGE date date DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE quantite quantite FLOAT NOT NULL, CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE don ADD CONSTRAINT `fk_client4` FOREIGN KEY (id_client) REFERENCES client (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE don ADD CONSTRAINT `fk_entite2` FOREIGN KEY (id_entite) REFERENCES entitecollecte (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('CREATE INDEX fk_entite2 ON don (id_entite)');
        $this->addSql('ALTER TABLE don RENAME INDEX idx_f8f081d9e173b1b8 TO fk_client4');
        $this->addSql('ALTER TABLE dossier_med DROP FOREIGN KEY FK_4A63B9CE173B1B8');
        $this->addSql('ALTER TABLE dossier_med DROP FOREIGN KEY FK_4A63B9C66546983');
        $this->addSql('ALTER TABLE dossier_med CHANGE taille taille FLOAT NOT NULL, CHANGE poid poid FLOAT NOT NULL, CHANGE temperature temperature FLOAT NOT NULL');
        $this->addSql('ALTER TABLE dossier_med ADD CONSTRAINT `fk_client5` FOREIGN KEY (id_client) REFERENCES client (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dossier_med ADD CONSTRAINT `fk_don` FOREIGN KEY (id_don) REFERENCES don (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dossier_med RENAME INDEX idx_4a63b9c66546983 TO fk_don');
        $this->addSql('ALTER TABLE dossier_med RENAME INDEX idx_4a63b9ce173b1b8 TO fk_client5');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON `user`');
    }
}
