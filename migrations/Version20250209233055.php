<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250209233055 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE plat DROP FOREIGN KEY FK_2038A207944722F2');
        $this->addSql('DROP INDEX IDX_2038A207944722F2 ON plat');
        $this->addSql('ALTER TABLE plat DROP prix_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE plat ADD prix_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE plat ADD CONSTRAINT FK_2038A207944722F2 FOREIGN KEY (prix_id) REFERENCES prix (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_2038A207944722F2 ON plat (prix_id)');
    }
}
