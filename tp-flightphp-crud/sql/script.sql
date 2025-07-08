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
-- cousin
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


<<<<<<< Updated upstream
=======
CREATE TABLE pret (
    id_pret INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT NOT NULL,
    id_type INT NOT NULL,
    id_ef INT NOT NULL,
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
ALTER TABLE echeance_remboursement
ADD COLUMN rassurance DECIMAL(15,2) DEFAULT 0.00;
>>>>>>> Stashed changes



-- données initiales type_pret
INSERT INTO type_pret (nom_type, taux_interet, duree_max) VALUES
('Prêt immobilier', 6.50, 240),     -- 20 ans
('Prêt automobile', 5.00, 60),      -- 5 ans
('Prêt personnel', 8.00, 36);       -- 3 ans

-- données initiales client
INSERT INTO client (nom, prenom, email, telephone, date_naissance) VALUES
('Rakoto', 'Jean', 'rakoto.jean@email.com', '0321234567', '1990-01-01'),
('Rasoanaivo', 'Miora', 'miora.rasoa@email.com', '0337654321', '1995-05-05');


---------------------------------

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

------------------------------------------------------------
DELIMITER //

CREATE PROCEDURE GetFondsDisponibles(
    IN p_id_ef INT,
    IN p_debut_yyyy_mm VARCHAR(7),
    IN p_fin_yyyy_mm VARCHAR(7)
)
BEGIN
    -- 🔸 Déclaration des variables en premier
    DECLARE v_solde_cumulatif DECIMAL(15,2) DEFAULT 0;
    DECLARE v_mois_courant VARCHAR(7);
    DECLARE v_montant_initial DECIMAL(15,2);
    DECLARE v_prets_accordes DECIMAL(15,2);
    DECLARE v_remboursements DECIMAL(15,2);
    DECLARE v_fin_curseur BOOLEAN DEFAULT FALSE;
    DECLARE v_premier_mois BOOLEAN DEFAULT TRUE;

    DECLARE p_debut DATE;
    DECLARE p_fin DATE;

    -- 🔸 Déclaration du curseur et du handler
    DECLARE cur CURSOR FOR 
        SELECT mois FROM temp_fonds_mensuels ORDER BY mois;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_fin_curseur = TRUE;

    -- 🔸 Conversion des paramètres en dates complètes
    SET p_debut = STR_TO_DATE(CONCAT(p_debut_yyyy_mm, '-01'), '%Y-%m-%d');
    SET p_fin = LAST_DAY(STR_TO_DATE(CONCAT(p_fin_yyyy_mm, '-01'), '%Y-%m-%d'));

    -- 🔸 Création table temporaire
    DROP TEMPORARY TABLE IF EXISTS temp_fonds_mensuels;
    CREATE TEMPORARY TABLE temp_fonds_mensuels (
        mois VARCHAR(7),
        montant_initial DECIMAL(15,2),
        prets_accordes DECIMAL(15,2),
        remboursements DECIMAL(15,2),
        fonds_disponibles DECIMAL(15,2),
        PRIMARY KEY (mois)
    );

    -- 🔸 Remplissage des données mensuelles brutes
    INSERT INTO temp_fonds_mensuels (mois, montant_initial, prets_accordes, remboursements, fonds_disponibles)
    SELECT 
        mois,
        SUM(montant_initial),
        SUM(prets_accordes),
        SUM(remboursements),
        0
    FROM (
        -- Mouvements de fonds initiaux
        SELECT 
            DATE_FORMAT(f.date_mouvement, '%Y-%m') AS mois,
            SUM(CASE WHEN f.type_mouvement = 0 THEN f.montant ELSE 0 END) AS montant_initial,
            0 AS prets_accordes,
            0 AS remboursements
        FROM fond f
        WHERE f.etablissement_id = p_id_ef
          AND f.date_mouvement BETWEEN p_debut AND p_fin
        GROUP BY mois

        UNION ALL

        -- Prêts accordés
        SELECT 
            DATE_FORMAT(p.date_debut, '%Y-%m') AS mois,
            0 AS montant_initial,
            SUM(p.montant) AS prets_accordes,
            0 AS remboursements
        FROM pret p
        WHERE p.id_ef = p_id_ef
          AND p.date_debut BETWEEN p_debut AND p_fin
        GROUP BY mois

        UNION ALL

        -- Remboursements reçus
        SELECT 
            DATE_FORMAT(er.date_paiement_effectif, '%Y-%m') AS mois,
            0 AS montant_initial,
            0 AS prets_accordes,
            SUM(er.part_capital + er.part_interet) AS remboursements
        FROM echeance_remboursement er
        JOIN pret p ON er.id_pret = p.id_pret
        WHERE p.id_ef = p_id_ef
          AND er.statut_paiement = 'payé'
          AND er.date_paiement_effectif BETWEEN p_debut AND p_fin
        GROUP BY mois
    ) AS data
    GROUP BY mois
    ORDER BY mois;

    -- 🔸 Calcul des fonds disponibles mensuels
    OPEN cur;

    boucle_mois: LOOP
        FETCH cur INTO v_mois_courant;
        IF v_fin_curseur THEN
            LEAVE boucle_mois;
        END IF;

        IF v_premier_mois THEN
            SELECT montant_initial, prets_accordes, remboursements 
            INTO v_montant_initial, v_prets_accordes, v_remboursements
            FROM temp_fonds_mensuels 
            WHERE mois = v_mois_courant;

            SET v_solde_cumulatif = v_montant_initial - v_prets_accordes + v_remboursements;
            SET v_premier_mois = FALSE;
        ELSE
            SELECT prets_accordes, remboursements 
            INTO v_prets_accordes, v_remboursements
            FROM temp_fonds_mensuels 
            WHERE mois = v_mois_courant;

            SET v_montant_initial = v_solde_cumulatif;
            SET v_solde_cumulatif = v_solde_cumulatif - v_prets_accordes + v_remboursements;

            UPDATE temp_fonds_mensuels 
            SET montant_initial = v_montant_initial
            WHERE mois = v_mois_courant;
        END IF;

        UPDATE temp_fonds_mensuels 
        SET fonds_disponibles = v_solde_cumulatif
        WHERE mois = v_mois_courant;
    END LOOP boucle_mois;

    CLOSE cur;

    -- 🔸 Résultat final
    SELECT 
        mois,
        montant_initial AS "fondDebut",
        prets_accordes AS "prets",
        remboursements AS "Remboursements",
        fonds_disponibles AS "fondFin"
    FROM temp_fonds_mensuels
    ORDER BY mois;

    -- 🔸 Nettoyage
    DROP TEMPORARY TABLE IF EXISTS temp_fonds_mensuels;
END //

DELIMITER ;
