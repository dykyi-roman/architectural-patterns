<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250527092301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create orders table for write model';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE orders (
            id UUID PRIMARY KEY,
            customer_id UUID NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT \'pending\',
            total_amount NUMERIC(10,2) NOT NULL,
            currency VARCHAR(3) NOT NULL DEFAULT \'USD\',
            created_at TIMESTAMP WITH TIME ZONE NOT NULL,
            updated_at TIMESTAMP WITH TIME ZONE NOT NULL,
            version INTEGER NOT NULL DEFAULT 1
        )');

        $this->addSql('CREATE INDEX idx_orders_customer_id ON orders (customer_id)');
        $this->addSql('CREATE INDEX idx_orders_status ON orders (status)');
        $this->addSql('CREATE INDEX idx_orders_created_at ON orders (created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE orders');
    }
}
