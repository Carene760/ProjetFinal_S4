CREATE DATABASE ProjetFinal_S4 CHARACTER SET utf8mb4;
USE ProjetFinal_S4;

-- Table client
CREATE TABLE client (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150),
    mot_de_passe VARCHAR(255) NOT NULL, -- hash du mot de passe (ex : bcrypt)
    date_naissance DATE NOT NULL,
    date_inscription DATE DEFAULT CURRENT_DATE
);

-- Table type_pret
CREATE TABLE type_pret (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    taux_interet DECIMAL(5,2) NOT NULL, -- exemple : 12.5%
    duree_max INT NOT NULL,             -- en mois
    montant_max DECIMAL(18,2) NOT NULL,
    montant_min DECIMAL(18,2) NOT NULL 
);

-- Table etablissement_financier
CREATE TABLE etablissement_financier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL
);


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

-- Table des prêts accordés
CREATE TABLE pret (
    id_pret INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT REFERENCES client(id_client),
    id_type INT REFERENCES type_pret(id_type),
    id_ef INT REFERENCES etablissement_financier(id_ef),
    montant DECIMAL(15, 2) NOT NULL,
    duree INT NOT NULL,
    date_debut DATE NOT NULL,
    frequence_remboursement ENUM('mensuel', 'trimestriel', 'annuel'),
    statut ENUM('en cours', 'remboursé', 'impayé') DEFAULT 'en cours',
    est_valide BOOLEAN DEFAULT FALSE
);

INSERT INTO etablissement_financier (nom, email, mot_de_passe)
VALUES ('Banque MADA', 'contact@banquemada.mg', 'bcrypt_hash_mada123');

-- Entrée de fonds : +150 000 000 Ar
INSERT INTO fond (etablissement_id, montant, type_mouvement)
VALUES (1, 150000000.00, 0);

INSERT INTO type_pret (nom, taux_interet, duree_max, montant_max, montant_min)
VALUES
('Prêt Immobilier', 6.50, 240, 150000000.00, 5000000.00),
('Prêt Auto', 5.00, 60, 60000000.00, 3000000.00),
('Prêt Personnel', 8.00, 36, 30000000.00, 1000000.00);


INSERT INTO client (nom, email, mot_de_passe, date_naissance)
VALUES
('Rakoto Jean', 'jean.rakoto@email.com', 'bcrypt_hash_rakoto123', '1990-04-12'),
('Rasoa Miora', 'miora.rasoa@email.com', 'bcrypt_hash_miora456', '1995-09-30');


-- Jean demande un prêt immobilier de 80M Ar pour 180 mois (15 ans)
INSERT INTO pret (
    id_client, id_type, id_ef, montant, duree, date_debut, frequence_remboursement, statut, est_valide
)
VALUES (
    1, 1, 1, 80000000.00, 180, '2025-07-01', 'mensuel', 'en cours', TRUE
);

-- Miora demande un prêt personnel de 5M Ar pour 24 mois
INSERT INTO pret (
    id_client, id_type, id_ef, montant, duree, date_debut, frequence_remboursement, statut, est_valide
)
VALUES (
    2, 3, 1, 5000000.00, 24, '2025-06-15', 'trimestriel', 'en cours', TRUE
);


