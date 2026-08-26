<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260613160737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE colors (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(60) NOT NULL, slug VARCHAR(70) NOT NULL, hex VARCHAR(7) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_C2BEC39F5E237E06 (name), UNIQUE INDEX UNIQ_C2BEC39F989D9B62 (slug), INDEX IDX_C2BEC39FB03A8386 (created_by_id), INDEX IDX_C2BEC39F896DBBDE (updated_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE colors ADD CONSTRAINT FK_C2BEC39FB03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE colors ADD CONSTRAINT FK_C2BEC39F896DBBDE FOREIGN KEY (updated_by_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE products ADD color_id INT DEFAULT NULL, DROP color');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A7ADA1FB5 FOREIGN KEY (color_id) REFERENCES colors (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_B3BA5A5A7ADA1FB5 ON products (color_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE colors DROP FOREIGN KEY FK_C2BEC39FB03A8386');
        $this->addSql('ALTER TABLE colors DROP FOREIGN KEY FK_C2BEC39F896DBBDE');
        $this->addSql('DROP TABLE colors');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5A7ADA1FB5');
        $this->addSql('DROP INDEX IDX_B3BA5A5A7ADA1FB5 ON products');
        $this->addSql('ALTER TABLE products ADD color VARCHAR(60) DEFAULT NULL, DROP color_id');
    }
}
