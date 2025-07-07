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
    montant_max DECIMAL(18,2) NOT NULL
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


INSERT INTO etablissement_financier (nom)
VALUES ('EF');


CREATE TABLE type_pret (
    id_type INT AUTO_INCREMENT PRIMARY KEY,
    nom_type VARCHAR(100) NOT NULL,
    taux_interet DECIMAL(5, 2) NOT NULL, -- ex: 5.5%
    duree_mois INT NOT NULL -- durée standard du prêt
);
-- Table des prêts accordés
CREATE TABLE pret (
    id_pret INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT REFERENCES client(id_client),
    id_type INT REFERENCES type_pret(id_type),
    id_ef INT REFERENCES etablissement_financier(id_ef),
    montant DECIMAL(15, 2) NOT NULL,
    date_debut DATE NOT NULL,
    frequence_remboursement ENUM('mensuel', 'trimestriel', 'annuel'),
    montant_remboursement DECIMAL(15,2),
    est_rembourse BOOLEAN DEFAULT FALSE
);


INSERT INTO type_pret (nom_type, taux_interet, duree_mois)
VALUES 
('Prêt immobilier', 6.50, 240),     -- 20 ans
('Prêt automobile', 5.00, 60),      -- 5 ans
('Prêt personnel', 8.00, 36);       -- 3 ans

INSERT INTO client (nom, prenom, email, telephone)
VALUES 
('Rakoto', 'Jean', 'rakoto.jean@email.com', '0321234567'),
('Rasoanaivo', 'Miora', 'miora.rasoa@email.com', '0337654321');
