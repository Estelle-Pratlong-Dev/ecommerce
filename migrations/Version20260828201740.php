<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260828201740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE promos (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(40) NOT NULL, percent INT DEFAULT NULL, amount_cents INT DEFAULT NULL, min_cents INT NOT NULL, active TINYINT NOT NULL, expires_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_31D1F70577153098 (code), INDEX IDX_31D1F705B03A8386 (created_by_id), INDEX IDX_31D1F705896DBBDE (updated_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE settings (id INT AUTO_INCREMENT NOT NULL, shipping_flat_cents INT NOT NULL, shipping_free_from_cents INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE promos ADD CONSTRAINT FK_31D1F705B03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE promos ADD CONSTRAINT FK_31D1F705896DBBDE FOREIGN KEY (updated_by_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE orders ADD shipping_cents INT NOT NULL, ADD discount_cents INT NOT NULL, ADD promo_code VARCHAR(40) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE promos DROP FOREIGN KEY FK_31D1F705B03A8386');
        $this->addSql('ALTER TABLE promos DROP FOREIGN KEY FK_31D1F705896DBBDE');
        $this->addSql('DROP TABLE promos');
        $this->addSql('DROP TABLE settings');
        $this->addSql('ALTER TABLE orders DROP shipping_cents, DROP discount_cents, DROP promo_code');
    }
}
