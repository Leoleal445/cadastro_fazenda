CREATE DATABASE IF NOT EXISTS fazenda_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE fazenda_db;

CREATE TABLE animais_teste (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  fazenda_id INT NOT NULL,
  brinco     VARCHAR(50) NOT NULL,
  sexo       ENUM('Macho', 'Fêmea') NOT NULL,
  raca       VARCHAR(100) NOT NULL,
  peso       DECIMAL(8,2) NOT NULL,
  situacao   TINYINT(1) NOT NULL DEFAULT 1,
  criado_em  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO animais_teste
  (fazenda_id, brinco, sexo, raca, peso)
VALUES
  (1, 'BR-001', 'Macho', 'Nelore', 450.50),
  (1, 'BR-002', 'Fêmea', 'Angus', 380.00),
  (2, 'BR-003', 'Macho', 'Girolando', 520.75);