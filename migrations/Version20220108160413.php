<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220108160413 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, todo_id INT NOT NULL, body VARCHAR(255) NOT NULL, completed TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_527EDB25EA1EBC33 (todo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE todo (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_todo (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, todo_id INT NOT NULL, is_owner TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_208FFA69A76ED395 (user_id), INDEX IDX_208FFA69EA1EBC33 (todo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25EA1EBC33 FOREIGN KEY (todo_id) REFERENCES todo (id)');
        $this->addSql('ALTER TABLE user_todo ADD CONSTRAINT FK_208FFA69A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_todo ADD CONSTRAINT FK_208FFA69EA1EBC33 FOREIGN KEY (todo_id) REFERENCES todo (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25EA1EBC33');
        $this->addSql('ALTER TABLE user_todo DROP FOREIGN KEY FK_208FFA69EA1EBC33');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE todo');
        $this->addSql('DROP TABLE user_todo');
    }
}
