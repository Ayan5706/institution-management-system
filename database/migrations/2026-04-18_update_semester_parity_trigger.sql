-- Update semester current trigger to allow multiple same-parity active semesters
DELIMITER //

DROP TRIGGER IF EXISTS enforce_single_current_semester //

CREATE TRIGGER enforce_single_current_semester
BEFORE UPDATE ON semesters
FOR EACH ROW
BEGIN
    DECLARE current_count INT;
    DECLARE new_parity INT;

    IF NEW.is_current = 1 THEN
        SET new_parity = MOD(NEW.semester_number, 2);

        SELECT COUNT(*) INTO current_count
        FROM semesters
        WHERE program_id = NEW.program_id
          AND is_current = 1
          AND id != NEW.id
          AND MOD(semester_number, 2) <> new_parity;

        IF current_count > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Cannot mix odd and even active semesters for same program';
        END IF;
    END IF;
END //

DELIMITER ;
