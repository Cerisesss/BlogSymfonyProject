<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250515075145 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE report DROP CONSTRAINT fk_c42f778494bdeeb6
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_c42f778494bdeeb6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report RENAME COLUMN reported_id TO post_reported_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report ADD CONSTRAINT FK_C42F778478FD6499 FOREIGN KEY (post_reported_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C42F778478FD6499 ON report (post_reported_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report DROP CONSTRAINT FK_C42F778478FD6499
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_C42F778478FD6499
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report RENAME COLUMN post_reported_id TO reported_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report ADD CONSTRAINT fk_c42f778494bdeeb6 FOREIGN KEY (reported_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_c42f778494bdeeb6 ON report (reported_id)
        SQL);
    }
}
