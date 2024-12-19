<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241218181610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE currency (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, symbol VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, symbol_native VARCHAR(255) NOT NULL, decimal_digits INTEGER NOT NULL, rounding INTEGER NOT NULL, code VARCHAR(255) NOT NULL, name_plural VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE historical_exchange_rate (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, base_currency_id INTEGER NOT NULL, currency_id INTEGER NOT NULL, date DATE NOT NULL, value DOUBLE PRECISION NOT NULL, CONSTRAINT FK_BA444BEF3101778E FOREIGN KEY (base_currency_id) REFERENCES currency (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BA444BEF38248176 FOREIGN KEY (currency_id) REFERENCES currency (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_BA444BEF3101778E ON historical_exchange_rate (base_currency_id)');
        $this->addSql('CREATE INDEX IDX_BA444BEF38248176 ON historical_exchange_rate (currency_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE currency');
        $this->addSql('DROP TABLE historical_exchange_rate');
    }
}
