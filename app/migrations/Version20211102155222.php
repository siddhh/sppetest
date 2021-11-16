<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211102155222 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la table Processus.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE processus_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE processus (id INT NOT NULL, application_id INT NOT NULL, reference VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, libelle VARCHAR(255) DEFAULT NULL, objet VARCHAR(1000) DEFAULT NULL, chaine_de_planification VARCHAR(255) DEFAULT NULL, description_planification VARCHAR(1000) DEFAULT NULL, debut_validite TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, fin_validite TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, version_importee VARCHAR(255) DEFAULT NULL, date_version_importee TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, libelle_version_importee VARCHAR(255) DEFAULT NULL, supprime_le TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, ajoute_le TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, maj_le TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EEEA8C1D3E030ACD ON processus (application_id)');
        $this->addSql('ALTER TABLE processus ADD CONSTRAINT FK_EEEA8C1D3E030ACD FOREIGN KEY (application_id) REFERENCES application (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE job ADD processus_id INT NOT NULL');
        $this->addSql('ALTER TABLE job ALTER processus_id DROP NOT NULL');
        $this->addSql('ALTER TABLE job ADD CONSTRAINT FK_FBD8E0F8A55629DC FOREIGN KEY (processus_id) REFERENCES processus (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_FBD8E0F8A55629DC ON job (processus_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job DROP CONSTRAINT FK_FBD8E0F8A55629DC');
        $this->addSql('DROP SEQUENCE processus_id_seq CASCADE');
        $this->addSql('DROP TABLE processus');
        $this->addSql('DROP INDEX IDX_FBD8E0F8A55629DC');
        $this->addSql('ALTER TABLE job DROP processus_id');
    }
}
