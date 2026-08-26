<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260613161551 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE brands (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(120) NOT NULL, slug VARCHAR(140) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_7EA244345E237E06 (name), UNIQUE INDEX UNIQ_7EA24434989D9B62 (slug), INDEX IDX_7EA24434B03A8386 (created_by_id), INDEX IDX_7EA24434896DBBDE (updated_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE brands ADD CONSTRAINT FK_7EA24434B03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE brands ADD CONSTRAINT FK_7EA24434896DBBDE FOREIGN KEY (updated_by_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE products ADD brand_id INT DEFAULT NULL, DROP brand');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A44F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_B3BA5A5A44F5D008 ON products (brand_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE brands DROP FOREIGN KEY FK_7EA24434B03A8386');
        $this->addSql('ALTER TABLE brands DROP FOREIGN KEY FK_7EA24434896DBBDE');
        $this->addSql('DROP TABLE brands');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5A44F5D008');
        $this->addSql('DROP INDEX IDX_B3BA5A5A44F5D008 ON products');
        $this->addSql('ALTER TABLE products ADD brand VARCHAR(120) DEFAULT NULL, DROP brand_id');
    }
}
