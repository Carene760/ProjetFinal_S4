CREATE DATABASE ProjetFinal_S4 CHARACTER SET utf8mb4;
USE ProjetFinal_S4;

-- Table client
CREATE TABLE client (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150),
    telephone VARCHAR(20),
    date_naissance DATE NOT NULL,
    date_inscription DATE DEFAULT CURRENT_DATE
);

-- Table établissement financier
CREATE TABLE etablissement_financier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE
);

-- Table fond
CREATE TABLE fond (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etablissement_id INT NOT NULL,
    date_mouvement DATE DEFAULT CURRENT_DATE,
    montant DECIMAL(18,2) NOT NULL,
    type_mouvement TINYINT(1) NOT NULL, -- 0 = ENTREE, 1 = SORTIE
    CONSTRAINT fk_etablissement FOREIGN KEY (etablissement_id)
        REFERENCES etablissement_financier(id)
        ON DELETE CASCADE
);

-- données initiales établissement
INSERT INTO etablissement_financier (nom, email)
VALUES ('EF', 'ef@email.com');

-- Table type_pret
CREATE TABLE type_pret (
    id_type INT AUTO_INCREMENT PRIMARY KEY,
    nom_type VARCHAR(100) NOT NULL,
    taux_interet DECIMAL(5,2) NOT NULL, -- en %
    duree_max INT NOT NULL              -- en mois
);

-- Table prêt
CREATE TABLE pret (
    id_pret INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT UNSIGNED NOT NULL,
    id_type INT UNSIGNED NOT NULL,
    id_ef INT UNSIGNED NOT NULL,
    montant DECIMAL(15,2) NOT NULL,
    duree INT NOT NULL,
    date_debut DATE NOT NULL,
    frequence_remboursement ENUM('mensuel', 'trimestriel', 'annuel') NOT NULL,
    statut ENUM('en cours', 'remboursé', 'impayé') DEFAULT 'en cours',
    pourcentage_assurance DECIMAL(5,2) DEFAULT 0.00,
    est_valide BOOLEAN DEFAULT FALSE,
    delai_mois INT DEFAULT 0,
    CONSTRAINT fk_client FOREIGN KEY (id_client) REFERENCES client(id),
    CONSTRAINT fk_type FOREIGN KEY (id_type) REFERENCES type_pret(id),
    CONSTRAINT fk_ef FOREIGN KEY (id_ef) REFERENCES etablissement_financier(id)
);

CREATE TABLE echeance_remboursement (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pret INT NOT NULL,
    mois_annee DATE NOT NULL,
    montant_total DECIMAL(15,2),
    part_interet DECIMAL(15,2),
    part_capital DECIMAL(15,2),
    statut_paiement ENUM('non payé', 'payé', 'en retard') DEFAULT 'non payé',
    date_paiement_effectif DATE,
    FOREIGN KEY (id_pret) REFERENCES pret(id_pret)
);


-- données initiales type_pret
INSERT INTO type_pret (nom_type, taux_interet, duree_max) VALUES
('Prêt immobilier', 6.50, 240),     -- 20 ans
('Prêt automobile', 5.00, 60),      -- 5 ans
('Prêt personnel', 8.00, 36);       -- 3 ans

-- données initiales client
INSERT INTO client (nom, prenom, email, telephone, date_naissance) VALUES
('Rakoto', 'Jean', 'rakoto.jean@email.com', '0321234567', '1990-01-01'),
('Rasoanaivo', 'Miora', 'miora.rasoa@email.com', '0337654321', '1995-05-05');
