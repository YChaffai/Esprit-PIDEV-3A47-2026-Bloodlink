<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260222054938 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE banque (id INT NOT NULL, nom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, type_sang VARCHAR(255) NOT NULL, dernier_don DATE NOT NULL, UNIQUE INDEX UNIQ_C7440455A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, banque_id INT NOT NULL, client_id INT NOT NULL, stock_id INT NOT NULL, reference INT NOT NULL, quantite INT NOT NULL, priorite VARCHAR(255) NOT NULL, type_sang VARCHAR(255) NOT NULL, status VARCHAR(20) NOT NULL, INDEX IDX_6EEAA67D37E080D9 (banque_id), INDEX IDX_6EEAA67D19EB6921 (client_id), INDEX IDX_6EEAA67DDCD6110 (stock_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE compagne (id INT AUTO_INCREMENT NOT NULL, type_sang JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', titre VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE compagne_entite_collecte (compagne_id INT NOT NULL, entite_collecte_id INT NOT NULL, INDEX IDX_83F833338EB43C7 (compagne_id), INDEX IDX_83F83333DD8DDF34 (entite_collecte_id), PRIMARY KEY(compagne_id, entite_collecte_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE demande (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, id_banque INT NOT NULL, type_sang VARCHAR(10) NOT NULL, quantite INT NOT NULL, urgence VARCHAR(50) NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_2694D7A519EB6921 (client_id), INDEX IDX_2694D7A597C17ED1 (id_banque), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE don (id INT AUTO_INCREMENT NOT NULL, id_client INT NOT NULL, date DATETIME NOT NULL, quantite DOUBLE PRECISION NOT NULL, type_don VARCHAR(255) NOT NULL, id_entite INT NOT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL, INDEX IDX_F8F081D9E173B1B8 (id_client), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dossier_med (id INT AUTO_INCREMENT NOT NULL, id_client INT NOT NULL, id_don INT NOT NULL, taille DOUBLE PRECISION NOT NULL, poid DOUBLE PRECISION NOT NULL, temperature DOUBLE PRECISION NOT NULL, sexe VARCHAR(255) NOT NULL, contact_urgence INT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, age INT NOT NULL, type_sang VARCHAR(255) NOT NULL, INDEX IDX_4A63B9CE173B1B8 (id_client), INDEX IDX_4A63B9C66546983 (id_don), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE entite_collecte (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, type VARCHAR(50) NOT NULL, adresse VARCHAR(255) NOT NULL, ville VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE password_reset_token (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, token VARCHAR(255) NOT NULL, expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', used TINYINT(1) NOT NULL, INDEX IDX_6B7BA4B6A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE questionnaire (id INT AUTO_INCREMENT NOT NULL, campagne_id INT NOT NULL, client_id INT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, age INT NOT NULL, sexe VARCHAR(255) NOT NULL, poids DOUBLE PRECISION NOT NULL, autres VARCHAR(255) DEFAULT NULL, date DATETIME NOT NULL, group_sanguin VARCHAR(5) NOT NULL, INDEX IDX_7A64DAF16227374 (campagne_id), INDEX IDX_7A64DAF19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rendez_vous (id INT AUTO_INCREMENT NOT NULL, questionnaire_id INT NOT NULL, entite_id INT NOT NULL, date_don DATETIME NOT NULL, status VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_65E8AA0ACE07E8FF (questionnaire_id), INDEX IDX_65E8AA0A9BEA957A (entite_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stock (id INT AUTO_INCREMENT NOT NULL, type_orgid INT NOT NULL, type_org VARCHAR(255) NOT NULL, type_sang VARCHAR(255) NOT NULL, quantite INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transfert (id INT AUTO_INCREMENT NOT NULL, demande_id INT NOT NULL, stock_id INT NOT NULL, from_org_id INT NOT NULL, from_org VARCHAR(255) NOT NULL, to_org_id INT NOT NULL, to_org VARCHAR(255) NOT NULL, date_envoie DATE NOT NULL, date_reception DATE NOT NULL, quantite INT NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_1E4EACBB80E95E18 (demande_id), INDEX IDX_1E4EACBBDCD6110 (stock_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, telephone VARCHAR(20) DEFAULT NULL, role VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE banque ADD CONSTRAINT FK_B1F6CB3CBF396750 FOREIGN KEY (id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D37E080D9 FOREIGN KEY (banque_id) REFERENCES banque (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DDCD6110 FOREIGN KEY (stock_id) REFERENCES stock (id)');
        $this->addSql('ALTER TABLE compagne_entite_collecte ADD CONSTRAINT FK_83F833338EB43C7 FOREIGN KEY (compagne_id) REFERENCES compagne (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE compagne_entite_collecte ADD CONSTRAINT FK_83F83333DD8DDF34 FOREIGN KEY (entite_collecte_id) REFERENCES entite_collecte (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE demande ADD CONSTRAINT FK_2694D7A519EB6921 FOREIGN KEY (client_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE demande ADD CONSTRAINT FK_2694D7A597C17ED1 FOREIGN KEY (id_banque) REFERENCES banque (id)');
        $this->addSql('ALTER TABLE don ADD CONSTRAINT FK_F8F081D9E173B1B8 FOREIGN KEY (id_client) REFERENCES client (id)');
        $this->addSql('ALTER TABLE dossier_med ADD CONSTRAINT FK_4A63B9CE173B1B8 FOREIGN KEY (id_client) REFERENCES client (id)');
        $this->addSql('ALTER TABLE dossier_med ADD CONSTRAINT FK_4A63B9C66546983 FOREIGN KEY (id_don) REFERENCES don (id)');
        $this->addSql('ALTER TABLE password_reset_token ADD CONSTRAINT FK_6B7BA4B6A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE questionnaire ADD CONSTRAINT FK_7A64DAF16227374 FOREIGN KEY (campagne_id) REFERENCES compagne (id)');
        $this->addSql('ALTER TABLE questionnaire ADD CONSTRAINT FK_7A64DAF19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0ACE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A9BEA957A FOREIGN KEY (entite_id) REFERENCES entite_collecte (id)');
        $this->addSql('ALTER TABLE transfert ADD CONSTRAINT FK_1E4EACBB80E95E18 FOREIGN KEY (demande_id) REFERENCES demande (id)');
        $this->addSql('ALTER TABLE transfert ADD CONSTRAINT FK_1E4EACBBDCD6110 FOREIGN KEY (stock_id) REFERENCES stock (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE banque DROP FOREIGN KEY FK_B1F6CB3CBF396750');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455A76ED395');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D37E080D9');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D19EB6921');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DDCD6110');
        $this->addSql('ALTER TABLE compagne_entite_collecte DROP FOREIGN KEY FK_83F833338EB43C7');
        $this->addSql('ALTER TABLE compagne_entite_collecte DROP FOREIGN KEY FK_83F83333DD8DDF34');
        $this->addSql('ALTER TABLE demande DROP FOREIGN KEY FK_2694D7A519EB6921');
        $this->addSql('ALTER TABLE demande DROP FOREIGN KEY FK_2694D7A597C17ED1');
        $this->addSql('ALTER TABLE don DROP FOREIGN KEY FK_F8F081D9E173B1B8');
        $this->addSql('ALTER TABLE dossier_med DROP FOREIGN KEY FK_4A63B9CE173B1B8');
        $this->addSql('ALTER TABLE dossier_med DROP FOREIGN KEY FK_4A63B9C66546983');
        $this->addSql('ALTER TABLE password_reset_token DROP FOREIGN KEY FK_6B7BA4B6A76ED395');
        $this->addSql('ALTER TABLE questionnaire DROP FOREIGN KEY FK_7A64DAF16227374');
        $this->addSql('ALTER TABLE questionnaire DROP FOREIGN KEY FK_7A64DAF19EB6921');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0ACE07E8FF');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A9BEA957A');
        $this->addSql('ALTER TABLE transfert DROP FOREIGN KEY FK_1E4EACBB80E95E18');
        $this->addSql('ALTER TABLE transfert DROP FOREIGN KEY FK_1E4EACBBDCD6110');
        $this->addSql('DROP TABLE banque');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE compagne');
        $this->addSql('DROP TABLE compagne_entite_collecte');
        $this->addSql('DROP TABLE demande');
        $this->addSql('DROP TABLE don');
        $this->addSql('DROP TABLE dossier_med');
        $this->addSql('DROP TABLE entite_collecte');
        $this->addSql('DROP TABLE password_reset_token');
        $this->addSql('DROP TABLE questionnaire');
        $this->addSql('DROP TABLE rendez_vous');
        $this->addSql('DROP TABLE stock');
        $this->addSql('DROP TABLE transfert');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
