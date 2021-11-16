<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211015145733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la table application.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE application_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE application (id INT NOT NULL, sous_domaine_id INT NOT NULL, exploitant_id INT NOT NULL, moe_id INT DEFAULT NULL, label VARCHAR(255) NOT NULL, supprime_le TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, ajoute_le TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, maj_le TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A45BDDC1A40AA975 ON application (sous_domaine_id)');
        $this->addSql('CREATE INDEX IDX_A45BDDC1C7B9512F ON application (exploitant_id)');
        $this->addSql('CREATE INDEX IDX_A45BDDC18B3246A8 ON application (moe_id)');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC1A40AA975 FOREIGN KEY (sous_domaine_id) REFERENCES domaine (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC1C7B9512F FOREIGN KEY (exploitant_id) REFERENCES service (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC18B3246A8 FOREIGN KEY (moe_id) REFERENCES service (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE application_id_seq CASCADE');
        $this->addSql('DROP TABLE application');
    }
}
