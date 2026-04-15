-- MySQL Trigger for enforcing single current semester per program
-- Per spec Part 3 Table 4 and Loophole #1 resolution
-- This trigger ensures only ONE semester can have is_current=1 per program

DELIMITER //

-- Drop trigger if exists
DROP TRIGGER IF EXISTS enforce_single_current_semester //

-- Create BEFORE UPDATE trigger
CREATE TRIGGER enforce_single_current_semester 
BEFORE UPDATE ON semesters
FOR EACH ROW
BEGIN
    DECLARE current_count INT;
    
    -- Only perform check if is_current is being set to 1
    IF NEW.is_current = 1 THEN
        -- Count how many other semesters in same program have is_current=1
        SELECT COUNT(*) INTO current_count
        FROM semesters
        WHERE program_id = NEW.program_id 
        AND is_current = 1 
        AND id != NEW.id;
        
        -- If there's already another current semester, raise error
        IF current_count > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Cannot set multiple semesters as current for same program';
        END IF;
    END IF;
END //

DELIMITER ;
