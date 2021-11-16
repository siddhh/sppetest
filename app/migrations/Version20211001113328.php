<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211001113328 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les champs d\'horodatage sur l\'entité User, ainsi que des indexes pour optimiser les recherches lors de l\'authentification ou des recherches.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_8d93d649aa08cb10');
        $this->addSql('ALTER TABLE "user" ADD supprime_le TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD ajoute_le TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD maj_le TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('CREATE INDEX user_login_idx ON "user" (login)');
        $this->addSql('CREATE INDEX user_supprime_le_idx ON "user" (supprime_le)');
        $this->addSql('CREATE INDEX user_motdepasse_idx ON "user" (motdepasse)');
        $this->addSql('CREATE INDEX user_nom_idx ON "user" (nom)');
        $this->addSql('CREATE INDEX user_prenom_idx ON "user" (prenom)');
        $this->addSql('CREATE INDEX user_balp_idx ON "user" (balp)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX user_login_idx');
        $this->addSql('DROP INDEX user_supprime_le_idx');
        $this->addSql('DROP INDEX user_motdepasse_idx');
        $this->addSql('DROP INDEX user_nom_idx');
        $this->addSql('DROP INDEX user_prenom_idx');
        $this->addSql('DROP INDEX user_balp_idx');
        $this->addSql('ALTER TABLE "user" DROP supprime_le');
        $this->addSql('ALTER TABLE "user" DROP ajoute_le');
        $this->addSql('ALTER TABLE "user" DROP maj_le');
        $this->addSql('CREATE UNIQUE INDEX uniq_8d93d649aa08cb10 ON "user" (login)');
    }
}
