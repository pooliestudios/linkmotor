<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141120200624 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('CREATE TABLE ext_log_entries (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(8) NOT NULL, logged_at DATETIME NOT NULL, object_id VARCHAR(64) DEFAULT NULL, object_class VARCHAR(255) NOT NULL, version INT NOT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', username VARCHAR(255) DEFAULT NULL, INDEX log_class_lookup_idx (object_class), INDEX log_date_lookup_idx (logged_at), INDEX log_user_lookup_idx (username), INDEX log_version_lookup_idx (object_id, object_class, version), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE action_stats (id INT AUTO_INCREMENT NOT NULL, project_id INT DEFAULT NULL, user_id INT DEFAULT NULL, date DATE NOT NULL, num_backlinks_created INT NOT NULL, num_checked_pages INT NOT NULL, num_contacts_made INT NOT NULL, INDEX IDX_B089AB16166D1F9C (project_id), INDEX IDX_B089AB16A76ED395 (user_id), INDEX date_id (date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE alerts (id INT AUTO_INCREMENT NOT NULL, backlink_id INT DEFAULT NULL, user_id INT DEFAULT NULL, project_id INT DEFAULT NULL, type VARCHAR(1) NOT NULL, hide_until DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_F77AC06B85BD2DD8 (backlink_id), INDEX IDX_F77AC06BA76ED395 (user_id), INDEX IDX_F77AC06B166D1F9C (project_id), INDEX created_at_idx (created_at), INDEX hide_until_idx (hide_until), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE backlinks (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, page_id INT DEFAULT NULL, project_id INT DEFAULT NULL, url VARCHAR(255) NOT NULL, url_ok SMALLINT DEFAULT NULL, xpath LONGTEXT DEFAULT NULL, xpath_last_crawl LONGTEXT DEFAULT NULL, xpath_ok SMALLINT DEFAULT NULL, type VARCHAR(1) NOT NULL, crawl_type VARCHAR(4) NOT NULL, type_last_crawl VARCHAR(1) DEFAULT NULL, type_ok SMALLINT NOT NULL, anchor VARCHAR(255) DEFAULT NULL, anchor_last_crawl VARCHAR(255) DEFAULT NULL, anchor_ok SMALLINT NOT NULL, follow SMALLINT NOT NULL, follow_last_crawl SMALLINT DEFAULT NULL, follow_ok SMALLINT NOT NULL, status_code VARCHAR(3) NOT NULL, status_code_last_crawl VARCHAR(3) DEFAULT NULL, status_code_ok SMALLINT DEFAULT NULL, meta_index SMALLINT NOT NULL, meta_index_last_crawl SMALLINT DEFAULT NULL, meta_index_ok SMALLINT DEFAULT NULL, meta_follow SMALLINT NOT NULL, meta_follow_last_crawl SMALLINT DEFAULT NULL, meta_follow_ok SMALLINT DEFAULT NULL, xrobots_follow SMALLINT NOT NULL, xrobots_follow_last_crawl SMALLINT DEFAULT NULL, xrobots_follow_ok SMALLINT DEFAULT NULL, xrobots_index SMALLINT NOT NULL, xrobots_index_last_crawl SMALLINT DEFAULT NULL, xrobots_index_ok SMALLINT DEFAULT NULL, robots_google SMALLINT NOT NULL, robots_google_last_crawl SMALLINT DEFAULT NULL, robots_google_ok SMALLINT DEFAULT NULL, is_offline SMALLINT NOT NULL, ignore_position SMALLINT NOT NULL, cost_type SMALLINT NOT NULL, price DOUBLE PRECISION NOT NULL, cost_note LONGTEXT DEFAULT NULL, last_crawled_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_4710E24DA76ED395 (user_id), INDEX IDX_4710E24DC4663E4 (page_id), INDEX IDX_4710E24D166D1F9C (project_id), INDEX created_at_idx (created_at), INDEX url_ok_idx (url_ok), INDEX type_ok_idx (type_ok), INDEX anchor_ok_idx (anchor_ok), INDEX follow_ok_idx (follow_ok), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE blacklist (id INT AUTO_INCREMENT NOT NULL, domain_id INT DEFAULT NULL, project_id INT DEFAULT NULL, note LONGTEXT DEFAULT NULL, INDEX IDX_3B175385115F0EE5 (domain_id), INDEX IDX_3B175385166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE competitors (id INT AUTO_INCREMENT NOT NULL, domain_id INT DEFAULT NULL, assigned_to_user_id INT DEFAULT NULL, project_id INT DEFAULT NULL, import_limit INT NOT NULL, import_interval INT NOT NULL, last_import_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_2DED50C6115F0EE5 (domain_id), INDEX IDX_2DED50C611578D11 (assigned_to_user_id), INDEX IDX_2DED50C6166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crawl_log (id INT AUTO_INCREMENT NOT NULL, backlink_id INT DEFAULT NULL, project_id INT DEFAULT NULL, url_ok TINYINT(1) DEFAULT NULL, xpath LONGTEXT DEFAULT NULL, xpath_ok TINYINT(1) DEFAULT NULL, type VARCHAR(1) DEFAULT NULL, type_ok TINYINT(1) NOT NULL, anchor VARCHAR(255) DEFAULT NULL, anchor_ok TINYINT(1) NOT NULL, follow TINYINT(1) DEFAULT NULL, follow_ok TINYINT(1) NOT NULL, status_code INT DEFAULT NULL, status_code_ok TINYINT(1) NOT NULL, meta_index TINYINT(1) DEFAULT NULL, meta_index_ok TINYINT(1) NOT NULL, meta_follow TINYINT(1) DEFAULT NULL, meta_follow_ok TINYINT(1) NOT NULL, x_robots_index TINYINT(1) DEFAULT NULL, x_robots_index_ok TINYINT(1) NOT NULL, x_robots_follow TINYINT(1) DEFAULT NULL, x_robots_follow_ok TINYINT(1) NOT NULL, robots_google TINYINT(1) DEFAULT NULL, robots_google_ok TINYINT(1) NOT NULL, crawl_type VARCHAR(4) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_E9A6716585BD2DD8 (backlink_id), INDEX IDX_E9A67165166D1F9C (project_id), INDEX created_at_idx (created_at), INDEX url_ok_idx (url_ok), INDEX type_ok_idx (type_ok), INDEX anchor_ok_idx (anchor_ok), INDEX follow_ok_idx (follow_ok), INDEX xpath_ok_idx (xpath_ok), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE domains (id INT AUTO_INCREMENT NOT NULL, vendor_id INT DEFAULT NULL, name VARCHAR(128) NOT NULL, authority INT NOT NULL, link_pop INT DEFAULT NULL, domain_pop INT DEFAULT NULL, net_pop INT DEFAULT NULL, first_day DATE DEFAULT NULL, last_crawled_at DATETIME DEFAULT NULL, INDEX IDX_8C7BBF9DF603EE73 (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE forgot_password_tokens (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, hash VARCHAR(32) NOT NULL, valid_until DATETIME NOT NULL, INDEX IDX_2350282BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE imports (id INT AUTO_INCREMENT NOT NULL, project_id INT DEFAULT NULL, user_id INT DEFAULT NULL, type INT NOT NULL, filename VARCHAR(128) NOT NULL, hash VARCHAR(32) NOT NULL, step INT NOT NULL, data LONGTEXT NOT NULL, progress INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_7895ED1C166D1F9C (project_id), INDEX IDX_7895ED1CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE keywords (id INT AUTO_INCREMENT NOT NULL, assigned_to_user_id INT DEFAULT NULL, project_id INT DEFAULT NULL, market_id INT DEFAULT NULL, keyword VARCHAR(255) NOT NULL, import_limit INT NOT NULL, import_interval INT NOT NULL, last_import_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_AA5FB55E11578D11 (assigned_to_user_id), INDEX IDX_AA5FB55E166D1F9C (project_id), INDEX IDX_AA5FB55E622F3F37 (market_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE markets (id INT AUTO_INCREMENT NOT NULL, name_en VARCHAR(128) NOT NULL, name_de VARCHAR(128) NOT NULL, iso_code VARCHAR(2) NOT NULL, INDEX name_en_idx (name_en), INDEX name_de_idx (name_de), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notes (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, page_id INT DEFAULT NULL, vendor_id INT DEFAULT NULL, content LONGTEXT NOT NULL, created DATETIME NOT NULL, entity INT NOT NULL, INDEX IDX_11BA68CA76ED395 (user_id), INDEX IDX_11BA68CC4663E4 (page_id), INDEX IDX_11BA68CF603EE73 (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification_settings (id INT AUTO_INCREMENT NOT NULL, project_id INT DEFAULT NULL, user_id INT DEFAULT NULL, warnings TINYINT(1) NOT NULL, all_warnings TINYINT(1) NOT NULL, warnings_when INT NOT NULL, errors TINYINT(1) NOT NULL, all_errors TINYINT(1) NOT NULL, errors_when INT NOT NULL, INDEX IDX_B0559860166D1F9C (project_id), INDEX IDX_B0559860A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE options (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, INDEX name_idx (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pages (id INT AUTO_INCREMENT NOT NULL, subdomain_id INT DEFAULT NULL, user_id INT DEFAULT NULL, status_id INT DEFAULT NULL, project_id INT DEFAULT NULL, source_competitor_id INT DEFAULT NULL, source_keyword_id INT DEFAULT NULL, url VARCHAR(255) NOT NULL, authority INT NOT NULL, link_pop INT DEFAULT NULL, domain_pop INT DEFAULT NULL, twitter_count INT DEFAULT NULL, facebook_count INT DEFAULT NULL, gplus_count INT DEFAULT NULL, scheme VARCHAR(5) NOT NULL, created_at DATETIME NOT NULL, last_modified_at DATETIME NOT NULL, last_crawled_at DATETIME DEFAULT NULL, INDEX IDX_2074E5758530A5DC (subdomain_id), INDEX IDX_2074E575A76ED395 (user_id), INDEX IDX_2074E5756BF700BD (status_id), INDEX IDX_2074E575166D1F9C (project_id), INDEX IDX_2074E575BDC90567 (source_competitor_id), INDEX IDX_2074E5756DE93476 (source_keyword_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE projects (id INT AUTO_INCREMENT NOT NULL, domain_id INT DEFAULT NULL, subdomain_id INT DEFAULT NULL, settings LONGTEXT NOT NULL, INDEX IDX_5C93B3A4115F0EE5 (domain_id), INDEX IDX_5C93B3A48530A5DC (subdomain_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE status (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(128) NOT NULL, sort_order INT NOT NULL, INDEX sort_order_idx (sort_order), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subdomains (id INT AUTO_INCREMENT NOT NULL, domain_id INT DEFAULT NULL, vendor_id INT DEFAULT NULL, name VARCHAR(128) NOT NULL, sichtbarkeitsindex DOUBLE PRECISION DEFAULT NULL, ovi DOUBLE PRECISION DEFAULT NULL, robots_txt LONGTEXT DEFAULT NULL, robots_txt_last_fetched DATETIME DEFAULT NULL, last_crawled_at DATETIME DEFAULT NULL, INDEX IDX_C7191752115F0EE5 (domain_id), INDEX IDX_C7191752F603EE73 (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, last_used_project_id INT DEFAULT NULL, name VARCHAR(128) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(88) NOT NULL, salt VARCHAR(40) NOT NULL, is_admin SMALLINT NOT NULL, items_per_page INT NOT NULL, options LONGTEXT NOT NULL, inactive TINYINT(1) NOT NULL, INDEX IDX_1483A5E9B894D15C (last_used_project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vendors (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(128) DEFAULT NULL, email VARCHAR(128) NOT NULL, phone VARCHAR(20) DEFAULT NULL, title SMALLINT DEFAULT NULL, company VARCHAR(64) DEFAULT NULL, street VARCHAR(128) DEFAULT NULL, zipcode VARCHAR(10) DEFAULT NULL, city VARCHAR(64) DEFAULT NULL, country VARCHAR(2) DEFAULT NULL, comment LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE action_stats ADD CONSTRAINT FK_B089AB16166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE action_stats ADD CONSTRAINT FK_B089AB16A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE alerts ADD CONSTRAINT FK_F77AC06B85BD2DD8 FOREIGN KEY (backlink_id) REFERENCES backlinks (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE alerts ADD CONSTRAINT FK_F77AC06BA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE alerts ADD CONSTRAINT FK_F77AC06B166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE backlinks ADD CONSTRAINT FK_4710E24DA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE backlinks ADD CONSTRAINT FK_4710E24DC4663E4 FOREIGN KEY (page_id) REFERENCES pages (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE backlinks ADD CONSTRAINT FK_4710E24D166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blacklist ADD CONSTRAINT FK_3B175385115F0EE5 FOREIGN KEY (domain_id) REFERENCES domains (id)');
        $this->addSql('ALTER TABLE blacklist ADD CONSTRAINT FK_3B175385166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE competitors ADD CONSTRAINT FK_2DED50C6115F0EE5 FOREIGN KEY (domain_id) REFERENCES domains (id)');
        $this->addSql('ALTER TABLE competitors ADD CONSTRAINT FK_2DED50C611578D11 FOREIGN KEY (assigned_to_user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE competitors ADD CONSTRAINT FK_2DED50C6166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE crawl_log ADD CONSTRAINT FK_E9A6716585BD2DD8 FOREIGN KEY (backlink_id) REFERENCES backlinks (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE crawl_log ADD CONSTRAINT FK_E9A67165166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE domains ADD CONSTRAINT FK_8C7BBF9DF603EE73 FOREIGN KEY (vendor_id) REFERENCES vendors (id)');
        $this->addSql('ALTER TABLE forgot_password_tokens ADD CONSTRAINT FK_2350282BA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE imports ADD CONSTRAINT FK_7895ED1C166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE imports ADD CONSTRAINT FK_7895ED1CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE keywords ADD CONSTRAINT FK_AA5FB55E11578D11 FOREIGN KEY (assigned_to_user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE keywords ADD CONSTRAINT FK_AA5FB55E166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE keywords ADD CONSTRAINT FK_AA5FB55E622F3F37 FOREIGN KEY (market_id) REFERENCES markets (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68CC4663E4 FOREIGN KEY (page_id) REFERENCES pages (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68CF603EE73 FOREIGN KEY (vendor_id) REFERENCES vendors (id)');
        $this->addSql('ALTER TABLE notification_settings ADD CONSTRAINT FK_B0559860166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification_settings ADD CONSTRAINT FK_B0559860A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE pages ADD CONSTRAINT FK_2074E5758530A5DC FOREIGN KEY (subdomain_id) REFERENCES subdomains (id)');
        $this->addSql('ALTER TABLE pages ADD CONSTRAINT FK_2074E575A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE pages ADD CONSTRAINT FK_2074E5756BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE pages ADD CONSTRAINT FK_2074E575166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pages ADD CONSTRAINT FK_2074E575BDC90567 FOREIGN KEY (source_competitor_id) REFERENCES competitors (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE pages ADD CONSTRAINT FK_2074E5756DE93476 FOREIGN KEY (source_keyword_id) REFERENCES keywords (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE projects ADD CONSTRAINT FK_5C93B3A4115F0EE5 FOREIGN KEY (domain_id) REFERENCES domains (id)');
        $this->addSql('ALTER TABLE projects ADD CONSTRAINT FK_5C93B3A48530A5DC FOREIGN KEY (subdomain_id) REFERENCES subdomains (id)');
        $this->addSql('ALTER TABLE subdomains ADD CONSTRAINT FK_C7191752115F0EE5 FOREIGN KEY (domain_id) REFERENCES domains (id)');
        $this->addSql('ALTER TABLE subdomains ADD CONSTRAINT FK_C7191752F603EE73 FOREIGN KEY (vendor_id) REFERENCES vendors (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9B894D15C FOREIGN KEY (last_used_project_id) REFERENCES projects (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE alerts DROP FOREIGN KEY FK_F77AC06B85BD2DD8');
        $this->addSql('ALTER TABLE crawl_log DROP FOREIGN KEY FK_E9A6716585BD2DD8');
        $this->addSql('ALTER TABLE pages DROP FOREIGN KEY FK_2074E575BDC90567');
        $this->addSql('ALTER TABLE blacklist DROP FOREIGN KEY FK_3B175385115F0EE5');
        $this->addSql('ALTER TABLE competitors DROP FOREIGN KEY FK_2DED50C6115F0EE5');
        $this->addSql('ALTER TABLE projects DROP FOREIGN KEY FK_5C93B3A4115F0EE5');
        $this->addSql('ALTER TABLE subdomains DROP FOREIGN KEY FK_C7191752115F0EE5');
        $this->addSql('ALTER TABLE pages DROP FOREIGN KEY FK_2074E5756DE93476');
        $this->addSql('ALTER TABLE keywords DROP FOREIGN KEY FK_AA5FB55E622F3F37');
        $this->addSql('ALTER TABLE backlinks DROP FOREIGN KEY FK_4710E24DC4663E4');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68CC4663E4');
        $this->addSql('ALTER TABLE action_stats DROP FOREIGN KEY FK_B089AB16166D1F9C');
        $this->addSql('ALTER TABLE alerts DROP FOREIGN KEY FK_F77AC06B166D1F9C');
        $this->addSql('ALTER TABLE backlinks DROP FOREIGN KEY FK_4710E24D166D1F9C');
        $this->addSql('ALTER TABLE blacklist DROP FOREIGN KEY FK_3B175385166D1F9C');
        $this->addSql('ALTER TABLE competitors DROP FOREIGN KEY FK_2DED50C6166D1F9C');
        $this->addSql('ALTER TABLE crawl_log DROP FOREIGN KEY FK_E9A67165166D1F9C');
        $this->addSql('ALTER TABLE imports DROP FOREIGN KEY FK_7895ED1C166D1F9C');
        $this->addSql('ALTER TABLE keywords DROP FOREIGN KEY FK_AA5FB55E166D1F9C');
        $this->addSql('ALTER TABLE notification_settings DROP FOREIGN KEY FK_B0559860166D1F9C');
        $this->addSql('ALTER TABLE pages DROP FOREIGN KEY FK_2074E575166D1F9C');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9B894D15C');
        $this->addSql('ALTER TABLE pages DROP FOREIGN KEY FK_2074E5756BF700BD');
        $this->addSql('ALTER TABLE pages DROP FOREIGN KEY FK_2074E5758530A5DC');
        $this->addSql('ALTER TABLE projects DROP FOREIGN KEY FK_5C93B3A48530A5DC');
        $this->addSql('ALTER TABLE action_stats DROP FOREIGN KEY FK_B089AB16A76ED395');
        $this->addSql('ALTER TABLE alerts DROP FOREIGN KEY FK_F77AC06BA76ED395');
        $this->addSql('ALTER TABLE backlinks DROP FOREIGN KEY FK_4710E24DA76ED395');
        $this->addSql('ALTER TABLE competitors DROP FOREIGN KEY FK_2DED50C611578D11');
        $this->addSql('ALTER TABLE forgot_password_tokens DROP FOREIGN KEY FK_2350282BA76ED395');
        $this->addSql('ALTER TABLE imports DROP FOREIGN KEY FK_7895ED1CA76ED395');
        $this->addSql('ALTER TABLE keywords DROP FOREIGN KEY FK_AA5FB55E11578D11');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68CA76ED395');
        $this->addSql('ALTER TABLE notification_settings DROP FOREIGN KEY FK_B0559860A76ED395');
        $this->addSql('ALTER TABLE pages DROP FOREIGN KEY FK_2074E575A76ED395');
        $this->addSql('ALTER TABLE domains DROP FOREIGN KEY FK_8C7BBF9DF603EE73');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68CF603EE73');
        $this->addSql('ALTER TABLE subdomains DROP FOREIGN KEY FK_C7191752F603EE73');
        $this->addSql('DROP TABLE ext_log_entries');
        $this->addSql('DROP TABLE action_stats');
        $this->addSql('DROP TABLE alerts');
        $this->addSql('DROP TABLE backlinks');
        $this->addSql('DROP TABLE blacklist');
        $this->addSql('DROP TABLE competitors');
        $this->addSql('DROP TABLE crawl_log');
        $this->addSql('DROP TABLE domains');
        $this->addSql('DROP TABLE forgot_password_tokens');
        $this->addSql('DROP TABLE imports');
        $this->addSql('DROP TABLE keywords');
        $this->addSql('DROP TABLE markets');
        $this->addSql('DROP TABLE notes');
        $this->addSql('DROP TABLE notification_settings');
        $this->addSql('DROP TABLE options');
        $this->addSql('DROP TABLE pages');
        $this->addSql('DROP TABLE projects');
        $this->addSql('DROP TABLE status');
        $this->addSql('DROP TABLE subdomains');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE vendors');
    }
}
