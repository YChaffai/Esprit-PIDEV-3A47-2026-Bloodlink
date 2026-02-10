<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209213033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE campagne (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, date_fin DATE NOT NULL, date_debut DATE DEFAULT NULL, type_sang VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE campagne_entite_collecte (campagne_id INT NOT NULL, entite_collecte_id INT NOT NULL, INDEX IDX_6E8B026F16227374 (campagne_id), INDEX IDX_6E8B026FDD8DDF34 (entite_collecte_id), PRIMARY KEY (campagne_id, entite_collecte_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE entite_collecte (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, localisation VARCHAR(255) NOT NULL, telephone INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE questionnaire (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, age INT NOT NULL, sexe VARCHAR(255) NOT NULL, poids DOUBLE PRECISION NOT NULL, autres VARCHAR(255) DEFAULT NULL, date DATETIME NOT NULL, group_sanguin VARCHAR(5) NOT NULL, campagne_id INT NOT NULL, client_id INT NOT NULL, INDEX IDX_7A64DAF16227374 (campagne_id), INDEX IDX_7A64DAF19EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE rendez_vous (id INT AUTO_INCREMENT NOT NULL, date_don DATETIME NOT NULL, status VARCHAR(255) NOT NULL, questionnaire_id INT NOT NULL, entite_id INT NOT NULL, UNIQUE INDEX UNIQ_65E8AA0ACE07E8FF (questionnaire_id), INDEX IDX_65E8AA0A9BEA957A (entite_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE campagne_entite_collecte ADD CONSTRAINT FK_6E8B026F16227374 FOREIGN KEY (campagne_id) REFERENCES campagne (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE campagne_entite_collecte ADD CONSTRAINT FK_6E8B026FDD8DDF34 FOREIGN KEY (entite_collecte_id) REFERENCES entite_collecte (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE questionnaire ADD CONSTRAINT FK_7A64DAF16227374 FOREIGN KEY (campagne_id) REFERENCES campagne (id)');
        $this->addSql('ALTER TABLE questionnaire ADD CONSTRAINT FK_7A64DAF19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0ACE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A9BEA957A FOREIGN KEY (entite_id) REFERENCES entite_collecte (id)');
        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(180) NOT NULL, CHANGE role role VARCHAR(50) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE campagne_entite_collecte DROP FOREIGN KEY FK_6E8B026F16227374');
        $this->addSql('ALTER TABLE campagne_entite_collecte DROP FOREIGN KEY FK_6E8B026FDD8DDF34');
        $this->addSql('ALTER TABLE questionnaire DROP FOREIGN KEY FK_7A64DAF16227374');
        $this->addSql('ALTER TABLE questionnaire DROP FOREIGN KEY FK_7A64DAF19EB6921');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0ACE07E8FF');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A9BEA957A');
        $this->addSql('DROP TABLE campagne');
        $this->addSql('DROP TABLE campagne_entite_collecte');
        $this->addSql('DROP TABLE entite_collecte');
        $this->addSql('DROP TABLE questionnaire');
        $this->addSql('DROP TABLE rendez_vous');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON `user`');
        $this->addSql('ALTER TABLE `user` CHANGE email email VARCHAR(255) NOT NULL, CHANGE role role VARCHAR(255) NOT NULL');
    }
}
