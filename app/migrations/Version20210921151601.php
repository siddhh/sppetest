<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210921151601 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initialisation de la base de données (services, utilisateurs, plans d\'exploitation, referenciel).';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE domaine_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE etat_traitement_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE granularite_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE plan_production_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE profil_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE service_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE domaine (id INT NOT NULL, domaine_parent_id INT DEFAULT NULL, label VARCHAR(255) NOT NULL, supprime_le TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, ajoute_le TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, maj_le TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_78AF0ACC64A164B0 ON domaine (domaine_parent_id)');
        $this->addSql('CREATE TABLE etat_traitement (id INT NOT NULL, label VARCHAR(255) NOT NULL, supprime_le TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, ajoute_le TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, maj_le TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE granularite (id INT NOT NULL, label VARCHAR(255) NOT NULL, supprime_le TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, ajoute_le TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, maj_le TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE plan_production (id INT NOT NULL, service_exploitant_id INT NOT NULL, domaine_id INT NOT NULL, granularite_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BE808BF61706EBAD ON plan_production (service_exploitant_id)');
        $this->addSql('CREATE INDEX IDX_BE808BF64272FC9F ON plan_production (domaine_id)');
        $this->addSql('CREATE INDEX IDX_BE808BF62886567D ON plan_production (granularite_id)');
        $this->addSql('CREATE TABLE profil (id INT NOT NULL, label VARCHAR(255) NOT NULL, supprime_le TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, ajoute_le TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, maj_le TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE service (id INT NOT NULL, profil_id INT NOT NULL, label VARCHAR(255) NOT NULL, balf VARCHAR(255) DEFAULT NULL, archive_le TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, ajoute_le TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, maj_le TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E19D9AD2275ED078 ON service (profil_id)');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, service_id INT DEFAULT NULL, balp VARCHAR(255) NOT NULL, motdepasse VARCHAR(255) DEFAULT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8D93D649ED5CA9E6 ON "user" (service_id)');
        $this->addSql('ALTER TABLE domaine ADD CONSTRAINT FK_78AF0ACC64A164B0 FOREIGN KEY (domaine_parent_id) REFERENCES domaine (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE plan_production ADD CONSTRAINT FK_BE808BF61706EBAD FOREIGN KEY (service_exploitant_id) REFERENCES service (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE plan_production ADD CONSTRAINT FK_BE808BF64272FC9F FOREIGN KEY (domaine_id) REFERENCES domaine (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE plan_production ADD CONSTRAINT FK_BE808BF62886567D FOREIGN KEY (granularite_id) REFERENCES granularite (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD2275ED078 FOREIGN KEY (profil_id) REFERENCES profil (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE domaine DROP CONSTRAINT FK_78AF0ACC64A164B0');
        $this->addSql('ALTER TABLE plan_production DROP CONSTRAINT FK_BE808BF64272FC9F');
        $this->addSql('ALTER TABLE plan_production DROP CONSTRAINT FK_BE808BF62886567D');
        $this->addSql('ALTER TABLE service DROP CONSTRAINT FK_E19D9AD2275ED078');
        $this->addSql('ALTER TABLE plan_production DROP CONSTRAINT FK_BE808BF61706EBAD');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649ED5CA9E6');
        $this->addSql('DROP SEQUENCE domaine_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE etat_traitement_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE granularite_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE plan_production_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE profil_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE service_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('DROP TABLE domaine');
        $this->addSql('DROP TABLE etat_traitement');
        $this->addSql('DROP TABLE granularite');
        $this->addSql('DROP TABLE plan_production');
        $this->addSql('DROP TABLE profil');
        $this->addSql('DROP TABLE service');
        $this->addSql('DROP TABLE "user"');
    }

    /**
     * Ajoute les données du référentiel de base
     */
    public function postUp(Schema $schema) : void
    {
        // Domaines principaux / racines
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), NULL, \'Domaine\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), NULL, \'Fiscalité\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), NULL, \'Gestion et Pilotage du SI\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), NULL, \'Gestion publique\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), NULL, \'Pilotage\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), NULL, \'Transverse\', NOW(), NOW())');
        // Sous-domaines Domaine
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), 1, \'Gestion du Domaine\', NOW(), NOW())');
        // Sous-domaines Fiscalité
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), 2, \'Assiette et Taxation des Professionnels\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), 2, \'Assiette et Taxation des particuliers\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), 2, \'Contrôle fiscal et Contentieux\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), 2, \'Foncier et Patrimoine\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), 2, \'Recouvrement Particuliers, Professionnels et Produits divers\', NOW(), NOW())');
        // Sous-domaines Gestion et Pilotage du SI
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), 3, \'Sécurité\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), 3, \'Infrastructures\', NOW(), NOW())');        
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), 3, \'Outils de production\', NOW(), NOW())');
        // Sous-domaines Gestion publique
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), 4, \'Comptabilité de l\'\'Etat\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), 4, \'Dépenses de l\'\'Etat et Paie\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), 4, \'Gestion comptable et financière des collectivités locales et des établissements publics\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), 4, \'Gestion des Fonds déposés\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), 4, \'Moyens de paiement\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), 4, \'Retraites de l\'\'Etat et gestion des pensions\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), 4, \'Valorisation, conseil fiscal et financier aux collectivités locales et établissements publics locaux\', NOW(), NOW())');
        // Sous-domaines Pilotage
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), 5, \'Audit, Risques et Contrôle de gestion\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), 5, \'Communication\', NOW(), NOW())');        
        // Sous-domaines Transverse
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), 6, \'Budget, Moyens et Logistique\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), 6, \'Gestion des RH\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), 6, \'Outillage\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO domaine (id, domaine_parent_id, label, ajoute_le, maj_le) VALUES (nextval(\'domaine_id_seq\'), 6, \'Référentiels partagés\', NOW(), NOW())');
        // Granularités
        $this->connection->executeQuery('INSERT INTO granularite (id, label, ajoute_le, maj_le) VALUES (nextval(\'granularite_id_seq\'), \'Application\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO granularite (id, label, ajoute_le, maj_le) VALUES (nextval(\'granularite_id_seq\'), \'Processus\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO granularite (id, label, ajoute_le, maj_le) VALUES (nextval(\'granularite_id_seq\'), \'Chaîne\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO granularite (id, label, ajoute_le, maj_le) VALUES (nextval(\'granularite_id_seq\'), \'Job\', NOW(), NOW())');
        // Etats des traitements
        $this->connection->executeQuery('INSERT INTO etat_traitement (id, label, ajoute_le, maj_le) VALUES (nextval(\'etat_traitement_id_seq\'), \'Prévisionnel automatique\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO etat_traitement (id, label, ajoute_le, maj_le) VALUES (nextval(\'etat_traitement_id_seq\'), \'Prévisionnel manuel\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO etat_traitement (id, label, ajoute_le, maj_le) VALUES (nextval(\'etat_traitement_id_seq\'), \'Réalisé Terminé\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO etat_traitement (id, label, ajoute_le, maj_le) VALUES (nextval(\'etat_traitement_id_seq\'), \'Réalisé En cours\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO etat_traitement (id, label, ajoute_le, maj_le) VALUES (nextval(\'etat_traitement_id_seq\'), \'Réalisé Annulé\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO etat_traitement (id, label, ajoute_le, maj_le) VALUES (nextval(\'etat_traitement_id_seq\'), \'Réalisé en Echec\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO etat_traitement (id, label, ajoute_le, maj_le) VALUES (nextval(\'etat_traitement_id_seq\'), \'Réalisé Avant prévisionnel\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO etat_traitement (id, label, ajoute_le, maj_le) VALUES (nextval(\'etat_traitement_id_seq\'), \'Réalisé Après prévisionnel\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO etat_traitement (id, label, ajoute_le, maj_le) VALUES (nextval(\'etat_traitement_id_seq\'), \'Jours non ouvrés\', NOW(), NOW())');
        // Profils
        $this->connection->executeQuery('INSERT INTO profil (id, label, ajoute_le, maj_le) VALUES (nextval(\'profil_id_seq\'), \'Administrateur SI-2A\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO profil (id, label, ajoute_le, maj_le) VALUES (nextval(\'profil_id_seq\'), \'ESI\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO profil (id, label, ajoute_le, maj_le) VALUES (nextval(\'profil_id_seq\'), \'Bureau d\'\'Etudes\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO profil (id, label, ajoute_le, maj_le) VALUES (nextval(\'profil_id_seq\'), \'Standard\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO profil (id, label, ajoute_le, maj_le) VALUES (nextval(\'profil_id_seq\'), \'CQMF\', NOW(), NOW())');
        $this->connection->executeQuery('INSERT INTO profil (id, label, ajoute_le, maj_le) VALUES (nextval(\'profil_id_seq\'), \'SI-2C\', NOW(), NOW())');
    }
}
