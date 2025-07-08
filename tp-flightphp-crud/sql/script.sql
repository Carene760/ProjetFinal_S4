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

INSERT INTO client (nom, email, mot_de_passe, date_naissance, date_inscription) VALUES
('Jean Dupont', 'jean.dupont@email.com', '$2y$10$Hx4NvY7bDZJ1kQwLp5WZ3u9XrS2vY8zA1B3C4D5E6F7G8H9I0J1K', '1985-03-15', '2023-01-10'),
('Marie Martin', 'marie.martin@email.com', '$2y$10$K1L2M3N4O5P6Q7R8S9T0U1V2W3X4Y5Z6A7B8C9D0E1F2G3H4I5J', '1990-07-22', '2023-02-05'),
('Pierre Durand', 'pierre.durand@email.com', '$2y$10$Q1W2E3R4T5Y6U7I8O9P0A1S2D3F4G5H6J7K8L9Z0X1C2V3B4N5M', '1978-11-30', '2023-03-12'),
('Sophie Lambert', 'sophie.lambert@email.com', '$2y$10$Z1X2C3V4B5N6M7L8K9J0H1G2F3D4S5A6Q7W8E9R0T1Y2U3I4O5P', '1995-05-18', '2023-04-20'),
('Thomas Leroy', 'thomas.leroy@email.com', '$2y$10$A1S2D3F4G5H6J7K8L9Z0X1C2V3B4N5M6Q7W8E9R0T1Y2U3I4O5P', '1982-09-25', '2023-05-15');

INSERT INTO type_pret (nom, taux_interet, duree_max, montant_max, montant_min) VALUES
('Prêt personnel', 7.50, 60, 50000.00, 1000.00),
('Prêt automobile', 5.25, 84, 100000.00, 5000.00),
('Prêt immobilier', 3.75, 300, 500000.00, 50000.00),
('Crédit renouvelable', 12.00, 36, 20000.00, 500.00),
('Prêt étudiant', 2.50, 120, 30000.00, 1000.00);

INSERT INTO etablissement_financier (nom, email, mot_de_passe) VALUES
('Banque Nationale', 'contact@banquenationale.com', '$2y$10$B1N2A3T4I5O6N7E8F9I0N1A2N3C4I5A6L7E8B9A0N1Q2U3E4S5T6'),
('Crédit Européen', 'info@crediteuropeen.fr', '$2y$10$C1R2E3D4I5T6E7U8R9O0P1E2N3F4I5C6I7A8L9B0A1N2Q3U4E5S6'),
('Finance Plus', 'contact@financeplus.com', '$2y$10$F1I2N3A4N5C6E7P8L9U0S1F2I3N4A5N6C7E8P9L0U1S2F3I4N5A6');

INSERT INTO fond (etablissement_id, date_mouvement, montant, type_mouvement) VALUES
(1, '2023-01-05', 1000000.00, 0),
(1, '2023-01-15', 250000.00, 1),
(2, '2023-01-10', 750000.00, 0),
(2, '2023-01-20', 150000.00, 1),
(3, '2023-01-15', 500000.00, 0),
(3, '2023-01-25', 100000.00, 1),
(1, '2023-02-01', 300000.00, 0),
(2, '2023-02-05', 200000.00, 0),
(3, '2023-02-10', 150000.00, 0);

INSERT INTO pret (id_client, id_type, id_ef, montant, duree, date_debut, frequence_remboursement, statut, est_valide) VALUES
(1, 1, 1, 15000.00, 48, '2023-02-01', 'mensuel', 'en cours', TRUE),
(2, 2, 2, 35000.00, 60, '2023-02-15', 'mensuel', 'en cours', TRUE),
(3, 3, 1, 250000.00, 240, '2023-03-01', 'mensuel', 'en cours', TRUE),
(4, 4, 3, 10000.00, 24, '2023-03-15', 'mensuel', 'en cours', TRUE),
(5, 5, 2, 20000.00, 36, '2023-04-01', 'mensuel', 'en cours', TRUE),
(1, 1, 3, 5000.00, 12, '2023-01-15', 'mensuel', 'remboursé', TRUE),
(3, 2, 2, 45000.00, 48, '2022-11-01', 'mensuel', 'impayé', TRUE);

