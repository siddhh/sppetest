<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211029090127 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute un champ priorité au profil d\'un service.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE profil ADD priorite SMALLINT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE profil DROP priorite');
    }

    /**
     * Ajoute les données du référentiel de base
     */
    public function postUp(Schema $schema) : void
    {
        // Défini les priorité des profils
        $this->connection->executeQuery('UPDATE profil SET priorite = 1000 WHERE priorite = 0 AND label = \'Administrateur SI-2A\'');
        $this->connection->executeQuery('UPDATE profil SET priorite = 500 WHERE priorite = 0 AND label = \'ESI\'');
        $this->connection->executeQuery('UPDATE profil SET priorite = 200 WHERE priorite = 0 AND label = \'Bureau d\'\'Etudes\'');
        $this->connection->executeQuery('UPDATE profil SET priorite = 10 WHERE priorite = 0 AND label = \'Standard\'');
        $this->connection->executeQuery('UPDATE profil SET priorite = 50 WHERE priorite = 0 AND label = \'CQMF\'');
        $this->connection->executeQuery('UPDATE profil SET priorite = 100 WHERE priorite = 0 AND label = \'SI-2C\'');
    }
}
