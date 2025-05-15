<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250515080944 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE likes ADD comment_like_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE likes ALTER post_like_id DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE likes ADD CONSTRAINT FK_49CA4E7D76E89822 FOREIGN KEY (comment_like_id) REFERENCES comment (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_49CA4E7D76E89822 ON likes (comment_like_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE likes DROP CONSTRAINT FK_49CA4E7D76E89822
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_49CA4E7D76E89822
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE likes DROP comment_like_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE likes ALTER post_like_id SET NOT NULL
        SQL);
    }
}
