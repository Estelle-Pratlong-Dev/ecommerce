<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260613095851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_images (id INT AUTO_INCREMENT NOT NULL, path VARCHAR(255) NOT NULL, alt VARCHAR(255) DEFAULT NULL, position INT NOT NULL, main TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, product_id INT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, INDEX IDX_8263FFCE4584665A (product_id), INDEX IDX_8263FFCEB03A8386 (created_by_id), INDEX IDX_8263FFCE896DBBDE (updated_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE product_images ADD CONSTRAINT FK_8263FFCE4584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_images ADD CONSTRAINT FK_8263FFCEB03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE product_images ADD CONSTRAINT FK_8263FFCE896DBBDE FOREIGN KEY (updated_by_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE categories ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE categories ADD CONSTRAINT FK_3AF34668B03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE categories ADD CONSTRAINT FK_3AF34668896DBBDE FOREIGN KEY (updated_by_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_3AF34668B03A8386 ON categories (created_by_id)');
        $this->addSql('CREATE INDEX IDX_3AF34668896DBBDE ON categories (updated_by_id)');
        $this->addSql('ALTER TABLE order_items ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB0B03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB0896DBBDE FOREIGN KEY (updated_by_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_62809DB0B03A8386 ON order_items (created_by_id)');
        $this->addSql('CREATE INDEX IDX_62809DB0896DBBDE ON order_items (updated_by_id)');
        $this->addSql('ALTER TABLE orders ADD updated_at DATETIME NOT NULL, ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEEB03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE896DBBDE FOREIGN KEY (updated_by_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_E52FFDEEB03A8386 ON orders (created_by_id)');
        $this->addSql('CREATE INDEX IDX_E52FFDEE896DBBDE ON orders (updated_by_id)');
        $this->addSql('ALTER TABLE products ADD brand VARCHAR(120) DEFAULT NULL, ADD color VARCHAR(60) DEFAULT NULL, ADD type VARCHAR(120) DEFAULT NULL, ADD updated_at DATETIME NOT NULL, ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL, DROP image');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5AB03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A896DBBDE FOREIGN KEY (updated_by_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_B3BA5A5AB03A8386 ON products (created_by_id)');
        $this->addSql('CREATE INDEX IDX_B3BA5A5A896DBBDE ON products (updated_by_id)');
        $this->addSql('ALTER TABLE users ADD postal_code VARCHAR(20) DEFAULT NULL, ADD city VARCHAR(120) DEFAULT NULL, ADD updated_at DATETIME NOT NULL, ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL, CHANGE address address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9B03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9896DBBDE FOREIGN KEY (updated_by_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_1483A5E9B03A8386 ON users (created_by_id)');
        $this->addSql('CREATE INDEX IDX_1483A5E9896DBBDE ON users (updated_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_images DROP FOREIGN KEY FK_8263FFCE4584665A');
        $this->addSql('ALTER TABLE product_images DROP FOREIGN KEY FK_8263FFCEB03A8386');
        $this->addSql('ALTER TABLE product_images DROP FOREIGN KEY FK_8263FFCE896DBBDE');
        $this->addSql('DROP TABLE product_images');
        $this->addSql('ALTER TABLE categories DROP FOREIGN KEY FK_3AF34668B03A8386');
        $this->addSql('ALTER TABLE categories DROP FOREIGN KEY FK_3AF34668896DBBDE');
        $this->addSql('DROP INDEX IDX_3AF34668B03A8386 ON categories');
        $this->addSql('DROP INDEX IDX_3AF34668896DBBDE ON categories');
        $this->addSql('ALTER TABLE categories DROP created_at, DROP updated_at, DROP created_by_id, DROP updated_by_id');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB0B03A8386');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB0896DBBDE');
        $this->addSql('DROP INDEX IDX_62809DB0B03A8386 ON order_items');
        $this->addSql('DROP INDEX IDX_62809DB0896DBBDE ON order_items');
        $this->addSql('ALTER TABLE order_items DROP created_at, DROP updated_at, DROP created_by_id, DROP updated_by_id');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEEB03A8386');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEE896DBBDE');
        $this->addSql('DROP INDEX IDX_E52FFDEEB03A8386 ON orders');
        $this->addSql('DROP INDEX IDX_E52FFDEE896DBBDE ON orders');
        $this->addSql('ALTER TABLE orders DROP updated_at, DROP created_by_id, DROP updated_by_id');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5AB03A8386');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5A896DBBDE');
        $this->addSql('DROP INDEX IDX_B3BA5A5AB03A8386 ON products');
        $this->addSql('DROP INDEX IDX_B3BA5A5A896DBBDE ON products');
        $this->addSql('ALTER TABLE products ADD image VARCHAR(255) DEFAULT NULL, DROP brand, DROP color, DROP type, DROP updated_at, DROP created_by_id, DROP updated_by_id');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9B03A8386');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9896DBBDE');
        $this->addSql('DROP INDEX IDX_1483A5E9B03A8386 ON users');
        $this->addSql('DROP INDEX IDX_1483A5E9896DBBDE ON users');
        $this->addSql('ALTER TABLE users DROP postal_code, DROP city, DROP updated_at, DROP created_by_id, DROP updated_by_id, CHANGE address address LONGTEXT DEFAULT NULL');
    }
}