-- Pour le prêt 1 (en cours)
INSERT INTO echeance_remboursement (id_pret, mois_annee, montant_total, part_interet, part_capital, statut_paiement, date_paiement_effectif) VALUES
(1, '2023-03-01', 362.50, 93.75, 268.75, 'payé', '2023-03-01'),
(1, '2023-04-01', 362.50, 91.41, 271.09, 'payé', '2023-04-01'),
(1, '2023-05-01', 362.50, 89.06, 273.44, 'payé', '2023-05-01'),
(1, '2023-06-01', 362.50, 86.72, 275.78, 'payé', '2023-06-01'),
(1, '2023-07-01', 362.50, 84.38, 278.12, 'non payé', NULL),

-- Pour le prêt 2 (en cours)
(2, '2023-03-15', 664.58, 153.13, 511.45, 'payé', '2023-03-15'),
(2, '2023-04-15', 664.58, 149.61, 514.97, 'payé', '2023-04-15'),
(2, '2023-05-15', 664.58, 146.09, 518.49, 'payé', '2023-05-15'),
(2, '2023-06-15', 664.58, 142.58, 522.00, 'non payé', NULL),

-- Pour le prêt 6 (remboursé)
(6, '2023-02-15', 433.33, 62.50, 370.83, 'payé', '2023-02-15'),
(6, '2023-03-15', 433.33, 58.33, 375.00, 'payé', '2023-03-15'),
(6, '2023-04-15', 433.33, 54.17, 379.16, 'payé', '2023-04-15'),
(6, '2023-05-15', 433.33, 50.00, 383.33, 'payé', '2023-05-15'),
(6, '2023-06-15', 433.33, 45.83, 387.50, 'payé', '2023-06-15'),
(6, '2023-07-15', 433.33, 41.67, 391.66, 'payé', '2023-07-15'),
(6, '2023-08-15', 433.33, 37.50, 395.83, 'payé', '2023-08-15'),
(6, '2023-09-15', 433.33, 33.33, 400.00, 'payé', '2023-09-15'),
(6, '2023-10-15', 433.33, 29.17, 404.16, 'payé', '2023-10-15'),
(6, '2023-11-15', 433.33, 25.00, 408.33, 'payé', '2023-11-15'),
(6, '2023-12-15', 433.33, 20.83, 412.50, 'payé', '2023-12-15'),
(6, '2024-01-15', 433.33, 16.67, 416.66, 'payé', '2024-01-15'),

-- Pour le prêt 7 (impayé)
(7, '2022-12-01', 1043.75, 196.88, 846.87, 'payé', '2022-12-01'),
(7, '2023-01-01', 1043.75, 191.41, 852.34, 'payé', '2023-01-01'),
(7, '2023-02-01', 1043.75, 185.94, 857.81, 'payé', '2023-02-01'),
(7, '2023-03-01', 1043.75, 180.47, 863.28, 'en retard', NULL),
(7, '2023-04-01', 1043.75, 175.00, 868.75, 'en retard', NULL),
(7, '2023-05-01', 1043.75, 169.53, 874.22, 'en retard', NULL),
(7, '2023-06-01', 1043.75, 164.06, 879.69, 'en retard', NULL);



DELIMITER //

CREATE PROCEDURE CalculInteretsMensuels(
    IN p_id_ef INT,
    IN p_debut DATE,
    IN p_fin DATE
)
BEGIN
    SELECT 
        mois,
        SUM(interets_recus) AS interets_recus,
        SUM(interets_courus) AS interets_courus,
        SUM(interets_recus + interets_courus) AS total_interets
    FROM (
        -- Intérêts reçus (payés)
        SELECT 
            DATE_FORMAT(er.date_paiement_effectif, '%Y-%m') AS mois,
            SUM(er.part_interet) AS interets_recus,
            0 AS interets_courus
        FROM echeance_remboursement er
        JOIN pret p ON er.id_pret = p.id_pret
        WHERE p.id_ef = p_id_ef
        AND er.statut_paiement = 'payé'
        AND er.date_paiement_effectif BETWEEN p_debut AND p_fin
        GROUP BY DATE_FORMAT(er.date_paiement_effectif, '%Y-%m')
        
        UNION ALL
        
        -- Intérêts courus (dus)
        SELECT 
            DATE_FORMAT(er.mois_annee, '%Y-%m') AS mois,
            0 AS interets_recus,
            SUM(er.part_interet) AS interets_courus
        FROM echeance_remboursement er
        JOIN pret p ON er.id_pret = p.id_pret
        WHERE p.id_ef = p_id_ef
        AND er.statut_paiement IN ('non payé', 'en retard')
        AND er.mois_annee BETWEEN p_debut AND p_fin
        GROUP BY DATE_FORMAT(er.mois_annee, '%Y-%m')
    ) AS combined_data
    GROUP BY mois
    ORDER BY mois;
END //

DELIMITER ;