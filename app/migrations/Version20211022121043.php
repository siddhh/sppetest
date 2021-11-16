<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211022121043 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout dans la table service du champ application';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE service_application (service_id INT NOT NULL, application_id INT NOT NULL, PRIMARY KEY(service_id, application_id))');
        $this->addSql('CREATE INDEX IDX_68C1FB6DED5CA9E6 ON service_application (service_id)');
        $this->addSql('CREATE INDEX IDX_68C1FB6D3E030ACD ON service_application (application_id)');
        $this->addSql('ALTER TABLE service_application ADD CONSTRAINT FK_68C1FB6DED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service_application ADD CONSTRAINT FK_68C1FB6D3E030ACD FOREIGN KEY (application_id) REFERENCES application (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE service_application');
    }
}
