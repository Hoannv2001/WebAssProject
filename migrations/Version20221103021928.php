<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221103021928 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE author (id INT AUTO_INCREMENT NOT NULL, author_name VARCHAR(255) NOT NULL, birthday DATE NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE book (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, author_id INT DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, book_name VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, publisher VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, INDEX IDX_CBE5A33112469DE2 (category_id), INDEX IDX_CBE5A331F675F31B (author_id), INDEX IDX_CBE5A331E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, date_order DATE NOT NULL, total_paymet DOUBLE PRECISION NOT NULL, customerEmail VARCHAR(180) DEFAULT NULL, INDEX IDX_F52993982FE7871C (customerEmail), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_items (id INT AUTO_INCREMENT NOT NULL, book_id INT DEFAULT NULL, order_b_id INT DEFAULT NULL, quantity INT NOT NULL, INDEX IDX_62809DB016A2B381 (book_id), INDEX IDX_62809DB0649AC6A1 (order_b_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, full_name VARCHAR(255) NOT NULL, phone_number VARCHAR(255) NOT NULL, PRIMARY KEY(email)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE book ADD CONSTRAINT FK_CBE5A33112469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE book ADD CONSTRAINT FK_CBE5A331F675F31B FOREIGN KEY (author_id) REFERENCES author (id)');
        $this->addSql('ALTER TABLE book ADD CONSTRAINT FK_CBE5A331E7927C74 FOREIGN KEY (email) REFERENCES user (email)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993982FE7871C FOREIGN KEY (customerEmail) REFERENCES user (email)');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB016A2B381 FOREIGN KEY (book_id) REFERENCES book (id)');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB0649AC6A1 FOREIGN KEY (order_b_id) REFERENCES `order` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book DROP FOREIGN KEY FK_CBE5A33112469DE2');
        $this->addSql('ALTER TABLE book DROP FOREIGN KEY FK_CBE5A331F675F31B');
        $this->addSql('ALTER TABLE book DROP FOREIGN KEY FK_CBE5A331E7927C74');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993982FE7871C');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB016A2B381');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB0649AC6A1');
        $this->addSql('DROP TABLE author');
        $this->addSql('DROP TABLE book');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE order_items');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
